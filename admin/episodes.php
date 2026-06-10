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
    // EPISODES
    elseif (isset($_POST['add_episode'])) {
        $pdo->prepare("INSERT INTO episodes (content_item_id, episode_number, episode_title, upload_date) VALUES (?, ?, ?, ?)")
            ->execute([$_POST['content_item_id'], $_POST['episode_number'] ?: 0, $_POST['episode_title'] ?: 'N/A', $_POST['upload_date']]);
    } elseif (isset($_POST['delete_episode'])) {
        $pdo->prepare("DELETE FROM episodes WHERE id = ?")->execute([$_POST['id']]);
    } elseif (isset($_POST['edit_episode'])) {
        $pdo->prepare("UPDATE episodes SET episode_number = ?, episode_title = ?, upload_date = ? WHERE id = ?")
            ->execute([$_POST['episode_number'], $_POST['episode_title'], $_POST['upload_date'], $_POST['id']]);
    }
}

$items = $pdo->query("SELECT * FROM content_items ORDER BY type, name")->fetchAll();
$episodes = $pdo->query("SELECT e.*, c.name as item_name, c.type FROM episodes e JOIN content_items c ON e.content_item_id = c.id ORDER BY e.upload_date DESC, e.id DESC")->fetchAll();

function getTypeColor($type) {
    return ($type == 'News') ? 'bg-warning text-dark' : (($type == 'Teledrama') ? 'bg-primary' : 'bg-success');
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid px-4">
    <h3 class="mb-4 text-primary">Content Management</h3>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title text-dark">System Items</h5>
                    <form method="POST" class="mb-3">
                        <div class="input-group mb-2">
                            <input type="text" name="name" class="form-control" placeholder="New Item Name" required>
                            <select name="type" class="form-select" style="max-width: 100px;">
                                <option value="Teledrama">Teledrama</option>
                                <option value="Program">Program</option>
                                <option value="News">News</option>
                            </select>
                        </div>
                        <button type="submit" name="add_content" class="btn btn-outline-primary w-100">Add Item</button>
                    </form>
                    <table class="table table-sm align-middle">
                        <?php foreach($items as $i): ?>
                        <tr>
                            <td>
                                <div id="view_c_<?php echo $i['id']; ?>">
                                    <strong><?php echo htmlspecialchars($i['name']); ?></strong><br>
                                    <span class="badge <?php echo getTypeColor($i['type']); ?>"><?php echo $i['type']; ?></span>
                                </div>
                                <form method="POST" id="edit_c_<?php echo $i['id']; ?>" style="display:none;" class="row g-1">
                                    <input type="hidden" name="id" value="<?php echo $i['id']; ?>">
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($i['name']); ?>" class="form-control form-control-sm mb-1" required>
                                    <select name="type" class="form-select form-select-sm mb-1">
                                        <option value="Teledrama" <?php if($i['type']=='Teledrama') echo 'selected'; ?>>Teledrama</option>
                                        <option value="Program" <?php if($i['type']=='Program') echo 'selected'; ?>>Program</option>
                                        <option value="News" <?php if($i['type']=='News') echo 'selected'; ?>>News</option>
                                    </select>
                                    <button type="submit" name="edit_content" class="btn btn-sm btn-success w-100">Save</button>
                                </form>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('view_c_<?php echo $i['id']; ?>').style.display='none'; document.getElementById('edit_c_<?php echo $i['id']; ?>').style.display='block';">Edit</button>
                                <form method="POST" onsubmit="return confirm('Delete?');" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo $i['id']; ?>">
                                    <button type="submit" name="delete_content" class="btn btn-sm btn-outline-danger">Del</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title text-dark">Log Episode</h5>
                    <form method="POST" class="row g-2">
                        <div class="col-md-4">
                            <select name="content_item_id" class="form-select" required>
                                <option value="">Select Item...</option>
                                <?php foreach($items as $i): ?>
                                    <option value="<?php echo $i['id']; ?>"><?php echo htmlspecialchars($i['type'] . ": " . $i['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2"><input type="number" name="episode_number" class="form-control" placeholder="Ep #"></div>
                        <div class="col-md-3"><input type="text" name="episode_title" class="form-control" placeholder="Title/Topic"></div>
                        <div class="col-md-3"><input type="date" name="upload_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                        <div class="col-12"><button type="submit" name="add_episode" class="btn btn-primary w-100">Log Entry</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title mb-3">Content Log History</h5>
            <table class="table table-hover align-middle">
                <thead class="table-light"><tr><th>Type</th><th>Name</th><th>Ep #</th><th>Title</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    <?php foreach($episodes as $e): ?>
                    <tr>
                        <td><span class="badge <?php echo getTypeColor($e['type']); ?>"><?php echo htmlspecialchars($e['type']); ?></span></td>
                        <td>
                            <div id="view_e_<?php echo $e['id']; ?>"><strong><?php echo htmlspecialchars($e['item_name']); ?></strong></div>
                            <form method="POST" id="edit_e_<?php echo $e['id']; ?>" style="display:none;" class="row g-1">
                                <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                                <input type="number" name="episode_number" value="<?php echo $e['episode_number']; ?>" class="form-control form-control-sm mb-1" placeholder="Ep #">
                                <input type="text" name="episode_title" value="<?php echo htmlspecialchars($e['episode_title']); ?>" class="form-control form-control-sm mb-1">
                                <input type="date" name="upload_date" value="<?php echo $e['upload_date']; ?>" class="form-control form-control-sm mb-1">
                                <button type="submit" name="edit_episode" class="btn btn-sm btn-success w-100">Save</button>
                            </form>
                        </td>
                        <td><?php echo $e['episode_number'] > 0 ? (int)$e['episode_number'] : '-'; ?></td>
                        <td><?php echo htmlspecialchars($e['episode_title']); ?></td>
                        <td><?php echo htmlspecialchars($e['upload_date']); ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('view_e_<?php echo $e['id']; ?>').style.display='none'; document.getElementById('edit_e_<?php echo $e['id']; ?>').style.display='block';">Edit</button>
                            <form method="POST" onsubmit="return confirm('Delete this entry?');" class="d-inline">
                                <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                                <button type="submit" name="delete_episode" class="btn btn-sm btn-outline-danger">Del</button>
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