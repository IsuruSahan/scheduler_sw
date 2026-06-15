<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// 1. Handle AJAX Delete Request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    echo json_encode(['status' => 'success']);
    exit();
}

// 2. Fetch Schedules
$schedules = $pdo->query("
    SELECT s.*, a.agency_name, c.client_name 
    FROM schedules s 
    JOIN agencies a ON s.agency_id = a.id 
    JOIN clients c ON s.client_id = c.id 
    ORDER BY s.id DESC
")->fetchAll();

include '../includes/header.php'; 
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Manage Schedules</h3>
        <a href="create.php" class="btn btn-primary">+ New Schedule</a>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" id="filterSearch" class="form-control" placeholder="Search name/ref...">
                </div>
                <div class="col-md-3">
                    <select id="filterStatus" class="form-select" onchange="filterTable()">
                        <option value="">All Statuses</option>
                        <option value="Active">Active</option>
                        <option value="Pending Approval">Pending Approval</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <table class="table table-hover bg-white border rounded">
        <thead class="table-light">
            <tr><th>Schedule</th><th>Client</th><th>Status</th><th>Budget</th><th>Actions</th></tr>
        </thead>
        <tbody id="scheduleTableBody">
            <?php foreach($schedules as $s): ?>
            <tr data-status="<?php echo htmlspecialchars($s['status']); ?>">
                <td>
                    <?php echo htmlspecialchars($s['schedule_name']); ?>
                    <br><small class="text-muted"><?php echo htmlspecialchars($s['reference_no']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($s['client_name']); ?></td>
                <td>
                    <span class="badge <?php echo $s['status'] == 'Active' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                        <?php echo htmlspecialchars($s['status']); ?>
                    </span>
                </td>
                <td>Rs. <?php echo number_format($s['budget_allocated'], 2); ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                    <button class="btn btn-sm btn-outline-danger" onclick="prepareDelete(<?php echo $s['id']; ?>, this)">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white"><h5 class="modal-title">Confirm Delete</h5></div>
            <div class="modal-body">Are you sure you want to delete this schedule? This action cannot be undone.</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteId = null;
let deleteRow = null;

function prepareDelete(id, btnElement) {
    deleteId = id;
    deleteRow = btnElement.closest('tr');
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    fetch('manage.php?action=delete&id=' + deleteId)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            deleteRow.remove();
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
        } else {
            alert('Failed to delete.');
        }
    });
});

function filterTable() {
    const searchVal = document.getElementById('filterSearch').value.toLowerCase();
    const statusVal = document.getElementById('filterStatus').value;
    
    document.querySelectorAll('#scheduleTableBody tr').forEach(tr => {
        const textMatch = tr.innerText.toLowerCase().includes(searchVal);
        const statusMatch = statusVal === "" || tr.getAttribute('data-status') === statusVal;
        tr.style.display = (textMatch && statusMatch) ? '' : 'none';
    });
}

document.getElementById('filterSearch').addEventListener('keyup', filterTable);
</script>

<?php include '../includes/footer.php'; ?>