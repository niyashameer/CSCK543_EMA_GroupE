<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$pageTitle = 'Search Recipes';

// Sanity check: count recipes in the DB so we know PHP + MySQL are wired up.
$recipeCount = 0;
try {
    $stmt = $pdo->query('SELECT COUNT(*) AS total FROM recipes');
    $recipeCount = $stmt->fetch()['total'];
} catch (PDOException $e) {
    $recipeCount = null; // Table probably doesn't exist yet .
}

require_once 'includes/header.php';
?>

<h1>Recipe Web App</h1>

<?php if ($recipeCount === null): ?>
    <p>Connected to PHP, but the <code>recipes</code> table wasn't found yet.
       Import <code>database/schema.sql</code> in phpMyAdmin, then refresh this page.</p>
<?php else: ?>
    <p>Setup working. PHP is connected to MySQL and found
       <strong><?php echo (int) $recipeCount; ?></strong> recipe(s) in the database.</p>
<?php endif; ?>

<p>This is your starting point &mdash; build the real search form and results here.</p>

<?php require_once 'includes/footer.php'; ?>
