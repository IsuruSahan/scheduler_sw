<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$agencies = $pdo->query("SELECT * FROM agencies ORDER BY agency_name")->fetchAll();
$clients = $pdo->query("SELECT * FROM clients ORDER BY client_name")->fetchAll();
$rate_cards = $pdo->query("SELECT * FROM rate_cards")->fetchAll();
$content_items = $pdo->query("SELECT id, name, type FROM content_items ORDER BY name")->fetchAll();
$platforms = $pdo->query("SELECT * FROM platforms ORDER BY platform_name")->fetchAll();
$placements = $pdo->query("SELECT * FROM ad_placements ORDER BY placement_name")->fetchAll();
$media_formats = $pdo->query("SELECT * FROM media_formats ORDER BY format_name")->fetchAll();
$inventory_data = $pdo->query("SELECT rate_card_id, total_capacity, used_qty FROM inventory")->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid px-4">
    <h3 class="mb-4">Create New Schedule</h3>
    
    <form action="process_schedule.php" method="POST" enctype="multipart/form-data" id="scheduleForm">
        <div class="card p-4 mb-4 shadow-sm border-0">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Agency</label><select name="agency_id" id="agency_id" class="form-select" onchange="updateClients()" required><option value="">Select Agency</option><?php foreach($agencies as $a): ?><option value="<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['agency_name']); ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label">Client</label><select name="client_id" id="client_id" class="form-select" required><option value="">Select Agency First</option></select></div>
                <div class="col-md-6"><label class="form-label">Schedule Name</label><input type="text" name="schedule_name" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Reference No.</label><input type="text" name="reference_no" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Total Budget (Rs.)</label><input type="number" name="budget" id="budget_input" class="form-control" oninput="updateTotalBudget()" required></div>
                <div class="col-md-4"><label class="form-label">Start Date</label><input type="date" name="start_date" id="start_date" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">End Date</label><input type="date" name="end_date" id="end_date" class="form-control" required></div>
<div class="col-md-6">
    <label class="form-label d-block">Assign Team(s):</label>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" name="assigned_team[]" value="Content Editor Team" id="team1" checked>
        <label class="form-check-label" for="team1">Content Editor Team</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" name="assigned_team[]" value="News Team" id="team2">
        <label class="form-check-label" for="team2">News Team</label>
    </div>
</div>            </div>
            <div class="mt-4"><label class="fw-bold me-3">Mode:</label>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="mode" value="sync" id="syncMode" checked onclick="toggleMode('sync')"><label class="form-check-label" for="syncMode">Sync</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="mode" value="custom" id="customMode" onclick="toggleMode('custom')"><label class="form-check-label" for="customMode">Custom</label></div>
            </div>
        </div>

        <div id="sync-container" class="card p-3 mb-3 border-primary shadow-sm">
            <label class="fw-bold">Media for all:</label>
            <div id="sync-files"><div class="input-group mb-2"><input type="file" name="media[sync][]" class="form-control"><input type="text" name="ref[sync][]" class="form-control" placeholder="Reference"><button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">×</button></div></div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFileGroup('sync-files', 'sync')">+ Add File</button>
        </div>

        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Content</th>
                    <th>Platform</th>
                    <th>Placement</th>
                    <th>Format</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Total</th>
                    <th class="custom-only" style="display:none;">Media</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="items-body"></tbody>
        </table>
        <button type="button" class="btn btn-primary mb-3" onclick="addRow()">+ Add Platform Row</button>
        <div class="mt-3 p-3 bg-light border rounded"><strong>Total Generated: Rs. <span id="total-generated">0</span></strong><div id="budget-warning" class="text-danger fw-bold mt-2" style="display:none;">⚠️ Warning: Total cost exceeds allocated budget!</div></div>
        <button type="submit" name="action" value="create" id="btn-create" class="btn btn-success float-end mt-3 mb-3">Create Schedule</button>
        <button type="submit" name="action" value="approve" id="btn-approve" class="btn btn-warning float-end mt-3 mb-3 me-2" style="display:none;">Send to Marketing Officer for Approval</button>
    </form>
</div>

<div class="modal fade" id="errorModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title">Inventory Error</h5></div><div class="modal-body" id="error-message"></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div></div></div></div>

<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white"><h5 class="modal-title">Success</h5></div>
            <div class="modal-body" id="success-message">Schedule created successfully!</div>
            <div class="modal-footer"><button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="contentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Select Program</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-2 mb-3"><div class="col-md-6"><input type="text" id="searchName" class="form-control" placeholder="Search Program Name..."></div></div>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover">
                        <thead><tr><th>Name</th><th>Type</th><th>Select</th></tr></thead>
                        <tbody id="contentModalBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const allClients = <?php echo json_encode($clients); ?>;
const allContent = <?php echo json_encode($content_items); ?>;
const allRates = <?php echo json_encode($rate_cards); ?>;
const allFormats = <?php echo json_encode($media_formats); ?>;
const allInventory = <?php echo json_encode($inventory_data); ?>;

let activeRow = null;
const modalEl = document.getElementById('contentModal');
const contentModal = new bootstrap.Modal(modalEl);

