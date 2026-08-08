<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$pdo = getDbConnection();
$userId = currentUserId();

// --- Filters ---
$status   = $_GET['status'] ?? '';
$priority = $_GET['priority'] ?? '';
$search   = trim($_GET['q'] ?? '');
$sort     = $_GET['sort'] ?? 'due_date';

$validStatuses  = ['', 'pending', 'in_progress', 'completed'];
$validPriorities = ['', 'low', 'medium', 'high'];
$validSorts = ['due_date', 'created_at', 'priority', 'title'];

if (!in_array($status, $validStatuses, true)) $status = '';
if (!in_array($priority, $validPriorities, true)) $priority = '';
if (!in_array($sort, $validSorts, true)) $sort = 'due_date';

$sql = 'SELECT * FROM tasks WHERE user_id = :uid';
$params = ['uid' => $userId];

if ($status !== '') {
    $sql .= ' AND status = :status';
    $params['status'] = $status;
}
if ($priority !== '') {
    $sql .= ' AND priority = :priority';
    $params['priority'] = $priority;
}
if ($search !== '') {
    $sql .= ' AND (title LIKE :search OR description LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

$sql .= match ($sort) {
    'priority'   => " ORDER BY CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END",
    'title'      => ' ORDER BY title COLLATE NOCASE ASC',
    'created_at' => ' ORDER BY created_at DESC',
    default      => " ORDER BY (due_date IS NULL), due_date ASC",
};

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Quick stats
$statsStmt = $pdo->prepare('SELECT status, COUNT(*) as c FROM tasks WHERE user_id = ? GROUP BY status');
$statsStmt->execute([$userId]);
$stats = ['pending' => 0, 'in_progress' => 0, 'completed' => 0];
foreach ($statsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $stats[$row['status']] = (int) $row['c'];
}

$pageTitle = 'My Tasks';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>My Tasks</h1>
    <a href="add_task.php" class="btn btn-primary">+ New Task</a>
</div>

<div class="stats-row">
    <div class="stat-chip"><span class="dot dot-pending"></span> Pending: <?= $stats['pending'] ?></div>
    <div class="stat-chip"><span class="dot dot-progress"></span> In progress: <?= $stats['in_progress'] ?></div>
    <div class="stat-chip"><span class="dot dot-done"></span> Completed: <?= $stats['completed'] ?></div>
</div>

<form method="get" action="index.php" class="filter-bar">
    <input type="text" name="q" placeholder="Search tasks…" value="<?= e($search) ?>">

    <select name="status">
        <option value="">All statuses</option>
        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>In progress</option>
        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
    </select>

    <select name="priority">
        <option value="">All priorities</option>
        <option value="high" <?= $priority === 'high' ? 'selected' : '' ?>>High</option>
        <option value="medium" <?= $priority === 'medium' ? 'selected' : '' ?>>Medium</option>
        <option value="low" <?= $priority === 'low' ? 'selected' : '' ?>>Low</option>
    </select>

    <select name="sort">
        <option value="due_date" <?= $sort === 'due_date' ? 'selected' : '' ?>>Sort: Due date</option>
        <option value="priority" <?= $sort === 'priority' ? 'selected' : '' ?>>Sort: Priority</option>
        <option value="created_at" <?= $sort === 'created_at' ? 'selected' : '' ?>>Sort: Newest</option>
        <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Sort: Title</option>
    </select>

    <button type="submit" class="btn">Filter</button>
    <?php if ($status || $priority || $search): ?>
        <a href="index.php" class="btn-link">Clear</a>
    <?php endif; ?>
</form>

<?php if (!$tasks): ?>
    <div class="empty-state">
        <p>No tasks match here.</p>
        <a href="add_task.php" class="btn btn-primary">Create your first task</a>
    </div>
<?php else: ?>
    <ul class="task-list">
        <?php foreach ($tasks as $task): ?>
            <li class="task-card status-<?= e($task['status']) ?> <?= isOverdue($task['due_date'], $task['status']) ? 'overdue' : '' ?>">
                <form method="post" action="toggle_status.php" class="task-toggle">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
                    <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI']) ?>">
                    <button type="submit" class="check-btn <?= $task['status'] === 'completed' ? 'checked' : '' ?>" title="Toggle complete">
                        <?= $task['status'] === 'completed' ? '✓' : '' ?>
                    </button>
                </form>

                <div class="task-body">
                    <div class="task-top">
                        <a href="edit_task.php?id=<?= (int) $task['id'] ?>" class="task-title"><?= e($task['title']) ?></a>
                        <span class="badge badge-<?= e($task['priority']) ?>"><?= e(ucfirst($task['priority'])) ?></span>
                    </div>
                    <?php if ($task['description']): ?>
                        <p class="task-desc"><?= nl2br(e($task['description'])) ?></p>
                    <?php endif; ?>
                    <div class="task-meta">
                        <span class="status-tag status-tag-<?= e($task['status']) ?>">
                            <?= e(str_replace('_', ' ', ucfirst($task['status']))) ?>
                        </span>
                        <?php if ($task['due_date']): ?>
                            <span class="due-date <?= isOverdue($task['due_date'], $task['status']) ? 'overdue-text' : '' ?>">
                                Due <?= e(date('M j, Y', strtotime($task['due_date']))) ?>
                                <?= isOverdue($task['due_date'], $task['status']) ? ' (overdue)' : '' ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="task-actions">
                    <a href="edit_task.php?id=<?= (int) $task['id'] ?>" class="btn-link">Edit</a>
                    <form method="post" action="delete_task.php" onsubmit="return confirm('Delete this task? This cannot be undone.');">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
                        <button type="submit" class="btn-link btn-danger">Delete</button>
                    </form>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
