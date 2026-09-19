-- =========================================================
-- Recipe Web App Database
--
-- Creates the relational structure for:
-- - users
-- - recipes
-- - categories
-- - ingredients
-- - allergens
-- - recipe steps
-- - ratings
-- - favourites
-- - dietary preferences
--
-- Relationship comments are included beside each table to
-- explain whether the relationship is one-to-many or
-- many-to-many.
-- =========================================================


-- =========================================================
-- CREATE DATABASE
--
-- Drop the existing database first so that running this file
-- always creates a clean and reproducible database structure.
-- WARNING: this removes any existing data in recipe_app.
-- =========================================================

DROP DATABASE IF EXISTS recipe_app;

CREATE DATABASE recipe_app;

USE recipe_app;


-- =========================================================
-- USERS
--
-- Stores account information for registered users.
-- Passwords should be stored as hashes rather than plain text.
--
-- Relationships:
--
-- users -> ratings
-- One-to-many:
-- one user can create many ratings, but each rating belongs
-- to one user.
--
-- users -> favourites
-- One-to-many:
-- one user can save many favourite recipes.
--
-- users -> user_dietary_preferences
-- One-to-many:
-- one user can have multiple dietary preference records.
--
-- users -> user_allergens
-- One-to-many:
-- one user can have multiple allergen records.
-- =========================================================

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,

    first_name VARCHAR(50) NOT NULL,

    last_name VARCHAR(50) NOT NULL,

    email VARCHAR(255) NOT NULL UNIQUE,

    password_hash VARCHAR(255) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================================
-- RECIPES
--
-- Stores the main information associated with each recipe.
--
-- Prep and cook time are stored separately so that they can
-- be searched independently or combined when required.
--
-- Relationships:
--
-- recipes -> recipe_categories
-- One-to-many:
-- one recipe can have many category links.
--
-- recipes -> recipe_ingredients
-- One-to-many:
-- one recipe can contain many ingredient records.
--
-- recipes -> recipe_steps
-- One-to-many:
-- one recipe can contain many ordered cooking steps.
--
-- recipes -> ratings
-- One-to-many:
-- one recipe can receive many ratings.
--
-- recipes -> favourites
-- One-to-many:
-- one recipe can be saved by many users.
-- =========================================================

CREATE TABLE recipes (
    recipe_id INT AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(150) NOT NULL,

    description TEXT,

    prep_time_minutes INT NOT NULL,

    cook_time_minutes INT NOT NULL,

    servings INT,

    difficulty ENUM(
        'Easy',
        'Medium',
        'Hard'
    ) NOT NULL DEFAULT 'Easy',

    image_path VARCHAR(255),

    source_url VARCHAR(500),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================================
-- CATEGORIES
--
-- Stores reusable recipe classifications.
--
-- category_type separates different kinds of classification,
-- such as:
-- - Course
-- - Dietary
-- - Cuisine
-- - Other
--
-- Relationships:
--
-- categories -> recipe_categories
-- One-to-many:
-- one category can be linked to many recipe_category records.
--
-- categories -> user_dietary_preferences
-- One-to-many:
-- one dietary category can be selected by many users.
--
-- Overall:
--
-- recipes <-> categories
-- Many-to-many:
-- one recipe can have many categories and one category can
-- belong to many recipes.
--
-- This many-to-many relationship is resolved through the
-- recipe_categories junction table.
-- =========================================================

CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,

    category_name VARCHAR(100) NOT NULL,

    category_type ENUM(
        'Course',
        'Dietary',
        'Cuisine',
        'Other'
    ) NOT NULL DEFAULT 'Other',

    UNIQUE (
        category_name,
        category_type
    )
);


