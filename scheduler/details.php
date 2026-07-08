<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$id = $_GET['id'] ?? 0;

// 1. Fetch Schedule Details
// 1. Fetch Schedule Details with days_run calculation
$stmt = $pdo->prepare("
    SELECT s.*, a.agency_name, c.client_name,
           DATEDIFF(CURRENT_DATE, s.start_date) as days_run
    FROM schedules s 
    JOIN agencies a ON s.agency_id = a.id 
    JOIN clients c ON s.client_id = c.id 
    WHERE s.id = ?
");
$stmt->execute([$id]);
$schedule = $stmt->fetch();



$stmt->execute([$id]);
$schedule = $stmt->fetch();

if (!$schedule) {
    header("Location: manage.php");
    exit();
}

// 2. Pre-calculate Auto-Daily-Rate for Reference
$start = new DateTime($schedule['start_date']);
$end = new DateTime($schedule['end_date']);
$totalDays = max(1, $end->diff($start)->days + 1);
$autoDailyRate = $schedule['budget_allocated'] / $totalDays;

// UPDATED: Using the correct column name 'changed_at'
$audit_stmt = $pdo->prepare("
    SELECT al.*, u.email as user_email
    FROM schedule_audit_log al
    LEFT JOIN users u ON al.changed_by = u.id
    WHERE al.schedule_id = ?
    ORDER BY al.changed_at DESC
");
$audit_stmt->execute([$id]);
$audit_logs = $audit_stmt->fetchAll();


$items_stmt = $pdo->prepare("
    SELECT si.*, ci.name AS content_name, p.platform_name, ap.placement_name, mf.format_name,
           GROUP_CONCAT(CONCAT(ma.file_path, '||', IFNULL(ma.file_reference, 'N/A')) SEPARATOR '###') as media_data
    FROM schedule_items si 
    JOIN content_items ci ON si.content_item_id = ci.id 
    JOIN platforms p ON si.platform_id = p.id 
    JOIN ad_placements ap ON si.placement_id = ap.id 
    JOIN rate_cards rc ON si.rate_card_id = rc.id -- Optimization: Join via PK
    JOIN media_formats mf ON rc.media_format_id = mf.id
    LEFT JOIN media_attachments ma ON si.id = ma.schedule_item_id
    WHERE si.schedule_id = ?
    GROUP BY si.id
");
$stmt = $pdo->prepare("
    SELECT s.budget_allocated, s.final_cost, 
           (SELECT SUM(cost) FROM schedule_items WHERE schedule_id = s.id) as total_item_cost
    FROM schedules s 
    WHERE s.id = ?
");
$stmt->execute([$id]); // Ensure $id is available here
$s = $stmt->fetch();

// 2. Determine the safe floor (the minimum)
// We use 0 if no costs are found yet to avoid errors
$finalCost = (float)($s['final_cost'] ?? 0);
$totalItemCost = (float)($s['total_item_cost'] ?? 0);
$minAllowed = max($finalCost, $totalItemCost);

$items_stmt->execute([$id]);
$items = $items_stmt->fetchAll();

$statusClass = ($schedule['status'] == 'Active') ? 'bg-success' : (($schedule['status'] == 'Stopped') ? 'bg-secondary' : 'bg-warning text-dark');
?>

<?php include '../includes/header.php'; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mx-4" role="alert">
        <strong>Error:</strong> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="container-fluid px-4">


    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="text-primary"><?php echo htmlspecialchars($schedule['schedule_name']); ?></h3>
            <span class="text-muted">Ref No: <?php echo htmlspecialchars($schedule['reference_no']); ?></span>
            <span class="badge <?php echo $statusClass; ?> ms-2"><?php echo htmlspecialchars($schedule['status']); ?></span>
        </div>
        <div>
            <?php if ($schedule['status'] === 'Active'): ?>
                <button class="btn btn-warning" onclick="openStopModal('<?php echo $schedule['start_date']; ?>', '<?php echo date('Y-m-d'); ?>', <?php echo $autoDailyRate; ?>)">Stop Schedule</button>
            <?php endif; ?>
<?php 
    // Add 'Pending Approval (Cost Review)' to the list of statuses that disable the buttons
    $isDisabled = ($schedule['status'] === 'Pending Approval' || 
                    $schedule['status'] === 'Pending Approval (Cost Review)' || 
                    $schedule['status'] === 'Stopped');
?>

<?php if ($isDisabled): ?>
    <button class="btn btn-secondary" disabled>
        <?php 
            if ($schedule['status'] === 'Stopped') echo 'Schedule Stopped';
            elseif ($schedule['status'] === 'Pending Approval (Cost Review)') echo 'Review Pending...';
            else echo 'Approval Pending...';
        ?>
    </button>
<?php else: ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#extendModal">
        Extend Schedule
    </button>
<?php endif; ?>

<?php if ($isDisabled): ?>
    <button class="btn btn-secondary" disabled>
        <?php 
            if ($schedule['status'] === 'Stopped') echo 'Schedule Stopped';
            elseif ($schedule['status'] === 'Pending Approval (Cost Review)') echo 'Review Pending...';
            else echo 'Approval Pending...';
        ?>
    </button>
<?php else: ?>
    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reduceModal">
        Reduce Budget
    </button>
<?php endif; ?>       


<a href="export_report.php?id=<?php echo $id; ?>" class="btn btn-success">Download Excel</a>
            <a href="manage.php" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

<?php if ($schedule['status'] === 'Stopped'): ?>
    <div class="card mb-4 border-info shadow-sm">
        <div class="card-header bg-info text-white"><strong>Schedule Finalization Summary</strong></div>
        <div class="card-body">
            <div class="row text-center">
<div class="col-md-3">
    <strong>Days Run:</strong><br>
    <?php 
    if ($schedule['status'] === 'Stopped') {
        echo ($schedule['total_days'] > 0) ? $schedule['days_run'] . ' / ' . $schedule['total_days'] : 'N/A';
    } else {
        $start = new DateTime($schedule['start_date']);
        $end = new DateTime($schedule['end_date']);
        $today = new DateTime();
        $daysRun = max(1, $today->diff($start)->days + 1);
        $totalDays = max(1, $end->diff($start)->days + 1);
        echo $daysRun . ' / ' . $totalDays . ' (Active)';
    }
    ?>
</div>                <div class="col-md-3"><strong>Total Spent:</strong><br>Rs. <?php echo number_format($schedule['final_cost'], 2); ?></div>
                <div class="col-md-3"><strong>Remaining Budget:</strong><br>Rs. <?php echo number_format($schedule['remaining_budget'], 2); ?></div>
                <div class="col-md-3"><strong>Final Status:</strong><br><span class="text-danger fw-bold">Stopped</span></div>
            </div>
        </div>
    </div>
<?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card p-3 shadow-sm border-0 h-100"><strong>Agency:</strong><br><?php echo htmlspecialchars($schedule['agency_name']); ?></div></div>
        <div class="col-md-3"><div class="card p-3 shadow-sm border-0"><strong>Client:</strong><br><?php echo htmlspecialchars($schedule['client_name']); ?></div></div>
        <div class="col-md-3"><div class="card p-3 shadow-sm border-0"><strong>Period:</strong><br><?php echo htmlspecialchars($schedule['start_date']) . ' to ' . htmlspecialchars($schedule['end_date']); ?></div></div>
        <div class="col-md-3"><div class="card p-3 shadow-sm border-0"><strong>Allocated Budget:</strong><br>Rs. <?php echo number_format($schedule['budget_allocated'], 2); ?></div></div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3"><strong>Detailed Content Breakdown</strong></div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Program</th><th>Platform / Placement</th><th>Format</th><th>Qty</th><th>Cost</th><th>Media Files & References</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item): 
                        $editData = json_encode([
                            'id' => $item['id'], 'sch_id' => $id, 'c_id' => $item['content_item_id'], 
                            'p_id' => $item['platform_id'], 'pl_id' => $item['placement_id'], 'qty' => $item['quantity']
                        ]);
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($item['content_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($item['platform_name'] . ' / ' . $item['placement_name']); ?></td>
                        <td><span class="badge bg-info"><?php echo htmlspecialchars($item['format_name']); ?></span></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>Rs. <?php echo number_format($item['cost'], 2); ?></td>
                        <td>
                            <?php if ($item['media_data']): 
                                $media_list = explode('###', $item['media_data']);
                                foreach ($media_list as $media): list($path, $ref) = explode('||', $media); ?>
                                    <div class="mb-1"><a href="<?php echo htmlspecialchars($path); ?>" target="_blank" class="btn btn-sm btn-outline-info">View</a> <small class="ms-2 text-muted fw-bold"><?php echo htmlspecialchars($ref); ?></small></div>
                                <?php endforeach; else: echo '<small class="text-muted">None</small>'; endif; ?>
                        </td>
                        <td class="text-end"><button class="btn btn-sm btn-outline-primary" onclick="openEditModal(<?php echo htmlspecialchars($editData); ?>)">Edit</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white py-3"><strong>Modification History</strong></div>
    <div class="table-responsive">
        <table class="table table-sm table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Activity</th>
                    <th>Previous</th>
                    <th>New</th>
                    <th>Changed By</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // 1. Corrected query using your exact table columns: changed_at
// UPDATED: Joined with 'roles' table to get the role name
$query = "SELECT al.*, u.email as user_email, r.role_name 
          FROM schedule_audit_log al 
          LEFT JOIN users u ON al.changed_by = u.id 
          LEFT JOIN roles r ON u.role_id = r.id 
          WHERE al.schedule_id = ? 
          ORDER BY al.changed_at DESC";
                          
                $logs = $pdo->prepare($query);
                $logs->execute([$id]);
                
                foreach($logs as $log): ?>
<tr>
    <td><?php echo date('d M Y H:i', strtotime($log['changed_at'])); ?></td>
    <td><span class="badge bg-dark"><?php echo htmlspecialchars($log['change_type']); ?></span></td>
    <td><?php echo is_numeric($log['old_value']) ? 'Rs. ' . number_format((float)$log['old_value'], 2) : htmlspecialchars($log['old_value']); ?></td>
    <td><?php echo is_numeric($log['new_value']) ? 'Rs. ' . number_format((float)$log['new_value'], 2) : htmlspecialchars($log['new_value']); ?></td>
    
    <td>
        <?php if ($log['user_email']): ?>
            <?php echo htmlspecialchars($log['user_email']); ?><br>
            <small class="badge bg-info text-dark"><?php echo htmlspecialchars($log['role_name'] ?? 'No Role'); ?></small>
        <?php else: ?>
            System
        <?php endif; ?>
    </td>
</tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
</div>

<div class="modal fade" id="stopScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="stopForm" class="modal-content">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="modal-header bg-danger text-white"><h5 class="modal-title">Manual Daily Cost Entry</h5></div>
            <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                <p class="text-muted">Automated daily rate is: Rs. <?php echo number_format($autoDailyRate, 2); ?>. Leave empty to use auto-calculation.</p>
                <table class="table table-bordered">
                    <thead><tr><th>Date</th><th>Manual Cost (Rs.)</th></tr></thead>
                    <tbody id="dailyCostRows"></tbody>
                </table>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-danger">Submit for Verification</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="update_item.php" method="POST" class="modal-content">
            <input type="hidden" name="item_id" id="edit_item_id">
            <input type="hidden" name="schedule_id" id="edit_schedule_id">
            <div class="modal-header"><h5 class="modal-title">Edit Program, Format & Qty</h5></div>
            <div class="modal-body">
                <label class="form-label">Program:</label>
                <select name="content_id" id="edit_content_id" class="form-select mb-2"><?php foreach($pdo->query("SELECT id, name FROM content_items") as $p) echo "<option value='{$p['id']}'>".htmlspecialchars($p['name'])."</option>"; ?></select>
                <label class="form-label">Platform:</label>
                <select name="platform_id" id="edit_platform_id" class="form-select mb-2"><?php foreach($pdo->query("SELECT id, platform_name FROM platforms") as $pl) echo "<option value='{$pl['id']}'>".htmlspecialchars($pl['platform_name'])."</option>"; ?></select>
                <label class="form-label">Placement:</label>
                <select name="placement_id" id="edit_placement_id" class="form-select mb-2"><?php foreach($pdo->query("SELECT id, placement_name FROM ad_placements") as $ap) echo "<option value='{$ap['id']}'>".htmlspecialchars($ap['placement_name'])."</option>"; ?></select>
                <label class="form-label">Format:</label>
                <select name="format_id" id="edit_format_id" class="form-select mb-2"><?php foreach($pdo->query("SELECT id, format_name FROM media_formats") as $f) echo "<option value='{$f['id']}'>".htmlspecialchars($f['format_name'])."</option>"; ?></select>
                <label class="form-label">Quantity:</label>
                <input type="number" name="quantity" id="edit_quantity" class="form-control" required>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Changes</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="reportModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-info text-white"><h5 class="modal-title">Schedule Finalization Report</h5></div><div class="modal-body" id="reportContent"></div><div class="modal-footer"><button type="button" class="btn btn-primary" onclick="location.reload()">Close & Refresh</button></div></div></div></div>

<div class="modal fade" id="extendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl"> <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Extend Schedule Builder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <iframe src="create_extension.php?schedule_id=<?php echo $id; ?>" 
                        style="width:100%; height:600px; border:none;"></iframe>
            </div>
        </div>
    </div>
</div>
                
                <label class="form-label">New End Date:</label>
                <input type="text" id="calendarExtend" name="new_end_date" class="form-control mb-3" 
                placeholder="Click to select date" required readonly>
                
                <label class="form-label">Additional Budget (Rs.):</label>
                <input type="number" name="add_budget" class="form-control" step="0.01" value="0" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="reduceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="manage.php?action=request_reduction" method="POST" class="modal-content">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reduce Budget</h5>
            </div>
            <div class="modal-body">
                <label class="form-label">New Total Budget (Rs.):</label>
                <input type="number" name="new_budget" class="form-control" 
                       step="0.01" 
                       min="<?php echo $minAllowed; ?>" 
                       required>
                <small class="text-danger">Minimum allowed: Rs. <?php echo number_format($minAllowed, 2); ?></small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitForm(this)">Confirm Reduction</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStopModal(startDate, endDate) {
    const tbody = document.getElementById('dailyCostRows');
    tbody.innerHTML = '';
    let start = new Date(startDate);
    let end = new Date(endDate);
    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
        let dateStr = d.toISOString().split('T')[0];
        tbody.innerHTML += `<tr><td>${dateStr}</td><td><input type="number" name="daily_costs[${dateStr}]" class="form-control" step="0.01" placeholder="Auto-calculated"></td></tr>`;
    }
    new bootstrap.Modal(document.getElementById('stopScheduleModal')).show();
}

document.getElementById('stopForm').addEventListener('submit', function(e) {
    e.preventDefault();
    fetch('manage.php?action=stop_manual', { method: 'POST', body: new FormData(this) })
    .then(r => r.json()).then(data => {
        if (data.status === 'success') {
            // Check if it's a full report OR just a message
            if (data.report) {
                // SUCCESS: Show the full breakdown modal
                let html = `<table class="table table-bordered">
                    <tr><th>Total Budget</th><td>Rs. ${data.report.total_budget}</td></tr>
                    <tr><th>Days Run</th><td>${data.report.active_days} / ${data.report.total_days}</td></tr>
                    <tr><th>Total Spent</th><td>Rs. ${data.report.burned_cost}</td></tr>
                    <tr><th>Remaining Budget</th><td>Rs. ${data.report.remaining_budget}</td></tr>
                </table>
                <h6>Inventory Released:</h6>
                <pre class="bg-light p-2">${data.report.inventory_details}</pre>`;
                
                document.getElementById('reportContent').innerHTML = html;
            } else {
                // PENDING APPROVAL: Show the specific status message
                document.getElementById('reportContent').innerHTML = `
                    <div class="alert alert-warning text-center">
                        <h5>${data.message}</h5>
                        <p>The schedule is now locked for review.</p>
                    </div>`;
            }
            
            // Hide Stop Modal and show Report Modal
            bootstrap.Modal.getInstance(document.getElementById('stopScheduleModal')).hide();
            new bootstrap.Modal(document.getElementById('reportModal')).show();
            
        } else {
            alert('Error: ' + data.message);
        }
    });
});

function openEditModal(data) {
    document.getElementById('edit_item_id').value = data.id;
    document.getElementById('edit_schedule_id').value = data.sch_id;
    document.getElementById('edit_content_id').value = data.c_id;
    document.getElementById('edit_platform_id').value = data.p_id;
    document.getElementById('edit_placement_id').value = data.pl_id;
    document.getElementById('edit_format_id').value = data.f_id;
    document.getElementById('edit_quantity').value = data.qty;
    new bootstrap.Modal(document.getElementById('editItemModal')).show();
}
document.getElementById('extendForm').addEventListener('submit', function(e) {
    e.preventDefault();
    fetch('manage.php?action=request_extension', { method: 'POST', body: new FormData(this) })
    .then(r => r.json()).then(data => {
        alert(data.message);
        if (data.status === 'success') location.reload();
    });
});

/**
 * Generic Form Handler
 * Works for Edit, Extend, and Reduce without needing specific IDs.
 */
function submitForm(btn) {
    const form = btn.closest('form');
    if (!form) {
        alert("Error: Form not found.");
        return;
    }

    // Browser-native validation (e.g., the 'min' attribute)
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const actionUrl = form.getAttribute('action'); 
    
    fetch(actionUrl, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            location.reload();
        } else {
            // This shows the error thrown by your PHP Exception
            alert("Error: " + (data.message || "Operation failed"));
        }
    })
    .catch(err => {
        console.error("Fetch Error:", err);
        alert("A system error occurred.");
    });
}

document.addEventListener("DOMContentLoaded", function() {
    flatpickr("#calendarExtend", {
        inline: false, // Calendar pops up only when clicked
        dateFormat: "Y-m-d",
        defaultDate: "<?php echo $schedule['end_date']; ?>",
        minDate: "<?php echo $schedule['start_date']; ?>",
        // Optional: Add a clear button
        allowInput: true
    });
});

</script>

<?php include '../includes/footer.php'; ?>