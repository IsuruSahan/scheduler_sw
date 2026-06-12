<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$agencies = $pdo->query("SELECT * FROM agencies ORDER BY agency_name")->fetchAll();
$clients = $pdo->query("SELECT * FROM clients ORDER BY client_name")->fetchAll();
$rate_cards = $pdo->query("SELECT * FROM rate_cards")->fetchAll();
$episodes_log = $pdo->query("SELECT e.*, c.name as item_name, c.type, c.id as content_item_id FROM episodes e JOIN content_items c ON e.content_item_id = c.id ORDER BY c.name, e.episode_number DESC")->fetchAll();
$platforms = $pdo->query("SELECT * FROM platforms ORDER BY platform_name")->fetchAll();
$placements = $pdo->query("SELECT * FROM ad_placements ORDER BY placement_name")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid px-4">
    <h3 class="mb-4">Create New Schedule</h3>
    
    <form action="process_schedule.php" method="POST" enctype="multipart/form-data">
        <div class="card p-4 mb-4 shadow-sm border-0">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Agency</label><select name="agency_id" id="agency_id" class="form-select" onchange="updateClients()" required><option value="">Select Agency</option><?php foreach($agencies as $a): ?><option value="<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['agency_name']); ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label">Client</label><select name="client_id" id="client_id" class="form-select" required><option value="">Select Agency First</option></select></div>
                <div class="col-md-6"><label class="form-label">Schedule Name</label><input type="text" name="schedule_name" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Reference No.</label><input type="text" name="reference_no" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Total Budget (Rs.)</label><input type="number" name="budget" id="budget_input" class="form-control" oninput="updateTotalBudget()" required></div>
                <div class="col-md-4"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Assign Team</label><select name="assigned_team" class="form-select" required><option value="Content Editor Team">Content Editor Team</option><option value="News Team">News Team</option></select></div>
            </div>
            <div class="mt-4"><label class="fw-bold me-3">Mode:</label>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="mode" value="sync" id="syncMode" checked onclick="toggleMode('sync')"><label class="form-check-label" for="syncMode">Sync</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="mode" value="custom" id="customMode" onclick="toggleMode('custom')"><label class="form-check-label" for="customMode">Custom</label></div>
            </div>
        </div>

        <div id="sync-container" class="card p-3 mb-3 border-primary shadow-sm">
            <label class="fw-bold">Media for ALL (Sync Mode):</label>
            <div id="sync-files"><div class="input-group mb-2"><input type="file" name="media[sync][]" class="form-control"><input type="text" name="ref[sync][]" class="form-control" placeholder="Reference"><button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">×</button></div></div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFileGroup('sync-files', 'sync')">+ Add File</button>
        </div>

        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr><th>Content</th><th>Platform</th><th>Placement</th><th>Qty</th><th>Rate</th><th>Total</th><th class="custom-only" style="display:none;">Media</th><th>Action</th></tr>
            </thead>
            <tbody id="items-body"></tbody>
        </table>
        <button type="button" class="btn btn-primary mb-3" onclick="addRow()">+ Add Platform Row</button>
        <div class="mt-3 p-3 bg-light border rounded"><strong>Total Generated: Rs. <span id="total-generated">0</span></strong><div id="budget-warning" class="text-danger fw-bold mt-2" style="display:none;">⚠️ Warning: Total cost exceeds allocated budget!</div></div>
        <button type="submit" class="btn btn-success float-end mt-3">Create Schedule</button>
    </form>
</div>

<div class="modal fade" id="errorModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title">Quantity exceeded</h5></div><div class="modal-body" id="error-message"></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div></div></div></div>

<script>
const allClients = <?php echo json_encode($clients); ?>;
const allEpisodes = <?php echo json_encode($episodes_log); ?>;
const allRates = <?php echo json_encode($rate_cards); ?>;

function updateClients() {
    const agencyId = document.getElementById('agency_id').value;
    const clientSelect = document.getElementById('client_id');
    
    // Reset client dropdown
    clientSelect.innerHTML = '<option value="">Select Client</option>';
    
    // Filter and add matching clients
    allClients.filter(c => c.agency_id == agencyId).forEach(c => {
        clientSelect.innerHTML += `<option value="${c.id}">${c.client_name}</option>`;
    });
}

function showError(msg) {
    document.getElementById('error-message').innerText = msg;
    new bootstrap.Modal(document.getElementById('errorModal')).show();
}

