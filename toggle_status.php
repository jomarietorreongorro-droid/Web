<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

verifyCsrf();

$id = (int) ($_POST['id'] ?? 0);
$redirect = $_POST['redirect'] ?? 'index.php';

$pdo = getDbConnection();
$stmt = $pdo->prepare('SELECT status FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$id, currentUserId()]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if ($task) {
    $newStatus = $task['status'] === 'completed' ? 'pending' : 'completed';
    $stmt = $pdo->prepare('UPDATE tasks SET status = ?, updated_at = datetime("now") WHERE id = ? AND user_id = ?');
    $stmt->execute([$newStatus, $id, currentUserId()]);
}

// Prevent open-redirect: only allow relative paths within this app
if (!preg_match('#^[a-zA-Z0-9_./?=&%-]+$#', $redirect) || str_contains($redirect, '://')) {
    $redirect = 'index.php';
}

header('Location: ' . $redirect);
exit;
