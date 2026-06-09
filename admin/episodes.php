<?php 
require_once '../config/config.php';
session_start();
if ($_SESSION['role'] !== 'Admin') { header("Location: " . BASE_URL . "index.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_content'])) {
        $stmt = $pdo->prepare("INSERT INTO content_items (name, type) VALUES (?, ?)");
        $stmt->execute([$_POST['name'], $_POST['type']]);
    } elseif (isset($_POST['add_episode'])) {
        $ep_num = !empty($_POST['episode_number']) ? $_POST['episode_number'] : 0;
        $ep_title = !empty($_POST['episode_title']) ? $_POST['episode_title'] : 'N/A';
        $stmt = $pdo->prepare("INSERT INTO episodes (content_item_id, episode_number, episode_title, upload_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['content_item_id'], $ep_num, $ep_title, $_POST['upload_date']]);
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
                    <ul class="list-group list-group-flush border-top">
                        <?php foreach($items as $i): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center small">
                                <?php echo htmlspecialchars($i['name']); ?>
                                <span class="badge <?php echo getTypeColor($i['type']); ?>"><?php echo $i['type']; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title text-dark">Log Episode / Daily Entry</h5>
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
                <thead class="table-light">
                    <tr><th>Type</th><th>Name</th><th>Ep #</th><th>Title / Topic</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php foreach($episodes as $e): ?>
                    <tr>
                        <td><span class="badge <?php echo getTypeColor($e['type']); ?>"><?php echo htmlspecialchars($e['type']); ?></span></td>
                        <td><strong class="text-dark"><?php echo htmlspecialchars($e['item_name']); ?></strong></td>
                        <td><?php echo $e['episode_number'] > 0 ? (int)$e['episode_number'] : '-'; ?></td>
                        <td><?php echo htmlspecialchars($e['episode_title']); ?></td>
                        <td><?php echo htmlspecialchars($e['upload_date']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>