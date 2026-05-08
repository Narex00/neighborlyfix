<?php
/**
 * Shared helper functions and utilities.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Sanitisation
function sanitize(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Redirect
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

// Flash Messages
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

// CSRF Protection
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

// Auth Helpers
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

// Image Upload
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

// Badge Helpers
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

// Time Ago
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

// Base URL helper
function base_url(string $path = ''): string {
    $base = '/neighborlyfix/';
    return $base . ltrim($path, '/');
}

// Simple Logging
function log_event(string $msg): void {
    $logFile = __DIR__ . '/../logs/system.log';
    $dir = dirname($logFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($logFile, date('c') . " - $msg\n", FILE_APPEND);
}

// Display Location with Google Maps
function display_location(string $location): void {
    if (empty($location)) {
        echo '<span class="text-muted">N/A</span>';
        return;
    }

    // Check if the location contains a URL
    $url_pattern = '/https?:\/\/[^\s]+/i';
    if (preg_match($url_pattern, $location, $matches)) {
        $raw_url = $matches[0]; // Keep raw URL
        $url = htmlspecialchars($raw_url);
        
        // Check if it is a Google Maps link
        if (strpos($raw_url, 'maps.google') !== false || strpos($raw_url, 'goo.gl/maps') !== false || strpos($raw_url, 'maps.app.goo.gl') !== false) {
            echo '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" class="text-decoration-none">' . $url . '</a>';
            
            // Try to extract coordinates
            $lat = null;
            $lng = null;
            
            // Format 1: @lat,lng or /@lat,lng
            if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $raw_url, $coords)) {
                $lat = $coords[1];
                $lng = $coords[2];
            }
            
            // Format 2: 3d lat and 4d lng (old Google Maps format)
            if (empty($lat) && preg_match('/3d(-?\d+\.\d+).*?4d(-?\d+\.\d+)/', $raw_url, $coords)) {
                $lat = $coords[1];
                $lng = $coords[2];
            }
            
            // Format 3: place/@lat,lng from maps.google.com
            if (empty($lat) && preg_match('/place\/@(-?\d+\.\d+),(-?\d+\.\d+)/', $raw_url, $coords)) {
                $lat = $coords[1];
                $lng = $coords[2];
            }
            
            // Use OpenStreetMap (free, no API key needed)
            if (!empty($lat) && !empty($lng)) {
                $map_id = 'map_' . uniqid();
                echo '<br><div class="mt-3" style="max-width:100%; border-radius:6px; border:3px solid #0d6efd; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                    <div id="' . $map_id . '" style="width:100%; height:400px;"></div>
                </div>
                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        var map = L.map("' . $map_id . '").setView([' . $lat . ', ' . $lng . '], 18);
                        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                            attribution: "&copy; OpenStreetMap contributors",
                            maxZoom: 19
                        }).addTo(map);
                        var redIcon = new L.Icon({
                            iconUrl: "data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 40%27%3E%3Cpath fill=%27%23dc3545%27 d=%27M12 0C5.383 0 0 5.383 0 12c0 7.5 12 28 12 28s12-20.5 12-28c0-6.617-5.383-12-12-12z%27/%3E%3C/svg%3E",
                            iconSize: [25, 40],
                            iconAnchor: [12, 40],
                            popupAnchor: [0, -35]
                        });
                        L.marker([' . $lat . ', ' . $lng . '], {icon: redIcon}).addTo(map).bindPopup("<b>Issue Location</b><br><a href=\"' . $url . '\" target=\"_blank\" rel=\"noopener\">View on Google Maps</a>");
                    });
                </script>';
            } else {
                echo '<br><a href="' . $url . '" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary mt-3">
                    <i class="fas fa-map me-1"></i>View on Google Maps
                </a>';
            }
        } else {
            // Non-maps URL — just show as clickable link
            echo '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" class="text-decoration-none">' . sanitize($location) . '</a>';
        }
    } else {
        // Plain text address — just display it
        echo sanitize($location);
    }
}

// Send Email via SMTP using PHPMailer
function send_email_smtp(string $to, string $subject, string $message): bool {
    require_once __DIR__ . '/../config/settings.php';
    require_once __DIR__ . '/../vendor/autoload.php';
    
    if (!USE_SMTP) {
        return false;
    }
    
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        
        // Enable SMTP
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->Port = SMTP_PORT;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->SMTPAuth = true;
        
        // Gmail credentials
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        
        // Set sender and recipient
        $mail->setFrom(SMTP_USER, 'NeighborlyIFix Admin');
        $mail->addAddress($to);
        
        // Email content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        // Send email
        if ($mail->send()) {
            log_event("Email sent successfully to: $to");
            return true;
        } else {
            log_event("Email Error: " . $mail->ErrorInfo);
            return false;
        }
        
    } catch (Exception $e) {
        log_event("Email Exception: " . $e->getMessage());
        return false;
    }
}