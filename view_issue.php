<?php
$page_title = 'View Issue';
require_once 'includes/auth_check.php';

$pdo = getDBConnection();
$id  = (int)($_GET['id'] ?? 0);
if ($id < 1) { set_flash('error','Invalid issue.'); redirect(base_url('dashboard.php')); }

$stmt = $pdo->prepare("SELECT i.*, c.name as category_name, c.icon as category_icon, u.full_name as reporter_name
    FROM issues i JOIN categories c ON i.category_id=c.id JOIN users u ON i.user_id=u.id WHERE i.id=?");
$stmt->execute([$id]);
$issue = $stmt->fetch();

if (!$issue) { set_flash('error','Issue not found.'); redirect(base_url('dashboard.php')); }
// Citizens can only view their own issues
if (!is_admin() && $issue['user_id'] !== current_user_id()) {
    set_flash('error','Access denied.'); redirect(base_url('dashboard.php'));
}

// Handle new comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (verify_csrf($_POST['csrf_token'] ?? '')) {
        $comment = trim($_POST['comment']);
        if (strlen($comment) >= 2) {
            $stmt = $pdo->prepare("INSERT INTO comments (issue_id,user_id,comment) VALUES (?,?,?)");
            $stmt->execute([$id, current_user_id(), $comment]);
            set_flash('success','Comment added.');
        }
    }
    unset($_SESSION['csrf_token']);
    redirect(base_url('view_issue.php?id='.$id));
}

// Fetch comments
$comments = $pdo->prepare("SELECT cm.*, u.full_name, u.role FROM comments cm JOIN users u ON cm.user_id=u.id WHERE cm.issue_id=? ORDER BY cm.created_at ASC");
$comments->execute([$id]);
$comments = $comments->fetchAll();

// Fetch history
$history = $pdo->prepare("SELECT h.*, u.full_name FROM issue_history h JOIN users u ON h.changed_by=u.id WHERE h.issue_id=? ORDER BY h.created_at DESC");
$history->execute([$id]);
$history = $history->fetchAll();

// Dependency info
$parent = null;
if ($issue['dependency_id']) {
    $pstmt = $pdo->prepare("SELECT id,title,status FROM issues WHERE id=?");
    $pstmt->execute([$issue['dependency_id']]);
    $parent = $pstmt->fetch();
}
$children = $pdo->prepare("SELECT id,title,status FROM issues WHERE dependency_id=?");
$children->execute([$id]);
$children = $children->fetchAll();

$page_title = 'Issue #'.$id;
require_once 'includes/header.php';
?>

