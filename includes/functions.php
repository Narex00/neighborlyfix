<?php
/**
 * Helper Functions — NeighborlyIFix
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/* ── Sanitisation ─────────────────────────────────── */
function sanitize(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/* ── Redirect ─────────────────────────────────────── */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/* ── Flash Messages ───────────────────────────────── */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/* ── CSRF Protection ──────────────────────────────── */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_expiry']) || $_SESSION['csrf_expiry'] < time()) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_expiry'] = time() + 3600; // 1 hour expiry
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && isset($_SESSION['csrf_expiry']) &&
           $_SESSION['csrf_expiry'] >= time() && hash_equals($_SESSION['csrf_token'], $token);
}

/* ── Auth Helpers ─────────────────────────────────── */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function is_admin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function current_user_id(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

function get_user(int $id): ?array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/* ── Image Upload ─────────────────────────────────── */
function upload_image(array $file, string $dir = 'uploads/issues/'): ?string {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2 MB

    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if (!in_array($mime, $allowed)) return null;
    if ($file['size'] > $maxSize) return null;

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('issue_', true) . '.' . strtolower($ext);
    $target = rtrim($dir, '/') . '/' . $filename;

    // Ensure directory exists
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $target)) {
        return $target;
    }
    return null;
}

/* ── Badge Helpers ────────────────────────────────── */
function status_badge(string $status): string {
    $map = [
        'open'        => '<span class="badge bg-warning text-dark"><i class="fas fa-circle-dot me-1"></i>Open</span>',
        'in_progress' => '<span class="badge bg-info text-white"><i class="fas fa-spinner me-1"></i>In Progress</span>',
        'resolved'    => '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Resolved</span>',
        'closed'      => '<span class="badge bg-secondary"><i class="fas fa-lock me-1"></i>Closed</span>',
    ];
    return $map[$status] ?? '<span class="badge bg-dark">Unknown</span>';
}

function priority_badge(string $priority): string {
    $map = [
        'low'      => '<span class="badge bg-success">Low</span>',
        'medium'   => '<span class="badge bg-warning text-dark">Medium</span>',
        'high'     => '<span class="badge bg-orange">High</span>',
        'critical' => '<span class="badge bg-danger">Critical</span>',
    ];
    return $map[$priority] ?? '<span class="badge bg-dark">Unknown</span>';
}

/* ── Time Ago ─────────────────────────────────────── */
function time_ago(string $datetime): string {
    $now  = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

/* ── Base URL helper ──────────────────────────────── */
function base_url(string $path = ''): string {
    $base = '/neighborlyfix/';
    return $base . ltrim($path, '/');
}

/* ── Simple Logging ──────────────────────────── */
function log_event(string $msg): void {
    $logFile = __DIR__ . '/../logs/system.log';
    $dir = dirname($logFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($logFile, date('c') . " - $msg\n", FILE_APPEND);
}