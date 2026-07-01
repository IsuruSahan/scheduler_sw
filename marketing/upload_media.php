<?php 
require_once __DIR__ . '/../config/config.php';
include '../includes/header.php'; 

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $schedule_name = $_POST['schedule_name'];
        $upload_dir = __DIR__ . '/../uploads/';

        // Ensure directories exist
        if (!is_dir($upload_dir . 'media')) mkdir($upload_dir . 'media', 0777, true);
        if (!is_dir($upload_dir . 'docs')) mkdir($upload_dir . 'docs', 0777, true);

        // Process Dynamic Media Rows
        if (!empty($_FILES['media_files']['name'])) {
            foreach ($_FILES['media_files']['tmp_name'] as $key => $tmp_name) {
                if (!empty($tmp_name)) {
                    $ref = $_POST['refs'][$key];
                    $path = 'media/' . time() . '_' . basename($_FILES['media_files']['name'][$key]);
                    
                    if (move_uploaded_file($tmp_name, $upload_dir . $path)) {
                        $pdo->prepare("INSERT INTO media_library (reference_no, schedule_name, file_path, file_type) VALUES (?, ?, ?, 'media')")
                            ->execute([$ref, $schedule_name, $path]);
                    }
                }
            }
        }

        // Process Documents
        if (!empty($_FILES['docs']['name'][0])) {
            foreach ($_FILES['docs']['tmp_name'] as $key => $tmp_name) {
                $path = 'docs/' . time() . '_' . basename($_FILES['docs']['name'][$key]);
                if (move_uploaded_file($tmp_name, $upload_dir . $path)) {
                    $pdo->prepare("INSERT INTO media_library (reference_no, schedule_name, file_path, file_type) VALUES (?, ?, ?, 'doc')")
                        ->execute([$_POST['reference_no'], $schedule_name, $path]);
                }
            }
        }
        $message = '<div class="alert alert-success">All assets uploaded and linked successfully.</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}
?>

<div class="container-fluid px-4">
    <h3 class="mb-4">Media & Documentation Upload</h3>
    <?php echo $message; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white">General Information</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Schedule Name</label>
                        <input type="text" name="schedule_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Main Reference No</label>
                        <input type="text" name="reference_no" class="form-control" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white">Upload Media Assets</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Media File</th>
                            <th>Individual Reference Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="mediaBody">
                        <tr>
                            <td><input type="file" name="media_files[]" class="form-control" required></td>
                            <td><input type="text" name="refs[]" class="form-control" placeholder="Unique Ref" required></td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-secondary" id="addRow">+ Add More Media</button>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white">Supporting Documents (Excel/PDF)</div>
            <div class="card-body">
                <input type="file" name="docs[]" class="form-control" multiple>
            </div>
        </div>

        <div class="text-end mb-4">
            <button type="submit" class="btn btn-primary btn-lg">Upload All Assets</button>
        </div>
    </form>
</div>

<script>
document.getElementById('addRow').addEventListener('click', function() {
    const tbody = document.getElementById('mediaBody');
    const newRow = `<tr>
        <td><input type="file" name="media_files[]" class="form-control" required></td>
        <td><input type="text" name="refs[]" class="form-control" placeholder="Unique Ref" required></td>
        <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
    </tr>`;
    tbody.insertAdjacentHTML('beforeend', newRow);
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-row')) {
        const rows = document.querySelectorAll('#mediaBody tr');
        if (rows.length > 1) {
            e.target.closest('tr').remove();
        } else {
            alert("At least one media file row is required.");
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>