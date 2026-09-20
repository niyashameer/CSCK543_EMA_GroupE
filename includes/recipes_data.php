<?php
/**
 * includes/recipes_data.php
 * -----------------------------------------------------------------
 * Data layer — runs the real PDO queries against recipes,
 * categories, ingredients, steps, ratings and allergens.
 *
 * Top-level pages (index.php, search.php, recipe.php) only call
 * the functions in this file — they never write SQL themselves.
 * -----------------------------------------------------------------
 */

/**
 * Returns a simplified alternative search term.
 *
 * This makes common plural searches more forgiving. For example,
 * searching for "mushrooms" will also search for "mushroom".
 *
 * This is intentionally lightweight rather than attempting full
 * linguistic stemming.
 */
function getSearchTerms(string $value): array
{
    $value = trim($value);

    if ($value === '') {
        return [];
    }

    $terms = [$value];

    // Add a simple singular alternative for words ending in "s".
    if (strlen($value) > 3 && strtolower(substr($value, -1)) === 's') {
        $singular = substr($value, 0, -1);

        if ($singular !== '') {
            $terms[] = $singular;
        }
    }

    return array_values(array_unique($terms));
}

/**
 * Returns recipes matching the given filters, sorted as requested.
 *
 * Each user's overall rating is derived from their three required
 * component scores:
 *
 * (taste + ease of preparation + presentation) / 3
 *
 * The recipe's overall rating is the average of these derived user
 * ratings.
 *
 * $filters keys (all optional):
 *   'search'            string   — matched against title and description
 *   'ingredient'        string   — matched against ingredient names used in the recipe
 *   'categories'        string[] — category_name values; a recipe must have
 *                                   ALL of the selected categories
 *   'difficulty'        string   — 'Easy' | 'Medium' | 'Hard'
 *   'max_time'          int      — prep_time_minutes + cook_time_minutes <= max_time
 *   'min_rating'        float    — derived average overall rating >= this value
 *   'exclude_allergens' int[]    — allergen_id values; recipes containing an
 *                                   ingredient with ANY of these allergens are excluded
 *   'sort'              string   — 'title_asc' | 'time_asc' | 'rating_desc' |
 *                                   'popularity' | 'difficulty_asc'
 */
