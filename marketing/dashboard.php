<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

check_role([3]); 
include '../includes/header.php'; 

// Fetch all relevant pending schedules
$requests = $pdo->query("
    SELECT s.id AS schedule_id, s.schedule_name, s.reference_no, s.budget_allocated, 
           s.status, s.start_date, s.end_date, c.client_name, a.agency_name,
           r.id AS request_id, r.additional_budget, r.new_end_date
    FROM schedules s
    JOIN clients c ON s.client_id = c.id
    JOIN agencies a ON s.agency_id = a.id
    LEFT JOIN schedule_approval_requests r ON s.id = r.schedule_id AND r.status = 'Pending'
    WHERE s.status IN ('Pending Approval', 'Pending Approval (Cost Review)')
    GROUP BY s.id
")->fetchAll();

// Grouping logic
$grouped = ['Pending Approval' => [], 'Pending Approval (Cost Review)' => []];
foreach ($requests as $req) {
    $grouped[$req['status']][] = $req;
}
?>

<div class="container-fluid px-4">
    <h3 class="mb-4">Marketing Approval Dashboard</h3>

    <?php foreach ($grouped as $status => $items): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between">
                <span><?php echo ($status === 'Pending Approval') ? 'Budget Extensions' : 'Cost Reviews'; ?></span>
                <span class="badge bg-light text-dark"><?php echo count($items); ?> Requests</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Ref / Schedule</th><th>Client</th><th>Current Budget</th><th>Req. Addition</th><th>New End Date</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="6" class="text-center text-muted">No requests in this category.</td></tr>
                        <?php else: foreach($items as $req): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($req['reference_no']); ?></strong><br><?php echo htmlspecialchars($req['schedule_name']); ?></td>
                            <td><?php echo htmlspecialchars($req['client_name']); ?></td>
                            <td>Rs. <?php echo number_format($req['budget_allocated'], 2); ?></td>
                            <td><span class="text-danger fw-bold"><?php echo $req['additional_budget'] ? 'Rs. ' . number_format($req['additional_budget'], 2) : 'N/A'; ?></span></td>
                            <td><?php echo htmlspecialchars($req['new_end_date'] ?? 'N/A'); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info text-white" onclick="showDetails(<?php echo htmlspecialchars(json_encode($req)); ?>)">Review</button>
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
            <div class="modal-header bg-primary text-white"><h5 class="modal-title">Request Impact Analysis</h5></div>
            <div class="modal-body" id="modalBodyContent"></div>
        </div>
    </div>
</div>

<script>
function showDetails(req) {
    const addBudget = parseFloat(req.additional_budget) || 0;
    const totalProposed = parseFloat(req.budget_allocated) + addBudget;
    const body = document.getElementById('modalBodyContent');
    const reqStr = JSON.stringify(req).replace(/'/g, "\\'");
    body.innerHTML = `
        <div class="row">
            <div class="col-6 border-end">
                <p><strong>Agency:</strong> ${req.agency_name}</p>
                <p><strong>Period:</strong> ${req.start_date} to ${req.end_date}</p>
                <p><strong>Current Budget:</strong> Rs. ${parseFloat(req.budget_allocated).toLocaleString()}</p>
            </div>
            <div class="col-6">
                <p><strong>Status:</strong> ${req.status}</p>
                <p><strong>Requested Addition:</strong> <span class="text-danger">Rs. ${addBudget.toLocaleString()}</span></p>
                <p><strong>Total Proposed:</strong> Rs. ${totalProposed.toLocaleString()}</p>
            </div>
        </div>
        <hr>
        <div class="text-end">
        <button class="btn btn-success" 
            onclick="processApproval(${req.request_id || 0}, ${req.schedule_id}, 'Approved', '${req.status}')">
            Approve
        </button>
        <button class="btn btn-danger" 
            onclick="processApproval(${req.request_id || 0}, ${req.schedule_id}, 'Rejected', '${req.status}')">
            Reject
        </button>
    </div>
    `;
    new bootstrap.Modal(document.getElementById('reviewModal')).show();
}

function processApproval(requestId, scheduleId, action, status) {
    fetch('../scheduler/manage.php?action=process_approval', {
        method: 'POST',
        body: new URLSearchParams({ 
            request_id: requestId, 
            schedule_id: scheduleId, 
            action: action, 
            status: status 
        })
    }).then(r => r.json()).then(data => {
        alert(data.message);
        if (data.status === 'success') location.reload();
    });
}
</script>

<?php include '../includes/footer.php'; ?>