function toggleMode(mode) {
    document.getElementById('sync-container').style.display = (mode === 'sync') ? 'block' : 'none';
    document.querySelectorAll('.custom-only').forEach(el => el.style.display = (mode === 'custom') ? 'table-cell' : 'none');
}

window.addFileGroup = (id, rowId) => {
    const container = document.getElementById(id);
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `<input type="file" name="media[${rowId}][]" class="form-control"><input type="text" name="ref[${rowId}][]" class="form-control" placeholder="Reference"><button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">×</button>`;
    container.appendChild(div);
};

function addRow() {
    const rowId = Date.now(); // Stable Unique ID
    const mode = document.querySelector('input[name="mode"]:checked').value;
    const row = document.createElement('tr');
    
    // Generate Options
    let optionsHtml = ''; let lastItem = null;
    allEpisodes.forEach(ep => {
        if (ep.item_name !== lastItem) {
            if (lastItem !== null) optionsHtml += '</optgroup>';
            optionsHtml += `<optgroup label="${ep.item_name} (${ep.type})">`;
            lastItem = ep.item_name;
        }
        optionsHtml += `<option value="${ep.id}" data-content-id="${ep.content_item_id}">[${ep.upload_date}] Ep ${ep.episode_number}: ${ep.episode_title}</option>`;
    });

    row.innerHTML = `
        <td>
            <input type="hidden" name="row_ids[]" value="${rowId}">
            <select name="episode_id[]" class="form-select" onchange="calculateCost(this.closest('tr'))">${optionsHtml}</select>
        </td>
        <td><select name="platform_id[]" class="form-select" onchange="calculateCost(this.closest('tr'))"><?php foreach($platforms as $p): ?><option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['platform_name']); ?></option><?php endforeach; ?></select></td>
        <td><select name="placement_id[]" class="form-select" onchange="calculateCost(this.closest('tr'))"><?php foreach($placements as $pl): ?><option value="<?php echo $pl['id']; ?>"><?php echo htmlspecialchars($pl['placement_name']); ?></option><?php endforeach; ?></select></td>
        <td><input type="number" name="quantity[]" class="form-control" value="1" min="1" oninput="calculateCost(this.closest('tr'))" required></td>
        <td>Rs. <span class="rate-display">0</span></td>
        <td>Rs. <span class="total-display">0</span></td>
        <td class="custom-only" style="display: ${mode === 'custom' ? 'table-cell' : 'none'}">
            <div id="row_media_${rowId}">
                <div class="input-group mb-2">
                    <input type="file" name="media[${rowId}][]" class="form-control">
                    <input type="text" name="ref[${rowId}][]" class="form-control" placeholder="Reference">
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFileGroup('row_media_${rowId}', ${rowId})">+ Add File</button>
        </td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); updateTotalBudget();">Remove</button></td>
    `;
    document.getElementById('items-body').appendChild(row);
    calculateCost(row);
}

function calculateCost(row) {
    const platform = row.querySelector('select[name="platform_id[]"]').value;
    const placement = row.querySelector('select[name="placement_id[]"]').value;
    const qtyInput = row.querySelector('input[name="quantity[]"]');
    const epSelect = row.querySelector('select[name="episode_id[]"]');
    const contentItemId = epSelect.options[epSelect.selectedIndex].getAttribute('data-content-id');
    
    const rateItem = allRates.find(r => r.platform_id == platform && r.placement_id == placement && r.content_item_id == contentItemId);
    
    if (rateItem) {
        qtyInput.setAttribute('max', rateItem.max_quantity);
        if (parseInt(qtyInput.value) > parseInt(rateItem.max_quantity)) {
            showError('Quantity exceeds max allowed limit of ' + rateItem.max_quantity);
            qtyInput.value = rateItem.max_quantity;
        }
        row.querySelector('.rate-display').innerText = rateItem.rate;
        row.querySelector('.total-display').innerText = (rateItem.rate * qtyInput.value).toFixed(2);
    }
    updateTotalBudget();
}

function updateTotalBudget() {
    let total = 0;
    document.querySelectorAll('.total-display').forEach(el => total += parseFloat(el.innerText || 0));
    document.getElementById('total-generated').innerText = total.toFixed(2);
    document.getElementById('budget-warning').style.display = (total > parseFloat(document.getElementById('budget_input').value || 0)) ? 'block' : 'none';
}
</script>
<?php include '../includes/footer.php'; ?>