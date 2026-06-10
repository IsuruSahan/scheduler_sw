<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$agencies = $pdo->query("SELECT * FROM agencies ORDER BY agency_name")->fetchAll();
$clients = $pdo->query("SELECT * FROM clients ORDER BY client_name")->fetchAll();

$episodes_log = $pdo->query("
    SELECT e.*, c.name as item_name, c.type 
    FROM episodes e 
    JOIN content_items c ON e.content_item_id = c.id 
    ORDER BY c.name, e.episode_number DESC
")->fetchAll();

$platforms = $pdo->query("SELECT * FROM platforms ORDER BY platform_name")->fetchAll();
$placements = $pdo->query("SELECT * FROM ad_placements ORDER BY placement_name")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid px-4">
    <h3 class="mb-4">Create New Schedule</h3>
    <form action="process_schedule.php" method="POST" enctype="multipart/form-data">
        <div class="card p-4 mb-4 shadow-sm border-0">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Agency</label>
                    <select name="agency_id" id="agency_id" class="form-select" onchange="updateClients()" required>
                        <option value="">Select Agency</option>
                        <?php foreach($agencies as $a): ?><option value="<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['agency_name']); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Client</label>
                    <select name="client_id" id="client_id" class="form-select" required><option value="">Select Agency First</option></select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Schedule Name</label>
                    <input type="text" name="schedule_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Total Budget (Rs.)</label>
                    <input type="number" name="budget" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
            </div>
            <div class="mt-4">
                <label class="fw-bold me-3">Mode:</label>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="mode" value="sync" id="syncMode" checked onclick="toggleMode('sync')"><label class="form-check-label" for="syncMode">Sync (Same for all)</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="mode" value="custom" id="customMode" onclick="toggleMode('custom')"><label class="form-check-label" for="customMode">Custom (Per-row)</label></div>
            </div>
        </div>

        <div id="sync-container" class="card p-3 mb-3 border-primary shadow-sm">
            <label class="fw-bold">Media for ALL platforms (Sync Mode):</label>
            <div id="sync-files">
                <div class="input-group mb-2"><input type="file" name="media[sync][]" class="form-control"><input type="text" name="ref[sync][]" class="form-control" placeholder="Reference"><button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">×</button></div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFileGroup('sync-files', 'sync')">+ Add File</button>
        </div>

        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr><th>Content (Grouped)</th><th>Platform</th><th>Placement</th><th class="custom-only" style="display:none;">Media & Reference</th><th>Action</th></tr>
            </thead>
            <tbody id="items-body"></tbody>
        </table>
        <button type="button" class="btn btn-primary mb-3" onclick="addRow()">+ Add Platform Row</button>
        <button type="submit" class="btn btn-success float-end">Create Schedule</button>
    </form>
</div>

<script>
const allClients = <?php echo json_encode($clients); ?>;
const allEpisodes = <?php echo json_encode($episodes_log); ?>;
let rowCount = 0;

function updateClients() {
    const agencyId = document.getElementById('agency_id').value;
    const clientSelect = document.getElementById('client_id');
    clientSelect.innerHTML = '<option value="">Select Client</option>';
    allClients.filter(c => c.agency_id == agencyId).forEach(c => {
        clientSelect.innerHTML += `<option value="${c.id}">${c.client_name}</option>`;
    });
}

function toggleMode(mode) {
    document.getElementById('sync-container').style.display = (mode === 'sync') ? 'block' : 'none';
    document.querySelectorAll('.custom-only').forEach(el => el.style.display = (mode === 'custom') ? 'table-cell' : 'none');
}

// Global function to add file inputs
window.addFileGroup = function(containerId, index) {
    const container = document.getElementById(containerId);
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="file" name="media[${index}][]" class="form-control">
        <input type="text" name="ref[${index}][]" class="form-control" placeholder="Reference">
        <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">×</button>
    `;
    container.appendChild(div);
};

function addRow() {
    const mode = document.querySelector('input[name="mode"]:checked').value;
    const tbody = document.getElementById('items-body');
    const row = document.createElement('tr');

    let optionsHtml = '';
    let lastItem = null;

    allEpisodes.forEach(ep => {
        if (ep.item_name !== lastItem) {
            if (lastItem !== null) optionsHtml += '</optgroup>';
            optionsHtml += `<optgroup label="${ep.item_name} (${ep.type})">`;
            lastItem = ep.item_name;
        }
        optionsHtml += `<option value="${ep.id}">[${ep.upload_date}] Ep ${ep.episode_number}: ${ep.episode_title}</option>`;
    });
    if (lastItem !== null) optionsHtml += '</optgroup>';

    row.innerHTML = `
        <td><select name="episode_id[]" class="form-select">${optionsHtml}</select></td>
        <td><select name="platform_id[]" class="form-select"><?php foreach($platforms as $p): ?><option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['platform_name']); ?></option><?php endforeach; ?></select></td>
        <td><select name="placement_id[]" class="form-select"><?php foreach($placements as $pl): ?><option value="<?php echo $pl['id']; ?>"><?php echo htmlspecialchars($pl['placement_name']); ?></option><?php endforeach; ?></select></td>
        <td class="custom-only" style="display: ${mode === 'custom' ? 'table-cell' : 'none'}">
            <div id="row_media_${rowCount}">
                <div class="input-group mb-2">
                    <input type="file" name="media[${rowCount}][]" class="form-control">
                    <input type="text" name="ref[${rowCount}][]" class="form-control" placeholder="Reference">
                    <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">×</button>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFileGroup('row_media_${rowCount}', ${rowCount})">+ Add File</button>
        </td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">Remove</button></td>
    `;
    tbody.appendChild(row);
    rowCount++;
}
</script>
<?php include '../includes/footer.php'; ?>