-- =========================================================
-- RECIPE CATEGORIES
--
-- Junction table connecting recipes and categories.
--
-- Relationship:
--
-- recipes <-> categories
-- Many-to-many.
--
-- One recipe can belong to many categories.
-- One category can be used by many recipes.
--
-- Internally this becomes:
--
-- recipes -> recipe_categories
-- One-to-many.
--
-- categories -> recipe_categories
-- One-to-many.
-- =========================================================

CREATE TABLE recipe_categories (
    recipe_id INT NOT NULL,

    category_id INT NOT NULL,

    PRIMARY KEY (
        recipe_id,
        category_id
    ),

    FOREIGN KEY (recipe_id)
        REFERENCES recipes(recipe_id)
        ON DELETE CASCADE,

    FOREIGN KEY (category_id)
        REFERENCES categories(category_id)
        ON DELETE CASCADE
);


-- =========================================================
-- INGREDIENTS
--
-- Stores each ingredient once so that the same ingredient
-- can be reused across multiple recipes.
--
-- Relationships:
--
-- ingredients -> recipe_ingredients
-- One-to-many:
-- one ingredient can appear in many recipe ingredient records.
--
-- ingredients -> ingredient_allergens
-- One-to-many:
-- one ingredient can be linked to multiple allergens.
--
-- Overall:
--
-- recipes <-> ingredients
-- Many-to-many:
-- one recipe contains many ingredients and one ingredient
-- can appear in many recipes.
--
-- This is resolved through recipe_ingredients.
-- =========================================================

CREATE TABLE ingredients (
    ingredient_id INT AUTO_INCREMENT PRIMARY KEY,

    ingredient_name VARCHAR(150) NOT NULL UNIQUE
);


-- =========================================================
-- RECIPE INGREDIENTS
--
-- Junction table connecting recipes and ingredients.
--
-- Relationship:
--
-- recipes <-> ingredients
-- Many-to-many.
--
-- One recipe can contain many ingredients.
-- One ingredient can be used by many recipes.
--
-- Internally:
--
-- recipes -> recipe_ingredients
-- One-to-many.
--
-- ingredients -> recipe_ingredients
-- One-to-many.
--
-- This table also stores values that belong specifically to
-- the ingredient within a particular recipe, such as:
-- - quantity
-- - unit
-- - preparation notes
-- - ingredient section
-- - display order
-- =========================================================

CREATE TABLE recipe_ingredients (
    recipe_ingredient_id INT AUTO_INCREMENT PRIMARY KEY,

    recipe_id INT NOT NULL,

    ingredient_id INT NOT NULL,

    -- VARCHAR allows values such as:
    -- 1/2
    -- 1 1/2
    -- "to taste"
    -- "a handful"
    quantity VARCHAR(50),

    unit VARCHAR(50),

    -- Examples:
    -- finely chopped
    -- crushed
    -- sliced
    notes VARCHAR(255),

    -- Allows grouping such as:
    -- "For the sauce"
    -- "For the dough"
    section_name VARCHAR(100),

    -- Keeps ingredients in the intended display order.
    display_order INT NOT NULL,

    FOREIGN KEY (recipe_id)
        REFERENCES recipes(recipe_id)
        ON DELETE CASCADE,

    FOREIGN KEY (ingredient_id)
        REFERENCES ingredients(ingredient_id)
        ON DELETE CASCADE,

    UNIQUE (
        recipe_id,
        display_order
    )
);


-- =========================================================
-- ALLERGENS
--
-- Stores reusable allergen classifications.
--
-- Examples:
-- - Gluten
-- - Milk
-- - Eggs
-- - Peanuts
-- - Tree nuts
-- - Sesame
--
-- Relationships:
--
-- allergens -> ingredient_allergens
-- One-to-many:
-- one allergen can be linked to many ingredients.
--
-- allergens -> user_allergens
-- One-to-many:
-- one allergen can be associated with many users.
--
-- Overall:
--
-- ingredients <-> allergens
-- Many-to-many:
-- one ingredient may contain multiple allergens and one
-- allergen may occur in many ingredients.
--
-- This is resolved through ingredient_allergens.
-- =========================================================

