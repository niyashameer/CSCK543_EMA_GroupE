<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireGuest();

$errors = [];
$firstName = '';
$lastName  = '';
$email     = '';
$selectedDietary  = [];
$selectedAllergens = [];

$dietaryOptions = $pdo->query(
    "SELECT category_id, category_name FROM categories WHERE category_type = 'Dietary' ORDER BY category_name"
)->fetchAll();

$allergenOptions = $pdo->query(
    "SELECT allergen_id, allergen_name FROM allergens ORDER BY allergen_name"
)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $selectedDietary   = array_map('intval', $_POST['dietary'] ?? []);
    $selectedAllergens = array_map('intval', $_POST['allergens'] ?? []);

    if ($firstName === '' || mb_strlen($firstName) > 50) {
        $errors[] = 'Please enter a first name (up to 50 characters).';
    }
    if ($lastName === '' || mb_strlen($lastName) > 50) {
        $errors[] = 'Please enter a last name (up to 50 characters).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email address already exists.';
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                $firstName,
                $lastName,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
            ]);
            $newUserId = (int) $pdo->lastInsertId();

            if (!empty($selectedDietary)) {
                $stmt = $pdo->prepare(
                    'INSERT INTO user_dietary_preferences (user_id, category_id) VALUES (?, ?)'
                );
                foreach ($selectedDietary as $categoryId) {
                    $stmt->execute([$newUserId, $categoryId]);
                }
            }

            if (!empty($selectedAllergens)) {
                $stmt = $pdo->prepare(
                    'INSERT INTO user_allergens (user_id, allergen_id) VALUES (?, ?)'
                );
                foreach ($selectedAllergens as $allergenId) {
                    $stmt->execute([$newUserId, $allergenId]);
                }
            }

            $pdo->commit();

            setFlash('success', 'Account created. You can now log in.');
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) {
                $errors[] = 'An account with that email address already exists.';
            } else {
                error_log($e->getMessage());
                $errors[] = 'Something went wrong creating your account. Please try again.';
            }
        }
    }
}

$pageTitle = 'Register';
require_once 'includes/header.php';
?>

<h1>Create an account</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error" role="alert">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="register.php" class="form-card" id="register-form" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">

    <div class="form-row">
        <label for="first_name">First name</label>
        <input type="text" id="first_name" name="first_name" maxlength="50" required
               value="<?= htmlspecialchars($firstName) ?>">
    </div>

    <div class="form-row">
        <label for="last_name">Last name</label>
        <input type="text" id="last_name" name="last_name" maxlength="50" required
               value="<?= htmlspecialchars($lastName) ?>">
    </div>

    <div class="form-row">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" required
               value="<?= htmlspecialchars($email) ?>">
    </div>

    <div class="form-row">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" minlength="8" required
               aria-describedby="password-hint">
        <small id="password-hint">At least 8 characters.</small>
    </div>

    <div class="form-row">
        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
        <small id="confirm-password-error" class="field-error" hidden>Passwords do not match.</small>
    </div>

    <fieldset class="form-row">
        <legend>Dietary preferences (optional)</legend>
        <?php foreach ($dietaryOptions as $option): ?>
            <label class="checkbox-label">
                <input type="checkbox" name="dietary[]" value="<?= (int) $option['category_id'] ?>"
                    <?= in_array((int) $option['category_id'], $selectedDietary, true) ? 'checked' : '' ?>>
                <?= htmlspecialchars($option['category_name']) ?>
            </label>
        <?php endforeach; ?>
    </fieldset>

    <fieldset class="form-row">
        <legend>Allergens to avoid (optional)</legend>
        <?php foreach ($allergenOptions as $option): ?>
            <label class="checkbox-label">
                <input type="checkbox" name="allergens[]" value="<?= (int) $option['allergen_id'] ?>"
                    <?= in_array((int) $option['allergen_id'], $selectedAllergens, true) ? 'checked' : '' ?>>
                <?= htmlspecialchars($option['allergen_name']) ?>
            </label>
        <?php endforeach; ?>
    </fieldset>

    <button type="submit" class="btn btn-primary">Register</button>
</form>

<p>Already have an account? <a href="login.php">Log in</a></p>

<?php require_once 'includes/footer.php'; ?>
