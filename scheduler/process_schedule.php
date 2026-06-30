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

    // 2. Loop through nested schedule data
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

            // B. Validate Daily Inventory (GLOBAL CAPACITY LOGIC)
            // 1. Get the Global limit for this Rate Card
            $stmt = $pdo->prepare("SELECT capacity_qty FROM inventory_daily_capacity WHERE rate_card_id = ?");
            $stmt->execute([$rc['id']]);
            $limit = (int)$stmt->fetchColumn(); 
            
            // 2. Calculate what has ALREADY been used for THIS SPECIFIC DATE
            $stmt = $pdo->prepare("SELECT SUM(quantity) FROM schedule_items WHERE rate_card_id = ? AND scheduled_date = ?");
            $stmt->execute([$rc['id'], $date]);
            $already_used = (int)$stmt->fetchColumn();
            
            // 3. Validation: (Already Used + Current Request) > Global Limit
            if (($already_used + $qty) > $limit) {
                throw new Exception("Inventory exhausted on $date. Available: " . ($limit - $already_used) . ", Requested: $qty.");
            }

            // C. Insert Item
            $stmt = $pdo->prepare("INSERT INTO schedule_items (schedule_id, content_item_id, platform_id, placement_id, quantity, cost, scheduled_date, rate_card_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$schedule_id, $cid, $pid, $plid, $qty, ($rc['rate'] * $qty), $date, $rc['id']]);
        }
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Schedule created successfully with global inventory validation.']);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>