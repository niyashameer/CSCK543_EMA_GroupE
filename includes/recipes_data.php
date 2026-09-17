<?php
/**
 * includes/recipes_data.php
 * -----------------------------------------------------------------
 * Data layer — runs the real PDO queries against recipes,
 * categories, ingredients, steps, ratings and allergens.
 *
 * Top-level pages (index.php, recipe.php) only ever call
 * getRecipes($filters), getRecipeById($id), getAllCategoriesGrouped(),
 * getAllAllergens() and getUserAllergenIds($userId) — they never
 * write SQL themselves.
 * -----------------------------------------------------------------
 */

/**
 * Returns recipes matching the given filters, sorted as requested.
 *
 * $filters keys (all optional):
 *   'search'            string   — matched against title and description
 *   'ingredient'        string   — matched against ingredient names used in the recipe
 *   'categories'        string[] — category_name values; a recipe matches
 *                                   if it has ANY of the selected categories
 *   'difficulty'        string   — 'Easy' | 'Medium' | 'Hard'
 *   'max_time'          int      — prep_time_minutes + cook_time_minutes <= max_time
 *   'min_rating'        float    — average overall rating >= this value
 *   'exclude_allergens' int[]    — allergen_id values; recipes containing an
 *                                   ingredient with ANY of these allergens are excluded
 *   'sort'              string   — 'title_asc' | 'time_asc' | 'rating_desc' |
 *                                   'newest' | 'popularity' | 'difficulty_asc'
 */
function getRecipes(array $filters = []): array
{
    global $pdo;

    $search           = trim($filters['search'] ?? '');
    $ingredient       = trim($filters['ingredient'] ?? '');
    $categories       = array_filter($filters['categories'] ?? []);
    $difficulty       = $filters['difficulty'] ?? '';
    $maxTime          = isset($filters['max_time']) && $filters['max_time'] !== '' ? (int) $filters['max_time'] : null;
    $minRating        = isset($filters['min_rating']) && $filters['min_rating'] !== '' ? (float) $filters['min_rating'] : null;
    $excludeAllergens = array_filter(array_map('intval', $filters['exclude_allergens'] ?? []));
    $sort             = $filters['sort'] ?? 'title_asc';

    $where  = [];
    $params = [];

    if ($search !== '') {
        $where[] = '(r.title LIKE ? OR r.description LIKE ?)';
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    if ($ingredient !== '') {
        $where[] = 'r.recipe_id IN (
            SELECT ri.recipe_id
            FROM recipe_ingredients ri
            JOIN ingredients i ON i.ingredient_id = ri.ingredient_id
            WHERE i.ingredient_name LIKE ?
        )';
        $params[] = "%{$ingredient}%";
    }

    if ($difficulty !== '' && in_array($difficulty, ['Easy', 'Medium', 'Hard'], true)) {
        $where[] = 'r.difficulty = ?';
        $params[] = $difficulty;
    }

    if ($maxTime !== null) {
        $where[] = '(r.prep_time_minutes + r.cook_time_minutes) <= ?';
        $params[] = $maxTime;
    }

    if (!empty($categories)) {
        $placeholders = implode(',', array_fill(0, count($categories), '?'));
        $where[] = "r.recipe_id IN (
            SELECT rc.recipe_id
            FROM recipe_categories rc
            JOIN categories c ON c.category_id = rc.category_id
            WHERE c.category_name IN ({$placeholders})
        )";
        foreach ($categories as $categoryName) {
            $params[] = $categoryName;
        }
    }

    if (!empty($excludeAllergens)) {
        $placeholders = implode(',', array_fill(0, count($excludeAllergens), '?'));
        $where[] = "r.recipe_id NOT IN (
            SELECT ri.recipe_id
            FROM recipe_ingredients ri
            JOIN ingredient_allergens ia ON ia.ingredient_id = ri.ingredient_id
            WHERE ia.allergen_id IN ({$placeholders})
        )";
        foreach ($excludeAllergens as $allergenId) {
            $params[] = $allergenId;
        }
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    // Minimum rating filters on the aggregated AVG(), so it has to be a
    // HAVING clause rather than WHERE — it's evaluated after grouping.
    $havingSql = '';
    if ($minRating !== null) {
        $havingSql = 'HAVING avg_rating >= ?';
        $params[] = $minRating;
    }

    switch ($sort) {
        case 'time_asc':
            $orderSql = 'ORDER BY (r.prep_time_minutes + r.cook_time_minutes) ASC';
            break;
        case 'rating_desc':
            $orderSql = 'ORDER BY avg_rating IS NULL, avg_rating DESC';
            break;
        case 'newest':
            $orderSql = 'ORDER BY r.created_at DESC';
            break;
        case 'popularity':
            $orderSql = 'ORDER BY ratings_count DESC, avg_rating IS NULL, avg_rating DESC';
            break;
        case 'difficulty_asc':
            $orderSql = "ORDER BY FIELD(r.difficulty, 'Easy', 'Medium', 'Hard')";
            break;
        case 'title_asc':
        default:
            $orderSql = 'ORDER BY r.title ASC';
            break;
    }

    // $params is built WHERE-placeholders-first, then the HAVING
    // placeholder, so it must be passed to execute() exactly as-is
    // (PDO binds positionally).
    $sql = "
        SELECT
            r.recipe_id, r.title, r.description, r.prep_time_minutes,
            r.cook_time_minutes, r.servings, r.difficulty, r.image_path,
            ROUND(AVG(rt.overall_rating), 1) AS avg_rating,
            COUNT(DISTINCT rt.rating_id) AS ratings_count
        FROM recipes r
        LEFT JOIN ratings rt ON rt.recipe_id = r.recipe_id
        {$whereSql}
        GROUP BY r.recipe_id, r.title, r.description, r.prep_time_minutes,
                 r.cook_time_minutes, r.servings, r.difficulty, r.image_path
        {$havingSql}
        {$orderSql}
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $recipes = $stmt->fetchAll();

    // Attach categories to each recipe (used for the tag list on cards/detail).
    foreach ($recipes as &$recipe) {
        $recipe['categories'] = getRecipeCategories((int) $recipe['recipe_id']);
        $recipe['avg_rating'] = $recipe['avg_rating'] !== null ? (float) $recipe['avg_rating'] : null;
        $recipe['ratings_count'] = (int) $recipe['ratings_count'];
    }
    unset($recipe);

    return $recipes;
}

