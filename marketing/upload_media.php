<?php 
require_once __DIR__ . '/../config/config.php';
include '../includes/header.php'; 

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Collect free-text fields
        $schedule_name = $_POST['schedule_name'];
        $agency_name   = $_POST['agency_name'];
        $client_name   = $_POST['client_name'];
        $start_date    = $_POST['start_date'];
        $end_date      = $_POST['end_date'];
        $note          = $_POST['note'];
        $upload_dir    = __DIR__ . '/../uploads/';

        if (!is_dir($upload_dir . 'media')) mkdir($upload_dir . 'media', 0777, true);
        if (!is_dir($upload_dir . 'docs')) mkdir($upload_dir . 'docs', 0777, true);

        // Process Media Files
        if (!empty($_FILES['media_files']['name'])) {
            foreach ($_FILES['media_files']['tmp_name'] as $key => $tmp_name) {
                if (!empty($tmp_name)) {
                   
                    $desc = $_POST['descriptions'][$key];
                    $path = 'media/' . time() . '_' . basename($_FILES['media_files']['name'][$key]);
                    
                    if (move_uploaded_file($tmp_name, $upload_dir . $path)) {
                        // Insert storing names instead of IDs


$stmt = $pdo->prepare("
    INSERT INTO media_library 
    (schedule_name, file_path, file_type, description, agency_name, client_name, start_date, end_date, note, is_acknowledged, acknowledged_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
");

// Execute array (9 variables)
$stmt->execute([
    $schedule_name, // 1
    $path,          // 2
    'media',        // 3
    $desc,          // 4
    $agency_name,   // 5
    $client_name,   // 6
    $start_date,    // 7
    $end_date,      // 8
    $note           // 9
]);
                    }
                }
            }
        }

        // Process Documents
        if (!empty($_FILES['docs']['name'][0])) {
            foreach ($_FILES['docs']['tmp_name'] as $key => $tmp_name) {
                $path = 'docs/' . time() . '_' . basename($_FILES['docs']['name'][$key]);
                if (move_uploaded_file($tmp_name, $upload_dir . $path)) {
                    $stmt = $pdo->prepare("INSERT INTO media_library (schedule_name, file_path, file_type, agency_name, client_name, start_date, end_date, note) VALUES (?, ?, 'doc', ?, ?, ?, ?, ?)");
                    $stmt->execute([$schedule_name, $path, $agency_name, $client_name, $start_date, $end_date, $note]);
                }
            }
        }
        $message = '<div class="alert alert-success">All assets uploaded successfully.</div>';
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
                        <label class="form-label">Agency Name</label>
                        <input type="text" name="agency_name" class="form-control" placeholder="Type Agency Name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Client Name</label>
                        <input type="text" name="client_name" class="form-control" placeholder="Type Client Name" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Schedule Name</label>
                        <input type="text" name="schedule_name" class="form-control" required>
                    </div>
                    <!-- <div class="col-md-4">
                        <label class="form-label">Main Reference No</label>
                        <input type="text" name="reference_no" class="form-control" required>
                    </div> -->
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Add any additional notes here..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white">Upload Media Assets</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead><tr><th>Media File</th><th>Description</th><th>Action</th></tr></thead>
                    <tbody id="mediaBody">
                        <tr>
                            <td><input type="file" name="media_files[]" class="form-control" required></td>
                            
                            <td><input type="text" name="descriptions[]" class="form-control" placeholder="Description"></td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-secondary" id="addRow">+ Add More Media</button>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white">Supporting Documents</div>
            <div class="card-body">
                <input type="file" name="docs[]" class="form-control" multiple>
            </div>
        </div>

        <div class="mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="ack" required>
                <label class="form-check-label" for="ack">I acknowledge that all information provided is accurate and files are correctly labeled.</label>
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
        
        <td><input type="text" name="descriptions[]" class="form-control" placeholder="Description"></td>
        <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
    </tr>`;
    tbody.insertAdjacentHTML('beforeend', newRow);
});

// 2. Remove row functionality
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-row')) {
        const rows = document.querySelectorAll('#mediaBody tr');
        // Ensure at least one row remains
        if (rows.length > 1) {
            e.target.closest('tr').remove();
        } else {
            alert("At least one media file row is required.");
        }
    }
});
</script>
<?php include '../includes/footer.php'; ?>