<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/recipes_data.php';

// Retrieve all recipes for the homepage.
$recipes = getRecipes([
    'search' => '',
    'ingredient' => '',
    'categories' => [],
    'difficulty' => '',
    'max_time' => '',
    'min_rating' => '',
    'exclude_allergens' => [],
    'sort' => 'title_asc',
]);

$pageTitle = 'Home';
require_once 'includes/header.php';
?>

<section class="home-hero">
    <h1>Find your next recipe</h1>

    <p>
        Browse our collection of recipes or search by ingredient,
        category, cooking time, rating and more.
    </p>

    <a href="search.php" class="btn btn-primary">
        Search recipes
    </a>
</section>

<section class="home-recipes" aria-labelledby="recipes-heading">
    <h2 id="recipes-heading">Our recipes</h2>

    <div class="recipe-grid">
        <?php foreach ($recipes as $recipe): ?>
            <?php
            // Homepage cards do not need search data attributes
            // or the number of submitted ratings.
            $recipeCardSearchData = false;
            $showRatingsCount = false;

            require 'includes/recipe_card.php';
            ?>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>