<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Recipe App' : 'Recipe App'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<a href="#main-content" class="skip-link">Skip to main content</a>
<header class="site-header">
    <a href="index.php" class="logo">Recipe App</a>
    <button type="button" id="nav-toggle" class="nav-toggle" aria-expanded="false" aria-controls="site-nav">
        <span class="sr-only">Menu</span>
        <span aria-hidden="true">&#9776;</span>
    </button>
    <nav id="site-nav">
        <?php $currentPage = basename($_SERVER['SCRIPT_NAME']); ?>
        <a href="index.php" <?= $currentPage === 'index.php' ? 'aria-current="page"' : '' ?>>Home</a>
        <a href="search.php" <?= $currentPage === 'search.php' ? 'aria-current="page"' : '' ?>>Search</a>
        <?php if (isLoggedIn()): ?>
            <a href="account.php" <?= $currentPage === 'account.php' ? 'aria-current="page"' : '' ?>>Account</a>
            <a href="logout.php">Log out</a>
        <?php else: ?>
            <a href="login.php" <?= $currentPage === 'login.php' ? 'aria-current="page"' : '' ?>>Log in</a>
            <a href="register.php" <?= $currentPage === 'register.php' ? 'aria-current="page"' : '' ?>>Register</a>
        <?php endif; ?>
    </nav>
</header>
<main class="site-main" id="main-content">

