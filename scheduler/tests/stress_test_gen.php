<?php
require_once __DIR__ . '/../../config/config.php';

// Helper to simulate a POST request to process_schedule.php
function simulateScheduleCreation($postData) {
    global $pdo; // <--- ADD THIS LINE
    
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = $postData;
    $_FILES = []; 
    
    ob_start();
    include __DIR__ . '/../process_schedule.php';
    $response = ob_get_clean();
    return json_decode($response, true);
}

// Fetch all necessary IDs for the loop
$contents = $pdo->query("SELECT id FROM content_items")->fetchAll(PDO::FETCH_COLUMN);
$rates = $pdo->query("SELECT content_item_id, platform_id, placement_id, media_format_id FROM rate_cards")->fetchAll(PDO::FETCH_ASSOC);

echo "--- Starting Stress Test: Cycling " . count($rates) . " Rate Card Combinations ---\n";

foreach ($rates as $index => $config) {
    $rowId = time() + $index;
    
    $testData = [
        'agency_id' => 1,
        'client_id' => 1,
        'schedule_name' => 'Automated Test ' . $rowId,
        'reference_no' => 'AUTO-' . $rowId,
        'budget' => 9999999, // Ensure budget is high enough so it doesn't trigger approval
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-30',
        'assigned_team' => 'News Team',
        'mode' => 'sync',
        'action' => 'create',
        'row_ids' => [$rowId],
        'content_item_id' => [$config['content_item_id']],
        'platform_id' => [$config['platform_id']],
        'placement_id' => [$config['placement_id']],
        'media_format_id' => [$config['media_format_id']],
        'quantity' => [1]
    ];

    $result = simulateScheduleCreation($testData);

    if ($result && $result['status'] === 'success') {
        echo "Row " . ($index + 1) . ": ✅ PASS (Rate Card ID: " . $config['platform_id'] . ")\n";
    } else {
        echo "Row " . ($index + 1) . ": ❌ FAIL (Error: " . ($result['message'] ?? 'Unknown') . ")\n";
        echo "   -> Config: Content:{$config['content_item_id']}, Plat:{$config['platform_id']}, Place:{$config['placement_id']}, Format:{$config['media_format_id']}\n";
    }
}
echo "--- Testing Complete ---\n";
?>