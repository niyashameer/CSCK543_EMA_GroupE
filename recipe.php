<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/recipes_data.php';

/*
 * Create a visual five-star representation of a rating.
 *
 * Average ratings are rounded to the nearest 0.5 so calculated
 * values such as 3.5 can be represented using half stars.
 *
 * The exact numeric rating is displayed separately, so the stars
 * are decorative and hidden from assistive technologies.
 */
function renderStars(float $rating): string
{
    $rating = max(0, min(5, $rating));
    $roundedRating = round($rating * 2) / 2;

    $html = '';

    for ($star = 1; $star <= 5; $star++) {
        if ($roundedRating >= $star) {
            $html .= '<span class="star star-full">★</span>';
        } elseif ($roundedRating >= $star - 0.5) {
            $html .= '<span class="star star-half">★</span>';
        } else {
            $html .= '<span class="star star-empty">★</span>';
        }
    }

    return $html;
}

$recipeId = (int) ($_GET['id'] ?? 0);
$recipe = getRecipeById($recipeId);

if ($recipe === null) {
    http_response_code(404);
    $pageTitle = 'Recipe not found';
    require_once 'includes/header.php';
    ?>
    <h1>Recipe not found</h1>
    <p>The recipe you requested could not be found.</p>

    <nav class="recipe-back-nav" aria-label="Recipe navigation">
        <a href="index.php">&larr; Back to home</a>
        <a href="search.php">&larr; Back to search</a>
    </nav>
    <?php
    require_once 'includes/footer.php';
    exit;
}

$dbWarning = null;
$isFavourited = false;
$userRating = null;

if (isLoggedIn()) {
    $userId = currentUserId();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'toggle_favourite') {
                $stmt = $pdo->prepare(
                    'SELECT 1
                     FROM favourites
                     WHERE user_id = ? AND recipe_id = ?'
                );

                $stmt->execute([$userId, $recipeId]);

                if ($stmt->fetch()) {
                    $pdo->prepare(
                        'DELETE FROM favourites
                         WHERE user_id = ? AND recipe_id = ?'
                    )->execute([$userId, $recipeId]);

                    setFlash(
                        'success',
                        'Removed from favourites.'
                    );
                } else {
                    $pdo->prepare(
                        'INSERT INTO favourites (
                            user_id,
                            recipe_id
                        )
                        VALUES (?, ?)'
                    )->execute([$userId, $recipeId]);

                    setFlash(
                        'success',
                        'Added to favourites.'
                    );
                }

                header(
                    'Location: recipe.php?id=' . $recipeId
                );
                exit;
            }

            if ($action === 'submit_rating') {
                /*
                 * All three component ratings are required.
                 *
                 * An overall rating is not submitted or stored.
                 * It is calculated from:
                 *
                 * (taste + ease + presentation) / 3
                 */
                $taste =
                    (int) ($_POST['taste_rating'] ?? 0);

                $difficultyR =
                    (int) ($_POST['difficulty_rating'] ?? 0);

                $presentation =
                    (int) ($_POST['presentation_rating'] ?? 0);

                $review = trim(
                    $_POST['review'] ?? ''
                );

                $ratingIsValid =
                    $taste >= 1
                    && $taste <= 5
                    && $difficultyR >= 1
                    && $difficultyR <= 5
                    && $presentation >= 1
                    && $presentation <= 5;

                if (!$ratingIsValid) {
                    setFlash(
                        'error',
                        'Please make sure all ratings are between 1 and 5.'
                    );
                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO ratings (
                            user_id,
                            recipe_id,
                            taste_rating,
                            difficulty_rating,
                            presentation_rating,
                            review
                        )
                        VALUES (?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            taste_rating =
                                VALUES(taste_rating),
                            difficulty_rating =
                                VALUES(difficulty_rating),
                            presentation_rating =
                                VALUES(presentation_rating),
                            review =
                                VALUES(review)'
                    );

                    $stmt->execute([
                        $userId,
                        $recipeId,
                        $taste,
                        $difficultyR,
                        $presentation,
                        $review
                    ]);

                    setFlash(
                        'success',
                        'Thanks, your rating has been saved.'
                    );
                }

                header(
                    'Location: recipe.php?id=' . $recipeId
                );
                exit;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());

            $dbWarning =
                "This action needs recipe #{$recipeId} "
                . 'to exist in the recipes table.';
        }
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT 1
             FROM favourites
             WHERE user_id = ? AND recipe_id = ?'
        );

        $stmt->execute([$userId, $recipeId]);

        $isFavourited =
            (bool) $stmt->fetch();

        $stmt = $pdo->prepare(
            'SELECT
                rating_id,
                user_id,
                recipe_id,
                taste_rating,
                difficulty_rating,
                presentation_rating,
                review,
                created_at
             FROM ratings
             WHERE user_id = ? AND recipe_id = ?'
        );

        $stmt->execute([$userId, $recipeId]);

        $userRating =
            $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}

