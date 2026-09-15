<?php
/**
 * Basic session helpers. Include this on any page that needs to know
 * whether a user is logged in, or that should require login.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,   // JS can't read the session cookie
        'samesite' => 'Lax',  // blocks the cookie on most cross-site requests (CSRF defence in depth)
        'secure'   => false,  // set to true once the site is served over HTTPS
    ]);
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function currentUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Redirects already-logged-in users away from guest-only pages
 * (login.php, register.php) so they can't "re-register" over an
 * active session.
 */
function requireGuest(): void
{
    if (isLoggedIn()) {
        header('Location: account.php');
        exit;
    }
}

/**
 * Fetches the full row for the currently logged-in user.
 * Returns null if nobody is logged in.
 */
function currentUser(PDO $pdo): ?array
{
    $userId = currentUserId();
    if ($userId === null) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    return $user ?: null;
}

/* -------------------------------------------------------------
 * CSRF protection
 *
 * One token per session. Every form that POSTs should include:
 *   <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
 * and every POST handler should call verifyCsrf() before doing
 * anything with the submitted data.
 * ----------------------------------------------------------- */

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $submitted)) {
        http_response_code(403);
        die('Your session expired or the form was submitted incorrectly. Please go back and try again.');
    }
}

/**
 * Sets a one-time flash message, read and cleared by flash().
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Reads and clears the flash message, if any.
 * Returns null if there isn't one.
 */
function flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
