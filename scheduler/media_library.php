<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// 1. Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT file_path FROM media_library WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $file = $stmt->fetch();
    
    if ($file && file_exists($file['file_path'])) {
        unlink($file['file_path']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM media_library WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: media_library.php");
    exit();
}

// 2. Handle File Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $target_dir = "../uploads/media/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    
    $file_path = $target_dir . time() . "_" . basename($_FILES["media_file"]["name"]);
    
    if (move_uploaded_file($_FILES["media_file"]["tmp_name"], $file_path)) {
        // We explicitly pass NULL for reference_no and other unused fields
        $stmt = $pdo->prepare("
            INSERT INTO media_library 
            (reference_no, schedule_name, file_path, file_type, description, agency_name, client_name) 
            VALUES (NULL, ?, ?, 'media', ?, NULL, NULL)
        ");
        $stmt->execute([
            $_POST['schedule_name'] ?? null,
            $file_path,
            $_POST['description'] ?? null
        ]);
        header("Location: media_library.php");
        exit();
    }
}

// 3. Fetch Paginated Data
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_records = $pdo->query("SELECT COUNT(*) FROM media_library")->fetchColumn();
$total_pages = ceil($total_records / $limit);

$stmt = $pdo->prepare("SELECT * FROM media_library ORDER BY uploaded_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$media_list = $stmt->fetchAll();

include '../includes/header.php'; 
?>

<div class="container-fluid px-4">
    <h2 class="mt-4">Media Library</h2>
    
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-2">
                    <div class="col-md-4"><input type="file" name="media_file" class="form-control" required></div>
                    <div class="col-md-3"><input type="text" name="schedule_name" placeholder="Schedule Name" class="form-control"></div>
                    <div class="col-md-3"><input type="text" name="description" placeholder="Description" class="form-control"></div>
                    <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Upload Media</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>File</th><th>Schedule</th><th>Description</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($media_list as $m): ?>
                <tr>
                    <td><a href="<?php echo htmlspecialchars($m['file_path']); ?>" target="_blank"><?php echo htmlspecialchars(basename($m['file_path'])); ?></a></td>
                    <td><?php echo htmlspecialchars($m['schedule_name'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($m['description'] ?? '-'); ?></td>
                    <td><a href="?action=delete&id=<?php echo $m['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this file?')">Delete</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <nav class="mt-3">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php include '../includes/footer.php'; ?>