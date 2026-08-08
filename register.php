<?php
require_once __DIR__ . '/includes/functions.php';

if (currentUserId()) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($username === '' || strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = 'That username is already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
            $stmt->execute([$username, $hash]);

            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            flash('Welcome to TaskFlow, ' . $username . '!');
            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle = 'Sign up';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-card">
    <h1>Create your account</h1>
    <?php foreach ($errors as $err): ?>
        <div class="flash flash-error"><?= e($err) ?></div>
    <?php endforeach; ?>
    <form method="post" action="register.php">
        <?= csrfField() ?>
        <label>Username
            <input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" required minlength="3" autofocus>
        </label>
        <label>Password
            <input type="password" name="password" required minlength="6">
        </label>
        <label>Confirm password
            <input type="password" name="confirm_password" required minlength="6">
        </label>
        <button type="submit" class="btn btn-primary">Sign up</button>
    </form>
    <p class="auth-switch">Already have an account? <a href="login.php">Log in</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
