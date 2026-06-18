<?php
function checkBudget($pdo, $schedule_id, $new_total_cost) {
    $stmt = $pdo->prepare("SELECT budget_allocated FROM schedules WHERE id = ?");
    $stmt->execute([$schedule_id]);
    $budget = $stmt->fetchColumn();

    if ($new_total_cost > $budget) {
        $pdo->prepare("UPDATE schedules SET status = 'Pending Approval' WHERE id = ?")->execute([$schedule_id]);
        return false; // Failed budget gate
    }
    return true;
}
?>