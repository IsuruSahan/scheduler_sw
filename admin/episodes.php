<?php 
require_once '../config/config.php';
session_start();
if ($_SESSION['role'] !== 'Admin') { header("Location: " . BASE_URL . "index.php"); exit(); }

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['content_item_id'])) {
        // Allow optional fields for News; default to 0 or 'N/A' if empty
        $ep_num = !empty($_POST['episode_number']) ? $_POST['episode_number'] : 0;
        $ep_title = !empty($_POST['episode_title']) ? $_POST['episode_title'] : 'N/A';
        
        $stmt = $pdo->prepare("INSERT INTO episodes (content_item_id, episode_number, episode_title, upload_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['content_item_id'], $ep_num, $ep_title, $_POST['upload_date']]);
    }
}

// Fetch items for the dropdown
$items = $pdo->query("SELECT * FROM content_items ORDER BY type, name")->fetchAll();

// Fetch episodes to display
$episodes = $pdo->query("SELECT e.*, c.name as item_name, c.type 
                         FROM episodes e 
                         JOIN content_items c ON e.content_item_id = c.id 
                         ORDER BY e.upload_date DESC")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="card p-4 shadow-sm">
    <h3 class="mb-4">Manage Content Entries</h3>
    <p class="text-muted small">Select your show/news, provide details, and the date.</p>
    
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-3">
            <select name="content_item_id" class="form-control" required>
                <option value="">Select Show/Program/News</option>
                <?php foreach($items as $i): ?>
                    <option value="<?php echo $i['id']; ?>"><?php echo htmlspecialchars($i['type'] . ": " . $i['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-1">
            <input type="number" name="episode_number" class="form-control" placeholder="Ep #">
        </div>
        <div class="col-md-4">
            <input type="text" name="episode_title" class="form-control" placeholder="Title/Topic">
        </div>
        <div class="col-md-2">
            <input type="date" name="upload_date" class="form-control" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Add Entry</button>
        </div>
    </form>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Type</th><th>Show Name</th><th>Ep #</th><th>Title</th><th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($episodes as $e): ?>
                <tr>
                    <td><span class="badge <?php echo ($e['type'] == 'News') ? 'bg-warning text-dark' : 'bg-secondary'; ?>">
                        <?php echo htmlspecialchars($e['type']); ?>
                    </span></td>
                    <td><?php echo htmlspecialchars($e['item_name']); ?></td>
                    <td><?php echo $e['episode_number'] > 0 ? (int)$e['episode_number'] : '-'; ?></td>
                    <td><?php echo htmlspecialchars($e['episode_title']); ?></td>
                    <td><?php echo htmlspecialchars($e['upload_date']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>