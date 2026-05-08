<?php
/**
 * User profile management and update interface.
 */
$page_title = 'My Profile';
require_once 'includes/auth_check.php';

$pdo  = getDBConnection();
$user = get_user(current_user_id());
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid token.'; }

    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name    = trim($_POST['full_name'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        if (strlen($name) < 2) $errors[] = 'Name must be at least 2 characters.';
        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET full_name=?,phone=?,address=? WHERE id=?");
            $stmt->execute([$name,$phone,$address,current_user_id()]);
            $_SESSION['user_name'] = $name;
            set_flash('success','Profile updated.');
            redirect(base_url('profile.php'));
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!password_verify($current, $user['password'])) $errors[] = 'Current password is incorrect.';
        if (strlen($new) < 8) $errors[] = 'New password must be at least 8 characters.';
        if ($new !== $confirm) $errors[] = 'New passwords do not match.';
        if (empty($errors)) {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash,current_user_id()]);
            set_flash('success','Password changed successfully.');
            redirect(base_url('profile.php'));
        }
    }
    unset($_SESSION['csrf_token']);
    $user = get_user(current_user_id()); // refresh
}

require_once 'includes/header.php';
?>

<section class="py-4">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-8">
    <h3 class="fw-bold mb-4"><i class="fas fa-user-circle me-2 text-primary"></i>My Profile</h3>

    <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo "<li>".sanitize($e)."</li>"; ?></ul></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><h5 class="mb-0 fw-semibold">Profile Details</h5></div>
        <div class="card-body">
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_profile">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= sanitize($user['full_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled>
                    <div class="form-text">Email cannot be changed.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Phone</label>
                    <input type="tel" name="phone" class="form-control" value="<?= sanitize($user['phone'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Address</label>
                    <textarea name="address" class="form-control" rows="2"><?= sanitize($user['address'] ?? '') ?></textarea>
                </div>
                <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Profile</button>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0 fw-semibold">Change Password</h5></div>
        <div class="card-body">
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="change_password">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <button class="btn btn-warning"><i class="fas fa-key me-1"></i>Change Password</button>
            </form>
        </div>
    </div>
</div>
</div>
</div>
</section>

<?php require_once 'includes/footer.php'; ?>