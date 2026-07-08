<?php 
require_once __DIR__ . '/../config/config.php';
include '../includes/header.php'; 

// Fetch all files. Ensure we select new metadata fields.
$stmt = $pdo->query("SELECT * FROM media_library ORDER BY reference_no ASC, uploaded_at DESC");
$assets = $stmt->fetchAll();

$grouped = [];
foreach ($assets as $a) {
    $name = $a['schedule_name'];
    if (!isset($grouped[$name])) {
        $grouped[$name] = [
            'refs' => [], 
            'media' => [],
            'docs' => [],
            'metadata' => $a // Keep a reference to one row to extract header metadata
        ];
    }
    
    // Store unique reference numbers for the table badge
    if (!in_array($a['reference_no'], $grouped[$name]['refs'])) {
        $grouped[$name]['refs'][] = $a['reference_no'];
    }
    
    // Collect files
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
                    <td>
                        <a href="javascript:void(0)" class="text-decoration-none fw-bold" 
                           onclick='showDetails(<?php echo json_encode($row['metadata']); ?>)'>
                           <?php echo htmlspecialchars($name); ?>
                        </a>
                    </td>

                    <td>
                        <button class="btn btn-sm btn-info" onclick='showModal(<?php echo json_encode($row['media']); ?>, "Media Assets")'>
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

<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="dTitle"></h5></div>
            <div class="modal-body" id="dBody"></div>
        </div>
    </div>
</div>

<script>
function showModal(files, title) {
    document.getElementById('mTitle').innerText = title;
    const mBody = document.getElementById('mBody');
    mBody.innerHTML = `
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr><th>Schedule Name</th><th>File Name</th><th>Description</th></tr>
            </thead>
            <tbody>
                ${files.length > 0 ? files.map(f => `<tr>
                    <td>${f.schedule_name}</td>
                    <td><a href="../uploads/${f.file_path}" target="_blank">${f.file_path.split('/').pop()}</a></td>
                    <td>${f.description || '-'}</td>
                </tr>`).join('') : '<tr><td colspan="3" class="text-center">No files found.</td></tr>'}
            </tbody>
        </table>
    `;
    new bootstrap.Modal(document.getElementById('assetModal')).show();
}

function showDetails(meta) {
    document.getElementById('dTitle').innerText = 'Details: ' + meta.schedule_name;
    document.getElementById('dBody').innerHTML = `
        <div class="row g-3">
            <div class="col-6"><strong>Agency:</strong> ${meta.agency_name || 'N/A'}</div>
            <div class="col-6"><strong>Client:</strong> ${meta.client_name || 'N/A'}</div>
            <div class="col-6"><strong>Start Date:</strong> ${meta.start_date || 'N/A'}</div>
            <div class="col-6"><strong>End Date:</strong> ${meta.end_date || 'N/A'}</div>
            <div class="col-12"><strong>Notes:</strong><p class="p-2 bg-light border">${meta.note || 'No notes provided.'}</p></div>
        </div>
    `;
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}
</script>
<?php include '../includes/footer.php'; ?>
