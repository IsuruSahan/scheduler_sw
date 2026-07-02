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
$media_library = $pdo->query("SELECT * FROM media_library ORDER BY reference_no, schedule_name")->fetchAll();
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
                <div class="col-md-4">
    <label class="form-label">Assigned Team(s)</label>
    <div class="border rounded p-2">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="assigned_team[]" value="Content Editor Team" id="team1">
            <label class="form-check-label" for="team1">Content Editor Team</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="assigned_team[]" value="News Team" id="team2">
            <label class="form-check-label" for="team2">News Team</label>
        </div>
    </div>
</div>
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
        <th>Media</th>
        <th>Rate</th>
        <th>Total</th>
        <th>Action</th>
    </tr>
</thead>
            <tbody id="items-body"></tbody>
        </table>
        <button type="button" class="btn btn-primary mb-3" onclick="addRow()">+ Add Platform Row</button>




<div class="col-md-4">
    <label class="form-label">Total Current Cost</label>
    <div id="total-generated" class="h4 text-primary">0.00</div> 
</div>

<div class="col-md-4">
    <label class="form-label">Budget Status</label>
    <div id="budget-warning" class="text-danger" style="display:none;">
        Warning: Budget Exceeded!
    </div>
</div>

<input type="hidden" name="status" id="statusField" value="Active">
        
        <button type="submit" id="submitBtn" class="btn btn-success float-end">Create Schedule</button>
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
    refreshInventoryData('all');
    
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

function updateTotalBudget() {

console.log("Current total calculation running...");
    let total = 0;
    // Sum up all rows (assuming your total display has class .total-display)
    document.querySelectorAll('.total-display').forEach(el => {
        total += parseFloat(el.innerText.replace(/[^0-9.]/g, '')) || 0;
    });

    const budget = parseFloat(document.querySelector('input[name="budget_allocated"]').value) || 0;
    const display = document.getElementById('totalCostDisplay');
    const submitBtn = document.getElementById('submitBtn');
    const statusField = document.getElementById('statusField');

    display.innerText = 'Rs. ' + total.toLocaleString(undefined, {minimumFractionDigits: 2});

    // Budget Logic: If cost > budget, trigger approval flow
    if (total > budget) {
        display.classList.replace('text-primary', 'text-danger');
        submitBtn.innerText = 'Send for Marketing Approval';
        submitBtn.classList.replace('btn-primary', 'btn-warning');
        statusField.value = 'Pending Approval (Cost Review)';
    } else {
        display.classList.replace('text-danger', 'text-primary');
        submitBtn.innerText = 'Create Schedule';
        submitBtn.classList.replace('btn-warning', 'btn-primary');
        statusField.value = 'Active';
    }
}

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
/**
 * 1. Global Budget Calculation
 * Calculates across ALL dates in scheduleData
 */
function updateTotalBudget() {
    let total = 0;
    
    // Sum up totals from the state object for all dates
    Object.values(scheduleData).forEach(dateItems => {
        dateItems.forEach(item => {
            const rowTotal = (parseFloat(item.rate) || 0) * (parseInt(item.qty) || 0);
            total += rowTotal;
        });
    });
    
    const budget = parseFloat(document.getElementById('budget_input').value || 0);
    const totalDisplay = document.getElementById('total-generated');
    const warning = document.getElementById('budget-warning');
    const submitBtn = document.getElementById('submitBtn'); // Ensure this ID is on your submit button
    const statusField = document.getElementById('statusField'); // Ensure this ID is on your hidden status input

    // Update UI display
    totalDisplay.innerText = total.toFixed(2);
    
    // Toggle Warning & Update Button Status
    if (total > budget) {
        warning.style.display = 'block';
        submitBtn.innerText = 'Send for Marketing Approval';
        submitBtn.classList.replace('btn-primary', 'btn-warning');
        statusField.value = 'Pending Approval (Cost Review)';
    } else {
        warning.style.display = 'none';
        submitBtn.innerText = 'Create Schedule';
        submitBtn.classList.replace('btn-warning', 'btn-primary');
        statusField.value = 'Active';
    }
}

/**
 * 2. Render Rows & Attach Real-Time Listeners
 */
