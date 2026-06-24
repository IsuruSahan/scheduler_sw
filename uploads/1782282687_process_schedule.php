<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Ensure uploads directory exists and is writable
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
    if (!is_writable($uploadDir)) { throw new Exception("Server Error: Upload directory is not writable."); }

    // 2. Pre-calculate total cost to determine status
    $total_calculated_cost = 0;
    foreach ($_POST['row_ids'] as $idx => $rowId) {
        $stmt = $pdo->prepare("SELECT rate FROM rate_cards WHERE content_item_id = ? AND platform_id = ? AND placement_id = ? AND media_format_id = ?");
        $stmt->execute([$_POST['content_item_id'][$idx], $_POST['platform_id'][$idx], $_POST['placement_id'][$idx], $_POST['media_format_id'][$idx]]);
        $rate = $stmt->fetchColumn() ?: 0;
        $total_calculated_cost += ($rate * (int)$_POST['quantity'][$idx]);
    }

    // Determine status (Budget exceeded OR manual approval trigger)
    $budget = floatval($_POST['budget']);
    $status = ($total_calculated_cost > $budget || $_POST['action'] === 'approve') ? 'Pending Approval' : 'Active';

    // 3. Handle Team selection (convert checkbox array to string)
    $assigned_teams = isset($_POST['assigned_team']) ? implode(', ', $_POST['assigned_team']) : 'Content Editor Team';

    // 4. Insert Schedule Header
    $stmt = $pdo->prepare("
        INSERT INTO schedules (agency_id, client_id, schedule_name, reference_no, assigned_team, budget_allocated, start_date, end_date, created_by, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_POST['agency_id'], $_POST['client_id'], $_POST['schedule_name'], $_POST['reference_no'],
        $assigned_teams, $budget, $_POST['start_date'], $_POST['end_date'], $_SESSION['user_id'], $status
    ]);
    $schedule_id = $pdo->lastInsertId();

    // 5. Loop through rows to insert items and update inventory
    foreach ($_POST['row_ids'] as $idx => $rowId) {
        $stmt = $pdo->prepare("
            SELECT r.id as rate_card_id, r.rate, i.total_capacity, i.used_qty 
            FROM rate_cards r
            LEFT JOIN inventory i ON r.id = i.rate_card_id
            WHERE r.platform_id = ? AND r.placement_id = ? AND r.content_item_id = ? AND r.media_format_id = ?
        ");
        $stmt->execute([$_POST['platform_id'][$idx], $_POST['placement_id'][$idx], $_POST['content_item_id'][$idx], $_POST['media_format_id'][$idx]]);
        $data = $stmt->fetch();

        if (!$data) throw new Exception("Rate card config missing for row " . ($idx + 1));
        
        $qty = (int)$_POST['quantity'][$idx];
        if ($qty > (($data['total_capacity'] ?? 0) - ($data['used_qty'] ?? 0))) {
            throw new Exception("Row " . ($idx + 1) . " exceeds available inventory.");
        }

        $stmt = $pdo->prepare("INSERT INTO schedule_items (schedule_id, content_item_id, platform_id, placement_id, quantity, cost) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$schedule_id, $_POST['content_item_id'][$idx], $_POST['platform_id'][$idx], $_POST['placement_id'][$idx], $qty, ($data['rate'] * $qty)]);
        $item_id = $pdo->lastInsertId();

        $pdo->prepare("UPDATE inventory SET used_qty = used_qty + ? WHERE rate_card_id = ?")->execute([$qty, $data['rate_card_id']]);
        
        // 6. Handle File Uploads
        $mode = $_POST['mode'];
        $filesToProcess = [];

        if (isset($_FILES['media']['error'])) {
            $errors = ($mode === 'sync') ? $_FILES['media']['error']['sync'] : $_FILES['media']['error'][$rowId] ?? [];
            foreach((array)$errors as $err) {
                if ($err !== UPLOAD_ERR_OK && $err !== UPLOAD_ERR_NO_FILE) throw new Exception("Upload Error: " . $err);
            }
        }

        if ($mode === 'sync' ? isset($_FILES['media']['name']['sync']) : isset($_FILES['media']['name'][$rowId])) {
            $key = ($mode === 'sync') ? 'sync' : $rowId;
            foreach ($_FILES['media']['name'][$key] as $i => $name) {
                if ($name) $filesToProcess[] = ['name' => $name, 'tmp' => $_FILES['media']['tmp_name'][$key][$i], 'ref' => $_POST['ref'][$key][$i] ?? ''];
            }
        }

        foreach ($filesToProcess as $file) {
            $target = $uploadDir . time() . '_' . preg_replace('/[^A-Za-z0-9.\-]/', '_', $file['name']);
            if (is_uploaded_file($file['tmp']) && move_uploaded_file($file['tmp'], $target)) {
                $pdo->prepare("INSERT INTO media_attachments (schedule_item_id, file_path, file_reference) VALUES (?, ?, ?)")
                    ->execute([$item_id, $target, $file['ref']]);
            } else {
                throw new Exception("File upload failed for " . $file['name']);
            }
        }
    }

    $pdo->commit();
    if (ob_get_length()) ob_clean();
    echo json_encode(['status' => 'success', 'message' => ($status === 'Pending Approval' ? 'Budget exceeded. Sent for approval.' : 'Schedule created successfully!')]);
    exit();

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    if (ob_get_length()) ob_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit();
}