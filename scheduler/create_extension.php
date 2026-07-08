<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$schedule_id = $_GET['schedule_id'] ?? 0;

// Fetch master data
$rate_cards = $pdo->query("SELECT * FROM rate_cards")->fetchAll();
$content_items = $pdo->query("SELECT id, name, type FROM content_items ORDER BY name")->fetchAll();
$platforms = $pdo->query("SELECT * FROM platforms ORDER BY platform_name")->fetchAll();
$placements = $pdo->query("SELECT * FROM ad_placements ORDER BY placement_name")->fetchAll();
$media_formats = $pdo->query("SELECT * FROM media_formats ORDER BY format_name")->fetchAll();
$inventory_data = $pdo->query("SELECT rate_card_id, capacity_qty FROM inventory_daily_capacity")->fetchAll(PDO::FETCH_ASSOC);

// Fetch existing items
$existing_stmt = $pdo->prepare("SELECT * FROM schedule_items WHERE schedule_id = ?");
$existing_stmt->execute([$schedule_id]);
$existing_items = $existing_stmt->fetchAll(PDO::FETCH_ASSOC);

$content_map = array_column($content_items, 'name', 'id');
$initial_data = [];
foreach ($existing_items as $item) {
    $date = $item['scheduled_date'];
    if (!isset($initial_data[$date])) $initial_data[$date] = [];
    $initial_data[$date][] = [
        'content_id' => $item['content_item_id'],
        'content_name' => $content_map[$item['content_item_id']] ?? 'Unknown',
        'platform_id' => $item['platform_id'],
        'placement_id' => $item['placement_id'],
        'format_id' => $item['rate_card_id'],
        'qty' => $item['quantity'],
        'rate' => ($item['quantity'] > 0) ? ($item['cost'] / $item['quantity']) : 0
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3">
    <form action="process_extension.php" method="POST" id="scheduleForm">
        <input type="hidden" name="schedule_id" value="<?php echo $schedule_id; ?>">
        <input type="hidden" name="full_schedule_json" id="full_schedule_json">
        
        <div class="row mb-3">
            <div class="col-md-4"><input type="date" id="start_date" class="form-control"></div>
            <div class="col-md-4"><input type="date" id="end_date" class="form-control"></div>
            <div class="col-md-4"><button type="button" class="btn btn-secondary w-100" onclick="initDateRange()">Initialize Dates</button></div>
        </div>

        <div id="date-selector" class="mb-3 d-flex flex-wrap"></div>
        <h5 id="current-date-title" class="text-primary">Select a date</h5>
        
        <table class="table table-bordered">
            <thead><tr><th>Program</th><th>Platform</th><th>Placement</th><th>Format</th><th>Qty</th><th>Cost</th><th>Action</th></tr></thead>
            <tbody id="items-body"></tbody>
        </table>
        <button type="button" class="btn btn-outline-secondary" onclick="addRow()">+ Add Item</button>
        <button type="submit" class="btn btn-primary">Save Extension</button>
    </form>

    <div class="modal fade" id="contentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5>Select Program</h5></div>
            <div class="modal-body">
                <table class="table">
                    <thead><tr><th>Name</th><th>Type</th><th>Action</th></tr></thead>
                    <tbody id="contentModalBody">
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Global State - Ensure these are defined at the very top
let scheduleData = <?php echo json_encode($initial_data); ?>;
let activeDate = null;
const allRates = <?php echo json_encode($rate_cards); ?>;
const allPlatforms = <?php echo json_encode($platforms); ?>;
const allPlacements = <?php echo json_encode($placements); ?>;
const allFormats = <?php echo json_encode($media_formats); ?>;
const allCapacity = <?php echo json_encode($inventory_data); ?>;
const allContent = <?php echo json_encode($content_items); ?>;

document.addEventListener("DOMContentLoaded", () => {
    // 1. Initial State Check
    if (Object.keys(scheduleData).length > 0) {
        initDateRange(); 
    }
});

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

function renderRowsForDate(date) {
    const tbody = document.getElementById('items-body');
    tbody.innerHTML = '';
    scheduleData[date].forEach((item, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
        <input type="hidden" name="schedule[${date}][content_id][]" value="${item.content_id}">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openContentModal(${index})">
            ${item.content_name || 'Select Program'}
        </button>
    </td>
            <td><select class="form-select plat" onchange="calculateRow(this.closest('tr'), ${index})">${createOptions(allPlatforms, 'platform_name', item.platform_id)}</select></td>
            <td><select class="form-select plac" onchange="calculateRow(this.closest('tr'), ${index})">${createOptions(allPlacements, 'placement_name', item.placement_id)}</select></td>
            <td><select class="form-select formt" onchange="calculateRow(this.closest('tr'), ${index})">${createOptions(allFormats, 'format_name', item.format_id)}</select></td>
            <td><input type="number" class="form-control qty" value="${item.qty}" onchange="calculateRow(this.closest('tr'), ${index})"></td>
            <td>Rs. <span class="cost-display">${(item.rate * item.qty).toFixed(2)}</span></td>
            <td><button type="button" class="btn btn-danger" onclick="removeRow(${index})">×</button></td>
        `;
        tbody.appendChild(tr);
    });
}

async function calculateRow(tr, index) {
    const pId = tr.querySelector('.plat').value;
    const plId = tr.querySelector('.plac').value;
    const fId = tr.querySelector('.formt').value;
    const qtyInput = tr.querySelector('.qty');
    const requestedQty = parseInt(qtyInput.value) || 0;
    
    const rateObj = allRates.find(r => r.platform_id == pId && r.placement_id == plId && r.media_format_id == fId);
    
    try {
        const response = await fetch(`../admin/get_usage.php?date=${activeDate}`);
        const usageData = await response.json();
        
        // 1. Get the raw total from the DB
        let used = parseInt(usageData[rateObj.id]) || 0;
        
        // 2. EXCLUDE THIS ROW'S EXISTING USAGE
        // If this row already exists in scheduleData, subtract its current qty from the used count
        if (scheduleData[activeDate][index] && scheduleData[activeDate][index].qty) {
            used -= parseInt(scheduleData[activeDate][index].qty);
        }
        
        // 3. Now validate against the "Clean" usage
        const maxCap = parseInt(allCapacity.find(c => c.rate_card_id == rateObj.id)?.capacity_qty) || 999;
        const remaining = maxCap - used;

        if (requestedQty > remaining) {
            alert(`Insufficient inventory! You are requesting ${requestedQty}, but only ${remaining} are available (excluding your current selection).`);
            qtyInput.value = remaining; 
        }
    } catch (e) { console.error("Validation failed:", e); }

   


        // 3. Update UI
        const finalQty = parseInt(qtyInput.value);
        const rate = parseFloat(rateObj.rate);
        scheduleData[activeDate][index].rate = rate;
        scheduleData[activeDate][index].qty = finalQty;
        tr.querySelector('.cost-display').innerText = (rate * finalQty).toFixed(2);
        
        updateTotalBudget();
    }

    function updateTotalBudget() {
        let total = 0;
        Object.values(scheduleData).forEach(dateItems => {
            dateItems.forEach(item => {
                total += (parseFloat(item.rate) || 0) * (parseInt(item.qty) || 0);
            });
        });
        // Ensure this ID exists in your HTML
        const display = document.getElementById('total-generated');
        if (display) display.innerText = total.toFixed(2);
    }

function createOptions(arr, key, selected) {
    return arr.map(i => `<option value="${i.id}" ${i.id == selected ? 'selected' : ''}>${i[key]}</option>`).join('');
}

function addRow() {
    if (!activeDate) return alert("Select a date!");
    scheduleData[activeDate].push({content_id: 1, content_name:'New Program', platform_id:1, placement_id:1, format_id:1, qty:1, rate:0});
    renderRowsForDate(activeDate);
}

function removeRow(index) {
    scheduleData[activeDate].splice(index, 1);
    renderRowsForDate(activeDate);
    updateTotalBudget();
}

document.getElementById('scheduleForm').addEventListener('submit', () => {
    document.getElementById('full_schedule_json').value = JSON.stringify(scheduleData);
});

function openContentModal(index) {
    activeRowIndex = index; 
    contentModal.show();
}

function selectContent(id, name) {
    if (activeRowIndex === null || activeDate === null) return;
    
    // Update the state
    scheduleData[activeDate][activeRowIndex].content_id = id;
    scheduleData[activeDate][activeRowIndex].content_name = name;
    
    // Refresh the UI and re-calculate cost
    renderRowsForDate(activeDate);
    contentModal.hide();
}

// Populate Modal on load
document.addEventListener("DOMContentLoaded", () => {
    contentModal = new bootstrap.Modal(document.getElementById('contentModal'));
    const tbody = document.getElementById('contentModalBody');
    
    // allContent comes from your PHP json_encode
    allContent.forEach(item => {
        tbody.innerHTML += `<tr>
            <td>${item.name}</td>
            <td>${item.type}</td>
            <td><button type="button" class="btn btn-primary btn-sm" onclick="selectContent(${item.id}, '${item.name}')">Select</button></td>
        </tr>`;
    });
});


</script>
</body>
</html>