function renderRowsForDate(date) {
    const tbody = document.getElementById('items-body');
    tbody.innerHTML = '';
    
    if (!scheduleData[date]) return;

    scheduleData[date].forEach((item, index) => {
        const rowTotal = (parseFloat(item.rate) || 0) * (parseInt(item.qty) || 0);
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
            <td><input type="number" name="schedule[${date}][quantity][]" class="form-control" value="${item.qty || 1}"></td>
            <td>
                <input type="hidden" name="schedule[${date}][media_ids][]" id="media_input_${date}_${index}" value="${(item.media_ids || []).join(',')}">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openMediaModal('${date}', ${index})">Select Media</button>
                <span class="badge bg-info ms-1" id="media_label_${date}_${index}">${(item.media_ids || []).length} Selected</span>
            </td>
            <td>Rs. <span class="rate-display">${(parseFloat(item.rate) || 0).toFixed(2)}</span></td>
            <td>Rs. <span class="total-display">${rowTotal.toFixed(2)}</span></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(${index})">×</button></td>
        `;

        // Attach event listeners to update state and trigger budget calculation
tr.querySelectorAll('select, input').forEach(el => {
    el.addEventListener('change', (e) => {
        const match = e.target.name.match(/\[(\w+)\]\[\]$/);
        
        if (match) {
            const key = match[1]; // Captured field name (e.g., "platform_id")
            
            // 1. Update the local state
            scheduleData[date][index][key] = e.target.value;
            
            // 2. Recalculate row cost (Synchronous call)
calculateCost(tr, index);
        }
    });
});

        tbody.appendChild(tr);
    });
    updateTotalBudget();
}

let globalUsageData = {};

async function refreshInventoryData(date) {
    try {
        const response = await fetch(`../admin/get_usage.php?date=${date}`);
        globalUsageData = await response.json();
    } catch (e) {
        console.error("Failed to load inventory:", e);
    }
}

// Call this once when the date changes or page loads
// await refreshInventoryData(activeDate);

// FIND THIS FUNCTION AND REPLACE IT ENTIRELY
async function calculateCost(row, index) {
    const platform = row.querySelector('select[name*="platform_id"]').value;
    const placement = row.querySelector('select[name*="placement_id"]').value;
    const format = row.querySelector('select[name*="format_id"]').value;
    const qtyInput = row.querySelector('input[name*="quantity"]');
    const requestedQty = parseInt(qtyInput.value) || 0;
    
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

    // 2. Fetch Usage from Server to get "Remaining"
    try {
        const response = await fetch(`../admin/get_usage.php?date=${activeDate}`);
        const usageData = await response.json();
        const alreadyUsed = parseInt(usageData[rateItem.id]) || 0;
        
        // Get Total Capacity
        const capacityItem = allCapacity.find(c => Number(c.rate_card_id) === Number(rateItem?.id));
        const maxCapacity = capacityItem ? parseInt(capacityItem.capacity_qty) : 999;
        const remaining = maxCapacity - alreadyUsed;

        // 3. Validation
        if (requestedQty > remaining) {
            document.getElementById('error-message').innerText = 
                "Quantity exceeds remaining inventory for " + activeDate + ". Remaining: " + remaining + ".";
            errorModal.show();
            qtyInput.value = remaining > 0 ? remaining : 0;
        }

        // 4. Update UI
        const finalQty = parseInt(qtyInput.value) || 0;
        scheduleData[activeDate][index].rate = rate;
        scheduleData[activeDate][index].qty = finalQty;

        row.querySelector('.rate-display').innerText = rate.toFixed(2);
        row.querySelector('.total-display').innerText = (rate * finalQty).toFixed(2);
        
        updateTotalBudget();

    } catch (error) {
        console.error("Inventory check failed:", error);
    }
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
        rate: 0,
        // ADD THIS LINE TO COPY THE MEDIA SELECTION
        media_ids: item.media_ids ? [...item.media_ids] : [] 
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

let activeMediaDate = null;
let activeMediaIndex = null;

function openMediaModal(date, index) {
    activeMediaDate = date;
    activeMediaIndex = index;
    
    // Pre-check existing selections
    const currentIds = document.getElementById(`media_input_${date}_${index}`).value.split(',');
    document.querySelectorAll('.media-checkbox').forEach(cb => {
        cb.checked = currentIds.includes(cb.value);
    });
    
    new bootstrap.Modal(document.getElementById('mediaModal')).show();
}

function saveMediaSelection() {
    const selected = Array.from(document.querySelectorAll('.media-checkbox:checked')).map(cb => cb.value);
    
    // SAVE TO STATE
    scheduleData[activeMediaDate][activeMediaIndex].media_ids = selected;
    
    // UPDATE UI
    document.getElementById(`media_input_${activeMediaDate}_${activeMediaIndex}`).value = selected.join(',');
    document.getElementById(`media_label_${activeMediaDate}_${activeMediaIndex}`).innerText = `${selected.length} Selected`;
    
    bootstrap.Modal.getInstance(document.getElementById('mediaModal')).hide();
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
<div class="modal fade" id="mediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Media Assets</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Ref No</th>
                                <th>Schedule Name</th>
                                <th>File Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $media_list = $pdo->query("SELECT * FROM media_library")->fetchAll();
                            foreach($media_list as $m): ?>
                            <tr>
                                <td><input type="checkbox" class="media-checkbox" value="<?php echo $m['id']; ?>"></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($m['reference_no']); ?></span></td>
                                <td><?php echo htmlspecialchars($m['schedule_name']); ?></td>
                                <td><?php echo htmlspecialchars(basename($m['file_path'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveMediaSelection()">Save Selection</button>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>