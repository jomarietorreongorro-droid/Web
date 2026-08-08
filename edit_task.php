<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$pdo = getDbConnection();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$id, currentUserId()]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    flash('Task not found.', 'error');
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $status = $_POST['status'] ?? 'pending';
    $dueDate = trim($_POST['due_date'] ?? '');

    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if (!in_array($priority, ['low', 'medium', 'high'], true)) {
        $priority = 'medium';
    }
    if (!in_array($status, ['pending', 'in_progress', 'completed'], true)) {
        $status = 'pending';
    }
    if ($dueDate !== '' && !DateTime::createFromFormat('Y-m-d', $dueDate)) {
        $errors[] = 'Due date is not valid.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'UPDATE tasks SET title = :title, description = :description, priority = :priority,
             status = :status, due_date = :due_date, updated_at = datetime("now")
             WHERE id = :id AND user_id = :uid'
        );
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'status' => $status,
            'due_date' => $dueDate !== '' ? $dueDate : null,
            'id' => $id,
            'uid' => currentUserId(),
        ]);
        flash('Task updated.');
        header('Location: index.php');
        exit;
    }

    // Keep submitted values on validation failure
    $task = array_merge($task, compact('title', 'description', 'priority', 'status', 'dueDate'));
    $task['due_date'] = $dueDate;
}

$pageTitle = 'Edit Task';
require __DIR__ . '/includes/header.php';
?>

<div class="form-card">
    <h1>Edit Task</h1>
    <?php foreach ($errors as $err): ?>
        <div class="flash flash-error"><?= e($err) ?></div>
    <?php endforeach; ?>
    <form method="post" action="edit_task.php?id=<?= (int) $task['id'] ?>">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
        <label>Title
            <input type="text" name="title" value="<?= e($task['title']) ?>" required autofocus maxlength="255">
        </label>
        <label>Description
            <textarea name="description" rows="4"><?= e($task['description']) ?></textarea>
        </label>
        <div class="form-row">
            <label>Status
                <select name="status">
                    <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>In progress</option>
                    <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </label>
            <label>Priority
                <select name="priority">
                    <option value="low" <?= $task['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                    <option value="medium" <?= $task['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="high" <?= $task['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                </select>
            </label>
            <label>Due date
                <input type="date" name="due_date" value="<?= e($task['due_date'] ?? '') ?>">
            </label>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="index.php" class="btn-link">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
