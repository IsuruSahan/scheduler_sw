<?php 
require_once __DIR__ . '/../config/config.php';
include '../includes/header.php'; 

$stmt = $pdo->query("SELECT * FROM media_library ORDER BY reference_no ASC, uploaded_at DESC");
$assets = $stmt->fetchAll();

$grouped = [];
foreach ($assets as $a) {
    $name = $a['schedule_name'];
    if (!isset($grouped[$name])) {
        $grouped[$name] = [
            'refs' => [], 
            'media' => [],
            'docs' => []
        ];
    }
    
    // Store unique reference numbers for the table badge
    if (!in_array($a['reference_no'], $grouped[$name]['refs'])) {
        $grouped[$name]['refs'][] = $a['reference_no'];
    }
    
    // Collect all files
    if ($a['file_type'] === 'media') {
        $grouped[$name]['media'][] = $a;
    } else {
        $grouped[$name]['docs'][] = $a;
    }
}
?>

<div class="container-fluid px-4">
    <h3 class="mb-4">Schedule Assets Overview</h3>
    <div class="card shadow-sm border-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Schedule Name</th>
   
                    <th>Media</th>
                    <th>Supporting Docs</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grouped as $name => $row): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($name); ?></strong></td>
 
                    <td>
                        <button class="btn btn-sm btn-primary" onclick='showModal(<?php echo json_encode($row['media']); ?>, "Media Assets")'>
                            View All Media (<?php echo count($row['media']); ?>)
                        </button>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-info text-white" onclick='showModal(<?php echo json_encode($row['docs']); ?>, "Supporting Docs")'>
                            View All Docs (<?php echo count($row['docs']); ?>)
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="assetModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="mTitle"></h5></div>
            <div class="modal-body" id="mBody"></div>
        </div>
    </div>
</div>

<script>
function showModal(files, title) {
    document.getElementById('mTitle').innerText = title;
    const mBody = document.getElementById('mBody');
    
    mBody.innerHTML = `
        <table class="table table-sm">
            <thead><tr><th>Reference No</th><th>File Name</th></tr></thead>
            <tbody>
                ${files.map(f => `<tr>
                    <td><span class="badge bg-dark">${f.reference_no}</span></td>
                    <td><a href="../uploads/${f.file_path}" target="_blank">${f.file_path.split('/').pop()}</a></td>
                </tr>`).join('')}
            </tbody>
        </table>
    `;
    new bootstrap.Modal(document.getElementById('assetModal')).show();
}
</script>
<?php include '../includes/footer.php'; ?>