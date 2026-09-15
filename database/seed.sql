-- =========================================================
-- Recipe Web App - Sample Data
--
-- Populates:
-- - categories
-- - allergens
-- - recipes
-- - ingredients
-- - recipe/category relationships
-- - recipe/ingredient relationships
-- - ingredient/allergen relationships
-- - recipe steps
--
-- This script assumes schema.sql has already been executed.
--
-- NOTE: fixed for MariaDB (XAMPP's default) compatibility.
-- MariaDB does not support naming a derived table's columns via
-- "... ) AS data (col1, col2, col3)" the way MySQL 8 does.
-- Instead, column names are given as aliases on the FIRST branch
-- of each UNION ALL, and the derived table is aliased with
-- "AS data" only (no column list).
-- =========================================================

USE recipe_app;


-- =========================================================
-- 1. CATEGORIES
-- =========================================================

INSERT INTO categories (category_name, category_type) VALUES

-- Course
('Main', 'Course'),
('Breakfast', 'Course'),

-- Dietary
('Vegan', 'Dietary'),
('Vegetarian', 'Dietary'),
('Dairy-free', 'Dietary'),
('Egg-free', 'Dietary'),
('Nut-free', 'Dietary'),
('Gluten-free', 'Dietary'),
('Healthy', 'Dietary'),
('Pregnancy-friendly', 'Dietary'),

-- Cuisine / application classifications
('Italian', 'Cuisine'),
('Indian', 'Cuisine'),
('Middle Eastern', 'Cuisine'),

-- Other
('Meat', 'Other');


-- =========================================================
-- 2. ALLERGENS
-- =========================================================

INSERT INTO allergens (allergen_name) VALUES
('Gluten'),
('Milk'),
('Soy'),
('Tree nuts'),
('Celery');


-- =========================================================
-- 3. RECIPES
-- =========================================================

INSERT INTO recipes (
    title,
    description,
    prep_time_minutes,
    cook_time_minutes,
    servings,
    difficulty,
    image_path,
    source_url
)
VALUES

(
    'Spaghetti bolognese with mushrooms and sun-dried tomatoes',
    'A rich spaghetti bolognese containing mushrooms, sun-dried tomatoes, herbs and beef.',
    30,
    120,
    8,
    'Medium',
    NULL,
    'https://www.bbc.co.uk/food/recipes/spaghettibolognese_67868'
),

(
    'Vegan pancakes',
    'Fluffy vegan pancakes suitable for breakfast and served with optional toppings.',
    30,
    30,
    2,
    'Easy',
    NULL,
    'https://www.bbc.co.uk/food/recipes/vegan_american_pancakes_76094'
),

(
    'Healthy pizza',
    'A quick vegetarian pizza with a yoghurt-based dough, roasted vegetables and cheese.',
    30,
    30,
    2,
    'Easy',
    NULL,
    'https://www.bbc.co.uk/food/recipes/healthy_pizza_55143'
),

(
    'Easy lamb biryani',
    'A layered lamb and basmati rice dish with herbs, spices and saffron.',
    480,
    120,
    8,
    'Medium',
    NULL,
    'https://www.bbc.co.uk/food/recipes/easy_lamb_biryani_46729'
),

(
    'Mushroom doner',
    'A vegetarian mushroom doner served in pitta bread with chilli sauce, yoghurt sauce and vegetables.',
    30,
    30,
    4,
    'Easy',
    NULL,
    'https://www.bbc.co.uk/food/recipes/mushroom_doner_22676'
);


-- =========================================================
-- 4. RECIPE CATEGORIES
-- =========================================================

-- ---------------------------------------------------------
-- Spaghetti Bolognese
-- ---------------------------------------------------------

INSERT INTO recipe_categories (recipe_id, category_id)
SELECT r.recipe_id, c.category_id
FROM recipes r
JOIN categories c
WHERE r.title =
    'Spaghetti bolognese with mushrooms and sun-dried tomatoes'
AND c.category_name IN (
    'Main',
    'Egg-free',
    'Nut-free',
    'Italian',
    'Meat'
);


-- ---------------------------------------------------------
-- Vegan Pancakes
-- ---------------------------------------------------------

INSERT INTO recipe_categories (recipe_id, category_id)
SELECT r.recipe_id, c.category_id
FROM recipes r
JOIN categories c
WHERE r.title = 'Vegan pancakes'
AND c.category_name IN (
    'Breakfast',
    'Vegan',
    'Vegetarian',
    'Dairy-free',
    'Egg-free',
    'Pregnancy-friendly'
);


-- ---------------------------------------------------------
-- Healthy Pizza
-- ---------------------------------------------------------

INSERT INTO recipe_categories (recipe_id, category_id)
SELECT r.recipe_id, c.category_id
FROM recipes r
JOIN categories c
WHERE r.title = 'Healthy pizza'
AND c.category_name IN (
    'Main',
    'Vegetarian',
    'Egg-free',
    'Healthy',
    'Nut-free',
    'Pregnancy-friendly',
    'Italian'
);


-- ---------------------------------------------------------
-- Easy Lamb Biryani
-- ---------------------------------------------------------

INSERT INTO recipe_categories (recipe_id, category_id)
SELECT r.recipe_id, c.category_id
FROM recipes r
JOIN categories c
WHERE r.title = 'Easy lamb biryani'
AND c.category_name IN (
    'Main',
    'Egg-free',
    'Gluten-free',
    'Pregnancy-friendly',
    'Indian',
    'Meat'
);


-- ---------------------------------------------------------
-- Mushroom Doner
-- ---------------------------------------------------------

INSERT INTO recipe_categories (recipe_id, category_id)
SELECT r.recipe_id, c.category_id
FROM recipes r
JOIN categories c
WHERE r.title = 'Mushroom doner'
AND c.category_name IN (
    'Main',
    'Vegetarian',
    'Egg-free',
    'Healthy',
    'Nut-free',
    'Pregnancy-friendly',
    'Middle Eastern'
);


-- =========================================================
-- 5. INGREDIENTS
-- =========================================================

INSERT INTO ingredients (ingredient_name) VALUES

-- Shared/common ingredients
('Olive oil'),
('Onion'),
('Garlic'),
('Sea salt'),
('Black pepper'),
('Caster sugar'),
('Dried oregano'),
('Fresh basil'),
('Yoghurt'),

-- Spaghetti Bolognese
('Smoked streaky bacon'),
('Lean minced beef'),
('Red wine'),
('Chopped tomatoes'),
('Marinated mushrooms'),
('Bay leaves'),
('Thyme'),
('Balsamic vinegar'),
('Sun-dried tomatoes'),
('Spaghetti'),
('Parmesan'),

-- Vegan Pancakes
('Self-raising flour'),
('Baking powder'),
('Soya milk'),
('Vanilla extract'),
('Sunflower oil'),

-- Healthy Pizza
('Self-raising wholemeal flour'),
('Pepper'),
('Courgette'),
('Red onion'),
('Dried chilli flakes'),
('Cheese'),
('Passata'),

-- Lamb Biryani
('Vegetable oil'),
('Ginger'),
('Kashmiri chilli powder'),
('Ground cumin'),
('Ground cardamom'),
('Lime'),
('Fresh coriander'),
('Fresh mint'),
('Green chillies'),
('Lamb'),
('Double cream'),
('Milk'),
('Saffron'),
('Basmati rice'),
('Pomegranate seeds'),

-- Mushroom Doner
('Rose harissa'),
('Lemon juice'),
('White wine vinegar'),
('Flatleaf parsley'),
('Dried mint'),
('Oyster mushrooms'),
('Garlic oil'),
('Paprika'),
('Ground coriander'),
('Celery salt'),
('Garlic granules'),
('Pitta bread'),
('White cabbage'),
('Tomatoes'),
('Pickled chillies');


-- =========================================================
-- 6. INGREDIENT ALLERGENS
-- =========================================================

INSERT INTO ingredient_allergens (ingredient_id, allergen_id)
SELECT i.ingredient_id, a.allergen_id
FROM ingredients i
JOIN allergens a
WHERE i.ingredient_name IN (
    'Self-raising flour',
    'Self-raising wholemeal flour',
    'Spaghetti',
    'Pitta bread'
)
AND a.allergen_name = 'Gluten';


INSERT INTO ingredient_allergens (ingredient_id, allergen_id)
SELECT i.ingredient_id, a.allergen_id
FROM ingredients i
JOIN allergens a
WHERE i.ingredient_name IN (
    'Yoghurt',
    'Parmesan',
    'Cheese',
    'Double cream',
    'Milk'
)
AND a.allergen_name = 'Milk';


INSERT INTO ingredient_allergens (ingredient_id, allergen_id)
SELECT i.ingredient_id, a.allergen_id
FROM ingredients i
JOIN allergens a
WHERE i.ingredient_name = 'Soya milk'
AND a.allergen_name = 'Soy';


INSERT INTO ingredient_allergens (ingredient_id, allergen_id)
SELECT i.ingredient_id, a.allergen_id
FROM ingredients i
JOIN allergens a
WHERE i.ingredient_name = 'Celery salt'
AND a.allergen_name = 'Celery';


-- =========================================================
-- 7. SPAGHETTI BOLOGNESE INGREDIENTS
-- =========================================================

INSERT INTO recipe_ingredients (
    recipe_id,
    ingredient_id,
    quantity,
    unit,
    notes,
    section_name,
    display_order
)

SELECT
    r.recipe_id,
    i.ingredient_id,
    data.quantity,
    data.unit,
    data.notes,
    NULL,
    data.display_order

FROM recipes r

JOIN (
    SELECT 'Olive oil' ingredient_name,
           '2' quantity, 'tbsp' unit, 'or sun-dried tomato oil' notes, 1 display_order

    UNION ALL
    SELECT 'Smoked streaky bacon',
           '6', 'rashers', 'chopped', 2

    UNION ALL
    SELECT 'Onion',
           '2', 'large', 'chopped', 3

    UNION ALL
    SELECT 'Garlic',
           '3', 'cloves', 'crushed', 4

    UNION ALL
    SELECT 'Lean minced beef',
           '1', 'kg', NULL, 5

    UNION ALL
    SELECT 'Red wine',
           '2', 'large glasses', NULL, 6

    UNION ALL
    SELECT 'Chopped tomatoes',
           '2 x 400', 'g cans', NULL, 7

    UNION ALL
    SELECT 'Marinated mushrooms',
           '290', 'g jar', 'drained', 8

    UNION ALL
    SELECT 'Bay leaves',
           '2', NULL, 'fresh or dried', 9

    UNION ALL
    SELECT 'Dried oregano',
           '1', 'tsp', NULL, 10

    UNION ALL
    SELECT 'Thyme',
           '1', 'tsp', 'dried or fresh', 11

    UNION ALL
    SELECT 'Balsamic vinegar',
           '1', 'drizzle', NULL, 12

    UNION ALL
    SELECT 'Sun-dried tomatoes',
           '12-14', 'halves', 'in oil', 13

    UNION ALL
    SELECT 'Sea salt',
           NULL, NULL, 'to taste', 14

    UNION ALL
    SELECT 'Black pepper',
           NULL, NULL, 'freshly ground', 15

    UNION ALL
    SELECT 'Fresh basil',
           '1', 'handful', 'torn', 16

    UNION ALL
    SELECT 'Spaghetti',
           '800g-1kg', NULL, 'dried', 17

    UNION ALL
    SELECT 'Parmesan',
           NULL, NULL, 'freshly grated, to serve', 18

) AS data

JOIN ingredients i
    ON i.ingredient_name = data.ingredient_name

WHERE r.title =
'Spaghetti bolognese with mushrooms and sun-dried tomatoes';


-- =========================================================
-- 8. VEGAN PANCAKE INGREDIENTS
-- =========================================================

INSERT INTO recipe_ingredients (
    recipe_id,
    ingredient_id,
    quantity,
    unit,
    notes,
    section_name,
    display_order
)

SELECT
    r.recipe_id,
    i.ingredient_id,
    data.quantity,
    data.unit,
    data.notes,
    NULL,
    data.display_order

FROM recipes r

JOIN (
    SELECT 'Self-raising flour' ingredient_name,
           '125' quantity, 'g' unit, NULL notes, 1 display_order

    UNION ALL
    SELECT 'Caster sugar',
           '2', 'tbsp', NULL, 2

    UNION ALL
    SELECT 'Baking powder',
           '1', 'tsp', NULL, 3

    UNION ALL
    SELECT 'Sea salt',
           '1', 'pinch', NULL, 4

    UNION ALL
    SELECT 'Soya milk',
           '150', 'ml', NULL, 5

    UNION ALL
    SELECT 'Vanilla extract',
           '1/4', 'tsp', NULL, 6

    UNION ALL
    SELECT 'Sunflower oil',
           '4', 'tsp', 'for frying', 7

) AS data

JOIN ingredients i
    ON i.ingredient_name = data.ingredient_name

WHERE r.title = 'Vegan pancakes';


-- =========================================================
-- 9. HEALTHY PIZZA INGREDIENTS
-- =========================================================

INSERT INTO recipe_ingredients (
    recipe_id,
    ingredient_id,
    quantity,
    unit,
    notes,
    section_name,
    display_order
)

SELECT
    r.recipe_id,
    i.ingredient_id,
    data.quantity,
    data.unit,
    data.notes,
    data.section_name,
    data.display_order

FROM recipes r

JOIN (

    SELECT 'Self-raising wholemeal flour' ingredient_name,
           '125' quantity, 'g' unit,
           'plus extra for dusting' notes,
           'For the base' section_name, 1 display_order

    UNION ALL
    SELECT 'Sea salt',
           '1', 'pinch',
           NULL,
           'For the base', 2

    UNION ALL
    SELECT 'Yoghurt',
           '125', 'g',
           'full-fat plain',
           'For the base', 3

    UNION ALL
    SELECT 'Pepper',
           '1', NULL,
           'yellow or orange, thinly sliced',
           'For the topping', 4

    UNION ALL
    SELECT 'Courgette',
           '1', NULL,
           'cut into slices',
           'For the topping', 5

    UNION ALL
    SELECT 'Red onion',
           '1', NULL,
           'cut into thin wedges',
           'For the topping', 6

    UNION ALL
    SELECT 'Olive oil',
           '1', 'tbsp',
           'plus extra for drizzling',
           'For the topping', 7

    UNION ALL
    SELECT 'Dried chilli flakes',
           '1/2', 'tsp',
           NULL,
           'For the topping', 8

    UNION ALL
    SELECT 'Cheese',
           '50', 'g',
           'mozzarella, cheddar or goats'' cheese',
           'For the topping', 9

    UNION ALL
    SELECT 'Black pepper',
           NULL, NULL,
           'freshly ground',
           'For the topping', 10

    UNION ALL
    SELECT 'Fresh basil',
           NULL, NULL,
           'optional, to serve',
           'For the topping', 11

    UNION ALL
    SELECT 'Passata',
           '6', 'tbsp',
           'approximately 100g',
           'For the tomato sauce', 12

    UNION ALL
    SELECT 'Dried oregano',
           '1', 'tsp',
           NULL,
           'For the tomato sauce', 13

) AS data

JOIN ingredients i
    ON i.ingredient_name = data.ingredient_name

WHERE r.title = 'Healthy pizza';


-- =========================================================
-- 10. EASY LAMB BIRYANI INGREDIENTS
-- =========================================================

INSERT INTO recipe_ingredients (
    recipe_id,
    ingredient_id,
    quantity,
    unit,
    notes,
    section_name,
    display_order
)

SELECT
    r.recipe_id,
    i.ingredient_id,
    data.quantity,
    data.unit,
    data.notes,
    NULL,
    data.display_order

FROM recipes r

JOIN (

    SELECT 'Vegetable oil' ingredient_name,
           '5' quantity, 'tbsp' unit, NULL notes, 1 display_order

    UNION ALL
    SELECT 'Onion',
           '2', NULL, 'finely sliced', 2

    UNION ALL
    SELECT 'Yoghurt',
           '200', 'g', 'Greek or natural', 3

    UNION ALL
    SELECT 'Ginger',
           '4', 'tbsp', 'finely grated', 4

    UNION ALL
    SELECT 'Garlic',
           '3', 'tbsp', 'finely grated', 5

    UNION ALL
    SELECT 'Kashmiri chilli powder',
           '1-2', 'tsp', NULL, 6

    UNION ALL
    SELECT 'Ground cumin',
           '5', 'tsp', NULL, 7

    UNION ALL
    SELECT 'Ground cardamom',
           '1', 'tsp', NULL, 8

    UNION ALL
    SELECT 'Sea salt',
           '4', 'tsp', NULL, 9

    UNION ALL
    SELECT 'Lime',
           '1', NULL, 'juice only', 10

    UNION ALL
    SELECT 'Fresh coriander',
           '30', 'g', 'finely chopped', 11

    UNION ALL
    SELECT 'Fresh mint',
           '30', 'g', 'finely chopped', 12

    UNION ALL
    SELECT 'Green chillies',
           '3-4', NULL, 'finely chopped', 13

    UNION ALL
    SELECT 'Lamb',
           '800', 'g',
           'boneless, cut into bite-sized pieces', 14

    UNION ALL
    SELECT 'Double cream',
           '4', 'tbsp', NULL, 15

    UNION ALL
    SELECT 'Milk',
           '1 1/2', 'tbsp', 'full-fat', 16

    UNION ALL
    SELECT 'Saffron',
           '1', 'tsp', 'strands', 17

    UNION ALL
    SELECT 'Basmati rice',
           '400', 'g', NULL, 18

    UNION ALL
    SELECT 'Pomegranate seeds',
           '2', 'tbsp', 'optional garnish', 19

) AS data

JOIN ingredients i
    ON i.ingredient_name = data.ingredient_name

WHERE r.title = 'Easy lamb biryani';


-- =========================================================
-- 11. MUSHROOM DONER INGREDIENTS
-- =========================================================

INSERT INTO recipe_ingredients (
    recipe_id,
    ingredient_id,
    quantity,
    unit,
    notes,
    section_name,
    display_order
)

SELECT
    r.recipe_id,
    i.ingredient_id,
    data.quantity,
    data.unit,
    data.notes,
    data.section_name,
    data.display_order

FROM recipes r

JOIN (

    SELECT 'Chopped tomatoes' ingredient_name,
           '400' quantity, 'g tin' unit,
           NULL notes,
           'For the chilli sauce' section_name, 1 display_order

    UNION ALL
    SELECT 'Rose harissa',
           '2', 'tbsp',
           NULL,
           'For the chilli sauce', 2

    UNION ALL
    SELECT 'Caster sugar',
           '2', 'tsp',
           NULL,
           'For the chilli sauce', 3

    UNION ALL
    SELECT 'Lemon juice',
           '1', 'squeeze',
           NULL,
           'For the chilli sauce', 4

    UNION ALL
    SELECT 'Onion',
           '1', NULL,
           'very thinly sliced',
           'For the onion', 5

    UNION ALL
    SELECT 'White wine vinegar',
           '2', 'tsp',
           NULL,
           'For the onion', 6

    UNION ALL
    SELECT 'Flatleaf parsley',
           '20', 'g',
           'finely chopped',
           'For the onion', 7

    UNION ALL
    SELECT 'Yoghurt',
           '150', 'g',
           'plain',
           'For the yoghurt sauce', 8

    UNION ALL
    SELECT 'Dried mint',
           '1', 'heaped tsp',
           NULL,
           'For the yoghurt sauce', 9

    UNION ALL
    SELECT 'Sea salt',
           NULL, NULL,
           'to taste',
           'For the yoghurt sauce', 10

    UNION ALL
    SELECT 'Black pepper',
           NULL, NULL,
           'freshly ground',
           'For the yoghurt sauce', 11

    UNION ALL
    SELECT 'Oyster mushrooms',
           '500', 'g',
           'thinly sliced',
           'For the doner', 12

    UNION ALL
    SELECT 'Garlic oil',
           '2', 'tsp',
           NULL,
           'For the doner', 13

    UNION ALL
    SELECT 'Paprika',
           '2', 'tsp',
           NULL,
           'For the doner', 14

    UNION ALL
    SELECT 'Ground coriander',
           '2', 'heaped tsp',
           NULL,
           'For the doner', 15

    UNION ALL
    SELECT 'Celery salt',
           '2', 'tsp',
           NULL,
           'For the doner', 16

    UNION ALL
    SELECT 'Garlic granules',
           '3', 'tsp',
           NULL,
           'For the doner', 17

    UNION ALL
    SELECT 'Pitta bread',
           '4', NULL,
           'white',
           'For the doner', 18

    UNION ALL
    SELECT 'White cabbage',
           '1/4', 'small',
           'finely shredded',
           'For the garnish', 19

    UNION ALL
    SELECT 'Tomatoes',
           '2', NULL,
           'sliced',
           'For the garnish', 20

    UNION ALL
    SELECT 'Pickled chillies',
           '4-6', NULL,
           'optional',
           'For the garnish', 21

) AS data

JOIN ingredients i
    ON i.ingredient_name = data.ingredient_name

WHERE r.title = 'Mushroom doner';


-- =========================================================
-- 12. RECIPE STEPS
-- =========================================================

-- ---------------------------------------------------------
-- Spaghetti Bolognese
-- ---------------------------------------------------------

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 1,
'Cook the bacon, onions and garlic, then brown the minced beef. Add the wine and reduce before adding the tomatoes, mushrooms, herbs and balsamic vinegar.',
20
FROM recipes
WHERE title =
'Spaghetti bolognese with mushrooms and sun-dried tomatoes';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 2,
'Prepare the sun-dried tomatoes, add them to the sauce, season and simmer gently until the sauce becomes rich and thick. Finish with basil.',
90
FROM recipes
WHERE title =
'Spaghetti bolognese with mushrooms and sun-dried tomatoes';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 3,
'Allow the sauce to settle while cooking the spaghetti. Drain the pasta and serve with the sauce, parmesan and black pepper.',
15
FROM recipes
WHERE title =
'Spaghetti bolognese with mushrooms and sun-dried tomatoes';


-- ---------------------------------------------------------
-- Vegan Pancakes
-- ---------------------------------------------------------

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 1,
'Mix the flour, sugar, baking powder and salt. Add the plant-based milk and vanilla and whisk until smooth.',
5
FROM recipes
WHERE title = 'Vegan pancakes';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 2,
'Heat a non-stick frying pan, add oil and coat the surface.',
3
FROM recipes
WHERE title = 'Vegan pancakes';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 3,
'Add portions of batter to the pan and spread each pancake to approximately 10cm in diameter.',
3
FROM recipes
WHERE title = 'Vegan pancakes';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 4,
'Cook until bubbles appear, flip and cook the other side until lightly golden.',
2
FROM recipes
WHERE title = 'Vegan pancakes';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 5,
'Keep cooked pancakes warm while repeating with the remaining batter, then serve with preferred toppings.',
10
FROM recipes
WHERE title = 'Vegan pancakes';


-- ---------------------------------------------------------
-- Healthy Pizza
-- ---------------------------------------------------------

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 1,
'Preheat the oven.',
5
FROM recipes
WHERE title = 'Healthy pizza';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 2,
'Combine the pepper, courgette, red onion and oil, season and roast the vegetables.',
15
FROM recipes
WHERE title = 'Healthy pizza';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 3,
'Combine the flour, salt, yoghurt and water to form the pizza dough, then knead briefly.',
5
FROM recipes
WHERE title = 'Healthy pizza';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 4,
'Roll the dough into a thin oval shape suitable for the baking tray.',
3
FROM recipes
WHERE title = 'Healthy pizza';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 5,
'Remove the roasted vegetables and bake the pizza base before turning it over.',
5
FROM recipes
WHERE title = 'Healthy pizza';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 6,
'Mix the passata and oregano, spread onto the base, add vegetables, chilli and cheese, then bake until cooked.',
10
FROM recipes
WHERE title = 'Healthy pizza';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 7,
'Season with black pepper, drizzle with olive oil and add basil if desired.',
2
FROM recipes
WHERE title = 'Healthy pizza';


-- ---------------------------------------------------------
-- Easy Lamb Biryani
-- ---------------------------------------------------------

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 1,
'Fry the sliced onions until lightly browned and crisp.',
18
FROM recipes
WHERE title = 'Easy lamb biryani';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 2,
'Combine half of the onions with yoghurt, ginger, garlic, spices, lime, herbs and chillies.',
5
FROM recipes
WHERE title = 'Easy lamb biryani';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 3,
'Coat the lamb in the marinade, cover and refrigerate.',
480
FROM recipes
WHERE title = 'Easy lamb biryani';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 4,
'Preheat the oven.',
5
FROM recipes
WHERE title = 'Easy lamb biryani';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 5,
'Warm the cream and milk with the saffron and leave to infuse.',
30
FROM recipes
WHERE title = 'Easy lamb biryani';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 6,
'Cook the basmati rice until just cooked but still firm, then drain.',
8
FROM recipes
WHERE title = 'Easy lamb biryani';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 7,
'Layer the lamb, rice, reserved onions, herbs and saffron mixture in a casserole.',
10
FROM recipes
WHERE title = 'Easy lamb biryani';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 8,
'Cover and bake, then allow the biryani to rest before serving. Garnish with pomegranate if desired.',
80
FROM recipes
WHERE title = 'Easy lamb biryani';


-- ---------------------------------------------------------
-- Mushroom Doner
-- ---------------------------------------------------------

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 1,
'Preheat the oven.',
5
FROM recipes
WHERE title = 'Mushroom doner';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 2,
'Heat the chopped tomatoes, harissa, sugar and lemon juice and reduce to form the chilli sauce.',
10
FROM recipes
WHERE title = 'Mushroom doner';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 3,
'Mix the sliced onion with white wine vinegar and parsley and set aside.',
3
FROM recipes
WHERE title = 'Mushroom doner';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 4,
'Combine the yoghurt and dried mint and season with salt and pepper.',
2
FROM recipes
WHERE title = 'Mushroom doner';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 5,
'Warm the pitta breads in the oven.',
5
FROM recipes
WHERE title = 'Mushroom doner';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 6,
'Dry-fry the mushrooms, add the seasonings and garlic oil, then add a little water and stir-fry briefly.',
5
FROM recipes
WHERE title = 'Mushroom doner';

INSERT INTO recipe_steps (recipe_id, step_number, instruction, duration_minutes)
SELECT recipe_id, 7,
'Split the pittas and fill with cabbage, tomato, onion and mushrooms. Finish with the chilli and yoghurt sauces.',
5
FROM recipes
WHERE title = 'Mushroom doner';