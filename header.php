<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' · ' : '' ?>TaskFlow</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
    <div class="topbar-inner">
        <a href="index.php" class="brand">TaskFlow</a>
        <nav>
            <?php if (currentUserId()): ?>
                <a href="index.php">My Tasks</a>
                <a href="add_task.php">+ New Task</a>
                <span class="user-pill"><?= e($_SESSION['username']) ?></span>
                <a href="logout.php" class="btn-link">Log out</a>
            <?php else: ?>
                <a href="login.php">Log in</a>
                <a href="register.php">Sign up</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
<?php foreach (getFlashes() as $f): ?>
    <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>
