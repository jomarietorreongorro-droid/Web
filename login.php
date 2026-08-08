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

    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: index.php');
        exit;
    }

    $errors[] = 'Invalid username or password.';
}

$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-card">
    <h1>Welcome back</h1>
    <?php foreach ($errors as $err): ?>
        <div class="flash flash-error"><?= e($err) ?></div>
    <?php endforeach; ?>
    <form method="post" action="login.php">
        <?= csrfField() ?>
        <label>Username
            <input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" required autofocus>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn btn-primary">Log in</button>
    </form>
    <p class="auth-switch">Don't have an account? <a href="register.php">Sign up</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
