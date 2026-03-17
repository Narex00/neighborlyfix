<?php
$page_title = 'Register';
require_once 'includes/functions.php';
if (is_logged_in()) redirect(base_url('dashboard.php'));

$errors = [];
$old = ['full_name'=>'','email'=>'','phone'=>'','address'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    }

    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    $old = compact('full_name','email','phone','address');

    if (strlen($full_name) < 2)  $errors[] = 'Full name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (strlen($password) < 8)  $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name,email,password,phone,address) VALUES (?,?,?,?,?)");
            $stmt->execute([$full_name, $email, $hash, $phone, $address]);
            set_flash('success', 'Account created successfully! Please log in.');
            redirect(base_url('login.php'));
        }
    }
    // Regenerate CSRF token after failed attempt
    unset($_SESSION['csrf_token']);
}
require_once 'includes/header.php';
?>

<section class="py-5">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-6 col-md-8">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <h3 class="fw-bold text-center mb-1">Create Account</h3>
            <p class="text-muted text-center mb-4">Join NeighborlyIFix and start reporting issues</p>

            <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach($errors as $e) echo "<li>".sanitize($e)."</li>"; ?></ul>
            </div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control" value="<?= sanitize($old['full_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= sanitize($old['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Phone Number</label>
                    <input type="tel" name="phone" class="form-control" value="<?= sanitize($old['phone']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Address</label>
                    <textarea name="address" class="form-control" rows="2"><?= sanitize($old['address']) ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" minlength="8" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </form>
            <p class="text-center mt-3 mb-0">Already have an account? <a href="<?= base_url('login.php') ?>">Log in</a></p>
        </div>
    </div>
</div>
</div>
</div>
</section>

<?php require_once 'includes/footer.php'; ?>