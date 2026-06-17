<?php 
require_once '../config/config.php';
session_start();
if ($_SESSION['role'] !== 'Admin') { header("Location: " . BASE_URL . "index.php"); exit(); }

// Handle Form Submission: Optimized Upsert
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_qty'])) {
    $rate_card_id = (int)$_POST['rate_card_id'];
    $new_capacity = (int)$_POST['qty'];

    // Using ON DUPLICATE KEY UPDATE. 
    // Requires a UNIQUE constraint on rate_card_id in the inventory table.
    $stmt = $pdo->prepare("
        INSERT INTO inventory (rate_card_id, total_capacity, used_qty) 
        VALUES (?, ?, 0) 
        ON DUPLICATE KEY UPDATE total_capacity = VALUES(total_capacity)
    ");
    $stmt->execute([$rate_card_id, $new_capacity]);
    
    // Redirect to prevent form resubmission on page refresh
    header("Location: inventory.php?status=success");
    exit();
}

// Fetch inventory data
$inventory_data = $pdo->query("
    SELECT r.id as rate_card_id, c.name as item_name, c.type, 
           p.platform_name, a.placement_name, mf.format_name,
           COALESCE(i.total_capacity, 0) as total_capacity,
           COALESCE(i.used_qty, 0) as used_qty
    FROM rate_cards r
    JOIN content_items c ON r.content_item_id = c.id
    JOIN platforms p ON r.platform_id = p.id
    JOIN ad_placements a ON r.placement_id = a.id
    JOIN media_formats mf ON r.media_format_id = mf.id
    LEFT JOIN inventory i ON r.id = i.rate_card_id
    ORDER BY c.name
")->fetchAll();

include '../includes/header.php'; 
?>

<div class="container-fluid px-4">
    <h3 class="mb-4">Inventory Management</h3>
    
    <?php if(isset($_GET['status'])): ?>
        <div class="alert alert-success">Inventory updated successfully.</div>
    <?php endif; ?>
    
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Show/Program</th>
                        <th>Format</th>
                        <th>Platform</th>
                        <th>Placement</th>
                        <th>Capacity</th>
                        <th>Booked</th>
                        <th>Balance</th>
                        <th>Update Capacity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventory_data as $row): 
                        $balance = $row['total_capacity'] - $row['used_qty'];
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['type']); ?>:</strong> <?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td><span class="badge bg-info"><?php echo htmlspecialchars($row['format_name']); ?></span></td>
                        <td><?php echo htmlspecialchars($row['platform_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['placement_name']); ?></td>
                        <td><?php echo (int)$row['total_capacity']; ?></td>
                        <td><span class="text-danger fw-bold"><?php echo (int)$row['used_qty']; ?></span></td>
                        <td>
                            <span class="badge <?php echo $balance > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $balance; ?>
                            </span>
                        </td>
                        <td style="width: 200px;">
                            <form method="POST" class="d-flex gap-2">
                                <input type="hidden" name="rate_card_id" value="<?php echo $row['rate_card_id']; ?>">
                                <input type="number" name="qty" value="<?php echo $row['total_capacity']; ?>" class="form-control form-control-sm" required>
                                <button type="submit" name="update_qty" class="btn btn-sm btn-success">Save</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>