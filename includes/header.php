<?php
/**
 * Shared page header and navigation template.
 */
require_once __DIR__ . '/functions.php';
$_current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NeighborlyIFix — Report and track civic infrastructure issues in your community.">
    <title><?= isset($page_title) ? sanitize($page_title) . ' — ' : '' ?>NeighborlyIFix</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>

<!-- ── NAVBAR ──────────────────────────────────────── -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= base_url() ?>">
            <i class="fas fa-tools text-primary me-2"></i>Neighborly<span class="text-primary">IFix</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $_current_page === 'index' ? 'active' : '' ?>" href="<?= base_url() ?>">Home</a>
                </li>
                <?php if (is_logged_in()): ?>
                    <?php if (is_admin()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin') !== false ? 'active' : '' ?>" href="<?= base_url('admin/') ?>">Admin Dashboard</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $_current_page === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('dashboard.php') ?>">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $_current_page === 'report_issue' ? 'active' : '' ?>" href="<?= base_url('report_issue.php') ?>">Report Issue</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $_current_page === 'my_issues' ? 'active' : '' ?>" href="<?= base_url('my_issues.php') ?>">My Issues</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link <?= $_current_page === 'about' ? 'active' : '' ?>" href="<?= base_url('about.php') ?>">About</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if (is_logged_in()): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?= sanitize($_SESSION['user_name'] ?? 'Account') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= base_url('profile.php') ?>"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= base_url('logout.php') ?>"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= base_url('login.php') ?>" class="btn btn-outline-primary">Login</a>
                    <a href="<?= base_url('register.php') ?>" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- ── FLASH MESSAGES ──────────────────────────────── -->
<?php $flash = get_flash(); if ($flash): ?>
<div class="container mt-3">
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : sanitize($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= sanitize($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>