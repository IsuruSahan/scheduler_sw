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
                <div class="col-md-4"><label class="form-label">Total Budget (Rs.)</label><input type="number" name="budget" id="budget_input" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Start Date</label><input type="date" name="start_date" id="start_date" class="form-control" onchange="initDateRange()" required></div>
                <div class="col-md-4"><label class="form-label">End Date</label><input type="date" name="end_date" id="end_date" class="form-control" onchange="initDateRange()" required></div>
            </div>
        </div>

        <div class="card p-3 mb-4">
            <h5>Date-Specific Allocation</h5>
            <div id="date-selector" class="d-flex flex-wrap mb-3"></div>
            <div class="d-flex justify-content-between">
                <h6 id="current-date-title">Select a date range above to begin</h6>
                <button type="button" class="btn btn-sm btn-secondary" onclick="copyPreviousDate()">Copy from Previous Date</button>
            </div>
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
        <th>Action</th>
    </tr>
</thead>
            <tbody id="items-body"></tbody>
        </table>
        <button type="button" class="btn btn-primary mb-3" onclick="addRow()">+ Add Platform Row</button>
        
        <button type="submit" class="btn btn-success float-end">Create Schedule</button>
    </form>
</div>

<script>
const allRates = <?php echo json_encode($rate_cards); ?>;
const allContent = <?php echo json_encode($content_items); ?>;
const allClients = <?php echo json_encode($clients); ?>;
const allPlatforms = <?php echo json_encode($platforms); ?>;
const allPlacements = <?php echo json_encode($placements); ?>;
const allFormats = <?php echo json_encode($media_formats); ?>;

let scheduleData = {}; 
let activeDate = null;
let activeRowIndex = null;
let contentModal;
document.addEventListener("DOMContentLoaded", () => {
    contentModal = new bootstrap.Modal(document.getElementById('contentModal'));
    
    // Populate Modal once
    const tbody = document.getElementById('contentModalBody');
    allContent.forEach(item => {
        tbody.innerHTML += `<tr>
            <td>${item.name}</td>
            <td>${item.type}</td>
            <td><button type="button" class="btn btn-primary btn-sm" onclick="selectContent(${item.id}, '${item.name}')">Select</button></td>
        </tr>`;
    });
});

function createOptions(arr, key, selected) {
    return arr.map(i => `<option value="${i.id}" ${i.id == selected ? 'selected' : ''}>${i[key]}</option>`).join('');
}

function updateClients() {
    const agencyId = document.getElementById('agency_id').value;
    const clientSelect = document.getElementById('client_id');
    
    // Clear existing options
    clientSelect.innerHTML = '<option value="">Select Client</option>';
    
    // Filter and add new options
    allClients.filter(c => String(c.agency_id) === String(agencyId)).forEach(c => {
        let opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.client_name;
        clientSelect.appendChild(opt);
    });
}

function openContentModal(index) {
    activeRowIndex = index; // Store which row index we are editing
    contentModal.show();
}

function selectContent(id, name) {
    if (activeRowIndex !== null && activeDate !== null) {
        // 1. Update the state object
        scheduleData[activeDate][activeRowIndex].content_id = id;
        scheduleData[activeDate][activeRowIndex].content_name = name;

        // 2. Render the table rows to update the UI button text
        renderRowsForDate(activeDate);

        // 3. Force calculation for this row
        // We use setTimeout to ensure the DOM has finished rendering the new row
        setTimeout(() => {
            const tbody = document.getElementById('items-body');
            // Select the specific row index
            const row = tbody.querySelectorAll('tr')[activeRowIndex];
            
            if (row) {
                calculateCost(row, activeRowIndex);
            }
        }, 50);

        // 4. Hide the modal
        contentModal.hide();
    }
}

function initDateRange() {
    const start = new Date(document.getElementById('start_date').value);
    const end = new Date(document.getElementById('end_date').value);
    const container = document.getElementById('date-selector');
    container.innerHTML = '';
    
    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
        let dateStr = d.toISOString().split('T')[0];
        if (!scheduleData[dateStr]) scheduleData[dateStr] = [];
        container.innerHTML += `<button type="button" class="btn btn-outline-info m-1" onclick="setActiveDate('${dateStr}')">${dateStr}</button>`;
    }
}

function setActiveDate(date) {
    activeDate = date;
    document.getElementById('current-date-title').innerText = "Managing: " + date;
    renderRowsForDate(date);
}

