<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$id = $_GET['id'] ?? 0;

// 1. Fetch Schedule Details
$stmt = $pdo->prepare("
    SELECT s.*, a.agency_name, c.client_name 
    FROM schedules s 
    JOIN agencies a ON s.agency_id = a.id 
    JOIN clients c ON s.client_id = c.id 
    WHERE s.id = ?
");
$stmt->execute([$id]);
$schedule = $stmt->fetch();

if (!$schedule) {
    header("Location: manage.php");
    exit();
}

// 2. Fetch Schedule Items with Media and Format details
$items_stmt = $pdo->prepare("
    SELECT si.*, ci.name AS content_name, p.platform_name, ap.placement_name, mf.format_name,
           rc.media_format_id, si.placement_id,
           GROUP_CONCAT(ma.file_path SEPARATOR '|') as file_paths
    FROM schedule_items si 
    JOIN content_items ci ON si.content_item_id = ci.id 
    JOIN platforms p ON si.platform_id = p.id 
    JOIN ad_placements ap ON si.placement_id = ap.id 
    JOIN rate_cards rc ON si.content_item_id = rc.content_item_id 
          AND si.platform_id = rc.platform_id 
          AND si.placement_id = rc.placement_id
    JOIN media_formats mf ON rc.media_format_id = mf.id
    LEFT JOIN media_attachments ma ON si.id = ma.schedule_item_id
    WHERE si.schedule_id = ?
    GROUP BY si.id
");
$items_stmt->execute([$id]);
$items = $items_stmt->fetchAll();

$statusClass = ($schedule['status'] == 'Active') ? 'bg-success' : (($schedule['status'] == 'Stopped') ? 'bg-secondary' : 'bg-warning text-dark');
?>

<?php include '../includes/header.php'; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mx-4" role="alert">
        <strong>Error:</strong> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="text-primary"><?php echo htmlspecialchars($schedule['schedule_name']); ?></h3>
            <span class="text-muted">Ref No: <?php echo htmlspecialchars($schedule['reference_no']); ?></span>
            <span class="badge <?php echo $statusClass; ?> ms-2"><?php echo htmlspecialchars($schedule['status']); ?></span>
        </div>
        <div>
            <?php if ($schedule['status'] === 'Active'): ?>
                <button class="btn btn-warning" onclick="stopSchedule(<?php echo $id; ?>)">Stop Schedule</button>
            <?php endif; ?>
            <a href="manage.php" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card p-3 shadow-sm border-0 h-100"><strong>Agency:</strong><br><?php echo htmlspecialchars($schedule['agency_name']); ?></div></div>
        <div class="col-md-3"><div class="card p-3 shadow-sm border-0"><strong>Client:</strong><br><?php echo htmlspecialchars($schedule['client_name']); ?></div></div>
        <div class="col-md-3"><div class="card p-3 shadow-sm border-0"><strong>Period:</strong><br><?php echo htmlspecialchars($schedule['start_date']) . ' to ' . htmlspecialchars($schedule['end_date']); ?></div></div>
        <div class="col-md-3"><div class="card p-3 shadow-sm border-0"><strong>Allocated Budget:</strong><br>Rs. <?php echo number_format($schedule['budget_allocated'], 2); ?></div></div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3"><strong>Detailed Content Breakdown</strong></div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Program</th>
                        <th>Platform / Placement</th>
                        <th>Format</th>
                        <th>Qty</th>
                        <th>Cost</th>
                        <th>Media</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($item['content_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($item['platform_name'] . ' / ' . $item['placement_name']); ?></td>
                        <td><span class="badge bg-info"><?php echo htmlspecialchars($item['format_name']); ?></span></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>Rs. <?php echo number_format($item['cost'], 2); ?></td>
                        <td>
                            <?php if ($item['file_paths']): 
                                $paths = explode('|', $item['file_paths']);
                                foreach ($paths as $path): ?>
                                    <a href="<?php echo htmlspecialchars($path); ?>" target="_blank" class="btn btn-sm btn-outline-info me-1">View</a>
                            <?php endforeach; else: echo '<small class="text-muted">None</small>'; endif; ?>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="openEditModal(<?php echo $item['id']; ?>, <?php echo $id; ?>, <?php echo $item['content_item_id']; ?>, <?php echo $item['platform_id']; ?>, <?php echo $item['placement_id']; ?>, <?php echo $item['media_format_id']; ?>, <?php echo $item['quantity']; ?>)">
                                Edit
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="update_item.php" method="POST" class="modal-content">
            <input type="hidden" name="item_id" id="edit_item_id">
            <input type="hidden" name="schedule_id" id="edit_schedule_id">
            <div class="modal-header"><h5 class="modal-title">Edit Program, Format & Qty</h5></div>
            <div class="modal-body">
                <label class="form-label">Program:</label>
                <select name="content_id" id="edit_content_id" class="form-select mb-2">
                    <?php foreach($pdo->query("SELECT id, name FROM content_items") as $p) echo "<option value='{$p['id']}'>{$p['name']}</option>"; ?>
                </select>
                
                <label class="form-label">Platform:</label>
                <select name="platform_id" id="edit_platform_id" class="form-select mb-2">
                    <?php foreach($pdo->query("SELECT id, platform_name FROM platforms") as $pl) echo "<option value='{$pl['id']}'>{$pl['platform_name']}</option>"; ?>
                </select>

                <label class="form-label">Placement:</label>
                <select name="placement_id" id="edit_placement_id" class="form-select mb-2">
                    <?php foreach($pdo->query("SELECT id, placement_name FROM ad_placements") as $ap) echo "<option value='{$ap['id']}'>{$ap['placement_name']}</option>"; ?>
                </select>

                <label class="form-label">Format:</label>
                <select name="format_id" id="edit_format_id" class="form-select mb-2">
                    <?php foreach($pdo->query("SELECT id, format_name FROM media_formats") as $f) echo "<option value='{$f['id']}'>{$f['format_name']}</option>"; ?>
                </select>

                <label class="form-label">Quantity:</label>
                <input type="number" name="quantity" id="edit_quantity" class="form-control" required>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Changes</button></div>
        </form>
    </div>
</div>

<script>
function stopSchedule(id) {
    if (!confirm('Are you sure you want to stop this schedule? This will release the allocated inventory.')) return;
    fetch('manage.php?action=stop&id=' + id).then(r => r.json()).then(data => {
        if (data.status === 'success') location.reload(); else alert('Error: ' + data.message);
    });
}
function openEditModal(itemId, scheduleId, contentId, platformId, placementId, formatId, qty) {
    document.getElementById('edit_item_id').value = itemId;
    document.getElementById('edit_schedule_id').value = scheduleId;
    document.getElementById('edit_content_id').value = contentId;
    document.getElementById('edit_platform_id').value = platformId;
    document.getElementById('edit_placement_id').value = placementId;
    document.getElementById('edit_format_id').value = formatId;
    document.getElementById('edit_quantity').value = qty;
    new bootstrap.Modal(document.getElementById('editItemModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>