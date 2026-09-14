-- =========================================================
-- 13. TEST QUERIES
--
-- These queries allow the inserted data to be checked.
-- =========================================================


-- Check recipes.
SELECT *
FROM recipes;


-- Check categories assigned to each recipe.
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


-- Check ingredients for each recipe.
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


-- Check recipe steps.
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


-- Check allergen mappings.
SELECT
    i.ingredient_name,
    a.allergen_name

FROM ingredients i

JOIN ingredient_allergens ia
    ON i.ingredient_id = ia.ingredient_id

JOIN allergens a
    ON ia.allergen_id = a.allergen_id

ORDER BY
    i.ingredient_name;