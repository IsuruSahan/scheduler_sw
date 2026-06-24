<?php
require_once __DIR__ . '/../config/config.php';
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM schedules WHERE id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Schedule_Report_'.$s['reference_no'].'.xls"');
echo "Schedule Name\tRef No\tBudget\tFinal Cost\tStatus\n";
echo "{$s['schedule_name']}\t{$s['reference_no']}\t{$s['budget_allocated']}\t{$s['final_cost']}\t{$s['status']}\n";
?>