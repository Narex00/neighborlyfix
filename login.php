<?php
/**
 * User authentication and login handler.
 */
$page_title = 'Login';
require_once 'includes/functions.php';
if (is_logged_in()) redirect(is_admin() ? base_url('admin/') : base_url('dashboard.php'));

$_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;

$errors = [];
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['login_attempts'] > 5) {
        $errors[] = 'Too many login attempts. Try later.';
    }

    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        $errors[] = 'Invalid security token.';
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $old_email = $email;

    if (empty($email) || empty($password)) {
        $errors[] = 'Please fill in all fields.';
    }

    if (empty($errors)) {
        $pdo  = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email']= $user['email'];
            $_SESSION['login_attempts'] = 0;

            set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');
            redirect($user['role'] === 'admin' ? base_url('admin/') : base_url('dashboard.php'));
        } else {
            $errors[] = 'Invalid email or password.';
            $_SESSION['login_attempts']++;
        }
    }
    unset($_SESSION['csrf_token']);
}
require_once 'includes/header.php';
?>

<section class="py-5">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-5 col-md-7">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <h3 class="fw-bold text-center mb-1">Welcome Back</h3>
            <p class="text-muted text-center mb-4">Sign in to your NeighborlyIFix account</p>

            <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach($errors as $e) echo "<li>".sanitize($e)."</li>"; ?></ul>
            </div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= sanitize($old_email) ?>" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>
            <p class="text-center mt-3 mb-0">Don't have an account? <a href="<?= base_url('register.php') ?>">Register</a></p>
        </div>
    </div>
</div>
</div>
</div>
</section>

<?php require_once 'includes/footer.php'; ?>