<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

// Fetch schedules with joined agency/client info
$schedules = $pdo->query("
    SELECT s.*, a.agency_name, c.client_name 
    FROM schedules s 
    JOIN agencies a ON s.agency_id = a.id 
    JOIN clients c ON s.client_id = c.id 
    ORDER BY s.created_at DESC
")->fetchAll();
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Manage Schedules</h3>
        <a href="create.php" class="btn btn-primary">+ New Schedule</a>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><input type="text" id="filterSearch" class="form-control" placeholder="Search name/ref..."></div>
                <div class="col-md-3">
                    <select id="filterStatus" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Active">Active</option>
                        <option value="Pending Approval">Pending Approval</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <table class="table table-hover bg-white border rounded">
        <thead class="table-light">
            <tr><th>Schedule</th><th>Client</th><th>Status</th><th>Budget</th><th>Actions</th></tr>
        </thead>
        <tbody id="scheduleTableBody">
            <?php foreach($schedules as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['schedule_name']); ?><br><small class="text-muted"><?php echo $s['reference_no']; ?></small></td>
                <td><?php echo htmlspecialchars($s['client_name']); ?></td>
                <td>
                    <span class="badge <?php echo $s['status'] == 'Active' ? 'bg-success' : 'bg-warning'; ?>">
                        <?php echo $s['status']; ?>
                    </span>
                </td>
                <td>Rs. <?php echo number_format($s['budget_allocated'], 2); ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                    <?php if($s['status'] == 'Pending Approval'): ?>
                        <button class="btn btn-sm btn-success">Approve</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// Basic search filter
document.getElementById('filterSearch').addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#scheduleTableBody tr').forEach(tr => {
        tr.style.display = tr.innerText.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>