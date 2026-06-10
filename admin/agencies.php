<?php 
require_once '../config/config.php';
session_start();
if ($_SESSION['role'] !== 'Admin') { header("Location: " . BASE_URL . "index.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_agency'])) {
        $pdo->prepare("INSERT INTO agencies (agency_name) VALUES (?)")->execute([$_POST['agency_name']]);
    } elseif (isset($_POST['add_client'])) {
        $pdo->prepare("INSERT INTO clients (agency_id, client_name) VALUES (?, ?)")
            ->execute([$_POST['agency_id'], $_POST['client_name']]);
    } elseif (isset($_POST['delete_client'])) {
        $pdo->prepare("DELETE FROM clients WHERE id = ?")->execute([$_POST['id']]);
    }
}

$agencies = $pdo->query("SELECT * FROM agencies")->fetchAll();
$clients = $pdo->query("SELECT c.*, a.agency_name FROM clients c JOIN agencies a ON c.agency_id = a.id")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid px-4">
    <h3 class="mb-4 text-primary">Manage Agencies & Clients</h3>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0">
                <h5>Add New Agency</h5>
                <form method="POST"><div class="input-group"><input type="text" name="agency_name" class="form-control" placeholder="Agency Name" required><button name="add_agency" class="btn btn-primary">Add</button></div></form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card p-3 shadow-sm border-0">
                <h5>Add Client to Agency</h5>
                <form method="POST" class="row g-2">
                    <div class="col-md-5"><select name="agency_id" class="form-select" required><?php foreach($agencies as $a): ?><option value="<?php echo $a['id']; ?>"><?php echo $a['agency_name']; ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-5"><input type="text" name="client_name" class="form-control" placeholder="Client Name" required></div>
                    <div class="col-md-2"><button name="add_client" class="btn btn-success w-100">Add</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 p-4 mt-4">
        <table class="table table-hover">
            <thead class="table-light"><tr><th>Agency</th><th>Client Name</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($clients as $c): ?>
                <tr>
                    <td><span class="badge bg-dark"><?php echo htmlspecialchars($c['agency_name']); ?></span></td>
                    <td><?php echo htmlspecialchars($c['client_name']); ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Delete this client?');">
                            <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                            <button name="delete_client" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>