// 1. Update your addRow function to store rate
function addRow() {
    if (!activeDate) {
        alert("Please select a date button first!");
        return;
    }
    scheduleData[activeDate].push({
        content_id: '', content_name: '', platform_id: 1, 
        placement_id: 1, format_id: 1, qty: 1, rate: 0
    });
    renderRowsForDate(activeDate);
}

// 2. Add the dynamic cost calculation
function calculateRowTotal(index) {
    const item = scheduleData[activeDate][index];
    // Find rate from your global rate_cards data
    const rateCard = <?php echo json_encode($rate_cards); ?>.find(r => 
        r.content_item_id == item.content_id && 
        r.platform_id == item.platform_id && 
        r.placement_id == item.placement_id && 
        r.media_format_id == item.format_id
    );
    
    item.rate = rateCard ? parseFloat(rateCard.rate) : 0;
    return item.rate * item.qty;
}

// 3. Update the total budget display
function updateTotalBudget() {
    let total = 0;
    // Loop through ALL dates in scheduleData to get global total
    Object.values(scheduleData).forEach(dateItems => {
        dateItems.forEach(item => {
            total += (item.rate * item.qty);
        });
    });
    
    const budget = parseFloat(document.getElementById('budget_input').value || 0);
    document.getElementById('total-generated').innerText = total.toFixed(2);
    
    const warning = document.getElementById('budget-warning');
    warning.style.display = (total > budget) ? 'block' : 'none';
}

function renderRowsForDate(date) {
    const tbody = document.getElementById('items-body');
    tbody.innerHTML = '';
    
    scheduleData[date].forEach((item, index) => {
        // Calculate current total
        const rowTotal = item.rate * item.qty;
        
        tbody.innerHTML += `
            <tr>
                <td>
                    <input type="hidden" class="content-id" value="${item.content_id}">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openContentModal(${index})">
                        ${item.content_name || 'Select Program'}
                    </button>
                </td>
                <td><select name="schedule[${date}][platform_id][]" class="form-select" onchange="calculateCost(this.closest('tr'), ${index})">${createOptions(<?php echo json_encode($platforms); ?>, 'platform_name', item.platform_id)}</select></td>
                <td><select name="schedule[${date}][placement_id][]" class="form-select" onchange="calculateCost(this.closest('tr'), ${index})">${createOptions(<?php echo json_encode($placements); ?>, 'placement_name', item.placement_id)}</select></td>
                <td><select name="schedule[${date}][format_id][]" class="form-select" onchange="calculateCost(this.closest('tr'), ${index})">${createOptions(<?php echo json_encode($media_formats); ?>, 'format_name', item.format_id)}</select></td>
                <td><input type="number" name="schedule[${date}][quantity][]" class="form-control" value="${item.qty}" oninput="calculateCost(this.closest('tr'), ${index})"></td>
                <td>Rs. <span class="rate-display">${item.rate.toFixed(2)}</span></td>
                <td>Rs. <span class="total-display">${rowTotal.toFixed(2)}</span></td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(${index})">×</button></td>
            </tr>`;
    });
}

function calculateCost(row, index) {
    const platform = row.querySelector('select[name*="platform_id"]').value;
    const placement = row.querySelector('select[name*="placement_id"]').value;
    const format = row.querySelector('select[name*="format_id"]').value;
    const qty = parseInt(row.querySelector('input[name*="quantity"]').value) || 0;
    const content_id = scheduleData[activeDate][index].content_id;

    if (!content_id) return;

    // Use the GLOBAL allRates variable
    const rateItem = allRates.find(r => 
        Number(r.content_item_id) === Number(content_id) && 
        Number(r.platform_id) === Number(platform) && 
        Number(r.placement_id) === Number(placement) && 
        Number(r.media_format_id) === Number(format)
    );

    const rate = rateItem ? parseFloat(rateItem.rate) : 0;
    
    // Update state
    scheduleData[activeDate][index].rate = rate;
    scheduleData[activeDate][index].qty = qty;

    // Update UI
    row.querySelector('.rate-display').innerText = rate.toFixed(2);
    row.querySelector('.total-display').innerText = (rate * qty).toFixed(2);
    
    updateTotalBudget();
}
</script>

<div class="modal fade" id="contentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover">
                        <thead><tr><th>Name</th><th>Type</th><th>Action</th></tr></thead>
                        <tbody id="contentModalBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>