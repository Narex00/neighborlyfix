<?php
$page_title = 'Manage Users';
require_once '../includes/admin_check.php';

$pdo = getDBConnection();

// Toggle active status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    if ($tid !== current_user_id()) {
        $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?")->execute([$tid]);
        set_flash('success', 'User status updated.');
    }
    redirect(base_url('admin/users.php'));
}

$search = trim($_GET['search'] ?? '');
$where = "1=1";
$params = [];
if ($search !== '') { $where = "(full_name LIKE ? OR email LIKE ?)"; $params = ["%$search%","%$search%"]; }

$stmt = $pdo->prepare("SELECT u.*, (SELECT COUNT(*) FROM issues WHERE user_id=u.id) as issue_count FROM users u WHERE $where ORDER BY u.created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<section class="py-4">
<div class="container">
    <h3 class="fw-bold mb-4"><i class="fas fa-users me-2 text-primary"></i>Manage Users</h3>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-8">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name or email…" value="<?= sanitize($search) ?>">
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i>Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Issues</th><th>Status</th><th class="d-none d-md-table-cell">Joined</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php foreach($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td class="fw-semibold"><?= sanitize($u['full_name']) ?></td>
                <td><small><?= sanitize($u['email']) ?></small></td>
                <td><span class="badge <?= $u['role']==='admin'?'bg-primary':'bg-secondary' ?>"><?= ucfirst($u['role']) ?></span></td>
                <td><?= $u['issue_count'] ?></td>
                <td><span class="badge <?= $u['is_active']?'bg-success':'bg-danger' ?>"><?= $u['is_active']?'Active':'Inactive' ?></span></td>
                <td class="d-none d-md-table-cell"><small class="text-muted"><?= date('d M Y', strtotime($u['created_at'])) ?></small></td>
                <td>
                    <?php if ($u['id'] !== current_user_id()): ?>
                    <a href="?toggle=<?= $u['id'] ?>" class="btn btn-sm btn-outline-<?= $u['is_active']?'danger':'success' ?>" onclick="return confirm('Toggle this user\'s status?')">
                        <i class="fas fa-<?= $u['is_active']?'ban':'check' ?>"></i>
                    </a>
                    <?php else: ?>
                    <span class="text-muted small">—</span>
                    <?php endif; ?>
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