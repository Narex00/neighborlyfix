<?php
/**
 * Administrator interface for issue management and filtering.
 */
$page_title = 'Manage Issues';
require_once '../includes/admin_check.php';

$pdo = getDBConnection();

$status   = $_GET['status'] ?? '';
$priority = $_GET['priority'] ?? '';
$category = (int)($_GET['category'] ?? 0);
$search   = trim($_GET['search'] ?? '');
$page_num = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 15;
$offset   = ($page_num - 1) * $perPage;

$where = ["1=1"];
$params = [];

if (in_array($status, ['open','in_progress','resolved','closed'])) { $where[] = "i.status=?"; $params[] = $status; }
if (in_array($priority, ['low','medium','high','critical'])) { $where[] = "i.priority=?"; $params[] = $priority; }
if ($category > 0) { $where[] = "i.category_id=?"; $params[] = $category; }
if ($search !== '') { $where[] = "(i.title LIKE ? OR i.description LIKE ? OR u.full_name LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }

$whereSQL = implode(' AND ', $where);
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM issues i JOIN users u ON i.user_id=u.id WHERE $whereSQL");
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

$stmt = $pdo->prepare("SELECT i.*, c.name as category_name, u.full_name as reporter_name FROM issues i JOIN categories c ON i.category_id=c.id JOIN users u ON i.user_id=u.id WHERE $whereSQL ORDER BY i.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$issues = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories WHERE is_active=1 ORDER BY name")->fetchAll();

require_once '../includes/header.php';
?>

<section class="py-4">
<div class="container">
    <h3 class="fw-bold mb-4"><i class="fas fa-tasks me-2 text-primary"></i>Manage Issues</h3>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="<?= sanitize($search) ?>" placeholder="Title, description, reporter…">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="open" <?= $status==='open'?'selected':'' ?>>Open</option>
                        <option value="in_progress" <?= $status==='in_progress'?'selected':'' ?>>In Progress</option>
                        <option value="resolved" <?= $status==='resolved'?'selected':'' ?>>Resolved</option>
                        <option value="closed" <?= $status==='closed'?'selected':'' ?>>Closed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Priority</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="low" <?= $priority==='low'?'selected':'' ?>>Low</option>
                        <option value="medium" <?= $priority==='medium'?'selected':'' ?>>Medium</option>
                        <option value="high" <?= $priority==='high'?'selected':'' ?>>High</option>
                        <option value="critical" <?= $priority==='critical'?'selected':'' ?>>Critical</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $category===$c['id']?'selected':'' ?>><?= sanitize($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <p class="text-muted small mb-2">Showing <?= count($issues) ?> of <?= $totalRows ?> issues</p>

    <div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Title</th><th class="d-none d-md-table-cell">Reporter</th><th class="d-none d-lg-table-cell">Category</th><th>Priority</th><th>Status</th><th class="d-none d-lg-table-cell">Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if (empty($issues)): ?>
                <tr><td colspan="8" class="text-center py-4 text-muted">No issues found.</td></tr>
            <?php else: foreach($issues as $r): ?>
            <tr>
                <td class="fw-semibold"><?= $r['id'] ?></td>
                <td><?= sanitize(mb_strimwidth($r['title'],0,50,'…')) ?></td>
                <td class="d-none d-md-table-cell"><small><?= sanitize($r['reporter_name']) ?></small></td>
                <td class="d-none d-lg-table-cell"><small><?= sanitize($r['category_name']) ?></small></td>
                <td><?= priority_badge($r['priority']) ?></td>
                <td><?= status_badge($r['status']) ?></td>
                <td class="d-none d-lg-table-cell"><small class="text-muted"><?= time_ago($r['created_at']) ?></small></td>
                <td class="text-nowrap">
                    <a href="<?= base_url('view_issue.php?id='.$r['id']) ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                    <a href="<?= base_url('admin/update_issue.php?id='.$r['id']) ?>" class="btn btn-sm btn-outline-warning" title="Update"><i class="fas fa-edit"></i></a>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination pagination-sm justify-content-center">
            <?php for ($i=1;$i<=$totalPages;$i++): ?>
            <li class="page-item <?= $i===$page_num?'active':'' ?>">
                <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&priority=<?= urlencode($priority) ?>&category=<?= $category ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>
</section>

<?php require_once '../includes/footer.php'; ?>