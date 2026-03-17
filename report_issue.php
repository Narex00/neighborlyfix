<?php
$page_title = 'Report Issue';
require_once 'includes/auth_check.php';
if (is_admin()) redirect(base_url('admin/'));

$pdo = getDBConnection();
$categories = $pdo->query("SELECT * FROM categories WHERE is_active=1 ORDER BY name")->fetchAll();

$errors = [];
$old = ['title'=>'','description'=>'','category_id'=>'','location'=>'','priority'=>'medium'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid security token.'; }

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $location    = trim($_POST['location'] ?? '');
    $priority    = $_POST['priority'] ?? 'medium';

    $old = compact('title','description','category_id','location','priority');

    if (strlen($title) < 5)       $errors[] = 'Title must be at least 5 characters.';
    if (strlen($title) > 255)     $errors[] = 'Title must not exceed 255 characters.';
    if (strlen($description) < 20) $errors[] = 'Description must be at least 20 characters.';
    if ($category_id < 1)          $errors[] = 'Please select a category.';
    if (!in_array($priority, ['low','medium','high','critical'])) $errors[] = 'Invalid priority level.';

    // Image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $imagePath = upload_image($_FILES['image']);
        if (!$imagePath) $errors[] = 'Invalid image. Allowed: JPG, PNG, WEBP (max 2MB).';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO issues (title,description,category_id,user_id,priority,location,image_path) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$title, $description, $category_id, current_user_id(), $priority, $location, $imagePath]);
        set_flash('success', 'Issue reported successfully! We will review it shortly.');
        redirect(base_url('my_issues.php'));
    }
    unset($_SESSION['csrf_token']);
}
require_once 'includes/header.php';
?>

<section class="py-4">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-8">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <h3 class="fw-bold mb-1"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Report an Issue</h3>
            <p class="text-muted mb-4">Provide details about the infrastructure problem you've identified.</p>

            <?php if ($errors): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo "<li>".sanitize($e)."</li>"; ?></ul></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" novalidate>
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Issue Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="e.g., Large pothole on Oak Street" value="<?= sanitize($old['title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $old['category_id']==$c['id']?'selected':'' ?>><?= sanitize($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="5" placeholder="Describe the issue in detail (minimum 20 characters)…" required><?= sanitize($old['description']) ?></textarea>
                    <div class="form-text"><span id="charCount">0</span> characters</div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Location</label>
                        <input type="text" name="location" class="form-control" placeholder="e.g., 12 Oak Street, District 5" value="<?= sanitize($old['location']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="low" <?= $old['priority']==='low'?'selected':'' ?>>Low</option>
                            <option value="medium" <?= $old['priority']==='medium'?'selected':'' ?>>Medium</option>
                            <option value="high" <?= $old['priority']==='high'?'selected':'' ?>>High</option>
                            <option value="critical" <?= $old['priority']==='critical'?'selected':'' ?>>Critical</option>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Attach Photo</label>
                    <input type="file" name="image" class="form-control" accept="image/*" id="imageInput">
                    <div class="form-text">Optional. Max 5 MB. JPG, PNG, GIF, or WEBP.</div>
                    <img id="imagePreview" class="img-thumbnail mt-2 d-none" style="max-height:200px" alt="Preview">
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="fas fa-paper-plane me-2"></i>Submit Report
                </button>
            </form>
        </div>
    </div>
</div>
</div>
</div>
</section>

<?php require_once 'includes/footer.php'; ?>