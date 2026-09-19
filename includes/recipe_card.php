<?php
/*
 * Reusable recipe card.
 *
 * Required:
 * $recipe - recipe data returned by getRecipes().
 *
 * Optional:
 * $recipeCardSearchData - when true, adds the data attributes
 * used by the search page's client-side filtering.
 *
 * $showRatingsCount - when true, displays the number of ratings
 * alongside the average rating.
 */

$recipeCardSearchData = $recipeCardSearchData ?? false;
$showRatingsCount = $showRatingsCount ?? false;

$totalTime =
    $recipe['prep_time_minutes']
    + $recipe['cook_time_minutes'];
?>

<article
    class="recipe-card"
    <?php if ($recipeCardSearchData): ?>
        data-title="<?= htmlspecialchars(
            mb_strtolower($recipe['title'])
        ) ?>"
        data-description="<?= htmlspecialchars(
            mb_strtolower($recipe['description'])
        ) ?>"
        data-time="<?= (int) $totalTime ?>"
        data-rating="<?= htmlspecialchars(
            (string) ($recipe['avg_rating'] ?? 0)
        ) ?>"
    <?php endif; ?>
>
    <a
        href="recipe.php?id=<?= (int) $recipe['recipe_id'] ?>"
        class="recipe-card-link"
    >
        <?php if (!empty($recipe['image_path'])): ?>
            <img
                src="<?= htmlspecialchars($recipe['image_path']) ?>"
                alt=""
                class="recipe-card-image"
            >
        <?php endif; ?>

        <h3>
            <?= htmlspecialchars($recipe['title']) ?>
        </h3>
    </a>

    <p class="recipe-card-meta">
        <?= htmlspecialchars($recipe['difficulty']) ?>
        &middot;
        <?= (int) $totalTime ?> mins

        <?php if ($recipe['avg_rating'] !== null): ?>
            &middot;
            <?= htmlspecialchars(
                (string) $recipe['avg_rating']
            ) ?>/5

            <?php if ($showRatingsCount): ?>
                (<?= (int) $recipe['ratings_count'] ?>
                rating<?= (int) $recipe['ratings_count'] === 1
                    ? ''
                    : 's' ?>)
            <?php endif; ?>
        <?php else: ?>
            &middot;
            Not yet rated
        <?php endif; ?>
    </p>

    <p class="recipe-card-desc">
        <?= htmlspecialchars($recipe['description']) ?>
    </p>

    <ul class="tag-list">
        <?php foreach ($recipe['categories'] as $category): ?>
            <li class="tag">
                <?= htmlspecialchars($category['category_name']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</article>