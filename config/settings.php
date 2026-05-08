<?php
/**
 * Application settings and environment configuration.
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Email Configuration
define('ADMIN_EMAIL', $_ENV['ADMIN_EMAIL'] ?? 'admin@example.com');
define('FROM_EMAIL', $_ENV['FROM_EMAIL'] ?? 'noreply@example.com');

// SMTP Configuration (Gmail)
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
define('SMTP_USER', $_ENV['SMTP_USER'] ?? '');
define('SMTP_PASS', $_ENV['SMTP_PASS'] ?? '');
define('USE_SMTP', $_ENV['USE_SMTP'] === 'true');
