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
// If your table is named 'inventory_daily_capacity'
$inventory_data = $pdo->query("SELECT rate_card_id, capacity_qty, capacity_date FROM inventory_daily_capacity")->fetchAll(PDO::FETCH_ASSOC);?>

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
const allCapacity = <?php echo json_encode($inventory_data); ?>; 


let scheduleData = {}; 
let activeDate = null;
let activeRowIndex = null;
let contentModal;
let errorModal;
document.addEventListener("DOMContentLoaded", () => {
    contentModal = new bootstrap.Modal(document.getElementById('contentModal'));
    errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    
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
    // 1. Safety check
    if (activeRowIndex === null || activeDate === null) return;

    // 2. Hide the modal immediately to free up the UI
    contentModal.hide();

    // 3. Perform updates after a short delay to allow the modal's 
    // fade-out animation to complete, preventing DOM conflicts.
    setTimeout(() => {
        // Update the state object directly
        scheduleData[activeDate][activeRowIndex].content_id = id;
        scheduleData[activeDate][activeRowIndex].content_name = name;

        // Render the UI
        renderRowsForDate(activeDate);

        // Force calculation for the specific row just updated
        // We look for the row by index in the newly rendered table
        const tbody = document.getElementById('items-body');
        const rows = tbody.querySelectorAll('tr');
        const targetRow = rows[activeRowIndex];

        if (targetRow) {
            calculateCost(targetRow, activeRowIndex);
        }
    }, 200); 
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

function updateScheduleState(index, key, value) {
    scheduleData[activeDate][index][key] = value;
    // Recalculate cost whenever a field changes
    const row = document.querySelectorAll('#items-body tr')[index];
    calculateCost(row, index);
}

function renderRowsForDate(date) {
    const tbody = document.getElementById('items-body');
    tbody.innerHTML = '';
    
    if (!scheduleData[date]) return;

    scheduleData[date].forEach((item, index) => {
        const rowTotal = (item.rate || 0) * (item.qty || 0);
        
        // Create the row element instead of concatenating a string
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td>
                <input type="hidden" name="schedule[${date}][content_id][]" value="${item.content_id}">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openContentModal(${index})">
                    ${item.content_name || 'Select Program'}
                </button>
            </td>
            <td><select name="schedule[${date}][platform_id][]" class="form-select">${createOptions(allPlatforms, 'platform_name', item.platform_id)}</select></td>
            <td><select name="schedule[${date}][placement_id][]" class="form-select">${createOptions(allPlacements, 'placement_name', item.placement_id)}</select></td>
            <td><select name="schedule[${date}][format_id][]" class="form-select">${createOptions(allFormats, 'format_name', item.format_id)}</select></td>
            <td><input type="number" name="schedule[${date}][quantity][]" class="form-control" value="${item.qty}"></td>
            <td>Rs. <span class="rate-display">${(item.rate || 0).toFixed(2)}</span></td>
            <td>Rs. <span class="total-display">${rowTotal.toFixed(2)}</span></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(${index})">×</button></td>
        `;

        // ATTACH EVENT LISTENERS to the new inputs
        // This ensures that the moment a user changes a value, the state is updated
        tr.querySelectorAll('select, input').forEach(el => {
            el.addEventListener('change', (e) => {
                const key = e.target.name.split('[')[2].replace(']', ''); 
                // e.g., "schedule[date][platform_id][]" -> "platform_id"
                scheduleData[date][index][key] = e.target.value;
                calculateCost(tr, index);
            });
        });

        tbody.appendChild(tr);
    });
    updateTotalBudget();
}

function calculateCost(row, index) {
    const platform = row.querySelector('select[name*="platform_id"]').value;
    const placement = row.querySelector('select[name*="placement_id"]').value;
    const format = row.querySelector('select[name*="format_id"]').value;
    const qtyInput = row.querySelector('input[name*="quantity"]');
    const requestedQty = parseInt(qtyInput.value) || 0;
    
    // Safety: ensure scheduleData exists
    if (!scheduleData[activeDate] || !scheduleData[activeDate][index]) return;
    const content_id = scheduleData[activeDate][index].content_id;

    if (!content_id) return;

    // 1. Get Rate
    const rateItem = allRates.find(r => 
        Number(r.content_item_id) === Number(content_id) && 
        Number(r.platform_id) === Number(platform) && 
        Number(r.placement_id) === Number(placement) && 
        Number(r.media_format_id) === Number(format)
    );
    const rate = rateItem ? parseFloat(rateItem.rate) : 0;

    // 2. Get GLOBAL CAPACITY (Removed date check)
    const capacityItem = allCapacity.find(c => Number(c.rate_card_id) === Number(rateItem?.id));
    
    const maxCapacity = capacityItem ? parseInt(capacityItem.capacity_qty) : 999; 

    // 3. Validation
    if (requestedQty > maxCapacity) {
        document.getElementById('error-message').innerText = 
            "Quantity exceeds global daily limit. Max allowed: " + maxCapacity + ".";
        errorModal.show();
        qtyInput.value = maxCapacity;
    }

    // 4. Update UI
    const finalQty = parseInt(qtyInput.value) || 0;
    scheduleData[activeDate][index].rate = rate;
    scheduleData[activeDate][index].qty = finalQty;

    row.querySelector('.rate-display').innerText = rate.toFixed(2);
    row.querySelector('.total-display').innerText = (rate * finalQty).toFixed(2);
    
    updateTotalBudget();
}

function copyPreviousDate() {
    if (!activeDate) return alert("Select a date first!");

    const dates = Object.keys(scheduleData).sort();
    const currentIndex = dates.indexOf(activeDate);

    if (currentIndex <= 0) return alert("No previous date to copy from!");

    const previousDate = dates[currentIndex - 1];
    let hasMissingInventory = false;

    // Helper to check inventory
 // Inside copyPreviousDate, update the helper:
const getCapacity = (content_id, platform_id, placement_id, format_id) => {
    const rateItem = allRates.find(r => 
        Number(r.content_item_id) === Number(content_id) && 
        Number(r.platform_id) === Number(platform_id) && 
        Number(r.placement_id) === Number(placement_id) && 
        Number(r.media_format_id) === Number(format_id)
    );
    if (!rateItem) return null;
    
    // Now just check if a global capacity entry exists for this rate_card_id
    return allCapacity.find(c => Number(c.rate_card_id) === Number(rateItem.id));
};

    // 1. Validate and Map
    const newItems = scheduleData[previousDate].map(item => {
        const cap = getCapacity(item.content_id, item.platform_id, item.placement_id, item.format_id, activeDate);
        
        if (!cap) {
            hasMissingInventory = true;
            return null;
        }

        return {
            content_id: item.content_id,
            content_name: item.content_name,
            platform_id: item.platform_id,
            placement_id: item.placement_id,
            format_id: item.format_id,
            qty: 1,
            rate: 0
        };
    });

    // 2. Error handling
    if (hasMissingInventory) {
        document.getElementById('error-message').innerText = 
            "Cannot copy: One or more configurations have no inventory capacity defined for " + activeDate + ".";
        errorModal.show(); // Ensure errorModal is initialized globally
        return; 
    }

    // 3. Apply copy
    scheduleData[activeDate] = newItems;
    renderRowsForDate(activeDate);
    
    // 4. Trigger calculation to refresh rates
    document.querySelectorAll('#items-body tr').forEach((row, index) => {
        calculateCost(row, index);
    });

    
}

function removeRow(index) {
    if (!activeDate) return;

    // Remove the item from the array
    scheduleData[activeDate].splice(index, 1);

    // Re-render the UI for the current date
    renderRowsForDate(activeDate);
    
    // Update the total budget after deletion
    updateTotalBudget();
}
</script>

<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Inventory Limit Exceeded</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="error-message"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

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