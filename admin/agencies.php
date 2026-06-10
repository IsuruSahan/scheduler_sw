<?php 
require_once '../config/config.php';
session_start();
if ($_SESSION['role'] !== 'Admin') { header("Location: " . BASE_URL . "index.php"); exit(); }

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ADD
    if (isset($_POST['add_agency'])) {
        $pdo->prepare("INSERT INTO agencies (agency_name, client_name) VALUES (?, ?)")
            ->execute([$_POST['agency_name'], $_POST['client_name']]);
    }
    // DELETE
    elseif (isset($_POST['delete_agency'])) {
        $pdo->prepare("DELETE FROM agencies WHERE id = ?")->execute([$_POST['id']]);
    }
    // EDIT
    elseif (isset($_POST['edit_agency'])) {
        $pdo->prepare("UPDATE agencies SET agency_name = ?, client_name = ? WHERE id = ?")
            ->execute([$_POST['agency_name'], $_POST['client_name'], $_POST['id']]);
    }
}

$agencies = $pdo->query("SELECT * FROM agencies ORDER BY agency_name ASC")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid px-4">
    <h3 class="mb-4 text-primary">Manage Agencies & Clients</h3>
    
    <div class="card shadow-sm border-0 p-4 mb-4">
        <form method="POST" class="row g-2">
            <div class="col-md-5">
                <input type="text" name="agency_name" class="form-control" placeholder="Agency Name" required>
            </div>
            <div class="col-md-5">
                <input type="text" name="client_name" class="form-control" placeholder="Client Name" required>
            </div>
            <div class="col-md-2">
                <button type="submit" name="add_agency" class="btn btn-primary w-100">Add Agency</button>
            </div>
        </form>
    </div>

    <div class="card shadow-sm border-0 p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th>Agency Name</th><th>Client Name</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($agencies as $a): ?>
                    <tr>
                        <td>
                            <div id="view_<?php echo $a['id']; ?>">
                                <?php echo htmlspecialchars($a['agency_name']); ?>
                            </div>
                            <form method="POST" id="edit_form_<?php echo $a['id']; ?>" style="display:none;" class="row g-1">
                                <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                <input type="text" name="agency_name" value="<?php echo htmlspecialchars($a['agency_name']); ?>" class="form-control form-control-sm mb-1" required>
                                <input type="text" name="client_name" value="<?php echo htmlspecialchars($a['client_name']); ?>" class="form-control form-control-sm mb-1" required>
                                <button type="submit" name="edit_agency" class="btn btn-sm btn-success w-100">Save Changes</button>
                            </form>
                        </td>
                        <td>
                            <div id="client_view_<?php echo $a['id']; ?>">
                                <?php echo htmlspecialchars($a['client_name']); ?>
                            </div>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('view_<?php echo $a['id']; ?>').style.display='none'; document.getElementById('client_view_<?php echo $a['id']; ?>').style.display='none'; document.getElementById('edit_form_<?php echo $a['id']; ?>').style.display='block'; this.style.display='none';">Edit</button>
                            <form method="POST" onsubmit="return confirm('Delete this agency?');" class="d-inline">
                                <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                <button type="submit" name="delete_agency" class="btn btn-sm btn-outline-danger">Delete</button>
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