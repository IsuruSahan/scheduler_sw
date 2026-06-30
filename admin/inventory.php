<?php 
require_once '../config/config.php';
session_start();
if ($_SESSION['role'] !== 'Admin') { header("Location: " . BASE_URL . "index.php"); exit(); }

// 2. Handle AJAX Edit
if (isset($_GET['action']) && $_GET['action'] === 'ajax_edit') {
    header('Content-Type: application/json');
    $rate_card_id = (int)$_POST['rate_card_id']; // Changed to rate_card_id
    $qty = (int)$_POST['qty'];
    
    // Update the global capacity for this rate card
    $stmt = $pdo->prepare("INSERT INTO inventory_daily_capacity (rate_card_id, capacity_qty) VALUES (?, ?) ON DUPLICATE KEY UPDATE capacity_qty = ?");
    if ($stmt->execute([$rate_card_id, $qty, $qty])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit();
}

// 2. Fetch Base Inventory
$allocations = $pdo->query("
    SELECT idc.*, c.name as item_name, p.platform_name, a.placement_name, f.format_name,
    (SELECT COALESCE(SUM(s.quantity), 0) 
     FROM schedule_items s 
     WHERE s.rate_card_id = idc.rate_card_id 
     AND s.scheduled_date = CURDATE()) as today_used,
    (idc.capacity_qty - (SELECT COALESCE(SUM(s.quantity), 0) 
                         FROM schedule_items s 
                         WHERE s.rate_card_id = idc.rate_card_id 
                         AND s.scheduled_date = CURDATE())) as remaining_today
    FROM inventory_daily_capacity idc
    JOIN rate_cards r ON idc.rate_card_id = r.id
    JOIN content_items c ON r.content_item_id = c.id
    JOIN platforms p ON r.platform_id = p.id
    JOIN ad_placements a ON r.placement_id = a.id
    JOIN media_formats f ON r.media_format_id = f.id
")->fetchAll();


$inventory_items = $pdo->query("
    SELECT r.id as rate_card_id, 
           c.name as item_name, 
           p.platform_name, 
           a.placement_name,
           f.format_name 
    FROM rate_cards r 
    JOIN content_items c ON r.content_item_id = c.id 
    JOIN platforms p ON r.platform_id = p.id 
    JOIN ad_placements a ON r.placement_id = a.id
    JOIN media_formats f ON r.media_format_id = f.id
")->fetchAll();

include '../includes/header.php'; 
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Global Daily Capacity Management</h3>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inventoryModal">+ Set Global Capacity</button>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <input type="text" id="dateFilter" class="form-control" placeholder="Check usage for date..." onchange="loadUsage(this.value)">
        </div>
        <div class="col-md-8">
            <input type="text" id="filterInput" class="form-control" placeholder="Search Item, Platform...">
        </div>
    </div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <table class="table table-hover align-middle" id="inventoryTable">
<thead>
    <tr>
        <th>Item</th>
        <th>Platform</th>
        <th>Placement</th>
        <th>Format</th>
        <th>Total Daily Cap</th>
        <th>Used (Selected Date)</th>
        <th>Remaining (Selected Date)</th> </tr>
</thead>
<tbody>
    <?php foreach ($allocations as $row): ?>
    <tr data-rate-id="<?php echo $row['rate_card_id']; ?>" data-cap="<?php echo $row['capacity_qty']; ?>">
        <td><?php echo htmlspecialchars($row['item_name']); ?></td>
        <td><?php echo htmlspecialchars($row['platform_name']); ?></td>
        <td><?php echo htmlspecialchars($row['placement_name']); ?></td>
        <td><?php echo htmlspecialchars($row['format_name']); ?></td>
        <td>
            <input type="number" class="form-control form-control-sm" value="<?php echo $row['capacity_qty']; ?>" style="width: 80px;" onchange="updateQty(this)">
        </td>
<td>
    <span class="usage-selected-date badge bg-info text-dark" id="usage-<?php echo $row['rate_card_id']; ?>">--</span>
</td>
<td>
    <span class="remaining-selected-date badge bg-secondary text-white" id="rem-<?php echo $row['rate_card_id']; ?>">--</span>
</td>
    </tr>
    <?php endforeach; ?>
</tbody>
        </table>
    </div>
</div>
</div>

<div class="modal fade" id="inventoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Global Daily Capacity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Select Inventory Item</label>
                    <select name="rate_card_id" class="form-select" required>
                        <?php foreach($inventory_items as $item): ?>
                            <option value="<?php echo $item['rate_card_id']; ?>">
                                <?php echo htmlspecialchars($item['item_name'] . ' | ' . $item['platform_name'] . ' | ' . $item['placement_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Daily Capacity</label>
                    <input type="number" name="qty" class="form-control" value="0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="update_global_qty" class="btn btn-success">Save Global Capacity</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Replace your existing flatpickr initialization
flatpickr("#dateFilter", { 
    dateFormat: "Y-m-d",
    onChange: function(selectedDates, dateStr, instance) {
        loadUsage(dateStr); // Trigger loadUsage when date is selected
    }
});

    // Fetch usage via AJAX when date changes
async function loadUsage(date) {
    if (!date) return;
    
    const url = `get_usage.php?date=${date}&t=${new Date().getTime()}`;
    
    try {
        const response = await fetch(url);
        const data = await response.json(); 
        
        // Loop through all rows to reset/update
        document.querySelectorAll('tr[data-rate-id]').forEach(row => {
            const rid = row.getAttribute('data-rate-id');
            const inputField = row.querySelector('input[type="number"]');
            
            // Get current capacity and usage
            const totalCap = parseInt(inputField.value) || 0;
            const used = data.hasOwnProperty(rid) ? parseInt(data[rid]) : 0;
            const remaining = totalCap - used;

            // Update specific elements using ID targeting
            const usageSpan = document.getElementById('usage-' + rid);
            const remSpan = document.getElementById('rem-' + rid);

            if (usageSpan) usageSpan.innerText = used;
            
            if (remSpan) {
                remSpan.innerText = remaining;
                remSpan.className = remaining < 0 
                    ? 'badge bg-danger text-white' 
                    : 'badge bg-secondary text-white';
            }
        });
    } catch (error) {
        console.error("Error fetching usage:", error);
    }
}
    // Filter logic
    document.getElementById('filterInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        document.querySelectorAll('#inventoryTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
        });
    });

function updateQty(input) {
    const row = input.closest('tr');
    const rateCardId = row.getAttribute('data-rate-id');
    const qty = input.value;
    
    // CRITICAL: Update the DOM value attribute so future calculations see the change
    input.setAttribute('value', qty); 
    
    const params = new URLSearchParams();
    params.append('rate_card_id', rateCardId);
    params.append('qty', qty);

    fetch('inventory.php?action=ajax_edit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(r => r.json())
    .then(d => { 
        if(d.status === 'success') {
            input.classList.add('is-valid');
            setTimeout(() => input.classList.remove('is-valid'), 2000);
            
            // OPTIONAL: Re-trigger loadUsage if a date is already selected
            const currentDate = document.getElementById('dateFilter').value;
            if(currentDate) loadUsage(currentDate);
        } else {
            alert('Update failed!');
        }
    });
}
</script>
<?php include '../includes/footer.php'; ?>