<?php 
require_once '../config/config.php';
session_start();
if ($_SESSION['role'] !== 'Admin') { header("Location: " . BASE_URL . "index.php"); exit(); }

// Helper function to handle inserts
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_platform'])) {
        $stmt = $pdo->prepare("INSERT INTO platforms (platform_name) VALUES (?)");
        $stmt->execute([$_POST['platform_name']]);
    } elseif (isset($_POST['add_placement'])) {
        $stmt = $pdo->prepare("INSERT INTO ad_placements (placement_name) VALUES (?)");
        $stmt->execute([$_POST['placement_name']]);
    }
}

$platforms = $pdo->query("SELECT * FROM platforms ORDER BY platform_name")->fetchAll();
$placements = $pdo->query("SELECT * FROM ad_placements ORDER BY placement_name")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid px-4">
    <h3 class="mb-4 text-primary">System Settings</h3>
    
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 shadow-sm border-0 bg-white">
                <div class="card-body">
                    <h5 class="card-title text-dark mb-3">Platforms</h5>
                    <form method="POST" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="platform_name" class="form-control" placeholder="e.g. YouTube" required>
                            <button type="submit" name="add_platform" class="btn btn-primary">Add Platform</button>
                        </div>
                    </form>
                    <ul class="list-group list-group-flush bg-white">
                        <?php foreach($platforms as $p): ?>
                            <li class="list-group-item bg-white small border-bottom text-muted">
                                <i class="bi bi-display me-2"></i><?php echo htmlspecialchars($p['platform_name']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 shadow-sm border-0 bg-white">
                <div class="card-body">
                    <h5 class="card-title text-dark mb-3">Ad Placements</h5>
                    <form method="POST" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="placement_name" class="form-control" placeholder="e.g. Mid-roll" required>
                            <button type="submit" name="add_placement" class="btn btn-primary">Add Placement</button>
                        </div>
                    </form>
                    <ul class="list-group list-group-flush bg-white">
                        <?php foreach($placements as $pl): ?>
                            <li class="list-group-item bg-white small border-bottom text-muted">
                                <i class="bi bi-play-circle me-2"></i><?php echo htmlspecialchars($pl['placement_name']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>