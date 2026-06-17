<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

try {
    $pdo->beginTransaction();

    // Ensure uploads directory exists
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // 1. Insert Schedule Header
    $stmt = $pdo->prepare("
        INSERT INTO schedules (agency_id, client_id, schedule_name, reference_no, assigned_team, budget_allocated, start_date, end_date, created_by, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $status = ($_POST['action'] === 'approve') ? 'Pending Approval' : 'Active';
    
    $stmt->execute([
        $_POST['agency_id'], $_POST['client_id'], $_POST['schedule_name'], $_POST['reference_no'],
        $_POST['assigned_team'], $_POST['budget'], $_POST['start_date'], $_POST['end_date'], $_SESSION['user_id'], $status
    ]);
    $schedule_id = $pdo->lastInsertId();

    // 2. Loop through rows
    foreach ($_POST['row_ids'] as $idx => $rowId) {
        $content_item_id = $_POST['content_item_id'][$idx]; 
        $qty = filter_var($_POST['quantity'][$idx], FILTER_VALIDATE_INT);
        $format_id = $_POST['media_format_id'][$idx];
        
        if ($qty === false || $qty < 1) throw new Exception("Invalid quantity for row " . ($idx + 1));

        // Fetch Rate AND Inventory
        $stmt = $pdo->prepare("
            SELECT r.id as rate_card_id, r.rate, i.total_capacity, i.used_qty 
            FROM rate_cards r
            LEFT JOIN inventory i ON r.id = i.rate_card_id
            WHERE r.platform_id = ? AND r.placement_id = ? AND r.content_item_id = ? AND r.media_format_id = ?
        ");
        $stmt->execute([
            $_POST['platform_id'][$idx], 
            $_POST['placement_id'][$idx], 
            $content_item_id, 
            $format_id
        ]);
        $data = $stmt->fetch();

        $rate = $data['rate'] ?? 0;
        $available_qty = ($data['total_capacity'] ?? 0) - ($data['used_qty'] ?? 0);
        $rate_card_id = $data['rate_card_id'] ?? 0;
        
        // Validate against Inventory
        if ($qty > $available_qty) {
            throw new Exception("Validation Error: Row " . ($idx + 1) . " exceeds available balance of " . $available_qty);
        }

        $row_cost = $rate * $qty;

        // Insert Schedule Item
        $stmt = $pdo->prepare("INSERT INTO schedule_items (schedule_id, content_item_id, platform_id, placement_id, quantity, cost) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$schedule_id, $content_item_id, $_POST['platform_id'][$idx], $_POST['placement_id'][$idx], $qty, $row_cost]);
        $item_id = $pdo->lastInsertId();

        // Update Inventory
        $stmt = $pdo->prepare("UPDATE inventory SET used_qty = used_qty + ? WHERE rate_card_id = ?");
        $stmt->execute([$qty, $rate_card_id]);

        // 3. Handle File Uploads
        $mode = $_POST['mode'];
        $filesToProcess = [];

        if ($mode === 'sync') {
            if (!empty($_FILES['media']['name']['sync'])) {
                foreach ($_FILES['media']['name']['sync'] as $fIdx => $name) {
                    if ($name) $filesToProcess[] = ['name' => $name, 'tmp' => $_FILES['media']['tmp_name']['sync'][$fIdx], 'ref' => $_POST['ref']['sync'][$fIdx] ?? ''];
                }
            }
        } else {
            if (isset($_FILES['media']['name'][$rowId])) {
                foreach ($_FILES['media']['name'][$rowId] as $fIdx => $name) {
                    if ($name) $filesToProcess[] = ['name' => $name, 'tmp' => $_FILES['media']['tmp_name'][$rowId][$fIdx], 'ref' => $_POST['ref'][$rowId][$fIdx] ?? ''];
                }
            }
        }

        foreach ($filesToProcess as $file) {
            $target = $uploadDir . time() . '_' . basename($file['name']);
            if (move_uploaded_file($file['tmp'], $target)) {
                $stmt = $pdo->prepare("INSERT INTO media_attachments (schedule_item_id, file_path, file_reference) VALUES (?, ?, ?)");
                $stmt->execute([$item_id, $target, $file['ref']]);
            } else {
                throw new Exception("File upload failed for " . $file['name']);
            }
        }
    }

    // 4. Final Budget Validation
    $stmt = $pdo->prepare("SELECT SUM(cost) FROM schedule_items WHERE schedule_id = ?");
    $stmt->execute([$schedule_id]);
    $total_cost = $stmt->fetchColumn();

    if ($total_cost > $_POST['budget'] && $_POST['action'] === 'create') {
        throw new Exception("Budget Exceeded: Total (Rs. " . number_format($total_cost, 2) . ") exceeds budget.");
    }

    $pdo->commit();
    header("Location: dashboard.php?status=" . ($status === 'Active' ? 'success' : 'pending'));
    exit();

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    header("Location: create.php?error=" . urlencode($e->getMessage()));
    exit();
}