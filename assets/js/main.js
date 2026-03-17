/**
 * NeighborlyIFix — Client-Side JavaScript
 */
document.addEventListener('DOMContentLoaded', function () {

    /* ── Image Preview ─────────────────────────── */
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = e => {
                    imagePreview.src = e.target.result;
                    imagePreview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.classList.add('d-none');
            }
        });
    }

    /* ── Character Counter ─────────────────────── */
    const descField = document.querySelector('textarea[name="description"]');
    const charCount = document.getElementById('charCount');
    if (descField && charCount) {
        const update = () => charCount.textContent = descField.value.length;
        descField.addEventListener('input', update);
        update();
    }

    /* ── Auto-dismiss alerts after 5 seconds ───── */
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    /* ── Confirm before toggling user status ───── */
    document.querySelectorAll('a[href*="toggle="]').forEach(link => {
        link.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to change this user\'s status?')) {
                e.preventDefault();
            }
        });
    });

    /* ── Bootstrap tooltip init ────────────────── */
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

    /* ── Prevent double submission ─────────────── */
    document.querySelectorAll('form button[type="submit"], form input[type="submit"]').forEach(btn => {
        btn.addEventListener('click', function () {
            this.disabled = true;
            this.textContent = 'Submitting...';
            this.form.submit();
        });
    });
});