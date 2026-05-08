<?php
/**
 * User dashboard with issue statistics and recent activity.
 */
$page_title = 'Dashboard';
require_once 'includes/auth_check.php';
if (is_admin()) redirect(base_url('admin/'));

$pdo = getDBConnection();
$uid = current_user_id();

$total      = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE user_id=?"); $total->execute([$uid]); $total = $total->fetchColumn();
$open       = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE user_id=? AND status='open'"); $open->execute([$uid]); $open = $open->fetchColumn();
$inProgress = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE user_id=? AND status='in_progress'"); $inProgress->execute([$uid]); $inProgress = $inProgress->fetchColumn();
$resolved   = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE user_id=? AND status IN ('resolved','closed')"); $resolved->execute([$uid]); $resolved = $resolved->fetchColumn();

$recentStmt = $pdo->prepare("SELECT i.*, c.name as category_name FROM issues i JOIN categories c ON i.category_id=c.id WHERE i.user_id=? ORDER BY i.created_at DESC LIMIT 5");
$recentStmt->execute([$uid]);
$recent = $recentStmt->fetchAll();

require_once 'includes/header.php';
?>

<section class="py-4">
<div class="container">
    <!-- Welcome -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h3 class="fw-bold mb-1">Welcome, <?= sanitize($_SESSION['user_name']) ?>!</h3>
            <p class="text-muted mb-0">Here's an overview of your reported issues.</p>
        </div>
        <a href="<?= base_url('report_issue.php') ?>" class="btn btn-primary mt-2 mt-md-0">
            <i class="fas fa-plus me-2"></i>Report New Issue
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center p-3 dash-card">
                <i class="fas fa-clipboard-list fa-2x text-primary mb-2"></i>
                <h4 class="fw-bold mb-0"><?= $total ?></h4>
                <small class="text-muted">Total Issues</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center p-3 dash-card">
                <i class="fas fa-circle-dot fa-2x text-warning mb-2"></i>
                <h4 class="fw-bold mb-0"><?= $open ?></h4>
                <small class="text-muted">Open</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center p-3 dash-card">
                <i class="fas fa-spinner fa-2x text-info mb-2"></i>
                <h4 class="fw-bold mb-0"><?= $inProgress ?></h4>
                <small class="text-muted">In Progress</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center p-3 dash-card">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h4 class="fw-bold mb-0"><?= $resolved ?></h4>
                <small class="text-muted">Resolved</small>
            </div>
        </div>
    </div>

    <!-- Recent Issues -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Recent Issues</h5>
            <a href="<?= base_url('my_issues.php') ?>" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($recent)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No issues reported yet. <a href="<?= base_url('report_issue.php') ?>">Report your first issue</a>.</p>
                </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Title</th><th>Category</th><th>Priority</th><th>Status</th><th>Date</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recent as $r): ?>
                        <tr>
                            <td class="fw-semibold"><?= $r['id'] ?></td>
                            <td><?= sanitize($r['title']) ?></td>
                            <td><small><?= sanitize($r['category_name']) ?></small></td>
                            <td><?= priority_badge($r['priority']) ?></td>
                            <td><?= status_badge($r['status']) ?></td>
                            <td><small class="text-muted"><?= time_ago($r['created_at']) ?></small></td>
                            <td><a href="<?= base_url('view_issue.php?id='.$r['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</section>

<?php require_once 'includes/footer.php'; ?>