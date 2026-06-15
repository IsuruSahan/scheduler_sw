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

    // 1. Insert Schedule Header (Added status column handling)
    // Note: Ensure your database table 'schedules' has a 'status' column
    $stmt = $pdo->prepare("
        INSERT INTO schedules (agency_id, client_id, schedule_name, reference_no, assigned_team, budget_allocated, start_date, end_date, created_by, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    // Default status to 'Active', if action is 'approve' it stays Pending
    $status = ($_POST['action'] === 'approve') ? 'Pending Approval' : 'Active';
    
    $stmt->execute([
        $_POST['agency_id'], $_POST['client_id'], $_POST['schedule_name'], $_POST['reference_no'],
        $_POST['assigned_team'], $_POST['budget'], $_POST['start_date'], $_POST['end_date'], $_SESSION['user_id'], $status
    ]);
    $schedule_id = $pdo->lastInsertId();

    // 2. Loop through rows
    foreach ($_POST['row_ids'] as $idx => $rowId) {
        $episode_id = $_POST['episode_id'][$idx];
        $qty = filter_var($_POST['quantity'][$idx], FILTER_VALIDATE_INT);
        
        if ($qty === false || $qty < 1) throw new Exception("Invalid quantity for row " . ($idx + 1));

        $stmt = $pdo->prepare("SELECT content_item_id FROM episodes WHERE id = ?");
        $stmt->execute([$episode_id]);
        $content_item_id = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT rate, max_quantity FROM rate_cards WHERE platform_id = ? AND placement_id = ? AND content_item_id = ?");
        $stmt->execute([$_POST['platform_id'][$idx], $_POST['placement_id'][$idx], $content_item_id]);
        $rateData = $stmt->fetch();
        
        $rate = $rateData['rate'] ?? 0;
        $max_qty = $rateData['max_quantity'] ?? 0;

        if ($qty > $max_qty) {
            throw new Exception("Validation Error: Row " . ($idx + 1) . " exceeds max quantity of " . $max_qty);
        }

        $row_cost = $rate * $qty;

        $stmt = $pdo->prepare("INSERT INTO schedule_items (schedule_id, episode_id, content_item_id, platform_id, placement_id, quantity, cost) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$schedule_id, $episode_id, $content_item_id, $_POST['platform_id'][$idx], $_POST['placement_id'][$idx], $qty, $row_cost]);
        $item_id = $pdo->lastInsertId();

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

    // If budget is exceeded and they tried to "create" (Active), block it.
    if ($total_cost > $_POST['budget'] && $_POST['action'] === 'create') {
        throw new Exception("Budget Exceeded: Total (Rs. " . number_format($total_cost, 2) . ") exceeds budget. Please send for approval instead.");
    }

    $pdo->commit();
    header("Location: dashboard.php?status=" . ($status === 'Active' ? 'success' : 'pending'));
    exit();

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    header("Location: create.php?error=" . urlencode($e->getMessage()));
    exit();
}