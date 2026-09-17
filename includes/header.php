<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Recipe App' : 'Recipe App'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <a href="index.php" class="logo">Recipe App</a>
    <button type="button" id="nav-toggle" class="nav-toggle" aria-expanded="false" aria-controls="site-nav">
        <span class="sr-only">Menu</span>
        <span aria-hidden="true">&#9776;</span>
    </button>
    <nav id="site-nav">
        <a href="index.php">Search</a>
        <?php if (isLoggedIn()): ?>
            <a href="account.php">Account</a>
            <a href="logout.php">Log out</a>
        <?php else: ?>
            <a href="login.php">Log in</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main class="site-main">
