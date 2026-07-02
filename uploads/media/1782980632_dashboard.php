<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

check_role([3]); 
include '../includes/header.php'; 

// Fetch requests with advanced calculation
// We calculate: Total Cost of Items, Days Run, and Remaining Budget
$requests = $pdo->query("
    SELECT s.id AS schedule_id, s.schedule_name, s.reference_no, s.budget_allocated, 
           s.status, s.start_date, s.end_date, c.client_name, a.agency_name,
           r.id AS request_id, r.additional_budget, r.new_end_date,
           (SELECT SUM(cost) FROM schedule_items WHERE schedule_id = s.id) as total_content_cost,
           DATEDIFF(CURRENT_DATE, s.start_date) as days_run
    FROM schedules s
    JOIN clients c ON s.client_id = c.id
    JOIN agencies a ON s.agency_id = a.id
    LEFT JOIN schedule_approval_requests r ON s.id = r.schedule_id AND r.status = 'Pending'
    WHERE s.status IN ('Pending Approval', 'Pending Approval (Cost Review)')
    GROUP BY s.id
")->fetchAll();

$grouped = ['Pending Approval' => [], 'Pending Approval (Cost Review)' => []];
foreach ($requests as $req) { $grouped[$req['status']][] = $req; }
?>

<div class="container-fluid px-4">
    <h3 class="mb-4">Marketing Approval Dashboard</h3>
    <?php foreach ($grouped as $status => $items): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between">
                <span><?php echo $status; ?></span>
                <span class="badge bg-light text-dark"><?php echo count($items); ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Ref / Schedule</th><th>Budget</th><th>Request Details</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="4" class="text-center text-muted">No requests found.</td></tr>
                        <?php else: foreach($items as $req): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($req['reference_no']); ?></strong><br><?php echo htmlspecialchars($req['schedule_name']); ?></td>
                            <td>Rs. <?php echo number_format($req['budget_allocated'], 2); ?></td>
                            <td>
                                <small>Addition: <span class="text-danger fw-bold">Rs. <?php echo number_format($req['additional_budget'] ?? 0, 2); ?></span></small><br>
                                <small>Days Elapsed: <?php echo $req['days_run']; ?> days</small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info text-white" onclick="showDetails(<?php echo htmlspecialchars(json_encode($req)); ?>)">Review Details</button>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title">Impact Analysis</h5></div>
            <div class="modal-body" id="modalBodyContent"></div>
        </div>
    </div>
</div>

<script>
function showDetails(req) {
    const addBudget = parseFloat(req.additional_budget) || 0;
    const totalProposed = parseFloat(req.budget_allocated) + addBudget;
    const contentCost = parseFloat(req.total_content_cost) || 0;
    
    const body = document.getElementById('modalBodyContent');
    body.innerHTML = `
        <div class="row mb-3">
            <div class="col-6">
                <p><strong>Agency:</strong> ${req.agency_name}</p>
                <p><strong>Period:</strong> ${req.start_date} to ${req.end_date}</p>
                <p><strong>Total Committed Content Cost:</strong> Rs. ${contentCost.toLocaleString()}</p>
            </div>
            <div class="col-6">
                <p><strong>Current Budget:</strong> Rs. ${parseFloat(req.budget_allocated).toLocaleString()}</p>
                <p><strong>Requested Addition:</strong> <span class="text-danger">Rs. ${addBudget.toLocaleString()}</span></p>
                <h5 class="mt-2"><strong>Total Proposed:</strong> Rs. ${totalProposed.toLocaleString()}</h5>
            </div>
        </div>
        <div class="alert ${totalProposed < contentCost ? 'alert-danger' : 'alert-success'}">
            ${totalProposed < contentCost ? '⚠️ Warning: Proposed budget is less than total content commitments!' : '✅ Budget is sufficient to cover content commitments.'}
        </div>
        <div class="text-end">
            <button class="btn btn-success" onclick="processApproval(${req.request_id || 0}, ${req.schedule_id}, 'Approved')">Approve</button>
            <button class="btn btn-danger" onclick="processApproval(${req.request_id || 0}, ${req.schedule_id}, 'Rejected')">Reject</button>
        </div>
    `;
    new bootstrap.Modal(document.getElementById('reviewModal')).show();
}

function processApproval(requestId, scheduleId, action) {
    fetch('../scheduler/manage.php?action=process_approval', {
        method: 'POST',
        body: new URLSearchParams({ request_id: requestId, schedule_id: scheduleId, action: action })
    }).then(r => r.json()).then(data => {
        alert(data.message);
        if (data.status === 'success') location.reload();
    });
}
</script>
<?php include '../includes/footer.php'; ?>