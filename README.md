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
5. Visit `http://localhost/CSCK543_EMA_GroupE/` in Chrome — you should see "Setup working" with a recipe count.

If you see a DB connection error, check `includes/db.php` matches your local MySQL
username/password (default XAMPP is `root` / no password — most people won't need to change anything).

## Project structure

- `index.php`, `login.php`, `register.php`, etc. — top-level pages (these become your URLs)
- `includes/` — shared PHP: DB connection, auth helpers, header/footer templates
- `assets/` — CSS, JS, images
- `database/` — `schema.sql` (table structure) and `seed.sql` (sample data incl. required BBC recipes)

## Workflow

- Everyone works on the same folder structure locally via their own XAMPP.
- Create a branch per feature (`git checkout -b feature/search-page`), push, open a PR to merge into `main`.
- If you change the DB structure, update `database/schema.sql` and mention it to the team so everyone re-imports.
