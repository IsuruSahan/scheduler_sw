<?php
// get_usage.php
require_once '../config/config.php';
header('Content-Type: application/json');

// Get the date from the URL (?date=YYYY-MM-DD)
$date = $_GET['date'] ?? date('Y-m-d');

try {
    // Sum quantity for the requested date, grouped by rate_card_id
    $stmt = $pdo->prepare("
        SELECT rate_card_id, SUM(quantity) as used 
        FROM schedule_items 
        WHERE scheduled_date = ? 
        GROUP BY rate_card_id
    ");
    $stmt->execute([$date]);

    // FETCH_KEY_PAIR turns the result into [rate_card_id => used_qty]
    // e.g., { "44": "5", "45": "2" }
    echo json_encode($stmt->fetchAll(PDO::FETCH_KEY_PAIR));
} catch (Exception $e) {
    echo json_encode([]); // Return empty object on error
}
?>