<?php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

if ($_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

$limit = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Get total count
$totalCount = $pdo->query("SELECT COUNT(*) FROM logs")->fetchColumn();
$totalPages = ceil($totalCount / $limit);

// Fetch logs with user info, limited and paginated
$stmt = $pdo->prepare("SELECT l.*, u.full_name FROM logs l JOIN users u ON l.user_id = u.user_id ORDER BY l.timestamp DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

include '../../includes/header.php';
?>

<style>
table { border-collapse: collapse; width: 100%; }
th, td { padding: 8px; border: 1px solid #ccc; }
thead { background-color: #f4f4f4; }
.pagination { margin-top: 20px; }
.pagination a {
    padding: 6px 12px;
    margin: 0 2px;
    border: 1px solid #ccc;
    text-decoration: none;
    color: #333;
}
.pagination a.active {
    background-color: #007bff;
    color: white;
    pointer-events: none;
}
</style>

<h2>Activity Logs</h2>

<table>
    <thead>
        <tr>
            <th>Timestamp</th>
            <th>User</th>
            <th>Action</th>
            <th>Target ID</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$logs): ?>
        <tr><td colspan="5">No logs found.</td></tr>
    <?php else: ?>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= $log['timestamp'] ?></td>
            <td><?= htmlspecialchars($log['full_name']) ?></td>
            <td><?= htmlspecialchars($log['action']) ?></td>
            <td><?= htmlspecialchars($log['target_id']) ?></td>
            <td><?= htmlspecialchars($log['description']) ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<div class="pagination">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a href="?page=<?= $p ?>" class="<?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
</div>

<p><a href="../dashboard/index.php">Back to Dashboard</a></p>

<?php include '../../includes/footer.php'; ?>
