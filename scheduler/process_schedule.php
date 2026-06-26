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

    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

    // 1. Insert Schedule Header
    $assigned_teams = isset($_POST['assigned_team']) ? implode(', ', $_POST['assigned_team']) : 'Content Editor Team';
    $stmt = $pdo->prepare("
        INSERT INTO schedules (agency_id, client_id, schedule_name, reference_no, assigned_team, budget_allocated, start_date, end_date, created_by, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['agency_id'], $_POST['client_id'], $_POST['schedule_name'], $_POST['reference_no'],
        $assigned_teams, floatval($_POST['budget']), $_POST['start_date'], $_POST['end_date'], $_SESSION['user_id'], 'Active'
    ]);
    $schedule_id = $pdo->lastInsertId();

    // 2. Loop through nested schedule data: schedule[date][field][]
    if (!isset($_POST['schedule'])) throw new Exception("No schedule items provided.");

    foreach ($_POST['schedule'] as $date => $items) {
        foreach ($items['content_id'] as $idx => $cid) {
            $qty = (int)$items['quantity'][$idx];
            $pid = $items['platform_id'][$idx];
            $plid = $items['placement_id'][$idx];
            $fid = $items['format_id'][$idx];

            // A. Get Rate Card ID
            $stmt = $pdo->prepare("SELECT id, rate FROM rate_cards WHERE content_item_id = ? AND platform_id = ? AND placement_id = ? AND media_format_id = ?");
            $stmt->execute([$cid, $pid, $plid, $fid]);
            $rc = $stmt->fetch();
            if (!$rc) throw new Exception("Rate card config missing for $date.");

            // B. Validate Daily Inventory from inventory_daily_capacity table
            $stmt = $pdo->prepare("SELECT capacity_qty FROM inventory_daily_capacity WHERE rate_card_id = ? AND capacity_date = ?");
            $stmt->execute([$rc['id'], $date]);
            $limit = (int)$stmt->fetchColumn();
            
            if ($qty > $limit) {
                throw new Exception("Inventory exhausted on $date. Available: $limit, Requested: $qty.");
            }

            // C. Insert Item with scheduled_date
            $stmt = $pdo->prepare("INSERT INTO schedule_items (schedule_id, content_item_id, platform_id, placement_id, quantity, cost, scheduled_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$schedule_id, $cid, $pid, $plid, $qty, ($rc['rate'] * $qty), $date]);
            
            // D. Update Inventory used_qty
            $pdo->prepare("UPDATE inventory SET used_qty = used_qty + ? WHERE rate_card_id = ?")->execute([$qty, $rc['id']]);
        }
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Schedule created successfully with daily inventory validation.']);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>