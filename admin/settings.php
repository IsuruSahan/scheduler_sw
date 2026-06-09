<?php 
require_once '../config/config.php';
session_start();
if ($_SESSION['role'] !== 'Admin') { header("Location: " . BASE_URL . "index.php"); exit(); }

// Helper function to handle inserts
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_program'])) {
        $stmt = $pdo->prepare("INSERT INTO content_items (name, type) VALUES (?, ?)");
        $stmt->execute([$_POST['name'], $_POST['type']]);
    } elseif (isset($_POST['add_platform'])) {
        $stmt = $pdo->prepare("INSERT INTO platforms (platform_name) VALUES (?)");
        $stmt->execute([$_POST['platform_name']]);
    } elseif (isset($_POST['add_placement'])) {
        $stmt = $pdo->prepare("INSERT INTO ad_placements (placement_name) VALUES (?)");
        $stmt->execute([$_POST['placement_name']]);
    }
}

$programs = $pdo->query("SELECT * FROM content_items ORDER BY type, name")->fetchAll();
$platforms = $pdo->query("SELECT * FROM platforms")->fetchAll();
$placements = $pdo->query("SELECT * FROM ad_placements")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid px-4">
    <h3 class="mb-4 text-primary">System Settings</h3>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title text-secondary mb-3">Content Items</h5>
                    <form method="POST" class="mb-3">
                        <div class="input-group mb-2">
                            <input type="text" name="name" class="form-control" placeholder="Name" required>
                            <select name="type" class="form-select" style="max-width: 100px;">
                                <option value="Teledrama">Teledrama</option>
                                <option value="Program">Program</option>
                                <option value="News">News</option>
                            </select>
                        </div>
                        <button type="submit" name="add_program" class="btn btn-primary w-100">Add Item</button>
                    </form>
                    <ul class="list-group list-group-flush">
                        <?php foreach($programs as $p): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center small">
                                <span><strong>[<?php echo htmlspecialchars($p['type']); ?>]</strong> <?php echo htmlspecialchars($p['name']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title text-secondary mb-3">Platforms</h5>
                    <form method="POST" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="platform_name" class="form-control" placeholder="e.g. YouTube" required>
                            <button type="submit" name="add_platform" class="btn btn-primary">Add</button>
                        </div>
                    </form>
                    <ul class="list-group list-group-flush">
                        <?php foreach($platforms as $p): ?>
                            <li class="list-group-item small"><?php echo htmlspecialchars($p['platform_name']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title text-secondary mb-3">Ad Placements</h5>
                    <form method="POST" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="placement_name" class="form-control" placeholder="e.g. Mid-roll" required>
                            <button type="submit" name="add_placement" class="btn btn-primary">Add</button>
                        </div>
                    </form>
                    <ul class="list-group list-group-flush">
                        <?php foreach($placements as $pl): ?>
                            <li class="list-group-item small"><?php echo htmlspecialchars($pl['placement_name']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>