document.getElementById('scheduleForm').addEventListener('submit', function(e) {
    e.preventDefault(); 
    const start = new Date(document.getElementById('start_date').value);
    const end = new Date(document.getElementById('end_date').value);
    if (end < start) { showError('Error: End Date cannot be earlier than Start Date.'); return; }

    const formData = new FormData(this);
    fetch('process_schedule.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('success-message').innerText = data.message;
            new bootstrap.Modal(document.getElementById('successModal')).show();
            document.getElementById('scheduleForm').reset();
            document.getElementById('items-body').innerHTML = '';
        } else { showError(data.message); }
    })
    .catch(err => showError('An unexpected error occurred.'));
});

function updateClients() {
    const agencyId = document.getElementById('agency_id').value;
    const clientSelect = document.getElementById('client_id');
    clientSelect.innerHTML = '<option value="">Select Client</option>';
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
    const container = document.getElementById(document.getElementById(id) ? id : 'sync-files');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `<input type="file" name="media[${rowId}][]" class="form-control"><input type="text" name="ref[${rowId}][]" class="form-control" placeholder="Reference"><button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">×</button>`;
    container.appendChild(div);
};

function addRow() {
    const rowId = Date.now();
    const mode = document.querySelector('input[name="mode"]:checked').value;
    const row = document.createElement('tr');
    row.innerHTML = `
        <td style="min-width: 200px;">
            <input type="hidden" name="row_ids[]" value="${rowId}">
            <input type="hidden" name="content_item_id[]" class="content-id">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openContentModal(this)">Select Program</button>
            <span class="selected-content-text ms-2"></span>
        </td>
        <td><select name="platform_id[]" class="form-select" onchange="calculateCost(this.closest('tr'))"><?php foreach($platforms as $p): ?><option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['platform_name']); ?></option><?php endforeach; ?></select></td>
        <td><select name="placement_id[]" class="form-select" onchange="calculateCost(this.closest('tr'))"><?php foreach($placements as $pl): ?><option value="<?php echo $pl['id']; ?>"><?php echo htmlspecialchars($pl['placement_name']); ?></option><?php endforeach; ?></select></td>
        <td><select name="media_format_id[]" class="form-select" onchange="calculateCost(this.closest('tr'))">${allFormats.map(f => `<option value="${f.id}">${f.format_name}</option>`).join('')}</select></td>    
        <td><input type="number" name="quantity[]" class="form-control" value="1" min="1" oninput="calculateCost(this.closest('tr'))" required></td>
        <td>Rs. <span class="rate-display">0</span></td>
        <td>Rs. <span class="total-display">0</span></td>
        <td class="custom-only" style="display: ${mode === 'custom' ? 'table-cell' : 'none'}">
            <div id="row_media_${rowId}"></div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFileGroup('row_media_${rowId}', ${rowId})">+ Add File</button>
        </td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); updateTotalBudget();">Remove</button></td>
    `;
    document.getElementById('items-body').appendChild(row);
}

function calculateCost(row) {
    const platform = row.querySelector('select[name="platform_id[]"]').value;
    const placement = row.querySelector('select[name="placement_id[]"]').value;
    const format = row.querySelector('select[name="media_format_id[]"]').value;
    const qtyInput = row.querySelector('input[name="quantity[]"]');
    const contentId = row.querySelector('.content-id').value;
    if (!contentId) return;
    const rateItem = allRates.find(r => r.platform_id == platform && r.placement_id == placement && r.content_item_id == contentId && r.media_format_id == format);
    if (rateItem) {
        const invItem = allInventory.find(i => i.rate_card_id == rateItem.id);
        const available = invItem ? (invItem.total_capacity - invItem.used_qty) : 0;
        if (parseInt(qtyInput.value) > available) {
            showError('Quantity exceeds available balance: ' + available);
            qtyInput.value = available;
        }
        row.querySelector('.rate-display').innerText = rateItem.rate;
        row.querySelector('.total-display').innerText = (rateItem.rate * qtyInput.value).toFixed(2);
    }
    updateTotalBudget();
}

function updateTotalBudget() {
    let total = 0;
    document.querySelectorAll('.total-display').forEach(el => total += parseFloat(el.innerText || 0));
    const budget = parseFloat(document.getElementById('budget_input').value || 0);
    const exceeded = total > budget;
    document.getElementById('total-generated').innerText = total.toFixed(2);
    document.getElementById('budget-warning').style.display = exceeded ? 'block' : 'none';
    document.getElementById('btn-create').style.display = exceeded ? 'none' : 'block';
    document.getElementById('btn-approve').style.display = exceeded ? 'block' : 'none';
}

function openContentModal(btn) { activeRow = btn.closest('tr'); contentModal.show(); }

function selectContent(id, name) {
    activeRow.querySelector('.content-id').value = id;
    activeRow.querySelector('.selected-content-text').innerText = name;
    contentModal.hide();
    calculateCost(activeRow);
}

document.getElementById('searchName').addEventListener('input', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#contentModalBody tr').forEach(row => {
        row.style.display = row.cells[0].innerText.toLowerCase().includes(val) ? '' : 'none';
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const tbody = document.getElementById('contentModalBody');
    allContent.forEach(item => {
        tbody.innerHTML += `<tr><td>${item.name}</td><td>${item.type}</td><td><button type="button" class="btn btn-primary btn-sm" onclick="selectContent(${item.id}, '${item.name}')">Select</button></td></tr>`;
    });
});
</script>
<?php include '../includes/footer.php'; ?>