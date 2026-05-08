<?php
/**
 * Authentication middleware to protect restricted pages.
 */
require_once __DIR__ . '/functions.php';
if (!is_logged_in()) {
    set_flash('error', 'Please log in to access this page.');
    redirect(base_url('login.php'));
}