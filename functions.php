<?php
require_once __DIR__ . '/../config.php';

session_start();

function currentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function requireLogin(): void {
    if (!currentUserId()) {
        header('Location: login.php');
        exit;
    }
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function flash(string $message, string $type = 'success'): void {
    $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
}

function getFlashes(): array {
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function verifyCsrf(): void {
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die('Invalid or expired form submission. Please go back and try again.');
    }
}

function csrfField(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token']) . '">';
}

function isOverdue(?string $dueDate, string $status): bool {
    if (!$dueDate || $status === 'completed') {
        return false;
    }
    return strtotime($dueDate) < strtotime(date('Y-m-d'));
}
