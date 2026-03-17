<!-- ── FOOTER ──────────────────────────────────────── -->
<footer class="footer bg-dark text-light pt-5 pb-4 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-tools text-primary me-2"></i>NeighborlyIFix</h5>
                <p class="text-secondary">A secure, community-driven civic issue reporting platform that bridges citizens and local authorities for transparent infrastructure management.</p>
            </div>
            <div class="col-lg-2 col-md-4">
                <h6 class="fw-semibold mb-3">Quick Links</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?= base_url() ?>" class="text-secondary text-decoration-none">Home</a></li>
                    <li class="mb-2"><a href="<?= base_url('about.php') ?>" class="text-secondary text-decoration-none">About</a></li>
                    <li class="mb-2"><a href="<?= base_url('login.php') ?>" class="text-secondary text-decoration-none">Login</a></li>
                    <li class="mb-2"><a href="<?= base_url('register.php') ?>" class="text-secondary text-decoration-none">Register</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-4">
                <h6 class="fw-semibold mb-3">Issue Categories</h6>
                <ul class="list-unstyled">
                    <li class="mb-2 text-secondary"><i class="fas fa-road me-2"></i>Roads & Potholes</li>
                    <li class="mb-2 text-secondary"><i class="fas fa-lightbulb me-2"></i>Streetlights</li>
                    <li class="mb-2 text-secondary"><i class="fas fa-water me-2"></i>Drainage</li>
                    <li class="mb-2 text-secondary"><i class="fas fa-trash me-2"></i>Waste Management</li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-4">
                <h6 class="fw-semibold mb-3">Contact</h6>
                <ul class="list-unstyled text-secondary">
                    <li class="mb-2"><i class="fas fa-envelope me-2"></i>support@neighborlyifix.com</li>
                    <li class="mb-2"><i class="fas fa-phone me-2"></i>+44 123 456 7890</li>
                    <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>United Kingdom</li>
                </ul>
            </div>
        </div>
        <hr class="my-4 border-secondary">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text-secondary small">&copy; <?= date('Y') ?> NeighborlyIFix. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                <p class="mb-0 text-secondary small">Built with PHP &amp; MySQL | Academic Research Project</p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>