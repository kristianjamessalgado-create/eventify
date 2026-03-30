<?php

function eventify_auto_complete_past_events(mysqli $conn): void
{
    static $ran = false;
    if ($ran) {
        return;
    }
    $ran = true;

    try {
        $col = $conn->query("SHOW COLUMNS FROM events LIKE 'status'");
        $hasCompleted = false;
        if ($col && ($row = $col->fetch_assoc())) {
            $type = strtolower((string)($row['Type'] ?? ''));
            $hasCompleted = (strpos($type, "'completed'") !== false);
        }

        $targetStatus = $hasCompleted ? 'completed' : 'closed';

        // End time fallback: if end_time is missing, treat it as 23:59:59 on event date.
        $sql = "
            UPDATE events
            SET status = ?
            WHERE status = 'active'
              AND TIMESTAMP(`date`, COALESCE(NULLIF(end_time, ''), '23:59:59')) < NOW()
        ";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $targetStatus);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Throwable $e) {
        // Keep dashboard available even if auto-complete fails.
    }
}
