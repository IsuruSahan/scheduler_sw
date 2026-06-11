<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // 1. Insert the Schedule Header
        // Update the INSERT statement in process_schedule.php
$stmt = $pdo->prepare("
    INSERT INTO schedules 
    (agency_id, client_id, schedule_name, reference_no, budget_allocated, start_date, end_date, created_by) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $_POST['agency_id'], 
    $_POST['client_id'], 
    $_POST['schedule_name'], 
    $_POST['reference_no'], // Capture the new field
    $_POST['budget'], 
    $_POST['start_date'], 
    $_POST['end_date'], 
    $_SESSION['user_id']
]);
        $schedule_id = $pdo->lastInsertId();

        // 2. Loop through the rows
        foreach ($_POST['episode_id'] as $idx => $episode_id) {
            // IMPORTANT: Fetch content_item_id from the episodes table to satisfy the NOT NULL constraint
            $stmt = $pdo->prepare("SELECT content_item_id FROM episodes WHERE id = ?");
            $stmt->execute([$episode_id]);
            $ep = $stmt->fetch();
            $content_item_id = $ep['content_item_id'];

            // Insert into schedule_items
            $stmt = $pdo->prepare("INSERT INTO schedule_items (schedule_id, episode_id, content_item_id, platform_id, placement_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$schedule_id, $episode_id, $content_item_id, $_POST['platform_id'][$idx], $_POST['placement_id'][$idx]]);
            $item_id = $pdo->lastInsertId();

            // 3. Handle File Uploads
            $media_files = [];
            $media_refs = [];

            if ($_POST['mode'] == 'sync') {
                $media_files = $_FILES['media']['sync'] ?? [];
                $media_refs = $_POST['ref']['sync'] ?? [];
            } else {
                // For custom mode, extract files for this specific row index
                $media_files['name'] = $_FILES['media']['name'][$idx] ?? [];
                $media_files['tmp_name'] = $_FILES['media']['tmp_name'][$idx] ?? [];
                $media_refs = $_POST['ref'][$idx] ?? [];
            }

            if (!empty($media_files['name'])) {
                foreach ($media_files['name'] as $fIdx => $fileName) {
                    if ($fileName) {
                        $target = '../uploads/' . time() . '_' . $fileName;
                        if (move_uploaded_file($media_files['tmp_name'][$fIdx], $target)) {
                            $pdo->prepare("INSERT INTO media_attachments (schedule_item_id, file_path, file_reference) VALUES (?, ?, ?)")
                                ->execute([$item_id, $target, $media_refs[$fIdx]]);
                        }
                    }
                }
            }
        }

        $pdo->commit();
        header("Location: dashboard.php?status=success");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error saving schedule: " . $e->getMessage());
    }
}