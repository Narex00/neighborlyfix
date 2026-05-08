<?php
/**
 * Home page overview and statistics.
 */
$page_title = 'Home';
require_once 'includes/functions.php';

$pdo = getDBConnection();
$totalIssues   = $pdo->query("SELECT COUNT(*) FROM issues")->fetchColumn();
$resolvedCount = $pdo->query("SELECT COUNT(*) FROM issues WHERE status IN ('resolved','closed')")->fetchColumn();
$userCount     = $pdo->query("SELECT COUNT(*) FROM users WHERE role='citizen'")->fetchColumn();

require_once 'includes/header.php';
?>

<!-- ── HERO ─────────────────────────────────────────── -->
<section class="hero-section">
    <div class="hero-overlay"></div>
    <div class="container hero-content text-center text-white position-relative" style="z-index:2">
        <h1 class="display-4 fw-800 mb-3">Your Community.<br>Your Voice.<br>Your <span class="text-warning">Fix</span>.</h1>
        <p class="lead mb-4 mx-auto" style="max-width:650px">Report infrastructure issues in your neighbourhood and track their resolution in real time. Together we build better communities.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <?php if (is_logged_in()): ?>
                <a href="<?= is_admin() ? base_url('admin/') : base_url('dashboard.php') ?>" class="btn btn-warning btn-lg px-4 fw-semibold"><i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard</a>
            <?php else: ?>
                <a href="<?= base_url('register.php') ?>" class="btn btn-warning btn-lg px-4 fw-semibold"><i class="fas fa-user-plus me-2"></i>Get Started</a>
                <a href="<?= base_url('login.php') ?>" class="btn btn-outline-light btn-lg px-4 fw-semibold"><i class="fas fa-sign-in-alt me-2"></i>Login</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ── STATS ────────────────────────────────────────── -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="stat-card p-4">
                    <i class="fas fa-flag fa-2x text-primary mb-3"></i>
                    <h2 class="fw-bold counter"><?= $totalIssues ?></h2>
                    <p class="text-muted mb-0">Issues Reported</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card p-4">
                    <i class="fas fa-check-double fa-2x text-success mb-3"></i>
                    <h2 class="fw-bold counter"><?= $resolvedCount ?></h2>
                    <p class="text-muted mb-0">Issues Resolved</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card p-4">
                    <i class="fas fa-users fa-2x text-info mb-3"></i>
                    <h2 class="fw-bold counter"><?= $userCount ?></h2>
                    <p class="text-muted mb-0">Active Citizens</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── HOW IT WORKS ─────────────────────────────────── -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center fw-bold mb-2">How It Works</h2>
        <p class="text-center text-muted mb-5">Three simple steps to a better neighbourhood</p>
        <div class="row g-4">
            <div class="col-md-4 text-center">
                <div class="step-circle mx-auto mb-3">1</div>
                <h5 class="fw-semibold">Report</h5>
                <p class="text-muted">Spot an issue? Submit a report with details, location, and a photo.</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="step-circle mx-auto mb-3">2</div>
                <h5 class="fw-semibold">Track</h5>
                <p class="text-muted">Follow your issue through every stage — from open to resolved.</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="step-circle mx-auto mb-3">3</div>
                <h5 class="fw-semibold">Resolved</h5>
                <p class="text-muted">Authorities act, issues get fixed, and your community improves.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── FEATURES ──────────────────────────────────────── -->
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center fw-bold mb-2">Key Features</h2>
        <p class="text-center text-muted mb-5">Built for transparency, security, and ease of use</p>
        <div class="row g-4">
            <div class="col-md-4 col-lg-3">
                <div class="feature-card card h-100 border-0 shadow-sm text-center p-4">
                    <i class="fas fa-shield-halved fa-2x text-primary mb-3"></i>
                    <h6 class="fw-semibold">Secure Platform</h6>
                    <p class="text-muted small mb-0">Server-side validation, CSRF protection, and encrypted passwords.</p>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="feature-card card h-100 border-0 shadow-sm text-center p-4">
                    <i class="fas fa-mobile-alt fa-2x text-primary mb-3"></i>
                    <h6 class="fw-semibold">Fully Responsive</h6>
                    <p class="text-muted small mb-0">Works seamlessly on desktop, tablet, and mobile devices.</p>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="feature-card card h-100 border-0 shadow-sm text-center p-4">
                    <i class="fas fa-diagram-project fa-2x text-primary mb-3"></i>
                    <h6 class="fw-semibold">Issue Dependencies</h6>
                    <p class="text-muted small mb-0">Link related issues to streamline resolution workflows.</p>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="feature-card card h-100 border-0 shadow-sm text-center p-4">
                    <i class="fas fa-clock-rotate-left fa-2x text-primary mb-3"></i>
                    <h6 class="fw-semibold">Full Audit Trail</h6>
                    <p class="text-muted small mb-0">Every status change is logged for complete transparency.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── CATEGORIES ────────────────────────────────────── -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center fw-bold mb-2">Report Any Issue</h2>
        <p class="text-center text-muted mb-5">We cover all major infrastructure categories</p>
        <div class="row g-3 justify-content-center">
            <?php
            $cats = $pdo->query("SELECT * FROM categories WHERE is_active=1")->fetchAll();
            foreach ($cats as $cat): ?>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center p-3 h-100 category-card">
                    <i class="fas <?= sanitize($cat['icon']) ?> fa-2x text-primary mb-2"></i>
                    <h6 class="mb-0 fw-semibold small"><?= sanitize($cat['name']) ?></h6>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── CTA ───────────────────────────────────────────── -->
<section class="cta-section py-5 text-white text-center">
    <div class="container">
        <h2 class="fw-bold mb-3">Ready to Make a Difference?</h2>
        <p class="lead mb-4">Join your neighbours in building a better community today.</p>
        <?php if (!is_logged_in()): ?>
        <a href="<?= base_url('register.php') ?>" class="btn btn-warning btn-lg px-5 fw-semibold">Create Free Account</a>
        <?php else: ?>
        <a href="<?= base_url('report_issue.php') ?>" class="btn btn-warning btn-lg px-5 fw-semibold">Report an Issue Now</a>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>