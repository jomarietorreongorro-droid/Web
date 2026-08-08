<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

verifyCsrf();

$id = (int) ($_POST['id'] ?? 0);
$pdo = getDbConnection();
$stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$id, currentUserId()]);

flash('Task deleted.');
header('Location: index.php');
exit;
