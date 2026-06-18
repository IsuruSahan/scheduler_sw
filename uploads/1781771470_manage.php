<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// 1. Handle AJAX Actions
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(['status' => 'success']);
        exit();
    } 
    
    if ($_GET['action'] === 'stop' && isset($_GET['id'])) {
        try {
            $pdo->beginTransaction();
            
            // 1. Get Schedule details to calculate burned cost
            $stmt = $pdo->prepare("SELECT * FROM schedules WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $s = $stmt->fetch();

            $start = new DateTime($s['start_date']);
            $end = new DateTime($s['end_date']);
            $today = new DateTime();
            
            $totalDays = max(1, $end->diff($start)->days + 1);
            $activeDays = max(1, min($totalDays, $today->diff($start)->days + 1));
            
            $dailyRate = $s['budget_allocated'] / $totalDays;
            $burnedCost = $dailyRate * $activeDays;
            $remainingBudget = max(0, $s['budget_allocated'] - $burnedCost);

            // 2. Release inventory based on remaining days
            $items = $pdo->prepare("SELECT content_item_id, platform_id, placement_id, quantity FROM schedule_items WHERE schedule_id = ?");
            $items->execute([$_GET['id']]);
            $inventory_log = "";
            
            foreach ($items as $item) {
                $remainingDays = max(0, $totalDays - $activeDays);
                $returnQty = ceil($item['quantity'] * ($remainingDays / $totalDays));

                $stmt = $pdo->prepare("UPDATE inventory SET used_qty = used_qty - ? WHERE rate_card_id = (SELECT id FROM rate_cards WHERE content_item_id = ? AND platform_id = ? AND placement_id = ? LIMIT 1)");
                $stmt->execute([$returnQty, $item['content_item_id'], $item['platform_id'], $item['placement_id']]);
                
                $inventory_log .= "Item: {$item['content_item_id']} | Returned: {$returnQty}\n";
            }
            
            // 3. Save final cost, summary metrics and stop status to database
            $pdo->prepare("
                UPDATE schedules 
                SET status = 'Stopped', 
                    final_cost = ?, 
                    days_run = ?, 
                    total_days = ?, 
                    remaining_budget = ? 
                WHERE id = ?"
            )->execute([$burnedCost, $activeDays, $totalDays, $remainingBudget, $_GET['id']]);
            
            $pdo->commit();
            
            echo json_encode([
                'status' => 'success',
                'report' => [
                    'total_budget' => number_format($s['budget_allocated'], 2),
                    'active_days' => $activeDays,
                    'total_days' => $totalDays,
                    'burned_cost' => number_format($burnedCost, 2),
                    'remaining_budget' => number_format($remainingBudget, 2),
                    'inventory_details' => $inventory_log
                ]
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();
    }
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
                        <option value="Stopped">Stopped</option>
                        <option value="Pending Approval">Pending Approval</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle bg-white mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Schedule / Ref</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th>Budget</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="scheduleTableBody">
                    <?php foreach($schedules as $s): ?>
                    <tr data-status="<?php echo htmlspecialchars($s['status']); ?>">
                        <td>
                            <div class="fw-bold"><?php echo htmlspecialchars($s['schedule_name']); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($s['reference_no']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($s['client_name']); ?></td>
                        <td>
                            <?php 
                                $badgeClass = 'bg-secondary';
                                if ($s['status'] == 'Active') $badgeClass = 'bg-success';
                                elseif ($s['status'] == 'Pending Approval') $badgeClass = 'bg-warning text-dark';
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>">
                                <?php echo htmlspecialchars($s['status']); ?>
                            </span>
                        </td>
                        <td>Rs. <?php echo number_format($s['budget_allocated'], 2); ?></td>
                        <td class="text-center">
                            <a href="details.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-info text-white">Manage</a>
                            <a href="edit.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <button class="btn btn-sm btn-outline-danger" onclick="prepareDelete(<?php echo $s['id']; ?>, this)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title">Confirm Delete</h5></div><div class="modal-body">Are you sure?</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-danger" id="confirmDeleteBtn">Yes, Delete</button></div></div></div></div>

<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white"><h5 class="modal-title">Schedule Finalization Report</h5></div>
            <div class="modal-body" id="reportContent"></div>
            <div class="modal-footer"><button type="button" class="btn btn-primary" onclick="location.reload()">Close & Refresh</button></div>
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
    .then(r => r.json()).then(data => {
        if (data.status === 'success') {
            deleteRow.remove();
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
        }
    });
});

function stopSchedule(id) {
    if (!confirm('Are you sure you want to stop this schedule?')) return;
    
    fetch('manage.php?action=stop&id=' + id)
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            let html = `
                <table class="table table-bordered">
                    <tr><th>Total Budget</th><td>Rs. ${data.report.total_budget}</td></tr>
                    <tr><th>Days Run</th><td>${data.report.active_days} / ${data.report.total_days}</td></tr>
                    <tr><th>Total Spent</th><td>Rs. ${data.report.burned_cost}</td></tr>
                    <tr><th>Remaining Budget</th><td>Rs. ${data.report.remaining_budget}</td></tr>
                </table>
                <h6>Itemized Inventory Released:</h6>
                <pre class="bg-light p-2">${data.report.inventory_details}</pre>
            `;
            document.getElementById('reportContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('reportModal')).show();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

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