function getRecipes(array $filters = []): array
{
    global $pdo;

    $search = trim($filters['search'] ?? '');
    $ingredient = trim($filters['ingredient'] ?? '');

    $categories = array_values(
        array_unique(
            array_filter($filters['categories'] ?? [])
        )
    );

    $difficulty = $filters['difficulty'] ?? '';

    $maxTime =
        isset($filters['max_time'])
        && $filters['max_time'] !== ''
            ? (int) $filters['max_time']
            : null;

    $minRating =
        isset($filters['min_rating'])
        && $filters['min_rating'] !== ''
            ? (float) $filters['min_rating']
            : null;

    $excludeAllergens = array_filter(
        array_map(
            'intval',
            $filters['exclude_allergens'] ?? []
        )
    );

    $sort = $filters['sort'] ?? 'title_asc';

    $where = [];
    $params = [];

    /*
     * Search recipe titles and descriptions.
     *
     * getSearchTerms() also supplies a simple singular alternative
     * when appropriate, so "mushrooms" can match "mushroom".
     */
    if ($search !== '') {
        $searchTerms = getSearchTerms($search);
        $searchConditions = [];

        foreach ($searchTerms as $term) {
            $searchConditions[] =
                '(r.title LIKE ? OR r.description LIKE ?)';

            $params[] = "%{$term}%";
            $params[] = "%{$term}%";
        }

        $where[] =
            '(' . implode(' OR ', $searchConditions) . ')';
    }

    /*
     * Search ingredients separately from the general recipe search.
     */
    if ($ingredient !== '') {
        $ingredientTerms = getSearchTerms($ingredient);
        $ingredientConditions = [];

        foreach ($ingredientTerms as $term) {
            $ingredientConditions[] =
                'i.ingredient_name LIKE ?';

            $params[] = "%{$term}%";
        }

        $where[] = 'r.recipe_id IN (
            SELECT ri.recipe_id
            FROM recipe_ingredients ri
            JOIN ingredients i
                ON i.ingredient_id = ri.ingredient_id
            WHERE ' . implode(
                ' OR ',
                $ingredientConditions
            ) . '
        )';
    }

    if (
        $difficulty !== ''
        && in_array(
            $difficulty,
            ['Easy', 'Medium', 'Hard'],
            true
        )
    ) {
        $where[] = 'r.difficulty = ?';
        $params[] = $difficulty;
    }

    if ($maxTime !== null) {
        $where[] =
            '(r.prep_time_minutes + r.cook_time_minutes) <= ?';

        $params[] = $maxTime;
    }

    /*
     * A recipe must contain ALL selected categories.
     *
     * The subquery first finds category matches for each recipe.
     * HAVING then checks that the number of distinct matched
     * categories equals the number selected by the user.
     */
    if (!empty($categories)) {
        $placeholders = implode(
            ',',
            array_fill(
                0,
                count($categories),
                '?'
            )
        );

        $where[] = "r.recipe_id IN (
            SELECT rc.recipe_id
            FROM recipe_categories rc
            JOIN categories c
                ON c.category_id = rc.category_id
            WHERE c.category_name IN ({$placeholders})
            GROUP BY rc.recipe_id
            HAVING COUNT(DISTINCT c.category_name) = ?
        )";

        foreach ($categories as $categoryName) {
            $params[] = $categoryName;
        }

        $params[] = count($categories);
    }

    if (!empty($excludeAllergens)) {
        $placeholders = implode(
            ',',
            array_fill(
                0,
                count($excludeAllergens),
                '?'
            )
        );

        $where[] = "r.recipe_id NOT IN (
            SELECT ri.recipe_id
            FROM recipe_ingredients ri
            JOIN ingredient_allergens ia
                ON ia.ingredient_id = ri.ingredient_id
            WHERE ia.allergen_id IN ({$placeholders})
        )";

        foreach ($excludeAllergens as $allergenId) {
            $params[] = $allergenId;
        }
    }

    $whereSql = !empty($where)
        ? 'WHERE ' . implode(' AND ', $where)
        : '';

    /*
     * The overall rating is calculated rather than stored.
     *
     * Each user's overall score is the mean of their three required
     * component ratings. AVG() then calculates the mean of those
     * user scores for the recipe.
     *
     * Minimum rating therefore uses HAVING because avg_rating is an
     * aggregate value calculated after grouping.
     */
    $havingSql = '';

    if ($minRating !== null) {
        $havingSql = 'HAVING avg_rating >= ?';
        $params[] = $minRating;
    }

    switch ($sort) {
        case 'time_asc':
            $orderSql =
                'ORDER BY
                    (r.prep_time_minutes + r.cook_time_minutes) ASC';
            break;

        case 'rating_desc':
            $orderSql =
                'ORDER BY
                    avg_rating IS NULL,
                    avg_rating DESC';
            break;

        case 'popularity':
            $orderSql =
                'ORDER BY
                    ratings_count DESC,
                    avg_rating IS NULL,
                    avg_rating DESC';
            break;

        case 'difficulty_asc':
            $orderSql =
                "ORDER BY
                    FIELD(
                        r.difficulty,
                        'Easy',
                        'Medium',
                        'Hard'
                    )";
            break;

        case 'title_asc':
        default:
            $orderSql = 'ORDER BY r.title ASC';
            break;
    }

    $sql = "
        SELECT
            r.recipe_id,
            r.title,
            r.description,
            r.prep_time_minutes,
            r.cook_time_minutes,
            r.servings,
            r.difficulty,
            r.image_path,

            ROUND(
                AVG(
                    (
                        rt.taste_rating
                        + rt.difficulty_rating
                        + rt.presentation_rating
                    ) / 3.0
                ),
                1
            ) AS avg_rating,

            COUNT(
                DISTINCT rt.rating_id
            ) AS ratings_count

        FROM recipes r

        LEFT JOIN ratings rt
            ON rt.recipe_id = r.recipe_id

        {$whereSql}

        GROUP BY
            r.recipe_id,
            r.title,
            r.description,
            r.prep_time_minutes,
            r.cook_time_minutes,
            r.servings,
            r.difficulty,
            r.image_path

        {$havingSql}

        {$orderSql}
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $recipes = $stmt->fetchAll();

    /*
     * Attach categories to each recipe. These are used for the
     * category tags shown on recipe cards and detail pages.
     */
    foreach ($recipes as &$recipe) {
        $recipe['categories'] = getRecipeCategories(
            (int) $recipe['recipe_id']
        );

        $recipe['avg_rating'] =
            $recipe['avg_rating'] !== null
                ? (float) $recipe['avg_rating']
                : null;

        $recipe['ratings_count'] =
            (int) $recipe['ratings_count'];
    }

    unset($recipe);

    return $recipes;
}

