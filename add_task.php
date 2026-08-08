<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$errors = [];
$title = '';
$description = '';
$priority = 'medium';
$dueDate = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $dueDate = trim($_POST['due_date'] ?? '');

    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if (!in_array($priority, ['low', 'medium', 'high'], true)) {
        $priority = 'medium';
    }
    if ($dueDate !== '' && !DateTime::createFromFormat('Y-m-d', $dueDate)) {
        $errors[] = 'Due date is not valid.';
    }

    if (!$errors) {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO tasks (user_id, title, description, priority, due_date, status)
             VALUES (:uid, :title, :description, :priority, :due_date, :status)'
        );
        $stmt->execute([
            'uid' => currentUserId(),
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'due_date' => $dueDate !== '' ? $dueDate : null,
            'status' => 'pending',
        ]);
        flash('Task created.');
        header('Location: index.php');
        exit;
    }
}

$pageTitle = 'New Task';
require __DIR__ . '/includes/header.php';
?>

<div class="form-card">
    <h1>New Task</h1>
    <?php foreach ($errors as $err): ?>
        <div class="flash flash-error"><?= e($err) ?></div>
    <?php endforeach; ?>
    <form method="post" action="add_task.php">
        <?= csrfField() ?>
        <label>Title
            <input type="text" name="title" value="<?= e($title) ?>" required autofocus maxlength="255">
        </label>
        <label>Description
            <textarea name="description" rows="4"><?= e($description) ?></textarea>
        </label>
        <div class="form-row">
            <label>Priority
                <select name="priority">
                    <option value="low" <?= $priority === 'low' ? 'selected' : '' ?>>Low</option>
                    <option value="medium" <?= $priority === 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="high" <?= $priority === 'high' ? 'selected' : '' ?>>High</option>
                </select>
            </label>
            <label>Due date
                <input type="date" name="due_date" value="<?= e($dueDate) ?>">
            </label>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Task</button>
            <a href="index.php" class="btn-link">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
