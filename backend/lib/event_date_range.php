<?php

/**
 * Multi-day events: optional end_date (inclusive last day) on events table.
 */

function eventify_events_end_date_ensure(mysqli $conn): bool
{
    try {
        $c = $conn->query("SHOW COLUMNS FROM events LIKE 'end_date'");
        if ($c && $c->num_rows > 0) {
            return true;
        }
        $conn->query(
            "ALTER TABLE events ADD COLUMN end_date DATE NULL DEFAULT NULL AFTER `date`"
        );
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function eventify_events_has_end_date(mysqli $conn): bool
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    try {
        $c = $conn->query("SHOW COLUMNS FROM events LIKE 'end_date'");
        $cached = (bool) ($c && $c->num_rows > 0);
    } catch (Throwable $e) {
        $cached = false;
    }
    return $cached;
}

/** Last calendar day of the event (inclusive). */
function eventify_event_last_day(array $event): string
{
    $start = substr(trim((string) ($event['date'] ?? '')), 0, 10);
    $end = substr(trim((string) ($event['end_date'] ?? '')), 0, 10);
    if ($start === '') {
        return '';
    }
    if ($end !== '' && $end >= $start) {
        return $end;
    }
    return $start;
}

/** Normalize POST end_date: null when empty or same as start. */
function eventify_parse_event_end_date(string $startDate, string $endDateRaw): array
{
    $endDateRaw = trim($endDateRaw);
    if ($endDateRaw === '') {
        return ['ok' => true, 'value' => null];
    }
    $startObj = DateTime::createFromFormat('Y-m-d', $startDate);
    $endObj = DateTime::createFromFormat('Y-m-d', $endDateRaw);
    if (!$startObj || $startObj->format('Y-m-d') !== $startDate) {
        return ['ok' => false, 'error' => 'Invalid start date format.'];
    }
    if (!$endObj || $endObj->format('Y-m-d') !== $endDateRaw) {
        return ['ok' => false, 'error' => 'Invalid end date format.'];
    }
    if ($endObj < $startObj) {
        return ['ok' => false, 'error' => 'End date cannot be before the start date.'];
    }
    if ($endDateRaw === $startDate) {
        return ['ok' => true, 'value' => null];
    }
    return ['ok' => true, 'value' => $endDateRaw];
}

/** FullCalendar start/end strings from an event row. */
function eventify_event_fc_bounds(array $event): array
{
    $date = substr(trim((string) ($event['date'] ?? '')), 0, 10);
    if ($date === '') {
        return ['start' => '', 'end' => null];
    }
    $endDate = substr(trim((string) ($event['end_date'] ?? '')), 0, 10);
    if ($endDate === '' || $endDate < $date) {
        $endDate = $date;
    }
    $startTime = trim((string) ($event['start_time'] ?? ''));
    $endTime = trim((string) ($event['end_time'] ?? ''));
    $start = trim($date . ' ' . ($startTime !== '' ? $startTime : '00:00:00'));

    if ($endDate > $date) {
        $end = trim($endDate . ' ' . ($endTime !== '' ? $endTime : '23:59:59'));
        return ['start' => $start, 'end' => $end];
    }
    if ($endTime !== '') {
        return ['start' => $start, 'end' => trim($date . ' ' . $endTime)];
    }
    return ['start' => $start, 'end' => null];
}
