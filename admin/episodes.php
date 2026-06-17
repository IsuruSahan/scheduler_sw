<?php 
require_once '../config/config.php';
session_start();
if ($_SESSION['role'] !== 'Admin') { header("Location: " . BASE_URL . "index.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CONTENT ITEMS
    if (isset($_POST['add_content'])) {
        $pdo->prepare("INSERT INTO content_items (name, type) VALUES (?, ?)")->execute([$_POST['name'], $_POST['type']]);
    } elseif (isset($_POST['delete_content'])) {
        $pdo->prepare("DELETE FROM content_items WHERE id = ?")->execute([$_POST['id']]);
    } elseif (isset($_POST['edit_content'])) {
        $pdo->prepare("UPDATE content_items SET name = ?, type = ? WHERE id = ?")->execute([$_POST['name'], $_POST['type'], $_POST['id']]);
    }
}

$items = $pdo->query("SELECT * FROM content_items ORDER BY type, name")->fetchAll();

function getTypeColor($type) {
    return ($type == 'News') ? 'bg-warning text-dark' : (($type == 'Teledrama') ? 'bg-primary' : 'bg-success');
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-lg-8">
            <h3 class="mb-4 text-primary">Content Management</h3>
            
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title text-dark mb-3">Add/Manage Content Items</h5>
                    
                    <form method="POST" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="name" class="form-control" placeholder="New Item Name" required>
                            <select name="type" class="form-select" style="max-width: 150px;">
                                <option value="Teledrama">Teledrama</option>
                                <option value="Program">Program</option>
                                <option value="News">News</option>
                            </select>
                            <button type="submit" name="add_content" class="btn btn-primary">Add Item</button>
                        </div>
                    </form>
                    
                    <hr>
                    
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Content Name & Type</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($items as $i): ?>
                                <tr>
                                    <td>
                                        <div id="view_c_<?php echo $i['id']; ?>">
                                            <strong><?php echo htmlspecialchars($i['name']); ?></strong><br>
                                            <span class="badge <?php echo getTypeColor($i['type']); ?>"><?php echo $i['type']; ?></span>
                                        </div>
                                        
                                        <form method="POST" id="edit_c_<?php echo $i['id']; ?>" style="display:none;" class="row g-2">
                                            <input type="hidden" name="id" value="<?php echo $i['id']; ?>">
                                            <div class="col-6">
                                                <input type="text" name="name" value="<?php echo htmlspecialchars($i['name']); ?>" class="form-control form-control-sm" required>
                                            </div>
                                            <div class="col-4">
                                                <select name="type" class="form-select form-select-sm">
                                                    <option value="Teledrama" <?php if($i['type']=='Teledrama') echo 'selected'; ?>>Teledrama</option>
                                                    <option value="Program" <?php if($i['type']=='Program') echo 'selected'; ?>>Program</option>
                                                    <option value="News" <?php if($i['type']=='News') echo 'selected'; ?>>News</option>
                                                </select>
                                            </div>
                                            <div class="col-2">
                                                <button type="submit" name="edit_content" class="btn btn-sm btn-success w-100">Save</button>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('view_c_<?php echo $i['id']; ?>').style.display='none'; document.getElementById('edit_c_<?php echo $i['id']; ?>').style.display='flex';">Edit</button>
                                        <form method="POST" onsubmit="return confirm('Delete this content item?');" class="d-inline">
                                            <input type="hidden" name="id" value="<?php echo $i['id']; ?>">
                                            <button type="submit" name="delete_content" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>