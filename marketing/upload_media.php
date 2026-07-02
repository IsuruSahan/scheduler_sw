<?php 
require_once __DIR__ . '/../config/config.php';


// --- AJAX INTERCEPTION BLOCK ---
// --- AJAX INTERCEPTION BLOCK ---
if (isset($_GET['ajax_action']) && $_GET['ajax_action'] === 'get_clients') {
    header('Content-Type: application/json');
    $agency_id = $_GET['agency_id'] ?? 0;
    $stmt = $pdo->prepare("SELECT id, client_name FROM clients WHERE agency_id = ? ORDER BY client_name");
    $stmt->execute([$agency_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit; 
}

include '../includes/header.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_record'])) {
    if ($_POST['type'] === 'agency') {
        $pdo->prepare("INSERT INTO agencies (agency_name) VALUES (?)")->execute([$_POST['new_name']]);
} else {
        $pdo->prepare("INSERT INTO clients (client_name, agency_id) VALUES (?, ?)")->execute([$_POST['new_name'], $_POST['agency_id']]);
    }
    // Safe redirect for CPanel/shared hosting
    echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['save_record'])) {
    try {
        $schedule_name = $_POST['schedule_name'];
        $agency_id     = $_POST['agency_id'];
        $client_id     = $_POST['client_id'];
        $start_date    = $_POST['start_date'];
        $end_date      = $_POST['end_date'];
        $note          = $_POST['note'];
        $upload_dir    = __DIR__ . '/../uploads/';

        if (!is_dir($upload_dir . 'media')) mkdir($upload_dir . 'media', 0777, true);
        if (!is_dir($upload_dir . 'docs')) mkdir($upload_dir . 'docs', 0777, true);

        // Fetch Names for logging
        $agency_name = $pdo->prepare("SELECT agency_name FROM agencies WHERE id=?");
        $agency_name->execute([$agency_id]);
        $aname = $agency_name->fetchColumn();

        $client_name = $pdo->prepare("SELECT client_name FROM clients WHERE id=?");
        $client_name->execute([$client_id]);
        $cname = $client_name->fetchColumn();

        // Process Media
        if (!empty($_FILES['media_files']['name'])) {
            foreach ($_FILES['media_files']['tmp_name'] as $key => $tmp_name) {
                if (!empty($tmp_name)) {
                    $desc = $_POST['descriptions'][$key];
                    $path = 'media/' . time() . '_' . basename($_FILES['media_files']['name'][$key]);
                    if (move_uploaded_file($tmp_name, $upload_dir . $path)) {
                        $stmt = $pdo->prepare("INSERT INTO media_library (schedule_name, file_path, file_type, description, agency_name, client_name, start_date, end_date, note, is_acknowledged, acknowledged_at) VALUES (?, ?, 'media', ?, ?, ?, ?, ?, ?, 1, NOW())");
                        $stmt->execute([$schedule_name, $path, $desc, $aname, $cname, $start_date, $end_date, $note]);
                    }
                }
            }
        }

        // --- REPLACE YOUR EXISTING "Process Documents" section with this: ---
if (!empty($_FILES['docs']['name'][0])) {
    foreach ($_FILES['docs']['tmp_name'] as $key => $tmp_name) {
        if (!empty($tmp_name)) {
            $doc_name = basename($_FILES['docs']['name'][$key]);
            $path = 'docs/' . time() . '_' . $doc_name;
            
            if (move_uploaded_file($tmp_name, $upload_dir . $path)) {
                $stmt = $pdo->prepare("
                    INSERT INTO media_library 
                    (schedule_name, file_path, file_type, agency_name, client_name, start_date, end_date, note) 
                    VALUES (?, ?, 'doc', ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$schedule_name, $path, $aname, $cname, $start_date, $end_date, $note]);
            }
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
                    <div class="col-md-5">
                        <label class="form-label">Agency</label>
                        <select name="agency_id" id="agency_id" class="form-select" onchange="loadClients()" required>
                            <option value="">Select Agency</option>
                            <?php foreach($pdo->query("SELECT * FROM agencies ORDER BY agency_name") as $a): ?>
                                <option value="<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['agency_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-primary w-100" onclick="showAddModal('agency')">+</button>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Client</label>
                        <select name="client_id" id="client_id" class="form-select" required>
                            <option value="">Select Agency First</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-primary w-100" onclick="showAddModal('client')">+</button>
                    </div>
                    <div class="col-md-4"><label class="form-label">Schedule Name</label><input type="text" name="schedule_name" class="form-control" required></div>
                    <div class="col-md-2"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" required></div>
                    <div class="col-md-2"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" required></div>
                    <div class="col-12"><label class="form-label">Notes</label><textarea name="note" class="form-control" rows="2"></textarea></div>
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
                            <td><input type="text" name="descriptions[]" class="form-control"></td>
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

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">Add New</h5></div>
            <div class="modal-body">
                <input type="hidden" name="save_record" value="1">
                <input type="hidden" name="type" id="modalType">
                <input type="hidden" name="agency_id" id="modalAgencyId">
                <input type="text" name="new_name" class="form-control" placeholder="Enter Name" required>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function loadClients() {
    const agencyId = document.getElementById('agency_id').value;
    const clientSelect = document.getElementById('client_id');

    if (!agencyId) {
        clientSelect.innerHTML = '<option value="">Select Agency First</option>';
        return;
    }

    // Use current URL with the ajax_action parameter
    fetch('?ajax_action=get_clients&agency_id=' + agencyId)
        .then(res => res.json())
        .then(data => {
            clientSelect.innerHTML = '<option value="">Select Client</option>';
            data.forEach(client => {
                clientSelect.innerHTML += `<option value="${client.id}">${client.client_name}</option>`;
            });
        })
        .catch(err => console.error('Error:', err));
}

function showAddModal(type) {
    document.getElementById('modalType').value = type;
    document.getElementById('modalAgencyId').value = document.getElementById('agency_id').value;
    document.getElementById('modalTitle').innerText = 'Add New ' + type;
    new bootstrap.Modal(document.getElementById('addModal')).show();
}

document.getElementById('addRow').addEventListener('click', () => {
    document.getElementById('mediaBody').insertAdjacentHTML('beforeend', '<tr><td><input type="file" name="media_files[]" class="form-control" required></td><td><input type="text" name="descriptions[]" class="form-control"></td><td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td></tr>');
});

document.addEventListener('click', (e) => {
    if (e.target.classList.contains('remove-row')) e.target.closest('tr').remove();
});
</script>
<?php include '../includes/footer.php'; ?>