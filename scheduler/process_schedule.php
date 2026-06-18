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
        $total_calculated_cost += ($rate * $_POST['quantity'][$idx]);
    }

    // Determine status based on budget
    $status = ($_POST['budget'] < $total_calculated_cost || $_POST['action'] === 'approve') ? 'Pending Approval' : 'Active';

    // 3. Insert Schedule Header
    $stmt = $pdo->prepare("
        INSERT INTO schedules (agency_id, client_id, schedule_name, reference_no, assigned_team, budget_allocated, start_date, end_date, created_by, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_POST['agency_id'], $_POST['client_id'], $_POST['schedule_name'], $_POST['reference_no'],
        $_POST['assigned_team'], $_POST['budget'], $_POST['start_date'], $_POST['end_date'], $_SESSION['user_id'], $status
    ]);
    $schedule_id = $pdo->lastInsertId();

    // 4. Loop through rows to insert items
    foreach ($_POST['row_ids'] as $idx => $rowId) {
        $content_item_id = $_POST['content_item_id'][$idx]; 
        $qty = filter_var($_POST['quantity'][$idx], FILTER_VALIDATE_INT);
        $format_id = $_POST['media_format_id'][$idx];
        
        if ($qty === false || $qty < 1) throw new Exception("Invalid quantity for row " . ($idx + 1));

        $stmt = $pdo->prepare("
            SELECT r.id as rate_card_id, r.rate, i.total_capacity, i.used_qty 
            FROM rate_cards r
            LEFT JOIN inventory i ON r.id = i.rate_card_id
            WHERE r.platform_id = ? AND r.placement_id = ? AND r.content_item_id = ? AND r.media_format_id = ?
        ");
        $stmt->execute([$_POST['platform_id'][$idx], $_POST['placement_id'][$idx], $content_item_id, $format_id]);
        $data = $stmt->fetch();

        $rate = $data['rate'] ?? 0;
        $available_qty = ($data['total_capacity'] ?? 0) - ($data['used_qty'] ?? 0);
        $rate_card_id = $data['rate_card_id'] ?? 0;
        
        if ($qty > $available_qty) {
            throw new Exception("Validation Error: Row " . ($idx + 1) . " exceeds available balance.");
        }

        $row_cost = $rate * $qty;
        $stmt = $pdo->prepare("INSERT INTO schedule_items (schedule_id, content_item_id, platform_id, placement_id, quantity, cost) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$schedule_id, $content_item_id, $_POST['platform_id'][$idx], $_POST['placement_id'][$idx], $qty, $row_cost]);
        $item_id = $pdo->lastInsertId();

        $pdo->prepare("UPDATE inventory SET used_qty = used_qty + ? WHERE rate_card_id = ?")->execute([$qty, $rate_card_id]);

        // 5. Handle File Uploads
        $mode = $_POST['mode'];
        $filesToProcess = [];

        if (isset($_FILES['media']['error'])) {
            $errors = ($mode === 'sync') ? $_FILES['media']['error']['sync'] : $_FILES['media']['error'][$rowId] ?? [];
            foreach((array)$errors as $err) {
                if ($err !== UPLOAD_ERR_OK && $err !== UPLOAD_ERR_NO_FILE) throw new Exception("PHP Upload Error Code: " . $err);
            }
        }

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
    echo json_encode(['status' => 'success', 'message' => ($status === 'Pending Approval' ? 'Schedule created and sent for approval!' : 'Schedule created successfully!')]);
    exit();

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    if (ob_get_length()) ob_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit();
}