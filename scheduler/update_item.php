<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // 1. Capture Form Inputs (Matching the dropdown names in details.php)
        $item_id = $_POST['item_id'];
        $schedule_id = $_POST['schedule_id'];
        $new_content_id = $_POST['content_id'];
        $new_platform_id = $_POST['platform_id'];
        $new_placement_id = $_POST['placement_id'];
        $new_format_id = $_POST['format_id'];
        $new_qty = (int)$_POST['quantity'];

        // 2. Fetch Old Item Details for Inventory Release
        $old_stmt = $pdo->prepare("SELECT quantity, content_item_id, platform_id, placement_id FROM schedule_items WHERE id = ?");
        $old_stmt->execute([$item_id]);
        $old = $old_stmt->fetch();

        // 3. Find NEW Rate Card ID & Rate 
        // Logic: Search for the rate card matching the new inputs
        $rate_check = $pdo->prepare("SELECT id, rate FROM rate_cards WHERE content_item_id = ? AND platform_id = ? AND placement_id = ? AND media_format_id = ? LIMIT 1");
        $rate_check->execute([$new_content_id, $new_platform_id, $new_placement_id, $new_format_id]);
        $new_rate = $rate_check->fetch();

        if (!$new_rate) {
            throw new Exception("No pricing configuration (Rate Card) found for this specific combination.");
        }

        // 4. Inventory Pre-Check
        // Find the rate_card_id of the OLD item to release its inventory
        $old_rc = $pdo->prepare("SELECT id FROM rate_cards WHERE content_item_id = ? AND platform_id = ? AND placement_id = ? LIMIT 1");
        $old_rc->execute([$old['content_item_id'], $old['platform_id'], $old['placement_id']]);
        $old_rc_id = $old_rc->fetchColumn();

        // Check availability of the NEW inventory
        $inv_check = $pdo->prepare("SELECT (total_capacity - used_qty) as current_available FROM inventory WHERE rate_card_id = ?");
        $inv_check->execute([$new_rate['id']]);
        $available = $inv_check->fetchColumn();

        // If editing the same item, add current quantity to 'available' to allow adjustments
        $adjustment = ($old_rc_id == $new_rate['id']) ? $old['quantity'] : 0;

        if ($new_qty > ($available + $adjustment)) {
            throw new Exception("Insufficient inventory. Requested: $new_qty, Max Available: " . ($available + $adjustment));
        }

        // 5. Atomic Inventory Swap
        // Release old inventory
        if ($old_rc_id) {
            $pdo->prepare("UPDATE inventory SET used_qty = used_qty - ? WHERE rate_card_id = ?")
                ->execute([$old['quantity'], $old_rc_id]);
        }

        // Deduct new inventory
        $pdo->prepare("UPDATE inventory SET used_qty = used_qty + ? WHERE rate_card_id = ?")
            ->execute([$new_qty, $new_rate['id']]);

        // 6. Update Schedule Item
        $new_cost = $new_qty * $new_rate['rate'];
        $pdo->prepare("UPDATE schedule_items SET content_item_id = ?, platform_id = ?, placement_id = ?, quantity = ?, cost = ? WHERE id = ?")
            ->execute([$new_content_id, $new_platform_id, $new_placement_id, $new_qty, $new_cost, $item_id]);

        // 7. Budget Gate
        $total_cost = $pdo->prepare("SELECT SUM(cost) FROM schedule_items WHERE schedule_id = ?");
        $total_cost->execute([$schedule_id]);
        $new_total = $total_cost->fetchColumn();

        $budget = $pdo->prepare("SELECT budget_allocated FROM schedules WHERE id = ?");
        $budget->execute([$schedule_id]);
        
        if ($new_total > $budget->fetchColumn()) {
            $pdo->prepare("UPDATE schedules SET status = 'Pending Approval' WHERE id = ?")->execute([$schedule_id]);
        }

        $pdo->commit();
        header("Location: details.php?id=$schedule_id&msg=updated");
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: details.php?id=$schedule_id&error=" . urlencode($e->getMessage()));
    }
}