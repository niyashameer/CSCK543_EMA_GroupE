# Recipe Web App — CSCK543 Group Assignment

PHP + MySQL + HTML5/CSS3/JS recipe search app, no frontend frameworks, targeting XAMPP + Google Chrome.

## Getting set up (every team member does this once)

1. Install [XAMPP](https://www.apachefriends.org/) (PHP 8.x bundle).
2. Clone this repo **directly inside your `htdocs` folder**, so the folder name matches:
   - Windows: `C:\xampp\htdocs\CSCK543_EMA_GroupE`
   - Mac: `/Applications/XAMPP/xamppfiles/htdocs/CSCK543_EMA_GroupE`
   ```bash
   cd C:\xampp\htdocs
   git clone <repo-url> CSCK543_EMA_GroupE
   ```
3. Open the **XAMPP Control Panel** and start **Apache** and **MySQL**.
4. Go to `http://localhost/phpmyadmin/`, click **Import**, and import `database/schema.sql`.
   Then import `database/seed.sql` the same way to load sample recipes.
5. Visit `http://localhost/CSCK543_EMA_GroupE/` in Chrome — you should see the recipe search page with results.

If you see a DB connection error, check `includes/db.php` matches your local MySQL
username/password (default XAMPP is `root` / no password — most people won't need to change anything).

## Pages

- `index.php` — recipe search. Filters: title/description text, ingredient contains,
  categories, difficulty, max total time, minimum rating, and "hide recipes containing
  [allergen]" (auto-suggested from your saved allergens if logged in). Sort by title,
  time, rating, popularity, difficulty, or newest. All filters run as real SQL and
  combine together in one query.
- `recipe.php?id=N` — recipe detail: ingredients (grouped by section), method with
  per-step timing, categories, average rating, link back to the original BBC Food page,
  a favourite toggle, and a rating form (overall + taste/difficulty/presentation + review).
- `register.php` / `login.php` / `logout.php` — account creation and sessions. Registration
  also captures optional dietary preferences and allergens.
- `account.php` — edit profile details and dietary/allergen preferences, change password,
  view and remove saved favourites.

## Project structure

- `index.php`, `login.php`, `register.php`, `recipe.php`, `account.php`, `logout.php`
  — top-level pages (these become your URLs)
- `includes/` — shared PHP:
  - `db.php` — PDO connection
  - `auth.php` — session helpers (`isLoggedIn`, `requireLogin`, `requireGuest`,
    `currentUser`), CSRF protection (`csrfToken`/`verifyCsrf`), and flash messages
    (`setFlash`/`flash`)
  - `recipes_data.php` — all recipe search/detail SQL lives here (`getRecipes`,
    `getRecipeById`, `getAllCategoriesGrouped`, `getAllAllergens`, `getUserAllergenIds`)
    so top-level pages never write raw SQL themselves
  - `header.php` / `footer.php` — shared page chrome, including the mobile nav
- `assets/` — `css/style.css` (responsive, mobile-first, with a skip link, visible
  focus states, and `aria-current` nav highlighting) and `js/main.js` (mobile nav
  toggle, password-match validation, live client-side search/sort)
- `database/` — `schema.sql` (table structure) and `seed.sql` (sample data incl. the
  required BBC recipes — currently 5 of the 8 listed in the brief; happy to add the
  remaining 3 — Couscous Salad, Plum Clafoutis, Mango Pie — if you want all 8)

## Security notes

- Passwords hashed with `password_hash()`/`password_verify()` (bcrypt)
- Every form has a CSRF token, checked with `hash_equals()`
- All queries use PDO prepared statements — no string-concatenated SQL
- `session_regenerate_id()` on login to prevent session fixation
- Session cookies are `httponly` + `SameSite=Lax` (flip `secure` to `true` once on HTTPS)

## Workflow

- Everyone works on the same folder structure locally via their own XAMPP.
- Create a branch per feature (`git checkout -b feature/search-page`), push, open a PR to merge into `main`.
- If you change the DB structure, update `database/schema.sql` and mention it to the team so everyone re-imports.
