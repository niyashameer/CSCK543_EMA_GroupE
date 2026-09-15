<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$pageTitle = 'Search Recipes';
require __DIR__ . '/header.php';
?>

<h1>Search recipes</h1>
<p>
    Recipe search and browsing will go here. This page is a placeholder so
    the navigation links in <code>header.php</code> resolve while the
    authentication feature is being built — the search/sort functionality
    is a separate task.
</p>

<?php require __DIR__ . '/footer.php'; ?>
