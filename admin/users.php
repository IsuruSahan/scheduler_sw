<?php 
require_once '../config/config.php';
session_start();
// Security check: Only Admin (Role 1) allowed
if ($_SESSION['role_id'] !== 1) { header("Location: " . BASE_URL . "index.php"); exit(); }

// Handle AJAX actions (Add/Delete)
if (isset($_POST['ajax_action'])) {
    if ($_POST['ajax_action'] == 'add') {
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)")
            ->execute([$_POST['name'], $_POST['email'], $pass, $_POST['role_id']]);
    } elseif ($_POST['ajax_action'] == 'delete') {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$_POST['id']]);
    }
    exit();
}

$users = $pdo->query("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id")->fetchAll();
$roles = $pdo->query("SELECT * FROM roles")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid px-4">
    <h3 class="mb-4 text-primary">Manage System Users</h3>
    
    <div class="card shadow-sm border-0 p-4 mb-4">
        <form onsubmit="handleUserAction(event, 'add')">
            <div class="row g-2">
                <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Full Name" required></div>
                <div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                <div class="col-md-2"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                <div class="col-md-2">
                    <select name="role_id" class="form-select">
                        <?php foreach($roles as $r): ?>
                            <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['role_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Create User</button></div>
            </div>
        </form>
    </div>

    <div class="card shadow-sm border-0 p-4">
        <table class="table table-hover align-middle">
            <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Role</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['name']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($u['role_name']); ?></span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?php echo $u['id']; ?>)">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function handleUserAction(e, action) {
    e.preventDefault();
    let formData = new FormData(e.target);
    formData.append('ajax_action', action);
    fetch('users.php', { method: 'POST', body: formData })
    .then(() => location.reload());
}

function deleteUser(id) {
    if(!confirm('Delete this user?')) return;
    let formData = new FormData();
    formData.append('ajax_action', 'delete');
    formData.append('id', id);
    fetch('users.php', { method: 'POST', body: formData })
    .then(() => location.reload());
}
</script>

<?php include '../includes/footer.php'; ?>