CREATE TABLE allergens (
    allergen_id INT AUTO_INCREMENT PRIMARY KEY,

    allergen_name VARCHAR(100) NOT NULL UNIQUE
);


-- =========================================================
-- INGREDIENT ALLERGENS
--
-- Junction table connecting ingredients and allergens.
--
-- Relationship:
--
-- ingredients <-> allergens
-- Many-to-many.
--
-- One ingredient can be associated with many allergens.
-- One allergen can occur in many ingredients.
--
-- Internally:
--
-- ingredients -> ingredient_allergens
-- One-to-many.
--
-- allergens -> ingredient_allergens
-- One-to-many.
-- =========================================================

CREATE TABLE ingredient_allergens (
    ingredient_id INT NOT NULL,

    allergen_id INT NOT NULL,

    PRIMARY KEY (
        ingredient_id,
        allergen_id
    ),

    FOREIGN KEY (ingredient_id)
        REFERENCES ingredients(ingredient_id)
        ON DELETE CASCADE,

    FOREIGN KEY (allergen_id)
        REFERENCES allergens(allergen_id)
        ON DELETE CASCADE
);


-- =========================================================
-- RECIPE STEPS
--
-- Stores each cooking instruction as a separate ordered step.
--
-- Relationship:
--
-- recipes -> recipe_steps
-- One-to-many.
--
-- One recipe can contain many steps.
-- Each step belongs to one recipe.
--
-- step_number preserves the correct cooking order.
-- duration_minutes stores the time associated with each step.
-- =========================================================

CREATE TABLE recipe_steps (
    step_id INT AUTO_INCREMENT PRIMARY KEY,

    recipe_id INT NOT NULL,

    step_number INT NOT NULL,

    instruction TEXT NOT NULL,

    duration_minutes INT NOT NULL,

    FOREIGN KEY (recipe_id)
        REFERENCES recipes(recipe_id)
        ON DELETE CASCADE,

    -- Prevents two steps within one recipe having the same
    -- step number.
    UNIQUE (
        recipe_id,
        step_number
    )
);


-- =========================================================
-- RATINGS
--
-- Stores ratings submitted by users for recipes.
--
-- Users rate each recipe across three criteria:
-- - taste
-- - ease of preparation
-- - presentation
--
-- Each criterion uses a 1-5 scale where a higher score is
-- more positive. For difficulty_rating specifically:
-- 1 represents difficult and 5 represents easy.
--
-- An overall rating is not stored because it can be derived
-- from the three component ratings:
--
-- (taste + ease + presentation) / 3
--
-- Avoiding a stored overall value prevents duplicated data
-- and ensures that the overall score always remains
-- consistent with its component ratings.
--
-- Relationships:
--
-- users -> ratings
-- One-to-many:
-- one user can create many ratings.
--
-- recipes -> ratings
-- One-to-many:
-- one recipe can receive many ratings.
--
-- Overall:
--
-- users <-> recipes
-- Many-to-many through ratings.
--
-- One user can rate many recipes.
-- One recipe can be rated by many users.
--
-- The UNIQUE constraint ensures that each user can submit
-- only one rating for a particular recipe.
-- =========================================================

CREATE TABLE ratings (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    recipe_id INT NOT NULL,

    -- 1 = poor, 5 = excellent.
    taste_rating TINYINT NOT NULL,

    -- Stored as difficulty_rating for consistency with the
    -- existing schema, but presented to users as "Ease of
    -- preparation":
    -- 1 = difficult, 5 = easy.
    difficulty_rating TINYINT NOT NULL,

    -- 1 = poor, 5 = excellent.
    presentation_rating TINYINT NOT NULL,

    review TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE,

    FOREIGN KEY (recipe_id)
        REFERENCES recipes(recipe_id)
        ON DELETE CASCADE,

    UNIQUE (
        user_id,
        recipe_id
    ),

    CHECK (
        taste_rating BETWEEN 1 AND 5
    ),

    CHECK (
        difficulty_rating BETWEEN 1 AND 5
    ),

    CHECK (
        presentation_rating BETWEEN 1 AND 5
    )
);


