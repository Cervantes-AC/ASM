<?php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

// Only allow admin access
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard/index.php?error=Access denied");
    exit;
}

// Pagination setup
$limit = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Total log count for pagination
$totalCount = $pdo->query("SELECT COUNT(*) FROM logs")->fetchColumn();
$totalPages = max(1, ceil($totalCount / $limit));

// Fetch logs with user info (paginated)
$stmt = $pdo->prepare("
    SELECT l.*, u.full_name 
    FROM logs l 
    LEFT JOIN users u ON l.user_id = u.user_id 
    ORDER BY l.timestamp DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/header.php';
?>

<style>
table {
    border-collapse: collapse;
    width: 100%;
}
th, td {
    padding: 8px;
    border: 1px solid #ccc;
}
thead {
    background-color: #f4f4f4;
}
.pagination {
    margin-top: 20px;
    text-align: center;
}
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
    <?php if (empty($logs)): ?>
        <tr><td colspan="5">No logs found.</td></tr>
    <?php else: ?>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= htmlspecialchars($log['timestamp']) ?></td>
            <td><?= htmlspecialchars($log['full_name'] ?? 'Unknown') ?></td>
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

<p><a href="../dashboard/index.php">← Back to Dashboard</a></p>

<?php include '../../includes/footer.php'; ?>
