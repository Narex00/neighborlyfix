<?php
/**
 * Administrator interface for issue editing and status updates.
 */
$page_title = 'Update Issue';
require_once '../includes/admin_check.php';

$pdo = getDBConnection();
$id  = (int)($_GET['id'] ?? 0);
if ($id < 1) { set_flash('error','Invalid issue.'); redirect(base_url('admin/issues.php')); }

$stmt = $pdo->prepare("SELECT * FROM issues WHERE id=?");
$stmt->execute([$id]);
$issue = $stmt->fetch();
if (!$issue) { set_flash('error','Issue not found.'); redirect(base_url('admin/issues.php')); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid token.'; }

    $newStatus   = $_POST['status'] ?? $issue['status'];
    $newPriority = $_POST['priority'] ?? $issue['priority'];
    $adminNotes  = trim($_POST['admin_notes'] ?? '');
    $depId       = (int)($_POST['dependency_id'] ?? 0);
    $changeNotes = trim($_POST['change_notes'] ?? '');

    if (!in_array($newStatus, ['open','in_progress','resolved','closed'])) $errors[] = 'Invalid status.';
    if (!in_array($newPriority, ['low','medium','high','critical'])) $errors[] = 'Invalid priority.';
    if ($depId === $id) $errors[] = 'An issue cannot depend on itself.';
    // Note: Circular dependency detection not implemented

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            // Log status change
            if ($newStatus !== $issue['status']) {
                $h = $pdo->prepare("INSERT INTO issue_history (issue_id,changed_by,field_changed,old_value,new_value,notes) VALUES (?,?,?,?,?,?)");
                $h->execute([$id, current_user_id(), 'Status', $issue['status'], $newStatus, $changeNotes]);
            }
            // Log priority change
            if ($newPriority !== $issue['priority']) {
                $h = $pdo->prepare("INSERT INTO issue_history (issue_id,changed_by,field_changed,old_value,new_value,notes) VALUES (?,?,?,?,?,?)");
                $h->execute([$id, current_user_id(), 'Priority', $issue['priority'], $newPriority, $changeNotes]);
            }
            // Log dependency change
            $oldDep = (int)$issue['dependency_id'];
            if ($depId !== $oldDep) {
                $h = $pdo->prepare("INSERT INTO issue_history (issue_id,changed_by,field_changed,old_value,new_value,notes) VALUES (?,?,?,?,?,?)");
                $h->execute([$id, current_user_id(), 'Dependency', $oldDep ?: 'None', $depId ?: 'None', $changeNotes]);
            }

            $resolvedAt = null;
            if ($newStatus === 'resolved' && $issue['status'] !== 'resolved') $resolvedAt = date('Y-m-d H:i:s');

            $upd = $pdo->prepare("UPDATE issues SET status=?, priority=?, admin_notes=?, dependency_id=?, resolved_at=COALESCE(?,resolved_at) WHERE id=?");
            $upd->execute([$newStatus, $newPriority, $adminNotes, $depId ?: null, $resolvedAt, $id]);

            $pdo->commit();
            set_flash('success', 'Issue #'.$id.' updated successfully.');
            redirect(base_url('view_issue.php?id='.$id));
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
    unset($_SESSION['csrf_token']);
    // Refresh issue data
    $stmt->execute([$id]);
    $issue = $stmt->fetch();
}

// Get other issues for dependency dropdown
$otherIssues = $pdo->prepare("SELECT id,title,status FROM issues WHERE id != ? ORDER BY id DESC");
$otherIssues->execute([$id]);
$otherIssues = $otherIssues->fetchAll();

require_once '../includes/header.php';
?>

<section class="py-4">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-8">
    <a href="<?= base_url('admin/issues.php') ?>" class="btn btn-outline-secondary btn-sm mb-3"><i class="fas fa-arrow-left me-1"></i>Back to Issues</a>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0 fw-bold">Update Issue #<?= $id ?></h4>
            <small class="text-muted"><?= sanitize($issue['title']) ?></small>
        </div>
        <div class="card-body p-4">
            <?php if ($errors): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo "<li>".sanitize($e)."</li>"; ?></ul></div>
            <?php endif; ?>

            <form method="POST">
                <?= csrf_field() ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach(['open','in_progress','resolved','closed'] as $s): ?>
                            <option value="<?= $s ?>" <?= $issue['status']===$s?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Priority</label>
                        <select name="priority" class="form-select">
                            <?php foreach(['low','medium','high','critical'] as $p): ?>
                            <option value="<?= $p ?>" <?= $issue['priority']===$p?'selected':'' ?>><?= ucfirst($p) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Link to Parent Issue (Dependency)</label>
                    <select name="dependency_id" class="form-select">
                        <option value="0">— No dependency —</option>
                        <?php foreach($otherIssues as $oi): ?>
                        <option value="<?= $oi['id'] ?>" <?= (int)$issue['dependency_id']===$oi['id']?'selected':'' ?>>#<?= $oi['id'] ?> — <?= sanitize(mb_strimwidth($oi['title'],0,60,'…')) ?> [<?= $oi['status'] ?>]</option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Link this issue to a related parent issue for dependency tracking.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Admin Notes</label>
                    <textarea name="admin_notes" class="form-control" rows="3" placeholder="Internal notes visible to the reporter…"><?= sanitize($issue['admin_notes'] ?? '') ?></textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Change Notes (for audit log)</label>
                    <input type="text" name="change_notes" class="form-control" placeholder="e.g., Assigned to maintenance team">
                </div>
                <button type="submit" class="btn btn-warning w-100 py-2 fw-semibold"><i class="fas fa-save me-2"></i>Save Changes</button>
            </form>
        </div>
    </div>
</div>
</div>
</div>
</section>

<?php require_once '../includes/footer.php'; ?>