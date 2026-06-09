<?php 
require_once '../config/config.php';
session_start();
if ($_SESSION['role'] !== 'Admin') { header("Location: " . BASE_URL . "index.php"); exit(); }

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['content_item_id']) && !empty($_POST['platform_id']) && !empty($_POST['placement_id'])) {
        $stmt = $pdo->prepare("INSERT INTO rate_cards (content_item_id, platform_id, placement_id, max_quantity, rate, media_format) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['content_item_id'], 
            $_POST['platform_id'], 
            $_POST['placement_id'], 
            $_POST['max_quantity'], 
            $_POST['rate'],
            $_POST['media_format']
        ]);
    }
}

// Fetch Dynamic Data
$items = $pdo->query("SELECT * FROM content_items ORDER BY type, name")->fetchAll();
$platforms = $pdo->query("SELECT * FROM platforms")->fetchAll();
$placements = $pdo->query("SELECT * FROM ad_placements")->fetchAll();

// Fetch Existing Rate Cards
$rates = $pdo->query("SELECT r.*, c.name as item_name, c.type, p.platform_name, a.placement_name 
                      FROM rate_cards r
                      JOIN content_items c ON r.content_item_id = c.id
                      JOIN platforms p ON r.platform_id = p.id
                      JOIN ad_placements a ON r.placement_id = a.id
                      ORDER BY r.id DESC")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="card p-4 shadow-sm">
    <h3 class="mb-4">Manage Rate Cards</h3>
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-3">
            <select name="content_item_id" class="form-control" required>
                <option value="">Select Show/Program/News</option>
                <?php foreach($items as $i): ?>
                    <option value="<?php echo $i['id']; ?>"><?php echo htmlspecialchars($i['type'] . ": " . $i['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-2">
            <select name="media_format" class="form-control" required>
                <option value="Full Video">Full Video</option>
                <option value="Clip">Clip</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="platform_id" class="form-control" required>
                <option value="">Platform</option>
                <?php foreach($platforms as $p): ?>
                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['platform_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-2">
            <select name="placement_id" class="form-control" required>
                <option value="">Placement</option>
                <?php foreach($placements as $pl): ?>
                    <option value="<?php echo $pl['id']; ?>"><?php echo htmlspecialchars($pl['placement_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-1">
            <input type="number" name="max_quantity" class="form-control" placeholder="Qty" required>
        </div>
        
        <div class="col-md-1">
            <input type="number" step="0.01" name="rate" class="form-control" placeholder="Rate" required>
        </div>
        
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">Add</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Type</th>
                    <th>Show/Name</th>
                    <th>Format</th>
                    <th>Platform</th>
                    <th>Placement</th>
                    <th>Qty</th>
                    <th>Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rates as $r): ?>
                <tr>
                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($r['type']); ?></span></td>
                    <td><?php echo htmlspecialchars($r['item_name']); ?></td>
                    <td>
                        <span class="badge <?php echo ($r['media_format'] == 'Clip') ? 'bg-warning text-dark' : 'bg-info'; ?>">
                            <?php echo $r['media_format']; ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($r['platform_name']); ?></td>
                    <td><?php echo htmlspecialchars($r['placement_name']); ?></td>
                    <td><?php echo (int)$r['max_quantity']; ?></td>
                    <td>Rs. <?php echo number_format($r['rate'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>