/**
 * Returns one complete recipe for the recipe detail page.
 *
 * Each user's overall rating is derived from their three required
 * component scores:
 *
 * (taste + ease of preparation + presentation) / 3
 *
 * The recipe's overall rating is the average of these derived user
 * ratings. Separate averages are also calculated for taste, ease of
 * preparation and presentation so the recipe page can show a more
 * detailed rating breakdown.
 *
 * The database column difficulty_rating is presented to users as
 * "Ease of preparation":
 *
 * 1 = difficult
 * 5 = easy
 */
function getRecipeById(int $id): ?array
{
    global $pdo;

    $stmt = $pdo->prepare('
        SELECT
            r.recipe_id,
            r.title,
            r.description,
            r.prep_time_minutes,
            r.cook_time_minutes,
            r.servings,
            r.difficulty,
            r.image_path,
            r.source_url,

            ROUND(
                AVG(
                    (
                        rt.taste_rating
                        + rt.difficulty_rating
                        + rt.presentation_rating
                    ) / 3.0
                ),
                1
            ) AS avg_rating,

            ROUND(
                AVG(rt.taste_rating),
                1
            ) AS avg_taste_rating,

            ROUND(
                AVG(rt.difficulty_rating),
                1
            ) AS avg_difficulty_rating,

            ROUND(
                AVG(rt.presentation_rating),
                1
            ) AS avg_presentation_rating,

            COUNT(
                DISTINCT rt.rating_id
            ) AS ratings_count

        FROM recipes r

        LEFT JOIN ratings rt
            ON rt.recipe_id = r.recipe_id

        WHERE r.recipe_id = ?

        GROUP BY
            r.recipe_id,
            r.title,
            r.description,
            r.prep_time_minutes,
            r.cook_time_minutes,
            r.servings,
            r.difficulty,
            r.image_path,
            r.source_url
    ');

    $stmt->execute([$id]);
    $recipe = $stmt->fetch();

    if (!$recipe) {
        return null;
    }

    $recipe['avg_rating'] =
        $recipe['avg_rating'] !== null
            ? (float) $recipe['avg_rating']
            : null;

    $recipe['avg_taste_rating'] =
        $recipe['avg_taste_rating'] !== null
            ? (float) $recipe['avg_taste_rating']
            : null;

    $recipe['avg_difficulty_rating'] =
        $recipe['avg_difficulty_rating'] !== null
            ? (float) $recipe['avg_difficulty_rating']
            : null;

    $recipe['avg_presentation_rating'] =
        $recipe['avg_presentation_rating'] !== null
            ? (float) $recipe['avg_presentation_rating']
            : null;

    $recipe['ratings_count'] =
        (int) $recipe['ratings_count'];

    $recipe['categories'] =
        getRecipeCategories($id);

    $recipe['ingredients'] =
        getRecipeIngredients($id);

    $recipe['steps'] =
        getRecipeSteps($id);

    $recipe['reviews'] =
        getRecipeReviews($id);

    return $recipe;
}

/**
 * Returns the individual ratings/reviews submitted for a recipe.
 *
 * The individual overall score is derived from the three required
 * component scores rather than being stored separately.
 *
 * Each rating is joined to its user so the reviewer's first and last
 * name can be displayed. A written review is optional, but the three
 * component scores are always available.
 */
function getRecipeReviews(int $recipeId): array
{
    global $pdo;

    $stmt = $pdo->prepare('
        SELECT
            rt.rating_id,

            ROUND(
                (
                    rt.taste_rating
                    + rt.difficulty_rating
                    + rt.presentation_rating
                ) / 3.0,
                1
            ) AS overall_rating,

            rt.taste_rating,
            rt.difficulty_rating,
            rt.presentation_rating,
            rt.review,
            rt.created_at,

            CONCAT(
                u.first_name,
                " ",
                u.last_name
            ) AS user_name

        FROM ratings rt

        JOIN users u
            ON u.user_id = rt.user_id

        WHERE rt.recipe_id = ?

        ORDER BY
            rt.created_at DESC,
            rt.rating_id DESC
    ');

    $stmt->execute([$recipeId]);

    $reviews = $stmt->fetchAll();

    /*
     * Convert numeric values returned by PDO into appropriate PHP
     * numeric types before they are used by the presentation layer.
     */
    foreach ($reviews as &$review) {
        $review['overall_rating'] =
            (float) $review['overall_rating'];

        $review['taste_rating'] =
            (int) $review['taste_rating'];

        $review['difficulty_rating'] =
            (int) $review['difficulty_rating'];

        $review['presentation_rating'] =
            (int) $review['presentation_rating'];
    }

    unset($review);

    return $reviews;
}

/**
 * Returns the categories assigned to one recipe.
 */
function getRecipeCategories(int $recipeId): array
{
    global $pdo;

    $stmt = $pdo->prepare('
        SELECT
            c.category_id,
            c.category_name,
            c.category_type
        FROM recipe_categories rc
        JOIN categories c
            ON c.category_id = rc.category_id
        WHERE rc.recipe_id = ?
        ORDER BY
            c.category_type,
            c.category_name
    ');

    $stmt->execute([$recipeId]);

    return $stmt->fetchAll();
}

/**
 * Returns the ingredients used by one recipe in their defined
 * display order.
 */
function getRecipeIngredients(int $recipeId): array
{
    global $pdo;

    $stmt = $pdo->prepare('
        SELECT
            ri.section_name,
            ri.display_order,
            ri.quantity,
            ri.unit,
            ri.notes,
            i.ingredient_name
        FROM recipe_ingredients ri
        JOIN ingredients i
            ON i.ingredient_id = ri.ingredient_id
        WHERE ri.recipe_id = ?
        ORDER BY ri.display_order
    ');

    $stmt->execute([$recipeId]);

    return $stmt->fetchAll();
}

/**
 * Returns the ordered preparation/cooking steps for one recipe.
 */
function getRecipeSteps(int $recipeId): array
{
    global $pdo;

    $stmt = $pdo->prepare('
        SELECT
            step_number,
            instruction,
            duration_minutes
        FROM recipe_steps
        WHERE recipe_id = ?
        ORDER BY step_number
    ');

    $stmt->execute([$recipeId]);

    return $stmt->fetchAll();
}

/**
 * All category names in use, grouped by type. Used to build the
 * search filter checkboxes.
 */
function getAllCategoriesGrouped(): array
{
    global $pdo;

    $stmt = $pdo->query('
        SELECT
            category_name,
            category_type
        FROM categories
        ORDER BY
            category_type,
            category_name
    ');

    $grouped = [];

    foreach ($stmt->fetchAll() as $row) {
        $grouped[$row['category_type']][] =
            $row['category_name'];
    }

    ksort($grouped);

    return $grouped;
}

/**
 * All allergens in the system. Used to build the "exclude recipes
 * containing" checkboxes on the search page.
 */
function getAllAllergens(): array
{
    global $pdo;

    $stmt = $pdo->query('
        SELECT
            allergen_id,
            allergen_name
        FROM allergens
        ORDER BY allergen_name
    ');

    return $stmt->fetchAll();
}

/**
 * Returns the allergen IDs a logged-in user has saved on their
 * profile. These are used to default the search allergen exclusions
 * on the user's first visit to the search page.
 */
function getUserAllergenIds(int $userId): array
{
    global $pdo;

    $stmt = $pdo->prepare('
        SELECT allergen_id
        FROM user_allergens
        WHERE user_id = ?
    ');

    $stmt->execute([$userId]);

    return array_map(
        'intval',
        array_column(
            $stmt->fetchAll(),
            'allergen_id'
        )
    );
}