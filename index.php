<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/recipes_data.php';

$hasSubmittedForm = isset($_GET['filtered']);

// On a first, unfiltered visit, default the allergen-exclusion checkboxes
// to whatever the logged-in user has saved on their profile — a small
// personalisation touch. Once they've actually submitted the form (even
// with everything unchecked), respect their explicit choice instead.
$defaultExcludeAllergens = [];
if (!$hasSubmittedForm && isLoggedIn()) {
    $defaultExcludeAllergens = getUserAllergenIds(currentUserId());
}

$filters = [
    'search'            => trim($_GET['search'] ?? ''),
    'ingredient'        => trim($_GET['ingredient'] ?? ''),
    'categories'        => $_GET['categories'] ?? [],
    'difficulty'        => $_GET['difficulty'] ?? '',
    'max_time'          => $_GET['max_time'] ?? '',
    'min_rating'        => $_GET['min_rating'] ?? '',
    'exclude_allergens' => $hasSubmittedForm ? ($_GET['exclude_allergens'] ?? []) : $defaultExcludeAllergens,
    'sort'              => $_GET['sort'] ?? 'title_asc',
];

$recipes = getRecipes($filters);
$categoryGroups = getAllCategoriesGrouped();
$allergenOptions = getAllAllergens();

$hasActiveFilters = $filters['search'] !== '' || $filters['ingredient'] !== ''
    || !empty($filters['categories']) || $filters['difficulty'] !== ''
    || $filters['max_time'] !== '' || $filters['min_rating'] !== ''
    || !empty($filters['exclude_allergens']);

$pageTitle = 'Search Recipes';
require_once 'includes/header.php';
?>

<h1>Search recipes</h1>

<form method="GET" action="index.php" class="search-form" id="search-form">
    <input type="hidden" name="filtered" value="1">

    <div class="search-row">
        <label for="search" class="sr-only">Search by title or description</label>
        <input type="search" id="search" name="search" placeholder="Search recipes by name&hellip;"
               value="<?= htmlspecialchars($filters['search']) ?>">

        <label for="sort" class="sr-only">Sort by</label>
        <select id="sort" name="sort">
            <option value="title_asc" <?= $filters['sort'] === 'title_asc' ? 'selected' : '' ?>>Title (A&ndash;Z)</option>
            <option value="time_asc" <?= $filters['sort'] === 'time_asc' ? 'selected' : '' ?>>Total time (shortest first)</option>
            <option value="rating_desc" <?= $filters['sort'] === 'rating_desc' ? 'selected' : '' ?>>Rating (highest first)</option>
            <option value="popularity" <?= $filters['sort'] === 'popularity' ? 'selected' : '' ?>>Most rated</option>
            <option value="difficulty_asc" <?= $filters['sort'] === 'difficulty_asc' ? 'selected' : '' ?>>Difficulty (easiest first)</option>
            <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest</option>
        </select>

        <button type="submit" class="btn btn-primary">Search</button>
        <?php if ($hasActiveFilters): ?>
            <a href="index.php" class="btn btn-link">Clear filters</a>
        <?php endif; ?>
    </div>

    <div class="search-row search-row--filters">
        <label for="ingredient">Contains ingredient</label>
        <input type="text" id="ingredient" name="ingredient" placeholder="e.g. mushroom"
               value="<?= htmlspecialchars($filters['ingredient']) ?>">

        <label for="difficulty">Difficulty</label>
        <select id="difficulty" name="difficulty">
            <option value="">Any</option>
            <?php foreach (['Easy', 'Medium', 'Hard'] as $level): ?>
                <option value="<?= $level ?>" <?= $filters['difficulty'] === $level ? 'selected' : '' ?>><?= $level ?></option>
            <?php endforeach; ?>
        </select>

        <label for="max_time">Max total time (mins)</label>
        <input type="number" id="max_time" name="max_time" min="0" step="5"
               value="<?= htmlspecialchars($filters['max_time']) ?>">

        <label for="min_rating">Minimum rating</label>
        <select id="min_rating" name="min_rating">
            <option value="">Any</option>
            <?php foreach (['3' => '3+', '3.5' => '3.5+', '4' => '4+', '4.5' => '4.5+'] as $value => $label): ?>
                <option value="<?= $value ?>" <?= $filters['min_rating'] === $value ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <fieldset class="search-row search-row--categories">
        <legend>Categories</legend>
        <?php foreach ($categoryGroups as $type => $names): ?>
            <div class="category-group">
                <span class="category-group-label"><?= htmlspecialchars($type) ?></span>
                <?php foreach ($names as $name): ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="categories[]" value="<?= htmlspecialchars($name) ?>"
                            <?= in_array($name, $filters['categories'], true) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($name) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </fieldset>

    <?php if (!empty($allergenOptions)): ?>
        <fieldset class="search-row search-row--categories">
            <legend>
                Hide recipes containing
                <?php if (isLoggedIn() && !empty($defaultExcludeAllergens) && !$hasSubmittedForm): ?>
                    <span class="legend-hint">(pre-filled from your saved allergens)</span>
                <?php endif; ?>
            </legend>
            <?php foreach ($allergenOptions as $allergen): ?>
                <label class="checkbox-label">
                    <input type="checkbox" name="exclude_allergens[]" value="<?= (int) $allergen['allergen_id'] ?>"
                        <?= in_array((int) $allergen['allergen_id'], array_map('intval', $filters['exclude_allergens']), true) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($allergen['allergen_name']) ?>
                </label>
            <?php endforeach; ?>
        </fieldset>
    <?php endif; ?>
</form>

<p id="results-count" aria-live="polite">
    <?= count($recipes) ?> recipe<?= count($recipes) === 1 ? '' : 's' ?> found
</p>

<div class="recipe-grid" id="recipe-grid">
    <?php foreach ($recipes as $recipe): ?>
        <?php $totalTime = $recipe['prep_time_minutes'] + $recipe['cook_time_minutes']; ?>
        <article class="recipe-card"
                 data-title="<?= htmlspecialchars(mb_strtolower($recipe['title'])) ?>"
                 data-time="<?= (int) $totalTime ?>"
                 data-rating="<?= htmlspecialchars((string) ($recipe['avg_rating'] ?? 0)) ?>">
            <a href="recipe.php?id=<?= (int) $recipe['recipe_id'] ?>" class="recipe-card-link">
                <h2><?= htmlspecialchars($recipe['title']) ?></h2>
            </a>
            <p class="recipe-card-meta">
                <?= htmlspecialchars($recipe['difficulty']) ?>
                &middot; <?= (int) $totalTime ?> mins
                <?php if ($recipe['avg_rating'] !== null): ?>
                    &middot; <?= htmlspecialchars((string) $recipe['avg_rating']) ?>/5
                    (<?= (int) $recipe['ratings_count'] ?> rating<?= $recipe['ratings_count'] === 1 ? '' : 's' ?>)
                <?php else: ?>
                    &middot; Not yet rated
                <?php endif; ?>
            </p>
            <p class="recipe-card-desc"><?= htmlspecialchars($recipe['description']) ?></p>
            <ul class="tag-list">
                <?php foreach ($recipe['categories'] as $category): ?>
                    <li class="tag"><?= htmlspecialchars($category['category_name']) ?></li>
                <?php endforeach; ?>
            </ul>
        </article>
    <?php endforeach; ?>
</div>

<?php if (empty($recipes)): ?>
    <p>No recipes matched your search. Try widening your filters.</p>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
