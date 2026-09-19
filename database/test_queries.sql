-- =========================================================
-- Recipe Web App - Test Queries
--
-- These queries provide basic checks for the database
-- structure, seeded recipe data and user-generated data.
--
-- Run schema.sql and seed.sql before using these queries.
-- =========================================================

USE recipe_app;


-- =========================================================
-- 1. TABLE COUNTS
--
-- Provides a quick check that the seed data has been loaded.
-- =========================================================

SELECT
    (SELECT COUNT(*) FROM recipes) AS recipes,
    (SELECT COUNT(*) FROM categories) AS categories,
    (SELECT COUNT(*) FROM ingredients) AS ingredients,
    (SELECT COUNT(*) FROM recipe_categories) AS recipe_categories,
    (SELECT COUNT(*) FROM recipe_ingredients) AS recipe_ingredients,
    (SELECT COUNT(*) FROM recipe_steps) AS recipe_steps,
    (SELECT COUNT(*) FROM allergens) AS allergens,
    (SELECT COUNT(*) FROM ingredient_allergens) AS ingredient_allergens;


-- =========================================================
-- 2. RECIPES
--
-- Checks the main recipe records, including image and source
-- paths used by the application.
-- =========================================================

SELECT
    recipe_id,
    title,
    description,
    prep_time_minutes,
    cook_time_minutes,
    servings,
    difficulty,
    image_path,
    source_url
FROM recipes
ORDER BY recipe_id;


-- =========================================================
-- 3. RECIPE CATEGORIES
--
-- Checks the many-to-many relationship between recipes and
-- categories.
-- =========================================================

SELECT
    r.title,
    c.category_name,
    c.category_type
FROM recipes r
JOIN recipe_categories rc
    ON r.recipe_id = rc.recipe_id
JOIN categories c
    ON rc.category_id = c.category_id
ORDER BY
    r.title,
    c.category_type,
    c.category_name;


-- =========================================================
-- 4. RECIPE INGREDIENTS
--
-- Checks ingredients, quantities, sections and display order
-- for each recipe.
-- =========================================================

SELECT
    r.title,
    ri.section_name,
    ri.display_order,
    ri.quantity,
    ri.unit,
    i.ingredient_name,
    ri.notes
FROM recipes r
JOIN recipe_ingredients ri
    ON r.recipe_id = ri.recipe_id
JOIN ingredients i
    ON ri.ingredient_id = i.ingredient_id
ORDER BY
    r.title,
    ri.display_order;


-- =========================================================
-- 5. RECIPE STEPS
--
-- Checks cooking instructions, their order and associated
-- durations.
-- =========================================================

SELECT
    r.title,
    rs.step_number,
    rs.duration_minutes,
    rs.instruction
FROM recipes r
JOIN recipe_steps rs
    ON r.recipe_id = rs.recipe_id
ORDER BY
    r.title,
    rs.step_number;


-- =========================================================
-- 6. ALLERGEN MAPPINGS
--
-- Checks the many-to-many relationship between ingredients
-- and allergens.
-- =========================================================

SELECT
    i.ingredient_name,
    a.allergen_name
FROM ingredients i
JOIN ingredient_allergens ia
    ON i.ingredient_id = ia.ingredient_id
JOIN allergens a
    ON ia.allergen_id = a.allergen_id
ORDER BY
    a.allergen_name,
    i.ingredient_name;


-- =========================================================
-- 7. USERS
--
-- Checks accounts created through the application.
-- Password hashes are intentionally not displayed.
--
-- This will initially return no rows after running only
-- schema.sql and seed.sql.
-- =========================================================

SELECT
    user_id,
    first_name,
    last_name,
    email,
    created_at
FROM users
ORDER BY user_id;


-- =========================================================
-- 8. RATINGS
--
-- Checks user ratings and calculates each user's overall
-- score from the three stored component ratings.
--
-- difficulty_rating is presented to users as ease of
-- preparation:
-- 1 = difficult, 5 = easy.
-- =========================================================

SELECT
    rt.rating_id,
    CONCAT(u.first_name, ' ', u.last_name) AS user_name,
    r.title,
    rt.taste_rating,
    rt.difficulty_rating AS ease_rating,
    rt.presentation_rating,

    ROUND(
        (
            rt.taste_rating
            + rt.difficulty_rating
            + rt.presentation_rating
        ) / 3.0,
        1
    ) AS calculated_overall_rating,

    rt.review,
    rt.created_at
FROM ratings rt
JOIN users u
    ON rt.user_id = u.user_id
JOIN recipes r
    ON rt.recipe_id = r.recipe_id
ORDER BY
    r.title,
    u.last_name,
    u.first_name;


-- =========================================================
-- 9. RECIPE AVERAGE RATINGS
--
-- Checks the calculated average rating for each recipe.
-- Recipes without ratings are retained using LEFT JOIN.
-- =========================================================

SELECT
    r.recipe_id,
    r.title,

    ROUND(
        AVG(
            (
                rt.taste_rating
                + rt.difficulty_rating
                + rt.presentation_rating
            ) / 3.0
        ),
        1
    ) AS average_rating,

    COUNT(rt.rating_id) AS ratings_count

FROM recipes r

LEFT JOIN ratings rt
    ON r.recipe_id = rt.recipe_id

GROUP BY
    r.recipe_id,
    r.title

ORDER BY r.recipe_id;


-- =========================================================
-- 10. FAVOURITES
--
-- Checks recipes saved by registered users.
-- =========================================================

SELECT
    CONCAT(u.first_name, ' ', u.last_name) AS user_name,
    r.title,
    f.created_at
FROM favourites f
JOIN users u
    ON f.user_id = u.user_id
JOIN recipes r
    ON f.recipe_id = r.recipe_id
ORDER BY
    u.last_name,
    u.first_name,
    r.title;


-- =========================================================
-- 11. USER ALLERGENS
--
-- Checks allergen preferences saved through user accounts.
-- =========================================================

SELECT
    CONCAT(u.first_name, ' ', u.last_name) AS user_name,
    a.allergen_name
FROM user_allergens ua
JOIN users u
    ON ua.user_id = u.user_id
JOIN allergens a
    ON ua.allergen_id = a.allergen_id
ORDER BY
    u.last_name,
    u.first_name,
    a.allergen_name;


-- =========================================================
-- 12. USER DIETARY PREFERENCES
--
-- Checks dietary preferences saved through user accounts.
-- =========================================================

SELECT
    CONCAT(u.first_name, ' ', u.last_name) AS user_name,
    c.category_name AS dietary_preference
FROM user_dietary_preferences udp
JOIN users u
    ON udp.user_id = u.user_id
JOIN categories c
    ON udp.category_id = c.category_id
ORDER BY
    u.last_name,
    u.first_name,
    c.category_name;