<section class="py-4">
<div class="container">
    <a href="<?= is_admin() ? base_url('admin/issues.php') : base_url('my_issues.php') ?>" class="btn btn-outline-secondary btn-sm mb-3"><i class="fas fa-arrow-left me-1"></i>Back</a>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                        <h4 class="fw-bold mb-1"><?= sanitize($issue['title']) ?></h4>
                        <span class="text-muted small">#<?= $issue['id'] ?></span>
                    </div>
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <?= status_badge($issue['status']) ?>
                        <?= priority_badge($issue['priority']) ?>
                        <span class="badge bg-light text-dark border"><i class="fas <?= sanitize($issue['category_icon']) ?> me-1"></i><?= sanitize($issue['category_name']) ?></span>
                    </div>
                    <p class="mb-3" style="white-space:pre-line"><?= sanitize($issue['description']) ?></p>

                    <?php if ($issue['image_path']): ?>
                    <div class="mb-3">
                        <img src="<?= base_url($issue['image_path']) ?>" class="img-fluid rounded shadow-sm" alt="Issue photo" style="max-height:400px">
                    </div>
                    <?php endif; ?>

                    <?php if ($issue['admin_notes']): ?>
                    <div class="alert alert-info mt-3">
                        <h6 class="fw-semibold"><i class="fas fa-sticky-note me-1"></i>Admin Notes</h6>
                        <p class="mb-0"><?= sanitize($issue['admin_notes']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Dependencies -->
            <?php if ($parent || !empty($children)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="mb-0 fw-semibold"><i class="fas fa-diagram-project me-2"></i>Issue Dependencies</h6></div>
                <div class="card-body">
                    <?php if ($parent): ?>
                    <p class="mb-1"><strong>Parent Issue:</strong> <a href="<?= base_url('view_issue.php?id='.$parent['id']) ?>">#<?= $parent['id'] ?> — <?= sanitize($parent['title']) ?></a> <?= status_badge($parent['status']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($children)): ?>
                    <p class="mb-1 mt-2"><strong>Dependent Issues:</strong></p>
                    <ul class="list-unstyled ms-3">
                        <?php foreach($children as $ch): ?>
                        <li class="mb-1"><a href="<?= base_url('view_issue.php?id='.$ch['id']) ?>">#<?= $ch['id'] ?> — <?= sanitize($ch['title']) ?></a> <?= status_badge($ch['status']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Comments -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0 fw-semibold"><i class="fas fa-comments me-2"></i>Comments (<?= count($comments) ?>)</h6></div>
                <div class="card-body">
                    <?php if (empty($comments)): ?>
                        <p class="text-muted">No comments yet.</p>
                    <?php else: ?>
                        <?php foreach($comments as $cm): ?>
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="avatar-circle <?= $cm['role']==='admin'?'bg-primary':'bg-secondary' ?> text-white">
                                    <?= strtoupper(substr($cm['full_name'],0,1)) ?>
                                </div>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <strong><?= sanitize($cm['full_name']) ?> <?= $cm['role']==='admin'?'<span class="badge bg-primary small">Admin</span>':'' ?></strong>
                                    <small class="text-muted"><?= time_ago($cm['created_at']) ?></small>
                                </div>
                                <p class="mb-0 mt-1"><?= sanitize($cm['comment']) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <hr>
                    <form method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-2">
                            <textarea name="comment" class="form-control" rows="3" placeholder="Add a comment…" required minlength="2"></textarea>
                        </div>
                        <button class="btn btn-primary btn-sm"><i class="fas fa-paper-plane me-1"></i>Post Comment</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="mb-0 fw-semibold">Details</h6></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted">Reported by</td><td class="fw-semibold"><?= sanitize($issue['reporter_name']) ?></td></tr>
                        <tr><td class="text-muted">Date</td><td><?= date('d M Y, H:i', strtotime($issue['created_at'])) ?></td></tr>
                        <tr><td class="text-muted">Location</td><td><?= $issue['location'] ? sanitize($issue['location']) : '<span class="text-muted">N/A</span>' ?></td></tr>
                        <tr><td class="text-muted">Last Updated</td><td><?= time_ago($issue['updated_at']) ?></td></tr>
                        <?php if($issue['resolved_at']): ?>
                        <tr><td class="text-muted">Resolved</td><td><?= date('d M Y, H:i', strtotime($issue['resolved_at'])) ?></td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <?php if (is_admin()): ?>
            <a href="<?= base_url('admin/update_issue.php?id='.$id) ?>" class="btn btn-warning w-100 mb-4"><i class="fas fa-edit me-2"></i>Update Issue</a>
            <?php endif; ?>

            <!-- History -->
            <?php if (!empty($history)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0 fw-semibold"><i class="fas fa-clock-rotate-left me-2"></i>History</h6></div>
                <div class="card-body p-3">
                    <?php foreach($history as $h): ?>
                    <div class="timeline-item mb-3">
                        <small class="text-muted d-block"><?= time_ago($h['created_at']) ?> — <?= sanitize($h['full_name']) ?></small>
                        <span class="small"><strong><?= sanitize($h['field_changed']) ?></strong>: <?= sanitize($h['old_value'] ?? 'N/A') ?> → <?= sanitize($h['new_value']) ?></span>
                        <?php if($h['notes']): ?><br><small class="text-muted"><?= sanitize($h['notes']) ?></small><?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</section>

<?php require_once 'includes/footer.php'; ?>