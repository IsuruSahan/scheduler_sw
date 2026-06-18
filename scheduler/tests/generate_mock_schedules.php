<?php
require_once __DIR__ . '/../../config/config.php';

try {
    $pdo->beginTransaction();

    // 1. Reference data
    $agencies = $pdo->query("SELECT id FROM agencies")->fetchAll(PDO::FETCH_COLUMN);
    $clients = $pdo->query("SELECT id FROM clients")->fetchAll(PDO::FETCH_COLUMN);
    $content = $pdo->query("SELECT id FROM content_items")->fetchAll(PDO::FETCH_COLUMN);
    $platforms = $pdo->query("SELECT id FROM platforms")->fetchAll(PDO::FETCH_COLUMN);
    $placements = $pdo->query("SELECT id FROM ad_placements")->fetchAll(PDO::FETCH_COLUMN);
    $formats = $pdo->query("SELECT id FROM media_formats")->fetchAll(PDO::FETCH_COLUMN);

    // Random name generators
    $prefixes = ['Summer', 'Flash', 'Mega', 'Brand', 'Global', 'Digital', 'Pro'];
    $suffixes = ['Blast', 'Promo', 'Launch', 'Campaign', 'Drive', 'Fest', 'Outreach'];

    // 2. Generate 5 Random Rate Cards and their Inventory
    $rate_card_ids = [];
    for ($k = 0; $k < 5; $k++) {
        $stmt = $pdo->prepare("INSERT INTO rate_cards (content_item_id, platform_id, placement_id, media_format_id, rate) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $content[array_rand($content)], 
            $platforms[array_rand($platforms)], 
            $placements[array_rand($placements)], 
            $formats[array_rand($formats)], 
            rand(500, 5000)
        ]);
        $rc_id = $pdo->lastInsertId();
        
        $pdo->prepare("INSERT INTO inventory (rate_card_id, total_capacity, used_qty) VALUES (?, 100, 0)")->execute([$rc_id]);
        $rate_card_ids[] = $rc_id;
    }

    // 3. Create 5 Random Schedules
    for ($i = 0; $i < 5; $i++) {
        $rc_id = $rate_card_ids[array_rand($rate_card_ids)];
        $stmt = $pdo->prepare("SELECT * FROM rate_cards WHERE id = ?");
        $stmt->execute([$rc_id]);
        $card = $stmt->fetch();

        // Randomly generated schedule name
        $random_name = $prefixes[array_rand($prefixes)] . ' ' . $suffixes[array_rand($suffixes)] . ' ' . rand(10, 99);

        // Create Schedule
        $stmt = $pdo->prepare("INSERT INTO schedules (agency_id, client_id, schedule_name, reference_no, start_date, end_date, budget_allocated, status, assigned_team) VALUES (?, ?, ?, ?, '2026-06-20', '2026-06-30', 50000, 'Active', 'News Team')");
        $stmt->execute([$agencies[array_rand($agencies)], $clients[array_rand($clients)], $random_name, "TEST-" . rand(100,999)]);
        $schedule_id = $pdo->lastInsertId();

        // Create Schedule Item
        $quantity = 1;
        $stmt = $pdo->prepare("INSERT INTO schedule_items (schedule_id, content_item_id, platform_id, placement_id, quantity, cost) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$schedule_id, $card['content_item_id'], $card['platform_id'], $card['placement_id'], $quantity, $card['rate']]);
        $item_id = $pdo->lastInsertId();

        // Inventory update
        $pdo->prepare("UPDATE inventory SET used_qty = used_qty + ? WHERE rate_card_id = ?")->execute([$quantity, $rc_id]);

        // Mock Media
        $pdo->prepare("INSERT INTO media_attachments (schedule_item_id, file_path) VALUES (?, '/uploads/mock_video_1.mp4')")->execute([$item_id]);
    }

    $pdo->commit();
    echo "Successfully generated 5 random schedules, e.g., '$random_name'.";
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}