-- =========================================================
-- FAVOURITES
--
-- Junction table connecting users and saved recipes.
--
-- Relationship:
--
-- users <-> recipes
-- Many-to-many through favourites.
--
-- One user can favourite many recipes.
-- One recipe can be favourited by many users.
--
-- Internally:
--
-- users -> favourites
-- One-to-many.
--
-- recipes -> favourites
-- One-to-many.
--
-- The composite primary key prevents the same user from
-- favouriting the same recipe more than once.
-- =========================================================

CREATE TABLE favourites (
    user_id INT NOT NULL,

    recipe_id INT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (
        user_id,
        recipe_id
    ),

    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE,

    FOREIGN KEY (recipe_id)
        REFERENCES recipes(recipe_id)
        ON DELETE CASCADE
);


-- =========================================================
-- USER DIETARY PREFERENCES
--
-- Junction table connecting users to dietary categories.
--
-- Relationship:
--
-- users <-> dietary categories
-- Many-to-many.
--
-- One user can have several dietary preferences.
-- One dietary category can be selected by many users.
--
-- Internally:
--
-- users -> user_dietary_preferences
-- One-to-many.
--
-- categories -> user_dietary_preferences
-- One-to-many.
--
-- Application logic should restrict category choices here
-- to records where category_type = 'Dietary'.
-- =========================================================

CREATE TABLE user_dietary_preferences (
    user_id INT NOT NULL,

    category_id INT NOT NULL,

    PRIMARY KEY (
        user_id,
        category_id
    ),

    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE,

    FOREIGN KEY (category_id)
        REFERENCES categories(category_id)
        ON DELETE CASCADE
);


-- =========================================================
-- USER ALLERGENS
--
-- Junction table connecting users and allergens.
--
-- Relationship:
--
-- users <-> allergens
-- Many-to-many.
--
-- One user can have multiple allergens.
-- One allergen can be associated with many users.
--
-- Internally:
--
-- users -> user_allergens
-- One-to-many.
--
-- allergens -> user_allergens
-- One-to-many.
--
-- This can later be used to remove unsuitable recipes from
-- search results or highlight possible allergens.
-- =========================================================

CREATE TABLE user_allergens (
    user_id INT NOT NULL,

    allergen_id INT NOT NULL,

    PRIMARY KEY (
        user_id,
        allergen_id
    ),

    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE,

    FOREIGN KEY (allergen_id)
        REFERENCES allergens(allergen_id)
        ON DELETE CASCADE
);


-- =========================================================
-- INDEXES
--
-- Indexes improve lookup performance on fields that are
-- likely to be used frequently for searching and filtering.
--
-- Primary keys and UNIQUE constraints already create indexes,
-- so additional indexes are only added where useful.
-- =========================================================


-- Speeds up title-based recipe searches.
CREATE INDEX idx_recipes_title
ON recipes(title);


-- Speeds up filtering by difficulty.
CREATE INDEX idx_recipes_difficulty
ON recipes(difficulty);


-- Speeds up preparation-time filtering.
CREATE INDEX idx_recipes_prep_time
ON recipes(prep_time_minutes);


-- Speeds up cooking-time filtering.
CREATE INDEX idx_recipes_cook_time
ON recipes(cook_time_minutes);


-- Helps retrieve categories by classification type.
CREATE INDEX idx_categories_type
ON categories(category_type);


-- Speeds up ingredient-name searching.
CREATE INDEX idx_ingredients_name
ON ingredients(ingredient_name);


-- Helps when calculating or retrieving ratings for a recipe.
CREATE INDEX idx_ratings_recipe
ON ratings(recipe_id);