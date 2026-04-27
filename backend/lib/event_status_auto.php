<?php

/**
 * Status used when an event is finished (auto or organizer "mark as ended").
 * Matches ENUM if `completed` exists, otherwise `closed`.
 */
function eventify_events_completed_or_closed_target(mysqli $conn): string
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $cached = 'closed';
    try {
        $col = $conn->query("SHOW COLUMNS FROM events LIKE 'status'");
        if ($col && ($row = $col->fetch_assoc())) {
            $type = strtolower((string) ($row['Type'] ?? ''));
            if (strpos($type, "'completed'") !== false) {
                $cached = 'completed';
            }
        }
    } catch (Throwable $e) {
        // keep default
    }
    return $cached;
}

function eventify_auto_complete_past_events(mysqli $conn): void
{
    static $ran = false;
    if ($ran) {
        return;
    }
    $ran = true;

    try {
        $targetStatus = eventify_events_completed_or_closed_target($conn);

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
