<?php 
require_once '../config/config.php';
session_start();
if ($_SESSION['role'] !== 'Admin') { header("Location: " . BASE_URL . "index.php"); exit(); }

/// 1. Handle Form Submission (Full Page Load)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_daily_qty'])) {
    $rate_card_id = (int)$_POST['rate_card_id'];
    $capacity_date = $_POST['capacity_date'];
    $new_capacity = (int)$_POST['qty'];
    $stmt = $pdo->prepare("INSERT INTO inventory_daily_capacity (rate_card_id, capacity_date, capacity_qty) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE capacity_qty = VALUES(capacity_qty)");
    $stmt->execute([$rate_card_id, $capacity_date, $new_capacity]);
    header("Location: inventory.php?status=success");
    exit();
}

// 2. Handle AJAX Edit
if (isset($_GET['action']) && $_GET['action'] === 'ajax_edit') {
    $id = (int)$_POST['id'];
    $qty = (int)$_POST['qty'];
    $stmt = $pdo->prepare("UPDATE inventory_daily_capacity SET capacity_qty = ? WHERE id = ?");
    if ($stmt->execute([$qty, $id])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit();
}

// 2. Fetch inventory items for Modal
$inventory_items = $pdo->query("
    SELECT r.id as rate_card_id, c.name as item_name, c.type, 
           p.platform_name, a.placement_name
    FROM rate_cards r
    JOIN content_items c ON r.content_item_id = c.id
    JOIN platforms p ON r.platform_id = p.id
    JOIN ad_placements a ON r.placement_id = a.id
")->fetchAll();

// 3. Fetch Existing Allocations
$allocations = $pdo->query("
    SELECT idc.*, c.name as item_name, p.platform_name, a.placement_name 
    FROM inventory_daily_capacity idc
    JOIN rate_cards r ON idc.rate_card_id = r.id
    JOIN content_items c ON r.content_item_id = c.id
    JOIN platforms p ON r.platform_id = p.id
    JOIN ad_placements a ON r.placement_id = a.id
    ORDER BY idc.capacity_date DESC
")->fetchAll();

include '../includes/header.php'; 
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Inventory Capacity Management</h3>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inventoryModal">
            + Set New Capacity
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white"><strong>Existing Capacity Combinations</strong></div>
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead><tr><th>Date</th><th>Item</th><th>Platform</th><th>Placement</th><th>Qty</th></tr></thead>
                <tbody>
                    <?php foreach ($allocations as $row): ?>
                    <tr>
    <td><?php echo $row['capacity_date']; ?></td>
    <td><?php echo htmlspecialchars($row['item_name']); ?></td>
    <td><?php echo htmlspecialchars($row['platform_name']); ?></td>
    <td><?php echo htmlspecialchars($row['placement_name']); ?></td>
    <td>
        <input type="number" class="form-control form-control-sm qty-input" 
               data-id="<?php echo $row['id']; ?>" 
               value="<?php echo $row['capacity_qty']; ?>" 
               style="width: 80px;"
               onchange="updateQty(this)">
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
            <div class="modal-header"><h5 class="modal-title">Set New Capacity</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Select Inventory Item</label>
                    <select name="rate_card_id" class="form-select" required>
                        <?php foreach($inventory_items as $item): ?>
                            <option value="<?php echo $item['rate_card_id']; ?>">
                                <?php echo $item['type'] . ': ' . $item['item_name'] . ' | ' . $item['platform_name'] . ' (' . $item['placement_name'] . ')'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Date</label>
                    <input type="text" name="capacity_date" class="form-control date-picker" required>
                </div>
                <div class="mb-3">
                    <label>Quantity</label>
                    <input type="number" name="qty" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" name="update_daily_qty" class="btn btn-success">Save Capacity</button></div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr(".date-picker", { dateFormat: "Y-m-d" });

    function updateQty(inputElement) {
    const id = inputElement.getAttribute('data-id');
    const qty = inputElement.value;
    
    fetch('inventory.php?action=ajax_edit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&qty=${qty}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            inputElement.classList.add('is-valid');
            setTimeout(() => inputElement.classList.remove('is-valid'), 2000);
        } else {
            alert('Failed to update quantity.');
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>