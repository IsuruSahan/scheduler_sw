<?php 
require_once '../config/config.php';
session_start();
if ($_SESSION['role'] !== 'Admin') { header("Location: " . BASE_URL . "index.php"); exit(); }

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add logic
    if (isset($_POST['add_platform'])) {
        $stmt = $pdo->prepare("INSERT INTO platforms (platform_name) VALUES (?)");
        $stmt->execute([$_POST['platform_name']]);
    } elseif (isset($_POST['add_placement'])) {
        $stmt = $pdo->prepare("INSERT INTO ad_placements (placement_name) VALUES (?)");
        $stmt->execute([$_POST['placement_name']]);
    }
    // Delete logic
    elseif (isset($_POST['delete_platform'])) {
        $stmt = $pdo->prepare("DELETE FROM platforms WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    } elseif (isset($_POST['delete_placement'])) {
        $stmt = $pdo->prepare("DELETE FROM ad_placements WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }
    // Edit logic
    elseif (isset($_POST['edit_platform'])) {
        $stmt = $pdo->prepare("UPDATE platforms SET platform_name = ? WHERE id = ?");
        $stmt->execute([$_POST['platform_name'], $_POST['id']]);
    } elseif (isset($_POST['edit_placement'])) {
        $stmt = $pdo->prepare("UPDATE ad_placements SET placement_name = ? WHERE id = ?");
        $stmt->execute([$_POST['placement_name'], $_POST['id']]);
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
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">Platforms</h5>
                    <form method="POST" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="platform_name" class="form-control" placeholder="New Platform" required>
                            <button type="submit" name="add_platform" class="btn btn-primary">Add</button>
                        </div>
                    </form>
                    <table class="table align-middle">
                        <?php foreach($platforms as $p): ?>
                        <tr>
                            <td>
                                <span id="text_p_<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['platform_name']); ?></span>
                                <form method="POST" id="form_p_<?php echo $p['id']; ?>" style="display:none;" class="row g-2">
                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <div class="col"><input type="text" name="platform_name" value="<?php echo htmlspecialchars($p['platform_name']); ?>" class="form-control form-control-sm"></div>
                                    <div class="col-auto"><button type="submit" name="edit_platform" class="btn btn-sm btn-success">Save</button></div>
                                </form>
                            </td>
                            <td class="text-end" style="width: 150px;">
                                <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('text_p_<?php echo $p['id']; ?>').style.display='none'; document.getElementById('form_p_<?php echo $p['id']; ?>').style.display='flex';">Edit</button>
                                <form method="POST" onsubmit="return confirm('Delete?');" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" name="delete_platform" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">Ad Placements</h5>
                    <form method="POST" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="placement_name" class="form-control" placeholder="New Placement" required>
                            <button type="submit" name="add_placement" class="btn btn-primary">Add</button>
                        </div>
                    </form>
                    <table class="table align-middle">
                        <?php foreach($placements as $pl): ?>
                        <tr>
                            <td>
                                <span id="text_pl_<?php echo $pl['id']; ?>"><?php echo htmlspecialchars($pl['placement_name']); ?></span>
                                <form method="POST" id="form_pl_<?php echo $pl['id']; ?>" style="display:none;" class="row g-2">
                                    <input type="hidden" name="id" value="<?php echo $pl['id']; ?>">
                                    <div class="col"><input type="text" name="placement_name" value="<?php echo htmlspecialchars($pl['placement_name']); ?>" class="form-control form-control-sm"></div>
                                    <div class="col-auto"><button type="submit" name="edit_placement" class="btn btn-sm btn-success">Save</button></div>
                                </form>
                            </td>
                            <td class="text-end" style="width: 150px;">
                                <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('text_pl_<?php echo $pl['id']; ?>').style.display='none'; document.getElementById('form_pl_<?php echo $pl['id']; ?>').style.display='flex';">Edit</button>
                                <form method="POST" onsubmit="return confirm('Delete?');" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo $pl['id']; ?>">
                                    <button type="submit" name="delete_placement" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>