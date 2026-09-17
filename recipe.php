<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/recipes_data.php';

$recipeId = (int) ($_GET['id'] ?? 0);
$recipe = getRecipeById($recipeId);

if ($recipe === null) {
    http_response_code(404);
    $pageTitle = 'Recipe not found';
    require_once 'includes/header.php';
    echo '<h1>Recipe not found</h1><p><a href="index.php">Back to search</a></p>';
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
                $stmt = $pdo->prepare('SELECT 1 FROM favourites WHERE user_id = ? AND recipe_id = ?');
                $stmt->execute([$userId, $recipeId]);

                if ($stmt->fetch()) {
                    $pdo->prepare('DELETE FROM favourites WHERE user_id = ? AND recipe_id = ?')
                        ->execute([$userId, $recipeId]);
                    setFlash('success', 'Removed from favourites.');
                } else {
                    $pdo->prepare('INSERT INTO favourites (user_id, recipe_id) VALUES (?, ?)')
                        ->execute([$userId, $recipeId]);
                    setFlash('success', 'Added to favourites.');
                }
                header('Location: recipe.php?id=' . $recipeId);
                exit;
            }

            if ($action === 'submit_rating') {
                $overall      = (int) ($_POST['overall_rating'] ?? 0);
                $taste        = $_POST['taste_rating'] !== '' ? (int) $_POST['taste_rating'] : null;
                $difficultyR  = $_POST['difficulty_rating'] !== '' ? (int) $_POST['difficulty_rating'] : null;
                $presentation = $_POST['presentation_rating'] !== '' ? (int) $_POST['presentation_rating'] : null;
                $review       = trim($_POST['review'] ?? '');

                if ($overall < 1 || $overall > 5) {
                    setFlash('error', 'Please give an overall rating between 1 and 5.');
                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO ratings (user_id, recipe_id, overall_rating, taste_rating, difficulty_rating, presentation_rating, review)
                         VALUES (?, ?, ?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE
                            overall_rating = VALUES(overall_rating),
                            taste_rating = VALUES(taste_rating),
                            difficulty_rating = VALUES(difficulty_rating),
                            presentation_rating = VALUES(presentation_rating),
                            review = VALUES(review)'
                    );
                    $stmt->execute([$userId, $recipeId, $overall, $taste, $difficultyR, $presentation, $review]);
                    setFlash('success', 'Thanks — your rating has been saved.');
                }
                header('Location: recipe.php?id=' . $recipeId);
                exit;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $dbWarning = "This action needs recipe #{$recipeId} to exist in the recipes table.";
        }
    }

    try {
        $stmt = $pdo->prepare('SELECT 1 FROM favourites WHERE user_id = ? AND recipe_id = ?');
        $stmt->execute([$userId, $recipeId]);
        $isFavourited = (bool) $stmt->fetch();

        $stmt = $pdo->prepare('SELECT * FROM ratings WHERE user_id = ? AND recipe_id = ?');
        $stmt->execute([$userId, $recipeId]);
        $userRating = $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}

$flash = flash();
$totalTime = $recipe['prep_time_minutes'] + $recipe['cook_time_minutes'];

// Group ingredients by section, preserving display order.
$ingredientSections = [];
foreach ($recipe['ingredients'] as $ingredient) {
    $section = $ingredient['section_name'] ?? '';
    $ingredientSections[$section][] = $ingredient;
}

$pageTitle = $recipe['title'];
require_once 'includes/header.php';
?>

<p><a href="index.php">&larr; Back to search</a></p>

<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" role="status">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<?php if ($dbWarning): ?>
    <div class="alert alert-error" role="alert"><?= htmlspecialchars($dbWarning) ?></div>
<?php endif; ?>

