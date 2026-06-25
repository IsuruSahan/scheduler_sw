<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

function logChange($pdo, $schedule_id, $type, $old_val, $new_val) {
    $stmt = $pdo->prepare("INSERT INTO schedule_audit_log (schedule_id, change_type, old_value, new_value) VALUES (?, ?, ?, ?)");
    $stmt->execute([$schedule_id, $type, (string)$old_val, (string)$new_val]);
}

// 1. Handle AJAX Actions
if (isset($_GET['action'])) {
    // Original Delete Logic
    if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(['status' => 'success']);
        exit();
    } 
    
    // Original Stop Logic
    if ($_GET['action'] === 'stop' && isset($_GET['id'])) {
        try {
            $pdo->beginTransaction();
            $id = $_GET['id'];
            $stmt = $pdo->prepare("SELECT * FROM schedules WHERE id = ?");
            $stmt->execute([$id]);
            $s = $stmt->fetch();

            $start = new DateTime($s['start_date']);
            $end = new DateTime($s['end_date']);
            $today = new DateTime();
            $totalDays = max(1, $end->diff($start)->days + 1);
            $activeDays = max(1, min($totalDays, $today->diff($start)->days + 1));
            
            $dailyRate = $s['budget_allocated'] / $totalDays;
            $burnedCost = $dailyRate * $activeDays;
            $remainingBudget = max(0, $s['budget_allocated'] - $burnedCost);

            $items = $pdo->prepare("SELECT content_item_id, platform_id, placement_id, quantity FROM schedule_items WHERE schedule_id = ?");
            $items->execute([$id]);
            $inventory_log = "";
            
            foreach ($items as $item) {
                $returnQty = ceil($item['quantity'] * (max(0, $totalDays - $activeDays) / $totalDays));
                $stmt = $pdo->prepare("UPDATE inventory SET used_qty = used_qty - ? WHERE rate_card_id = (SELECT id FROM rate_cards WHERE content_item_id = ? AND platform_id = ? AND placement_id = ? LIMIT 1)");
                $stmt->execute([$returnQty, $item['content_item_id'], $item['platform_id'], $item['placement_id']]);
                $inventory_log .= "Item: {$item['content_item_id']} | Returned: {$returnQty}\n";
            }
            
            $pdo->prepare("UPDATE schedules SET status = 'Stopped', final_cost = ?, days_run = ?, total_days = ?, remaining_budget = ? WHERE id = ?")
                ->execute([$burnedCost, $activeDays, $totalDays, $remainingBudget, $id]);
            
            $pdo->commit();
            echo json_encode(['status' => 'success', 'report' => ['total_budget' => number_format($s['budget_allocated'], 2), 'active_days' => $activeDays, 'total_days' => $totalDays, 'burned_cost' => number_format($burnedCost, 2), 'remaining_budget' => number_format($remainingBudget, 2), 'inventory_details' => $inventory_log]]);
        } catch (Exception $e) { $pdo->rollBack(); echo json_encode(['status' => 'error', 'message' => $e->getMessage()]); }
        exit();
    }

 // Budget Extension & Date Adjustment Logic
if ($_GET['action'] === 'request_extension' && isset($_POST['id'])) {
    try {
        $pdo->beginTransaction();
        $id = (int)$_POST['id'];
        $addBudget = (float)$_POST['add_budget']; // This might be 0
        $newEndDate = $_POST['new_end_date'];
        
        // 1. Get current schedule info
        $stmt = $pdo->prepare("SELECT end_date, budget_allocated FROM schedules WHERE id = ?");
        $stmt->execute([$id]);
        $old = $stmt->fetch();
        
        // 2. Logic:
        // If addBudget > 0: Send to approval (as before)
        // If addBudget == 0: Only update the End Date, keep budget exactly as it is
        if ($addBudget > 0) {
            $pdo->prepare("INSERT INTO schedule_approval_requests (schedule_id, new_end_date, additional_budget, status) VALUES (?, ?, ?, 'Pending')")
                ->execute([$id, $newEndDate, $addBudget]);
            
            $pdo->prepare("UPDATE schedules SET status = 'Pending Approval' WHERE id = ?")
                ->execute([$id]);
                
            echo json_encode(['status' => 'success', 'message' => 'Budget increase requested. Sent to Marketing Officer.']);
        } else {
            // Path A: Auto-update ONLY the Date. Budget remains unchanged.
            $pdo->prepare("UPDATE schedules SET end_date = ? WHERE id = ?")
                ->execute([$newEndDate, $id]);
            
            // Log the change
            logChange($pdo, $id, 'Date Adjustment', $old['end_date'], $newEndDate);
            
            echo json_encode(['status' => 'success', 'message' => 'Schedule date extended successfully. Budget remained unchanged.']);
        }
        
        $pdo->commit();
    } catch (Exception $e) { 
        $pdo->rollBack(); 
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]); 
    }
    exit();
}

