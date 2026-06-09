<?php 
require_once '../config/config.php';
session_start();
// Security check: Only Admin can access
if ($_SESSION['role'] !== 'Admin') { header("Location: " . BASE_URL . "index.php"); exit(); }

// Logic to add a new agency
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("INSERT INTO agencies (agency_name, client_name) VALUES (?, ?)");
    $stmt->execute([$_POST['agency_name'], $_POST['client_name']]);
}

// Fetch all for display
$agencies = $pdo->query("SELECT * FROM agencies")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="card p-4">
    <h3>Manage Agencies & Clients</h3>
    <form method="POST" class="row mb-4">
        <div class="col-md-5">
            <input type="text" name="agency_name" class="form-control" placeholder="Agency Name" required>
        </div>
        <div class="col-md-5">
            <input type="text" name="client_name" class="form-control" placeholder="Client Name" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Add</button>
        </div>
    </form>

    <table class="table table-hover">
        <thead><tr><th>Agency</th><th>Client</th></tr></thead>
        <tbody>
            <?php foreach ($agencies as $a): ?>
            <tr><td><?php echo $a['agency_name']; ?></td><td><?php echo $a['client_name']; ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>