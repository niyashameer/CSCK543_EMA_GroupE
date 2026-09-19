# Recipe Web App - CSCK543 Group Assignment

A dynamic recipe web application developed using PHP, MySQL, HTML5, CSS3
and JavaScript.

The application allows users to browse recipes, search and filter
recipes using multiple criteria, create an account, save favourites,
store dietary and allergen preferences, and submit recipe ratings and
reviews.

The project was developed for use with XAMPP and Google Chrome without
the use of frontend frameworks.

## GitHub Repository

https://github.com/niyashameer/CSCK543_EMA_GroupE/

## Getting Started

### 1. Install XAMPP

Install XAMPP with PHP 8.x:

https://www.apachefriends.org/

### 2. Place the project in `htdocs`

Clone or extract the project directly into the XAMPP `htdocs` directory.

Windows:

``` text
C:\xampp\htdocs\CSCK543_EMA_GroupE
```

Mac:

``` text
/Applications/XAMPP/xamppfiles/htdocs/CSCK543_EMA_GroupE
```

Example using Git:

``` bash
cd C:\xampp\htdocs
git clone https://github.com/niyashameer/CSCK543_EMA_GroupE.git CSCK543_EMA_GroupE
```

### 3. Start the required services

Open the XAMPP Control Panel and start Apache.

Start or connect to the MySQL server used by the application.

### 4. Create the database

The `database` directory contains:

-   `schema.sql` - creates the database tables, relationships,
    constraints and indexes.
-   `seed.sql` - inserts the sample recipe, category, ingredient,
    allergen and recipe-step data.
-   `test_queries.sql` - contains queries used to verify the database
    and its relationships.
-   `recipe_app_dump.sql` - complete MySQL dump containing the final
    database structure and data.

The complete database can be recreated by importing:

``` text
database/recipe_app_dump.sql
```

Alternatively, import `schema.sql` followed by `seed.sql`.

### 5. Configure the database connection

The PDO database connection is configured in:

``` text
includes/db.php
```

Ensure that the database username and password match the local MySQL
configuration.

A standard XAMPP installation commonly uses:

``` text
Username: root
Password: [empty]
```

Local MySQL configurations may differ.

### 6. Open the application

Open the following address in Google Chrome:

``` text
http://localhost/CSCK543_EMA_GroupE/
```

## Main Pages

### Home

`index.php`

Provides the main landing page and displays the available recipes using
reusable recipe cards.

### Search

`search.php`

Allows recipes to be searched, filtered and sorted.

Available criteria include:

-   Recipe title or description
-   Ingredient
-   Category
-   Recipe difficulty
-   Maximum total preparation and cooking time
-   Minimum average rating
-   Allergen exclusion

Logged-in users can also use their saved allergen preferences when
filtering recipes.

Recipes can be sorted by:

-   Title
-   Total time
-   Average rating
-   Popularity
-   Difficulty

Multiple filters can be combined within the same search.

### Recipe Details

`recipe.php?id=N`

Displays the complete information for an individual recipe, including:

-   Description and image
-   Categories
-   Preparation time
-   Cooking time
-   Total time
-   Servings
-   Difficulty
-   Ingredients and quantities
-   Ingredient sections
-   Ordered preparation steps
-   Time required for individual steps
-   Original recipe source
-   Average user rating
-   Rating breakdown
-   User reviews

Logged-in users can save or remove the recipe from their favourites and
submit or update a rating.

Ratings consist of three dimensions:

-   Taste
-   Ease of preparation
-   Presentation

Each dimension is rated from 1 to 5. For ease of preparation, 1
represents difficult and 5 represents easy.

A user's overall rating is calculated automatically as the mean of the
three rating dimensions. The overall recipe rating is calculated from
submitted user ratings rather than being stored separately in the
database.

### Authentication

`register.php`, `login.php` and `logout.php`

Users can create an account, log in and log out.