// Budget Reduction Logic
if ($_GET['action'] === 'request_reduction' && isset($_POST['id'])) {
    try {
        $pdo->beginTransaction();
        
        $id = (int)$_POST['id'];
        $newBudget = (float)$_POST['new_budget'];
        
        // 1. Fetch current schedule and content commitments
        // We calculate both the money already spent (final_cost) 
        // AND the total committed cost of items currently in the schedule
        $stmt = $pdo->prepare("
            SELECT s.budget_allocated, s.final_cost, 
                   (SELECT SUM(cost) FROM schedule_items WHERE schedule_id = s.id) as total_item_cost
            FROM schedules s 
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        $s = $stmt->fetch();
        
        if (!$s) throw new Exception("Schedule not found.");

        // 2. Validation A: Cannot reduce budget below the amount already spent
        if ($newBudget < $s['final_cost']) {
            throw new Exception("Cannot reduce budget below the amount already spent (Rs. " . number_format($s['final_cost'], 2) . ")");
        }

        // 3. Validation B: Cannot reduce budget below the total cost of committed items
        $minAllowed = max((float)$s['final_cost'], (float)$s['total_item_cost']);
        if ($newBudget < $minAllowed) {
            throw new Exception("Cannot reduce budget below total committed content costs (Rs. " . number_format($minAllowed, 2) . ")");
        }

        // 4. Update Budget
        $pdo->prepare("UPDATE schedules SET budget_allocated = ? WHERE id = ?")
            ->execute([$newBudget, $id]);

        // 5. Save Audit Log
        $logStmt = $pdo->prepare("
            INSERT INTO schedule_audit_log (schedule_id, change_type, old_value, new_value) 
            VALUES (?, 'Budget Reduction', ?, ?)
        ");
        $logStmt->execute([$id, $s['budget_allocated'], $newBudget]);
        
        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Budget reduced successfully.']);
    } catch (Exception $e) { 
        $pdo->rollBack(); 
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]); 
    }
    exit();
}

    // REPLACE the stop_manual block in manage.php with this:
    if ($_GET['action'] === 'stop_manual' && isset($_POST['id'])) {
        try {
            $pdo->beginTransaction();
            $id = $_POST['id'];
            $stmt = $pdo->prepare("SELECT * FROM schedules WHERE id = ?");
            $stmt->execute([$id]);
            $s = $stmt->fetch();
            
            $start = new DateTime($s['start_date']);
            $end = new DateTime($s['end_date']);
            $today = new DateTime();
            $totalDays = max(1, $end->diff($start)->days + 1);
            $activeDays = max(1, $today->diff($start)->days + 1);
            $autoCost = ($s['budget_allocated'] / $totalDays) * $activeDays;

            $manualCosts = array_filter($_POST['daily_costs'], fn($val) => $val !== '' && $val !== null);
            $manualTotal = array_sum($manualCosts);
            
            foreach ($manualCosts as $date => $val) {
                $pdo->prepare("INSERT INTO schedule_daily_costs (schedule_id, cost_date, manual_cost) VALUES (?, ?, ?)")
                    ->execute([$id, $date, $val]);
            }

            if (!empty($manualCosts) && $manualTotal > $autoCost) {
                $pdo->prepare("UPDATE schedules SET status = 'Pending Approval (Cost Review)', final_cost = ? WHERE id = ?")
                    ->execute([$manualTotal, $id]);
                echo json_encode(['status' => 'success', 'message' => "Manual cost exceeds estimate. Sent for approval."]);
            } else {
                $finalCost = ($manualTotal > 0) ? $manualTotal : $autoCost;
                
                $items = $pdo->prepare("SELECT si.quantity, si.content_item_id, si.platform_id, si.placement_id, ci.name as content_name FROM schedule_items si JOIN content_items ci ON si.content_item_id = ci.id WHERE schedule_id = ?");
                $items->execute([$id]);
                $inventory_log = "";
                foreach ($items as $item) {
                    $returnQty = ceil($item['quantity'] * max(0, $totalDays - $activeDays) / $totalDays);
                    $pdo->prepare("UPDATE inventory SET used_qty = used_qty - ? WHERE rate_card_id = (SELECT id FROM rate_cards WHERE content_item_id = ? AND platform_id = ? AND placement_id = ? LIMIT 1)")
                        ->execute([$returnQty, $item['content_item_id'], $item['platform_id'], $item['placement_id']]);
                    $inventory_log .= "Item: {$item['content_name']} | Returned: {$returnQty}\n";
                }
                
                $pdo->prepare("UPDATE schedules SET status = 'Stopped', final_cost = ?, days_run = ?, total_days = ?, remaining_budget = ? WHERE id = ?")
                    ->execute([$finalCost, $activeDays, $totalDays, max(0, $s['budget_allocated'] - $finalCost), $id]);

                // RETURN THE REPORT OBJECT HERE
                echo json_encode([
                    'status' => 'success',
                    'report' => [
                        'total_budget' => number_format($s['budget_allocated'], 2),
                        'active_days' => $activeDays,
                        'total_days' => $totalDays,
                        'burned_cost' => number_format($finalCost, 2),
                        'remaining_budget' => number_format(max(0, $s['budget_allocated'] - $finalCost), 2),
                        'inventory_details' => $inventory_log
                    ]
                ]);
            }
            $pdo->commit();
        } catch (Exception $e) { $pdo->rollBack(); echo json_encode(['status' => 'error', 'message' => $e->getMessage()]); }
        exit();
    }

   if ($_GET['action'] === 'process_approval') {
    $req_id = $_POST['request_id'];
    $sch_id = $_POST['schedule_id'];
    $action = $_POST['action'];
    $status = $_POST['status'];

    try {
        $pdo->beginTransaction();

        if ($action === 'Approved') {
            // 1. If it's a formal budget request, update budget
            if ($req_id > 0) {
                $stmt = $pdo->prepare("SELECT * FROM schedule_approval_requests WHERE id = ?");
                $stmt->execute([$req_id]);
                $req = $stmt->fetch();
                
                $pdo->prepare("UPDATE schedules SET end_date = ?, budget_allocated = budget_allocated + ? WHERE id = ?")
                    ->execute([$req['new_end_date'], $req['additional_budget'], $sch_id]);
                
                $pdo->prepare("UPDATE schedule_approval_requests SET status = 'Approved' WHERE id = ?")
                    ->execute([$req_id]);
            }
            // 2. Always set schedule to Active
            $pdo->prepare("UPDATE schedules SET status = 'Active' WHERE id = ?")->execute([$sch_id]);
        } else {
            // Reject: Just revert status
            $pdo->prepare("UPDATE schedules SET status = 'Active' WHERE id = ?")->execute([$sch_id]);
            if ($req_id > 0) {
                $pdo->prepare("UPDATE schedule_approval_requests SET status = 'Rejected' WHERE id = ?")->execute([$req_id]);
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => "Request $action successfully."]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}


}

$schedules = $pdo->query("SELECT s.*, a.agency_name, c.client_name FROM schedules s JOIN agencies a ON s.agency_id = a.id JOIN clients c ON s.client_id = c.id ORDER BY s.id DESC")->fetchAll();

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
                <div class="col-md-3"><input type="text" id="filterSearch" class="form-control" placeholder="Search name/ref..."></div>
                <div class="col-md-3">
                    <select id="filterStatus" class="form-select" onchange="filterTable()">
                        <option value="">All Statuses</option>
                        <option value="Active">Active</option>
                        <option value="Stopped">Stopped</option>
                        <option value="Pending Approval">Pending Approval</option>
                        <option value="Pending Approval (Cost Review)">Pending Review</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle bg-white mb-0">
                <thead class="table-light">
                    <tr><th>Schedule / Ref</th><th>Client</th><th>Team</th><th>Status</th><th>Budget</th><th class="text-center">Actions</th></tr>
                </thead>
                <tbody id="scheduleTableBody">
                    <?php foreach($schedules as $s): ?>
                    <tr data-status="<?php echo htmlspecialchars($s['status']); ?>">
                        <td>
                            <div class="fw-bold"><?php echo htmlspecialchars($s['schedule_name']); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($s['reference_no']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($s['client_name']); ?></td>
                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($s['assigned_team']); ?></span></td>
                        <td>
                            <?php $badgeClass = ($s['status'] == 'Active') ? 'bg-success' : (($s['status'] == 'Pending Approval (Cost Review)') ? 'bg-danger' : (($s['status'] == 'Pending Approval') ? 'bg-warning text-dark' : 'bg-secondary')); ?>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($s['status']); ?></span>
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

<script>
let deleteId = null; let deleteRow = null;
function prepareDelete(id, btnElement) { deleteId = id; deleteRow = btnElement.closest('tr'); new bootstrap.Modal(document.getElementById('deleteModal')).show(); }

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    fetch('manage.php?action=delete&id=' + deleteId)
    .then(r => r.json()).then(data => {
        if (data.status === 'success') { deleteRow.remove(); bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide(); }
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