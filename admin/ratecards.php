<?php 
require_once '../config/config.php';
session_start();
if ($_SESSION['role'] !== 'Admin') { header("Location: " . BASE_URL . "index.php"); exit(); }

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ADD - Removed max_quantity
    if (isset($_POST['add_rate'])) {
        $stmt = $pdo->prepare("INSERT INTO rate_cards (content_item_id, platform_id, placement_id, rate, media_format_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['content_item_id'], $_POST['platform_id'], $_POST['placement_id'], $_POST['rate'], $_POST['media_format_id']]);
    } 
    // DELETE
    elseif (isset($_POST['delete_rate'])) {
        $pdo->prepare("DELETE FROM rate_cards WHERE id = ?")->execute([$_POST['id']]);
    }
    // EDIT - Removed max_quantity update
    elseif (isset($_POST['edit_rate'])) {
        $pdo->prepare("UPDATE rate_cards SET rate = ? WHERE id = ?")
            ->execute([$_POST['rate'], $_POST['id']]);
    }
}

// Fetch Data
$items = $pdo->query("SELECT * FROM content_items ORDER BY type, name")->fetchAll();
$platforms = $pdo->query("SELECT * FROM platforms")->fetchAll();
$placements = $pdo->query("SELECT * FROM ad_placements")->fetchAll();
$formats = $pdo->query("SELECT * FROM media_formats ORDER BY format_name")->fetchAll();
$rates = $pdo->query("SELECT r.*, c.name as item_name, c.type, p.platform_name, a.placement_name, mf.format_name 
                      FROM rate_cards r
                      JOIN content_items c ON r.content_item_id = c.id
                      JOIN platforms p ON r.platform_id = p.id
                      JOIN ad_placements a ON r.placement_id = a.id
                      JOIN media_formats mf ON r.media_format_id = mf.id
                      ORDER BY r.id DESC")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid px-4">
    <h3 class="mb-4">Manage Rate Cards</h3>
    
    <form method="POST" class="row g-2 mb-4 p-3 bg-light rounded">
        <div class="col-md-3">
            <select name="content_item_id" class="form-select" required>
                <option value="">Select Show/Program</option>
                <?php foreach($items as $i): ?>
                    <option value="<?php echo $i['id']; ?>"><?php echo htmlspecialchars($i['type'] . ": " . $i['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="media_format_id" class="form-select" required>
                <option value="">Select Format</option>
                <?php foreach($formats as $f): ?>
                    <option value="<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['format_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="platform_id" class="form-select" required>
                <option value="">Platform</option>
                <?php foreach($platforms as $p): ?><option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['platform_name']); ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="placement_id" class="form-select" required>
                <option value="">Placement</option>
                <?php foreach($placements as $pl): ?><option value="<?php echo $pl['id']; ?>"><?php echo htmlspecialchars($pl['placement_name']); ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2"><input type="number" step="0.01" name="rate" class="form-control" placeholder="Rate (Rs.)" required></div>
        <div class="col-md-1"><button type="submit" name="add_rate" class="btn btn-primary w-100">Add</button></div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Type</th><th>Show/Name</th><th>Format</th><th>Platform</th><th>Placement</th><th>Rate per qty</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($rates as $r): ?>
                <tr>
                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($r['type']); ?></span></td>
                    <td><?php echo htmlspecialchars($r['item_name']); ?></td>
                    <td><span class="badge bg-info"><?php echo htmlspecialchars($r['format_name']); ?></span></td>
                    <td><?php echo htmlspecialchars($r['platform_name']); ?></td>
                    <td><?php echo htmlspecialchars($r['placement_name']); ?></td>
                    <td>
                        <div id="view_rate_<?php echo $r['id']; ?>">Rs. <?php echo number_format($r['rate'], 2); ?></div>
                        <form method="POST" id="edit_rate_<?php echo $r['id']; ?>" style="display:none;" class="row g-1">
                            <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                            <input type="number" step="0.01" name="rate" value="<?php echo $r['rate']; ?>" class="form-control form-control-sm">
                            <button type="submit" name="edit_rate" class="btn btn-sm btn-success mt-1">Save</button>
                        </form>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('view_rate_<?php echo $r['id']; ?>').style.display='none'; document.getElementById('edit_rate_<?php echo $r['id']; ?>').style.display='block';">Edit</button>
                        <form method="POST" onsubmit="return confirm('Delete this rate card?');" class="d-inline">
                            <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                            <button type="submit" name="delete_rate" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>