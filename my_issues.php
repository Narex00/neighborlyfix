<?php
$page_title = 'My Issues';
require_once 'includes/auth_check.php';
if (is_admin()) redirect(base_url('admin/'));

$pdo = getDBConnection();
$uid = current_user_id();

// Filters
$status   = $_GET['status'] ?? '';
$search   = trim($_GET['search'] ?? '');
$page_num = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 10;
$offset   = ($page_num - 1) * $perPage;

$where = ["i.user_id = ?"];
$params = [$uid];

if (in_array($status, ['open','in_progress','resolved','closed'])) {
    $where[] = "i.status = ?";
    $params[] = $status;
}
if ($search !== '') {
    $where[] = "(i.title LIKE ? OR i.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSQL = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM issues i WHERE $whereSQL");
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

$stmt = $pdo->prepare("SELECT i.*, c.name as category_name FROM issues i JOIN categories c ON i.category_id=c.id WHERE $whereSQL ORDER BY i.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$issues = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<section class="py-4">
<div class="container">
    <h3 class="fw-bold mb-4"><i class="fas fa-list-check me-2 text-primary"></i>My Issues</h3>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="<?= sanitize($search) ?>" placeholder="Search issues…">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="open" <?= $status==='open'?'selected':'' ?>>Open</option>
                        <option value="in_progress" <?= $status==='in_progress'?'selected':'' ?>>In Progress</option>
                        <option value="resolved" <?= $status==='resolved'?'selected':'' ?>>Resolved</option>
                        <option value="closed" <?= $status==='closed'?'selected':'' ?>>Closed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                </div>
                <div class="col-md-3 text-end">
                    <a href="<?= base_url('report_issue.php') ?>" class="btn btn-success btn-sm"><i class="fas fa-plus me-1"></i>New Issue</a>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($issues)): ?>
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted">No issues found.</p>
        </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Title</th><th class="d-none d-md-table-cell">Category</th><th>Priority</th><th>Status</th><th class="d-none d-lg-table-cell">Reported</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($issues as $r): ?>
                    <tr>
                        <td class="fw-semibold"><?= $r['id'] ?></td>
                        <td><?= sanitize($r['title']) ?></td>
                        <td class="d-none d-md-table-cell"><small><?= sanitize($r['category_name']) ?></small></td>
                        <td><?= priority_badge($r['priority']) ?></td>
                        <td><?= status_badge($r['status']) ?></td>
                        <td class="d-none d-lg-table-cell"><small class="text-muted"><?= time_ago($r['created_at']) ?></small></td>
                        <td><a href="<?= base_url('view_issue.php?id='.$r['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i===$page_num?'active':'' ?>">
                <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</div>
</section>

<?php require_once 'includes/footer.php'; ?>