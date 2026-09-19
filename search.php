<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/recipes_data.php';

$hasSubmittedForm = isset($_GET['filtered']);

// On a first, unfiltered visit, default the allergen-exclusion checkboxes
// to whatever the logged-in user has saved on their profile.
// Once the form has been submitted, respect the user's explicit choices.
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
    'exclude_allergens' => $hasSubmittedForm
        ? ($_GET['exclude_allergens'] ?? [])
        : $defaultExcludeAllergens,
    'sort'              => $_GET['sort'] ?? 'title_asc',
];

$recipes = getRecipes($filters);
$categoryGroups = getAllCategoriesGrouped();
$allergenOptions = getAllAllergens();

$hasActiveFilters =
    $filters['search'] !== ''
    || $filters['ingredient'] !== ''
    || !empty($filters['categories'])
    || $filters['difficulty'] !== ''
    || $filters['max_time'] !== ''
    || $filters['min_rating'] !== ''
    || !empty($filters['exclude_allergens']);

$pageTitle = 'Search Recipes';
require_once 'includes/header.php';
?>

<div class="search-page">
    <h1>Search recipes</h1>

    <div class="search-layout">

        <aside class="search-sidebar" aria-label="Recipe search filters">
            <form
                method="GET"
                action="search.php"
                class="search-form"
                id="search-form"
            >
                <input type="hidden" name="filtered" value="1">

                <div class="search-field search-field--main">
                    <label for="search">
                        Search recipes
                    </label>

                    <input
                        type="search"
                        id="search"
                        name="search"
                        placeholder="Title or description..."
                        value="<?= htmlspecialchars($filters['search']) ?>"
                    >
                </div>

                <div class="search-field">
                    <label for="ingredient">
                        Contains ingredient
                    </label>

                    <input
                        type="text"
                        id="ingredient"
                        name="ingredient"
                        placeholder="e.g. mushroom"
                        value="<?= htmlspecialchars($filters['ingredient']) ?>"
                    >
                </div>

                <div class="search-field">
                    <label for="difficulty">
                        Difficulty
                    </label>

                    <select id="difficulty" name="difficulty">
                        <option value="">Any</option>

                        <?php foreach (['Easy', 'Medium', 'Hard'] as $level): ?>
                            <option
                                value="<?= $level ?>"
                                <?= $filters['difficulty'] === $level
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= $level ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="search-field">
                    <label for="max_time">
                        Max total time (mins)
                    </label>

                    <input
                        type="number"
                        id="max_time"
                        name="max_time"
                        min="0"
                        step="5"
                        value="<?= htmlspecialchars($filters['max_time']) ?>"
                    >
                </div>

                <div class="search-field">
                    <label for="min_rating">
                        Minimum rating
                    </label>

                    <select id="min_rating" name="min_rating">
                        <option value="">Any</option>

                        <?php foreach (
                            [
                                '3' => '3+',
                                '3.5' => '3.5+',
                                '4' => '4+',
                                '4.5' => '4.5+'
                            ] as $value => $label
                        ): ?>
                            <option
                                value="<?= $value ?>"
                                <?= $filters['min_rating'] === $value
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <fieldset class="search-filter-group">
                    <legend>Categories</legend>

                    <?php foreach ($categoryGroups as $type => $names): ?>
                        <div class="category-group">
                            <span class="category-group-label">
                                <?= htmlspecialchars($type) ?>
                            </span>

                            <?php foreach ($names as $name): ?>
                                <label class="checkbox-label">
                                    <input
                                        type="checkbox"
                                        name="categories[]"
                                        value="<?= htmlspecialchars($name) ?>"
                                        <?= in_array(
                                            $name,
                                            $filters['categories'],
                                            true
                                        ) ? 'checked' : '' ?>
                                    >
                                    <?= htmlspecialchars($name) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

                <?php if (!empty($allergenOptions)): ?>
                    <fieldset class="search-filter-group">
                        <legend>
                            Hide recipes containing
                        </legend>

                        <?php if (
                            isLoggedIn()
                            && !empty($defaultExcludeAllergens)
                            && !$hasSubmittedForm
                        ): ?>
                            <p class="legend-hint">
                                Pre-filled from your saved allergens
                            </p>
                        <?php endif; ?>

                        <?php foreach ($allergenOptions as $allergen): ?>
                            <label class="checkbox-label">
                                <input
                                    type="checkbox"
                                    name="exclude_allergens[]"
                                    value="<?= (int) $allergen['allergen_id'] ?>"
                                    <?= in_array(
                                        (int) $allergen['allergen_id'],
                                        array_map(
                                            'intval',
                                            $filters['exclude_allergens']
                                        ),
                                        true
                                    ) ? 'checked' : '' ?>
                                >
                                <?= htmlspecialchars(
                                    $allergen['allergen_name']
                                ) ?>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                <?php endif; ?>

                <div class="search-actions">
                    <button type="submit" class="btn btn-primary">
                        Search
                    </button>

                    <?php if ($hasActiveFilters): ?>
                        <a href="search.php" class="btn btn-link">
                            Clear filters
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </aside>

        <section
            class="search-results"
            id="search-results"
            aria-labelledby="results-heading"
        >
            <div class="search-results-header">
                <div>
                    <h2 id="results-heading">Results</h2>

                    <p id="results-count" aria-live="polite">
                        <?= count($recipes) ?>
                        recipe<?= count($recipes) === 1 ? '' : 's' ?> found
                    </p>
                </div>

                <div class="search-sort">
                    <label for="sort">Sort by</label>

                    <select
                        id="sort"
                        name="sort"
                        form="search-form"
                    >
                        <option
                            value="title_asc"
                            <?= $filters['sort'] === 'title_asc'
                                ? 'selected'
                                : '' ?>
                        >
                            Title (A&ndash;Z)
                        </option>

                        <option
                            value="time_asc"
                            <?= $filters['sort'] === 'time_asc'
                                ? 'selected'
                                : '' ?>
                        >
                            Total time (shortest first)
                        </option>

                        <option
                            value="rating_desc"
                            <?= $filters['sort'] === 'rating_desc'
                                ? 'selected'
                                : '' ?>
                        >
                            Rating (highest first)
                        </option>

                        <option
                            value="popularity"
                            <?= $filters['sort'] === 'popularity'
                                ? 'selected'
                                : '' ?>
                        >
                            Most rated
                        </option>

                        <option
                            value="difficulty_asc"
                            <?= $filters['sort'] === 'difficulty_asc'
                                ? 'selected'
                                : '' ?>
                        >
                            Difficulty (easiest first)
                        </option>
                    </select>
                </div>
            </div>

            <?php if (!empty($recipes)): ?>
                <div class="recipe-grid" id="recipe-grid">
                    <?php foreach ($recipes as $recipe): ?>
                        <?php
                        /*
                         * Search cards need the data attributes used by
                         * main.js for client-side search enhancements.
                         *
                         * The search results also display the number of
                         * ratings alongside each recipe's average score.
                         */
                        $recipeCardSearchData = true;
                        $showRatingsCount = true;

                        require 'includes/recipe_card.php';
                        ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-results">
                    No recipes matched your search. Try widening your filters.
                </p>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>