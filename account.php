<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$userId = currentUserId();
$user   = currentUser($pdo);

$profileErrors  = [];
$passwordErrors = [];

$dietaryOptions = $pdo->query(
    "SELECT category_id, category_name FROM categories WHERE category_type = 'Dietary' ORDER BY category_name"
)->fetchAll();

$allergenOptions = $pdo->query(
    "SELECT allergen_id, allergen_name FROM allergens ORDER BY allergen_name"
)->fetchAll();

function loadSelectedIds(PDO $pdo, string $table, string $column, int $userId): array
{
    $stmt = $pdo->prepare("SELECT {$column} FROM {$table} WHERE user_id = ?");
    $stmt->execute([$userId]);
    return array_map('intval', array_column($stmt->fetchAll(), $column));
}

$selectedDietary   = loadSelectedIds($pdo, 'user_dietary_preferences', 'category_id', $userId);
$selectedAllergens = loadSelectedIds($pdo, 'user_allergens', 'allergen_id', $userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $selectedDietary   = array_map('intval', $_POST['dietary'] ?? []);
        $selectedAllergens = array_map('intval', $_POST['allergens'] ?? []);

        if ($firstName === '' || mb_strlen($firstName) > 50) {
            $profileErrors[] = 'Please enter a first name (up to 50 characters).';
        }
        if ($lastName === '' || mb_strlen($lastName) > 50) {
            $profileErrors[] = 'Please enter a last name (up to 50 characters).';
        }

        if (empty($profileErrors)) {
            $pdo->beginTransaction();

            $pdo->prepare('UPDATE users SET first_name = ?, last_name = ? WHERE user_id = ?')
                ->execute([$firstName, $lastName, $userId]);

            $pdo->prepare('DELETE FROM user_dietary_preferences WHERE user_id = ?')->execute([$userId]);
            if (!empty($selectedDietary)) {
                $stmt = $pdo->prepare('INSERT INTO user_dietary_preferences (user_id, category_id) VALUES (?, ?)');
                foreach ($selectedDietary as $categoryId) {
                    $stmt->execute([$userId, $categoryId]);
                }
            }

            $pdo->prepare('DELETE FROM user_allergens WHERE user_id = ?')->execute([$userId]);
            if (!empty($selectedAllergens)) {
                $stmt = $pdo->prepare('INSERT INTO user_allergens (user_id, allergen_id) VALUES (?, ?)');
                foreach ($selectedAllergens as $allergenId) {
                    $stmt->execute([$userId, $allergenId]);
                }
            }

            $pdo->commit();

            $_SESSION['first_name'] = $firstName;
            setFlash('success', 'Profile updated.');
            header('Location: account.php');
            exit;
        }

        $user['first_name'] = $firstName;
        $user['last_name']  = $lastName;
    }

    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPassword, $user['password_hash'])) {
            $passwordErrors[] = 'Current password is incorrect.';
        }
        if (strlen($newPassword) < 8) {
            $passwordErrors[] = 'New password must be at least 8 characters long.';
        }
        if ($newPassword !== $confirmPassword) {
            $passwordErrors[] = 'New passwords do not match.';
        }

        if (empty($passwordErrors)) {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?')
                ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);

            setFlash('success', 'Password changed.');
            header('Location: account.php');
            exit;
        }
    }

    if ($action === 'remove_favourite') {
        $recipeId = (int) ($_POST['recipe_id'] ?? 0);
        $pdo->prepare('DELETE FROM favourites WHERE user_id = ? AND recipe_id = ?')
            ->execute([$userId, $recipeId]);

        setFlash('success', 'Removed from favourites.');
        header('Location: account.php');
        exit;
    }
}

$stmt = $pdo->prepare(
    'SELECT r.recipe_id, r.title, r.difficulty, r.prep_time_minutes, r.cook_time_minutes,
            ROUND(AVG(rt.overall_rating), 1) AS avg_rating,
            f.created_at
     FROM favourites f
     JOIN recipes r ON r.recipe_id = f.recipe_id
     LEFT JOIN ratings rt ON rt.recipe_id = r.recipe_id
     WHERE f.user_id = ?
     GROUP BY r.recipe_id, r.title, r.difficulty, r.prep_time_minutes,
              r.cook_time_minutes, f.created_at
     ORDER BY f.created_at DESC'
);
$stmt->execute([$userId]);
$favourites = $stmt->fetchAll();

