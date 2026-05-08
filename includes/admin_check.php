<?php
/**
 * Administrator access control middleware.
 */
require_once __DIR__ . '/functions.php';
if (!is_logged_in()) {
    set_flash('error', 'Please log in to access this page.');
    redirect(base_url('login.php'));
} elseif (!is_admin()) {
    set_flash('error', 'Access denied. Administrator privileges required.');
    redirect(base_url('dashboard.php'));
}