function getRecipeById(int $id): ?array
{
    global $pdo;

    $stmt = $pdo->prepare('
        SELECT
            r.recipe_id, r.title, r.description, r.prep_time_minutes,
            r.cook_time_minutes, r.servings, r.difficulty, r.image_path, r.source_url,
            ROUND(AVG(rt.overall_rating), 1) AS avg_rating,
            COUNT(DISTINCT rt.rating_id) AS ratings_count
        FROM recipes r
        LEFT JOIN ratings rt ON rt.recipe_id = r.recipe_id
        WHERE r.recipe_id = ?
        GROUP BY r.recipe_id, r.title, r.description, r.prep_time_minutes,
                 r.cook_time_minutes, r.servings, r.difficulty, r.image_path, r.source_url
    ');
    $stmt->execute([$id]);
    $recipe = $stmt->fetch();

    if (!$recipe) {
        return null;
    }

    $recipe['avg_rating'] = $recipe['avg_rating'] !== null ? (float) $recipe['avg_rating'] : null;
    $recipe['ratings_count'] = (int) $recipe['ratings_count'];
    $recipe['categories'] = getRecipeCategories($id);
    $recipe['ingredients'] = getRecipeIngredients($id);
    $recipe['steps'] = getRecipeSteps($id);

    return $recipe;
}

function getRecipeCategories(int $recipeId): array
{
    global $pdo;
    $stmt = $pdo->prepare('
        SELECT c.category_id, c.category_name, c.category_type
        FROM recipe_categories rc
        JOIN categories c ON c.category_id = rc.category_id
        WHERE rc.recipe_id = ?
        ORDER BY c.category_type, c.category_name
    ');
    $stmt->execute([$recipeId]);
    return $stmt->fetchAll();
}

function getRecipeIngredients(int $recipeId): array
{
    global $pdo;
    $stmt = $pdo->prepare('
        SELECT ri.section_name, ri.display_order, ri.quantity, ri.unit, ri.notes,
               i.ingredient_name
        FROM recipe_ingredients ri
        JOIN ingredients i ON i.ingredient_id = ri.ingredient_id
        WHERE ri.recipe_id = ?
        ORDER BY ri.display_order
    ');
    $stmt->execute([$recipeId]);
    return $stmt->fetchAll();
}

function getRecipeSteps(int $recipeId): array
{
    global $pdo;
    $stmt = $pdo->prepare('
        SELECT step_number, instruction, duration_minutes
        FROM recipe_steps
        WHERE recipe_id = ?
        ORDER BY step_number
    ');
    $stmt->execute([$recipeId]);
    return $stmt->fetchAll();
}

/**
 * All category names in use, grouped by type — used to build the
 * search filter checkboxes.
 */
function getAllCategoriesGrouped(): array
{
    global $pdo;

    $stmt = $pdo->query('
        SELECT category_name, category_type
        FROM categories
        ORDER BY category_type, category_name
    ');

    $grouped = [];
    foreach ($stmt->fetchAll() as $row) {
        $grouped[$row['category_type']][] = $row['category_name'];
    }
    ksort($grouped);

    return $grouped;
}

/**
 * All allergens in the system — used to build the "exclude recipes
 * containing" checkboxes on the search page.
 */
function getAllAllergens(): array
{
    global $pdo;
    $stmt = $pdo->query('SELECT allergen_id, allergen_name FROM allergens ORDER BY allergen_name');
    return $stmt->fetchAll();
}

/**
 * The allergen_ids a logged-in user has saved on their profile —
 * used to default the "exclude recipes containing" checkboxes to
 * their own allergens the first time they land on the search page.
 */
function getUserAllergenIds(int $userId): array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT allergen_id FROM user_allergens WHERE user_id = ?');
    $stmt->execute([$userId]);
    return array_map('intval', array_column($stmt->fetchAll(), 'allergen_id'));
}
