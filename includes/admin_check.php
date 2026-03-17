<?php
require_once __DIR__ . '/functions.php';
if (!is_logged_in() || !is_admin()) {
    set_flash('error', 'Access denied. Administrator privileges required.');
    redirect(base_url('login.php'));
}