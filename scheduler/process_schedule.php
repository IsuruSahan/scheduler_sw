<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Ensure the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Insert Schedule Header
    // Capture assigned teams or default to 'Content Editor Team'
// 1. Collect and process the team string
$assigned_teams_array = $_POST['assigned_team'] ?? [];
$assigned_team_string = implode(', ', $assigned_teams_array); 

// 2. Corrected Query: Removed duplicate 'assigned_team' and added missing 'created_by' placeholder
$stmt = $pdo->prepare("
    INSERT INTO schedules 
    (agency_id, client_id, schedule_name, reference_no, assigned_team, budget_allocated, start_date, end_date, created_by, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $_POST['agency_id'], 
    $_POST['client_id'], 
    $_POST['schedule_name'], 
    $_POST['reference_no'],
    $assigned_team_string,
    floatval($_POST['budget_allocated']), 
    $_POST['start_date'], 
    $_POST['end_date'], 
    $_SESSION['user_id'],
    $_POST['status'] // Capture the status from JavaScript
]);
    
    $schedule_id = $pdo->lastInsertId();

    // 2. Loop through nested schedule data from the POST request
    // 2. Process the JSON data sent from the frontend
    if (!isset($_POST['full_schedule_json'])) {
        throw new Exception("No schedule items provided in the request.");
    }

    $scheduleData = json_decode($_POST['full_schedule_json'], true);

    if (!$scheduleData) {
        throw new Exception("Failed to decode schedule data.");
    }

    foreach ($scheduleData as $date => $items) {
        // $items is now the array of rows for this date
        foreach ($items as $item) {
            $cid  = $item['content_id'];
            $qty  = (int)($item['qty'] ?? 1);
            $pid  = $item['platform_id'];
            $plid = $item['placement_id'];
            $fid  = $item['format_id'];
            $media_ids = $item['media_ids'] ?? [];

            // A. Get Rate Card ID and Rate
            $stmt = $pdo->prepare("SELECT id, rate FROM rate_cards WHERE content_item_id = ? AND platform_id = ? AND placement_id = ? AND media_format_id = ?");
            $stmt->execute([$cid, $pid, $plid, $fid]);
            $rc = $stmt->fetch();
            
            if (!$rc) {
                throw new Exception("Rate card configuration missing for items on $date.");
            }

            // B. Validate Global Daily Capacity
            $stmt = $pdo->prepare("SELECT capacity_qty FROM inventory_daily_capacity WHERE rate_card_id = ?");
            $stmt->execute([$rc['id']]);
            $limit = (int)$stmt->fetchColumn(); 
            
            $stmt = $pdo->prepare("SELECT SUM(quantity) FROM schedule_items WHERE rate_card_id = ? AND scheduled_date = ?");
            $stmt->execute([$rc['id'], $date]);
            $already_used = (int)$stmt->fetchColumn();
            
            if (($already_used + $qty) > $limit) {
                throw new Exception("Inventory exhausted on $date. Capacity: $limit, Currently Used: $already_used, Requested: $qty.");
            }

            // C. Insert Schedule Item
            $stmt = $pdo->prepare("INSERT INTO schedule_items (schedule_id, content_item_id, platform_id, placement_id, quantity, cost, scheduled_date, rate_card_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$schedule_id, $cid, $pid, $plid, $qty, ($rc['rate'] * $qty), $date, $rc['id']]);
            $schedule_item_id = $pdo->lastInsertId();

            // D. Insert Media Links
            if (!empty($media_ids)) {
                $stmt_media = $pdo->prepare("INSERT INTO schedule_item_media (schedule_item_id, media_id) VALUES (?, ?)");
                foreach ($media_ids as $mid) {
                    if (!empty($mid)) {
                        $stmt_media->execute([$schedule_item_id, $mid]);
                    }
                }
            }
        }
    }

    // Commit transaction if all inserts passed validation
    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Schedule created successfully with global inventory validation.']);

} catch (Exception $e) {
    // Rollback if any error occurs to maintain data integrity
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>