Registration also allows users to save dietary preferences and
allergens.

### Account

`account.php`

Logged-in users can:

-   Edit their profile information
-   Update dietary preferences
-   Update allergen preferences
-   Change their password
-   View saved favourite recipes
-   Remove recipes from their favourites

## Project Structure

``` text
CSCK543_EMA_GroupE/
|
|-- index.php
|-- search.php
|-- recipe.php
|-- register.php
|-- login.php
|-- logout.php
|-- account.php
|
|-- includes/
|   |-- db.php
|   |-- auth.php
|   |-- recipes_data.php
|   |-- recipe_card.php
|   |-- header.php
|   `-- footer.php
|
|-- assets/
|   |-- css/
|   |   `-- style.css
|   |-- js/
|   |   `-- main.js
|   `-- images/
|       `-- recipes/
|
`-- database/
    |-- schema.sql
    |-- seed.sql
    |-- test_queries.sql
    `-- recipe_app_dump.sql
```

## Shared PHP Components

### `includes/db.php`

Creates the PDO connection to the MySQL database.

### `includes/auth.php`

Contains authentication and session-related functionality, including:

-   Login state helpers
-   Access control
-   Current-user information
-   CSRF protection
-   Flash messages

### `includes/recipes_data.php`

Contains the shared database queries used for recipe functionality,
including:

-   Recipe retrieval
-   Search and filtering
-   Sorting
-   Recipe details
-   Categories
-   Ingredients
-   Recipe steps
-   Allergens
-   Ratings and reviews

Keeping this logic in a shared file reduces duplicated database queries
across the application.

### `includes/recipe_card.php`

Reusable recipe-card component used to provide consistent recipe
presentation across the home and search pages.

### `includes/header.php` and `includes/footer.php`

Shared page structure used throughout the application, including
navigation and responsive mobile navigation.

## Frontend

### CSS

`assets/css/style.css`

Provides the application's responsive styling, including:

-   Mobile-first responsive layout
-   Recipe cards
-   Search interface
-   Recipe detail layout
-   Rating and review presentation
-   Visible keyboard focus states
-   Responsive navigation

### JavaScript

`assets/js/main.js`

Provides client-side behaviour including:

-   Mobile navigation
-   Password validation
-   Search-page enhancements
-   Search form submission behaviour
-   Preservation of search-page scroll position

Core recipe searching and filtering is performed server-side using PHP
and MySQL.

## Database

The relational database contains 13 tables:

1.  `users`
2.  `recipes`
3.  `categories`
4.  `recipe_categories`
5.  `ingredients`
6.  `recipe_ingredients`
7.  `allergens`
8.  `ingredient_allergens`
9.  `recipe_steps`
10. `ratings`
11. `favourites`
12. `user_dietary_preferences`
13. `user_allergens`

Junction tables are used to represent many-to-many relationships such as
recipes and categories, recipes and ingredients, users and dietary
preferences, and ingredients and allergens.

The sample data contains five recipes sourced from BBC Food:

-   Spaghetti bolognese with mushrooms and sun-dried tomatoes
-   Vegan pancakes
-   Healthy pizza
-   Easy lamb biryani
-   Mushroom doner

Recipe source links are retained within the database.

## Security

The application includes:

-   Password hashing using `password_hash()` and verification using
    `password_verify()`
-   PDO prepared statements for database queries
-   CSRF protection for forms
-   Session ID regeneration following authentication
-   HTTP-only session cookies
-   `SameSite=Lax` session-cookie configuration
-   Server-side validation of submitted data
-   HTML output escaping using `htmlspecialchars()`

## Development Workflow

Development was completed collaboratively using Git and GitHub.

Feature branches were used for separate areas of development before
changes were reviewed and merged into the main branch.

Database structure changes were maintained in `database/schema.sql`,
while `database/seed.sql` provides the sample recipe data required to
recreate the application database.
