<?php
/**
 * About page with project description.
 */
$page_title = 'About';
require_once 'includes/header.php';
?>

<section class="py-5">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-8">
    <h2 class="fw-bold mb-4">About NeighborlyIFix</h2>
    <p>NeighborlyIFix is a secure, web-based civic issue reporting and management platform developed as part of an academic research project. It bridges the gap between citizens and local authorities by providing structured, transparent, and traceable infrastructure issue resolution workflows.</p>
    <h5 class="fw-semibold mt-4">Our Mission</h5>
    <p>To empower communities with a digital platform that transforms how infrastructure problems are reported, tracked, and resolved — fostering transparency, accountability, and civic participation.</p>
    <h5 class="fw-semibold mt-4">Key Capabilities</h5>
    <ul>
        <li>Structured issue reporting with categories, priorities, and media uploads</li>
        <li>Real-time status tracking with full audit trail</li>
        <li>Issue dependency management adapted from software engineering principles</li>
        <li>Role-based access control (Citizens &amp; Administrators)</li>
        <li>Responsive design compatible with all devices</li>
        <li>Secure implementation with CSRF protection, input validation, and password hashing</li>
    </ul>
    <h5 class="fw-semibold mt-4">Technology Stack</h5>
    <div class="row g-2 mt-2">
        <div class="col-6 col-md-3"><div class="card border-0 bg-light text-center p-3"><i class="fab fa-php fa-2x text-primary mb-2"></i><small class="fw-semibold">PHP 8+</small></div></div>
        <div class="col-6 col-md-3"><div class="card border-0 bg-light text-center p-3"><i class="fas fa-database fa-2x text-primary mb-2"></i><small class="fw-semibold">MySQL</small></div></div>
        <div class="col-6 col-md-3"><div class="card border-0 bg-light text-center p-3"><i class="fab fa-bootstrap fa-2x text-primary mb-2"></i><small class="fw-semibold">Bootstrap 5</small></div></div>
        <div class="col-6 col-md-3"><div class="card border-0 bg-light text-center p-3"><i class="fab fa-js fa-2x text-primary mb-2"></i><small class="fw-semibold">JavaScript</small></div></div>
    </div>
</div>
</div>
</div>
</section>

<?php require_once 'includes/footer.php'; ?>