$flash = flash();
$pageTitle = 'Account';
require_once 'includes/header.php';
?>

<h1>Your account</h1>

<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" role="status">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<section class="account-section">
    <h2>Profile details</h2>

    <?php if (!empty($profileErrors)): ?>
        <div class="alert alert-error" role="alert">
            <ul>
                <?php foreach ($profileErrors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="account.php" class="form-card">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
        <input type="hidden" name="action" value="update_profile">

        <div class="form-row">
            <label for="first_name">First name</label>
            <input type="text" id="first_name" name="first_name" maxlength="50" required
                   value="<?= htmlspecialchars($user['first_name']) ?>">
        </div>

        <div class="form-row">
            <label for="last_name">Last name</label>
            <input type="text" id="last_name" name="last_name" maxlength="50" required
                   value="<?= htmlspecialchars($user['last_name']) ?>">
        </div>

        <div class="form-row">
            <label>Email address</label>
            <p><?= htmlspecialchars($user['email']) ?></p>
        </div>

        <fieldset class="form-row">
            <legend>Dietary preferences</legend>
            <?php foreach ($dietaryOptions as $option): ?>
                <label class="checkbox-label">
                    <input type="checkbox" name="dietary[]" value="<?= (int) $option['category_id'] ?>"
                        <?= in_array((int) $option['category_id'], $selectedDietary, true) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($option['category_name']) ?>
                </label>
            <?php endforeach; ?>
        </fieldset>

        <fieldset class="form-row">
            <legend>Allergens to avoid</legend>
            <?php foreach ($allergenOptions as $option): ?>
                <label class="checkbox-label">
                    <input type="checkbox" name="allergens[]" value="<?= (int) $option['allergen_id'] ?>"
                        <?= in_array((int) $option['allergen_id'], $selectedAllergens, true) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($option['allergen_name']) ?>
                </label>
            <?php endforeach; ?>
        </fieldset>

        <button type="submit" class="btn btn-primary">Save changes</button>
    </form>
</section>

<section class="account-section">
    <h2>Change password</h2>

    <?php if (!empty($passwordErrors)): ?>
        <div class="alert alert-error" role="alert">
            <ul>
                <?php foreach ($passwordErrors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="account.php" class="form-card" id="password-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
        <input type="hidden" name="action" value="change_password">

        <div class="form-row">
            <label for="current_password">Current password</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>

        <div class="form-row">
            <label for="new_password">New password</label>
            <input type="password" id="new_password" name="new_password" minlength="8" required>
        </div>

        <div class="form-row">
            <label for="confirm_password">Confirm new password</label>
            <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
            <small id="confirm-password-error" class="field-error" hidden>Passwords do not match.</small>
        </div>

        <button type="submit" class="btn btn-secondary">Change password</button>
    </form>
</section>

<section class="account-section">
    <h2>Your favourite recipes</h2>

    <?php if (empty($favourites)): ?>
        <p>You haven't saved any favourites yet. <a href="index.php">Find a recipe</a> to get started.</p>
    <?php else: ?>
        <ul class="favourites-list">
            <?php foreach ($favourites as $recipe): ?>
                <li class="favourite-card">
                    <div>
                        <a href="recipe.php?id=<?= (int) $recipe['recipe_id'] ?>">
                            <?= htmlspecialchars($recipe['title']) ?>
                        </a>
                        <p class="favourite-meta">
                            <?= htmlspecialchars($recipe['difficulty']) ?>
                            &middot; <?= (int) $recipe['prep_time_minutes'] + (int) $recipe['cook_time_minutes'] ?> mins total
                            <?php if ($recipe['avg_rating'] !== null): ?>
                                &middot; <?= htmlspecialchars($recipe['avg_rating']) ?>/5 rating
                            <?php endif; ?>
                        </p>
                    </div>
                    <form method="POST" action="account.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                        <input type="hidden" name="action" value="remove_favourite">
                        <input type="hidden" name="recipe_id" value="<?= (int) $recipe['recipe_id'] ?>">
                        <button type="submit" class="btn btn-link">Remove</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