<article class="recipe-detail">
    <header class="recipe-detail-header">
        <h1><?= htmlspecialchars($recipe['title']) ?></h1>
        <p><?= htmlspecialchars($recipe['description']) ?></p>

        <ul class="tag-list">
            <?php foreach ($recipe['categories'] as $category): ?>
                <li class="tag"><?= htmlspecialchars($category['category_name']) ?></li>
            <?php endforeach; ?>
        </ul>

        <dl class="recipe-facts">
            <div><dt>Difficulty</dt><dd><?= htmlspecialchars($recipe['difficulty']) ?></dd></div>
            <div><dt>Prep time</dt><dd><?= (int) $recipe['prep_time_minutes'] ?> mins</dd></div>
            <div><dt>Cook time</dt><dd><?= (int) $recipe['cook_time_minutes'] ?> mins</dd></div>
            <div><dt>Total time</dt><dd><?= (int) $totalTime ?> mins</dd></div>
            <div><dt>Servings</dt><dd><?= htmlspecialchars((string) ($recipe['servings'] ?? '—')) ?></dd></div>
            <div>
                <dt>Rating</dt>
                <dd>
                    <?php if ($recipe['avg_rating'] !== null): ?>
                        <?= htmlspecialchars((string) $recipe['avg_rating']) ?>/5
                        (<?= (int) $recipe['ratings_count'] ?> rating<?= $recipe['ratings_count'] === 1 ? '' : 's' ?>)
                    <?php else: ?>
                        Not yet rated
                    <?php endif; ?>
                </dd>
            </div>
        </dl>

        <?php if (!empty($recipe['source_url'])): ?>
            <p class="recipe-source">
                Recipe adapted from
                <a href="<?= htmlspecialchars($recipe['source_url']) ?>" target="_blank" rel="noopener noreferrer">BBC Food</a>.
            </p>
        <?php endif; ?>

        <?php if (isLoggedIn()): ?>
            <form method="POST" action="recipe.php?id=<?= (int) $recipeId ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <input type="hidden" name="action" value="toggle_favourite">
                <button type="submit" class="btn <?= $isFavourited ? 'btn-secondary' : 'btn-primary' ?>">
                    <?= $isFavourited ? 'Remove from favourites' : 'Save to favourites' ?>
                </button>
            </form>
        <?php else: ?>
            <p><a href="login.php">Log in</a> to save this recipe to your favourites or rate it.</p>
        <?php endif; ?>
    </header>

    <section class="recipe-section">
        <h2>Ingredients</h2>
        <?php foreach ($ingredientSections as $sectionName => $items): ?>
            <?php if ($sectionName !== ''): ?>
                <h3><?= htmlspecialchars($sectionName) ?></h3>
            <?php endif; ?>
            <ul class="ingredient-list">
                <?php foreach ($items as $item): ?>
                    <li>
                        <?php if ($item['quantity']): ?>
                            <span class="ingredient-qty"><?= htmlspecialchars($item['quantity']) ?><?= $item['unit'] ? ' ' . htmlspecialchars($item['unit']) : '' ?></span>
                        <?php endif; ?>
                        <?= htmlspecialchars($item['ingredient_name']) ?>
                        <?php if ($item['notes']): ?>
                            <span class="ingredient-notes">(<?= htmlspecialchars($item['notes']) ?>)</span>
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
                    <span class="step-text"><?= htmlspecialchars($step['instruction']) ?></span>
                    <span class="step-duration"><?= (int) $step['duration_minutes'] ?> mins</span>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

    <?php if (isLoggedIn()): ?>
        <section class="recipe-section">
            <h2><?= $userRating ? 'Update your rating' : 'Rate this recipe' ?></h2>
            <form method="POST" action="recipe.php?id=<?= (int) $recipeId ?>" class="form-card">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <input type="hidden" name="action" value="submit_rating">

                <div class="form-row">
                    <label for="overall_rating">Overall rating (1&ndash;5)</label>
                    <input type="number" id="overall_rating" name="overall_rating" min="1" max="5" required
                           value="<?= htmlspecialchars((string) ($userRating['overall_rating'] ?? '')) ?>">
                </div>
                <div class="form-row">
                    <label for="taste_rating">Taste (1&ndash;5, optional)</label>
                    <input type="number" id="taste_rating" name="taste_rating" min="1" max="5"
                           value="<?= htmlspecialchars((string) ($userRating['taste_rating'] ?? '')) ?>">
                </div>
                <div class="form-row">
                    <label for="difficulty_rating">Difficulty (1&ndash;5, optional)</label>
                    <input type="number" id="difficulty_rating" name="difficulty_rating" min="1" max="5"
                           value="<?= htmlspecialchars((string) ($userRating['difficulty_rating'] ?? '')) ?>">
                </div>
                <div class="form-row">
                    <label for="presentation_rating">Presentation (1&ndash;5, optional)</label>
                    <input type="number" id="presentation_rating" name="presentation_rating" min="1" max="5"
                           value="<?= htmlspecialchars((string) ($userRating['presentation_rating'] ?? '')) ?>">
                </div>
                <div class="form-row">
                    <label for="review">Review (optional)</label>
                    <textarea id="review" name="review" rows="3"><?= htmlspecialchars($userRating['review'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary"><?= $userRating ? 'Update rating' : 'Submit rating' ?></button>
            </form>
        </section>
    <?php endif; ?>
</article>

<?php require_once 'includes/footer.php'; ?>