$flash = flash();

$totalTime =
    $recipe['prep_time_minutes']
    + $recipe['cook_time_minutes'];

// Group ingredients by section, preserving display order.
$ingredientSections = [];

foreach ($recipe['ingredients'] as $ingredient) {
    $section =
        $ingredient['section_name'] ?? '';

    $ingredientSections[$section][] =
        $ingredient;
}

$pageTitle = $recipe['title'];

require_once 'includes/header.php';
?>

<nav
    class="recipe-back-nav"
    aria-label="Recipe navigation"
>
    <a href="index.php">
        &larr; Back to home
    </a>

    <a href="search.php">
        &larr; Back to search
    </a>
</nav>

<?php if ($flash): ?>
    <div
        class="alert alert-<?= htmlspecialchars(
            $flash['type']
        ) ?>"
        role="status"
    >
        <?= htmlspecialchars(
            $flash['message']
        ) ?>
    </div>
<?php endif; ?>

<?php if ($dbWarning): ?>
    <div
        class="alert alert-error"
        role="alert"
    >
        <?= htmlspecialchars($dbWarning) ?>
    </div>
<?php endif; ?>

<article class="recipe-detail">
    <header class="recipe-detail-header">
        <h1>
            <?= htmlspecialchars(
                $recipe['title']
            ) ?>
        </h1>

        <?php if (!empty($recipe['image_path'])): ?>
            <img
                src="<?= htmlspecialchars(
                    $recipe['image_path']
                ) ?>"
                alt=""
                class="recipe-detail-image"
            >
        <?php endif; ?>

        <p>
            <?= htmlspecialchars(
                $recipe['description']
            ) ?>
        </p>

        <ul class="tag-list">
            <?php foreach ($recipe['categories'] as $category): ?>
                <li class="tag">
                    <?= htmlspecialchars(
                        $category['category_name']
                    ) ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <dl class="recipe-facts">
            <div>
                <dt>Difficulty</dt>
                <dd>
                    <?= htmlspecialchars(
                        $recipe['difficulty']
                    ) ?>
                </dd>
            </div>

            <div>
                <dt>Prep time</dt>
                <dd>
                    <?= (int) $recipe['prep_time_minutes'] ?>
                    mins
                </dd>
            </div>

            <div>
                <dt>Cook time</dt>
                <dd>
                    <?= (int) $recipe['cook_time_minutes'] ?>
                    mins
                </dd>
            </div>

            <div>
                <dt>Total time</dt>
                <dd>
                    <?= (int) $totalTime ?> mins
                </dd>
            </div>

            <div>
                <dt>Servings</dt>
                <dd>
                    <?= htmlspecialchars(
                        (string) (
                            $recipe['servings'] ?? 'N/A'
                        )
                    ) ?>
                </dd>
            </div>

            <div>
                <dt>Rating</dt>
                <dd>
                    <?php if ($recipe['avg_rating'] !== null): ?>
                        <span
                            class="stars stars-small"
                            aria-hidden="true"
                        >
                            <?= renderStars(
                                (float) $recipe['avg_rating']
                            ) ?>
                        </span>

                        <?= htmlspecialchars(
                            (string) $recipe['avg_rating']
                        ) ?>/5

                        (<?= (int) $recipe['ratings_count'] ?>
                        rating<?= $recipe['ratings_count'] === 1
                            ? ''
                            : 's' ?>)
                    <?php else: ?>
                        Not yet rated
                    <?php endif; ?>
                </dd>
            </div>
        </dl>

        <?php if (!empty($recipe['source_url'])): ?>
            <div class="recipe-source">
                <p>
                    Recipe source:
                    <a
                        href="<?= htmlspecialchars(
                            $recipe['source_url']
                        ) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <?= htmlspecialchars(
                            $recipe['source_url']
                        ) ?>
                    </a>
                </p>

                <?php if (!empty($recipe['image_path'])): ?>
                    <p>
                        Image source:
                        <a
                            href="<?= htmlspecialchars(
                                $recipe['source_url']
                            ) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <?= htmlspecialchars(
                                $recipe['source_url']
                            ) ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (isLoggedIn()): ?>
            <form
                method="POST"
                action="recipe.php?id=<?= (int) $recipeId ?>"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        csrfToken()
                    ) ?>"
                >

                <input
                    type="hidden"
                    name="action"
                    value="toggle_favourite"
                >

                <button
                    type="submit"
                    class="btn <?= $isFavourited
                        ? 'btn-secondary'
                        : 'btn-primary' ?>"
                >
                    <?= $isFavourited
                        ? 'Remove from favourites'
                        : 'Save to favourites' ?>
                </button>
            </form>
        <?php else: ?>
            <p>
                <a href="login.php">Log in</a>
                to save this recipe to your favourites or rate it.
            </p>
        <?php endif; ?>
    </header>

    <section class="recipe-section">
        <h2>Ingredients</h2>

        <?php foreach ($ingredientSections as $sectionName => $items): ?>
            <?php if ($sectionName !== ''): ?>
                <h3>
                    <?= htmlspecialchars(
                        $sectionName
                    ) ?>
                </h3>
            <?php endif; ?>

            <ul class="ingredient-list">
                <?php foreach ($items as $item): ?>
                    <li>
                        <?php if ($item['quantity']): ?>
                            <span class="ingredient-qty">
                                <?= htmlspecialchars(
                                    $item['quantity']
                                ) ?>

                                <?= $item['unit']
                                    ? ' ' . htmlspecialchars(
                                        $item['unit']
                                    )
                                    : '' ?>
                            </span>
                        <?php endif; ?>

                        <?= htmlspecialchars(
                            $item['ingredient_name']
                        ) ?>

                        <?php if ($item['notes']): ?>
                            <span class="ingredient-notes">
                                (<?= htmlspecialchars(
                                    $item['notes']
                                ) ?>)
                            </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </section>

    <section class="recipe-section">
        <h2>Method</h2>

        <ol class="step-list">
            <?php foreach ($recipe['steps'] as $step): ?>
                <li>
                    <span class="step-text">
                        <?= htmlspecialchars(
                            $step['instruction']
                        ) ?>
                    </span>

                    <span class="step-duration">
                        <?= (int) $step['duration_minutes'] ?>
                        mins
                    </span>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

    <section class="recipe-section recipe-ratings">
        <h2>Ratings and reviews</h2>

        <?php if ($recipe['ratings_count'] > 0): ?>
            <div class="rating-summary">
                <div class="rating-overall">
                    <h3>Overall rating</h3>

                    <div class="rating-overall-score">
                        <span
                            class="stars stars-large"
                            aria-hidden="true"
                        >
                            <?= renderStars(
                                (float) $recipe['avg_rating']
                            ) ?>
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                (string) $recipe['avg_rating']
                            ) ?>/5
                        </strong>
                    </div>

                    <p>
                        Based on
                        <?= (int) $recipe['ratings_count'] ?>
                        rating<?= $recipe['ratings_count'] === 1
                            ? ''
                            : 's' ?>.
                    </p>
                </div>

                <dl class="rating-breakdown">
                    <div>
                        <dt>Taste</dt>
                        <dd>
                            <?php if (
                                $recipe['avg_taste_rating'] !== null
                            ): ?>
                                <span
                                    class="stars"
                                    aria-hidden="true"
                                >
                                    <?= renderStars(
                                        (float) $recipe[
                                            'avg_taste_rating'
                                        ]
                                    ) ?>
                                </span>

                                <span>
                                    <?= htmlspecialchars(
                                        (string) $recipe[
                                            'avg_taste_rating'
                                        ]
                                    ) ?>/5
                                </span>
                            <?php else: ?>
                                Not rated
                            <?php endif; ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Ease of preparation</dt>
                        <dd>
                            <?php if (
                                $recipe['avg_difficulty_rating'] !== null
                            ): ?>
                                <span
                                    class="stars"
                                    aria-hidden="true"
                                >
                                    <?= renderStars(
                                        (float) $recipe[
                                            'avg_difficulty_rating'
                                        ]
                                    ) ?>
                                </span>

                                <span>
                                    <?= htmlspecialchars(
                                        (string) $recipe[
                                            'avg_difficulty_rating'
                                        ]
                                    ) ?>/5
                                </span>
                            <?php else: ?>
                                Not rated
                            <?php endif; ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Presentation</dt>
                        <dd>
                            <?php if (
                                $recipe[
                                    'avg_presentation_rating'
                                ] !== null
                            ): ?>
                                <span
                                    class="stars"
                                    aria-hidden="true"
                                >
                                    <?= renderStars(
                                        (float) $recipe[
                                            'avg_presentation_rating'
                                        ]
                                    ) ?>
                                </span>

                                <span>
                                    <?= htmlspecialchars(
                                        (string) $recipe[
                                            'avg_presentation_rating'
                                        ]
                                    ) ?>/5
                                </span>
                            <?php else: ?>
                                Not rated
                            <?php endif; ?>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="review-list">
                <?php foreach ($recipe['reviews'] as $review): ?>
                    <article class="review-card">
                        <header class="review-header">
                            <h3>
                                <?= htmlspecialchars(
                                    $review['user_name']
                                ) ?>
                            </h3>

                            <div class="review-overall">
                                <span
                                    class="stars"
                                    aria-hidden="true"
                                >
                                    <?= renderStars(
                                        (float) $review[
                                            'overall_rating'
                                        ]
                                    ) ?>
                                </span>

                                <strong>
                                    <?= htmlspecialchars(
                                        (string) $review[
                                            'overall_rating'
                                        ]
                                    ) ?>/5
                                </strong>
                            </div>
                        </header>

                        <dl class="review-scores">
                            <div>
                                <dt>Taste</dt>
                                <dd>
                                    <span
                                        class="stars"
                                        aria-hidden="true"
                                    >
                                        <?= renderStars(
                                            (float) $review[
                                                'taste_rating'
                                            ]
                                        ) ?>
                                    </span>

                                    <span>
                                        <?= (int) $review[
                                            'taste_rating'
                                        ] ?>/5
                                    </span>
                                </dd>
                            </div>

                            <div>
                                <dt>Ease of preparation</dt>
                                <dd>
                                    <span
                                        class="stars"
                                        aria-hidden="true"
                                    >
                                        <?= renderStars(
                                            (float) $review[
                                                'difficulty_rating'
                                            ]
                                        ) ?>
                                    </span>

                                    <span>
                                        <?= (int) $review[
                                            'difficulty_rating'
                                        ] ?>/5
                                    </span>
                                </dd>
                            </div>

                            <div>
                                <dt>Presentation</dt>
                                <dd>
                                    <span
                                        class="stars"
                                        aria-hidden="true"
                                    >
                                        <?= renderStars(
                                            (float) $review[
                                                'presentation_rating'
                                            ]
                                        ) ?>
                                    </span>

                                    <span>
                                        <?= (int) $review[
                                            'presentation_rating'
                                        ] ?>/5
                                    </span>
                                </dd>
                            </div>
                        </dl>

                        <?php if (
                            trim($review['review'] ?? '') !== ''
                        ): ?>
                            <p class="review-text">
                                <?= nl2br(
                                    htmlspecialchars(
                                        $review['review']
                                    )
                                ) ?>
                            </p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>
                No ratings yet. Be the first to rate this recipe.
            </p>
        <?php endif; ?>
    </section>

    <?php if (isLoggedIn()): ?>
        <section class="recipe-section">
            <h2>
                <?= $userRating
                    ? 'Update your rating'
                    : 'Rate this recipe' ?>
            </h2>

            <p class="rating-help">
                Rate the recipe in each category from 1 to 5.
                Higher scores are more positive. Your overall rating
                will be calculated automatically from these three scores.
            </p>

            <form
                method="POST"
                action="recipe.php?id=<?= (int) $recipeId ?>"
                class="form-card rating-form"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        csrfToken()
                    ) ?>"
                >

                <input
                    type="hidden"
                    name="action"
                    value="submit_rating"
                >

                <div class="form-row">
                    <label for="taste_rating">
                        Taste (1&ndash;5)
                    </label>

                    <input
                        type="number"
                        id="taste_rating"
                        name="taste_rating"
                        min="1"
                        max="5"
                        required
                        value="<?= htmlspecialchars(
                            (string) (
                                $userRating['taste_rating'] ?? ''
                            )
                        ) ?>"
                    >
                </div>

                <div class="form-row">
                    <label for="difficulty_rating">
                        Ease of preparation (1&ndash;5)
                    </label>

                    <input
                        type="number"
                        id="difficulty_rating"
                        name="difficulty_rating"
                        min="1"
                        max="5"
                        required
                        aria-describedby="ease-rating-help"
                        value="<?= htmlspecialchars(
                            (string) (
                                $userRating['difficulty_rating'] ?? ''
                            )
                        ) ?>"
                    >

                    <small id="ease-rating-help">
                        1 = difficult, 5 = easy
                    </small>
                </div>

                <div class="form-row">
                    <label for="presentation_rating">
                        Presentation (1&ndash;5)
                    </label>

                    <input
                        type="number"
                        id="presentation_rating"
                        name="presentation_rating"
                        min="1"
                        max="5"
                        required
                        value="<?= htmlspecialchars(
                            (string) (
                                $userRating[
                                    'presentation_rating'
                                ] ?? ''
                            )
                        ) ?>"
                    >
                </div>

                <div class="form-row">
                    <label for="review">
                        Review (optional)
                    </label>

                    <textarea
                        id="review"
                        name="review"
                        rows="3"
                    ><?= htmlspecialchars(
                        $userRating['review'] ?? ''
                    ) ?></textarea>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <?= $userRating
                        ? 'Update rating'
                        : 'Submit rating' ?>
                </button>
            </form>
        </section>
    <?php endif; ?>
</article>

<?php require_once 'includes/footer.php'; ?>