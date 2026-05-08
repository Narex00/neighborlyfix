<?php
/**
 * Administrator dashboard with system overview and statistics.
 */
$page_title = 'Admin Dashboard';
require_once '../includes/admin_check.php';

$pdo = getDBConnection();
$totalIssues = $pdo->query("SELECT COUNT(*) FROM issues")->fetchColumn();
$openCount   = $pdo->query("SELECT COUNT(*) FROM issues WHERE status='open'")->fetchColumn();
$ipCount     = $pdo->query("SELECT COUNT(*) FROM issues WHERE status='in_progress'")->fetchColumn();
$resCount    = $pdo->query("SELECT COUNT(*) FROM issues WHERE status='resolved'")->fetchColumn();
$closedCount = $pdo->query("SELECT COUNT(*) FROM issues WHERE status='closed'")->fetchColumn();
$userCount   = $pdo->query("SELECT COUNT(*) FROM users WHERE role='citizen'")->fetchColumn();

$criticalCount = $pdo->query("SELECT COUNT(*) FROM issues WHERE priority='critical' AND status NOT IN ('resolved','closed')")->fetchColumn();

$recent = $pdo->query("SELECT i.*, c.name as category_name, u.full_name as reporter_name FROM issues i JOIN categories c ON i.category_id=c.id JOIN users u ON i.user_id=u.id ORDER BY i.created_at DESC LIMIT 8")->fetchAll();

require_once '../includes/header.php';
?>

<section class="py-4">
<div class="container">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-tachometer-alt me-2 text-primary"></i>Admin Dashboard</h3>
            <p class="text-muted mb-0">System overview and management</p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="<?= base_url('admin/issues.php') ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-list me-1"></i>All Issues</a>
            <a href="<?= base_url('admin/users.php') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-users me-1"></i>Users</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2">
            <div class="card border-0 shadow-sm text-center p-3 dash-card">
                <i class="fas fa-clipboard-list fa-lg text-primary mb-2"></i>
                <h5 class="fw-bold mb-0"><?= $totalIssues ?></h5>
                <small class="text-muted">Total</small>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card border-0 shadow-sm text-center p-3 dash-card border-start border-warning border-3">
                <i class="fas fa-circle-dot fa-lg text-warning mb-2"></i>
                <h5 class="fw-bold mb-0"><?= $openCount ?></h5>
                <small class="text-muted">Open</small>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card border-0 shadow-sm text-center p-3 dash-card border-start border-info border-3">
                <i class="fas fa-spinner fa-lg text-info mb-2"></i>
                <h5 class="fw-bold mb-0"><?= $ipCount ?></h5>
                <small class="text-muted">In Progress</small>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card border-0 shadow-sm text-center p-3 dash-card border-start border-success border-3">
                <i class="fas fa-check-circle fa-lg text-success mb-2"></i>
                <h5 class="fw-bold mb-0"><?= $resCount + $closedCount ?></h5>
                <small class="text-muted">Resolved</small>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card border-0 shadow-sm text-center p-3 dash-card border-start border-danger border-3">
                <i class="fas fa-triangle-exclamation fa-lg text-danger mb-2"></i>
                <h5 class="fw-bold mb-0"><?= $criticalCount ?></h5>
                <small class="text-muted">Critical</small>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card border-0 shadow-sm text-center p-3 dash-card">
                <i class="fas fa-users fa-lg text-secondary mb-2"></i>
                <h5 class="fw-bold mb-0"><?= $userCount ?></h5>
                <small class="text-muted">Citizens</small>
            </div>
        </div>
    </div>

    <!-- Recent Issues -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Recent Issues</h5>
            <a href="<?= base_url('admin/issues.php') ?>" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Title</th><th class="d-none d-md-table-cell">Reporter</th><th class="d-none d-md-table-cell">Category</th><th>Priority</th><th>Status</th><th class="d-none d-lg-table-cell">Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach($recent as $r): ?>
                <tr>
                    <td class="fw-semibold"><?= $r['id'] ?></td>
                    <td><?= sanitize($r['title']) ?></td>
                    <td class="d-none d-md-table-cell"><small><?= sanitize($r['reporter_name']) ?></small></td>
                    <td class="d-none d-md-table-cell"><small><?= sanitize($r['category_name']) ?></small></td>
                    <td><?= priority_badge($r['priority']) ?></td>
                    <td><?= status_badge($r['status']) ?></td>
                    <td class="d-none d-lg-table-cell"><small class="text-muted"><?= time_ago($r['created_at']) ?></small></td>
                    <td>
                        <a href="<?= base_url('view_issue.php?id='.$r['id']) ?>" class="btn btn-sm btn-outline-primary me-1" title="View"><i class="fas fa-eye"></i></a>
                        <a href="<?= base_url('admin/update_issue.php?id='.$r['id']) ?>" class="btn btn-sm btn-outline-warning" title="Update"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</section>

<?php require_once '../includes/footer.php'; ?>