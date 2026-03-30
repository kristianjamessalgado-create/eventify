<?php
session_start();
if (!defined('BASE_URL')) {
    define('BASE_URL', '/school_events');
}
include __DIR__ . '/config/db.php';
include __DIR__ . '/config/config.php';
include __DIR__ . '/config/csrf.php';

$token = trim($_GET['t'] ?? '');
$confirmed = false;
$already_done = false; // Student already confirmed attendance for this event
$error = '';
$event = null;
$geo_required = false;
$eventHasGeo = false;
$geo_radius_m = 300.0; // Allowed check-in radius from event pin

function eventify_haversine_m(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $earth = 6371000.0;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2)
        + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earth * $c;
}

if ($token === '') {
    $error = 'Invalid or missing check-in link. Please scan the event QR code again.';
} else {
    // Load event by check-in token; ensure token exists (generate for old events)
    try {
        $geoColCheck = $conn->query("SHOW COLUMNS FROM events WHERE Field IN ('latitude','longitude')");
        if ($geoColCheck && $geoColCheck->num_rows >= 2) {
            $eventHasGeo = true;
        }
    } catch (Throwable $e) {
        $eventHasGeo = false;
    }

    if ($eventHasGeo) {
        $stmt = $conn->prepare("SELECT id, title, date, start_time, end_time, location, status, checkin_token, latitude, longitude FROM events WHERE checkin_token = ?");
    } else {
        $stmt = $conn->prepare("SELECT id, title, date, start_time, end_time, location, status, checkin_token FROM events WHERE checkin_token = ?");
    }
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();
    $event = $res->fetch_assoc();
    $stmt->close();

    if (!$event) {
        $error = 'This check-in link is invalid or has expired.';
    } elseif (!in_array(strtolower($event['status'] ?? ''), ['active'], true)) {
        $error = 'Check-in is only available for approved events.';
    }
}

// Require student login to confirm attendance
if (!$error && $event && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $returnUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . BASE_URL . '/checkin.php?t=' . urlencode($token);
        header('Location: ' . BASE_URL . '/views/login.php?redirect=' . urlencode($returnUrl));
        exit();
    }
    if ($_SESSION['role'] !== 'student') {
        $error = 'Only students can confirm attendance. Please log in with a student account.';
        $event = null;
    } else {
        // Check if this student already confirmed attendance for this event
        $uid = (int) $_SESSION['user_id'];
        $eid = (int) $event['id'];
        $chk = $conn->prepare("SELECT 1 FROM registrations WHERE user_id = ? AND event_id = ? AND status = 'present' LIMIT 1");
        $chk->bind_param("ii", $uid, $eid);
        $chk->execute();
        $chk->store_result();
        $already_done = $chk->num_rows > 0;
        $chk->close();
    }
}

// Handle confirm attendance (POST)
if (!$error && $event && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && isset($_SESSION['user_id']) && $_SESSION['role'] === 'student') {
    if (!csrf_validate()) {
        $error = 'Invalid request. Please try again.';
    } else {
    $user_id = (int) $_SESSION['user_id'];
    $event_id = (int) $event['id'];

    // Security inputs from browser (live geolocation + per-device fingerprint)
    $device_hash = trim((string)($_POST['device_hash'] ?? ''));
    $geo_lat_raw = trim((string)($_POST['geo_lat'] ?? ''));
    $geo_lng_raw = trim((string)($_POST['geo_lng'] ?? ''));
    $geo_accuracy_raw = trim((string)($_POST['geo_accuracy'] ?? ''));
    $geo_ts_raw = trim((string)($_POST['geo_ts'] ?? ''));

    $geo_lat = is_numeric($geo_lat_raw) ? (float)$geo_lat_raw : null;
    $geo_lng = is_numeric($geo_lng_raw) ? (float)$geo_lng_raw : null;
    $geo_accuracy = is_numeric($geo_accuracy_raw) ? (float)$geo_accuracy_raw : null;
    $geo_ts = ctype_digit($geo_ts_raw) ? (int)$geo_ts_raw : 0;
    $server_now_ms = (int) round(microtime(true) * 1000);
    $event_lat = isset($event['latitude']) && $event['latitude'] !== null ? (float)$event['latitude'] : null;
    $event_lng = isset($event['longitude']) && $event['longitude'] !== null ? (float)$event['longitude'] : null;
    $enforce_geofence = $geo_required && $eventHasGeo && $event_lat !== null && $event_lng !== null;

    if ($device_hash === '' || strlen($device_hash) < 16) {
        $error = 'Device verification failed. Please use a modern browser and try again.';
    } elseif ($geo_required && ($geo_lat === null || $geo_lng === null)) {
        $error = 'Live location is required to check in. Please allow location access and try again.';
    } elseif ($geo_required && ($geo_ts <= 0 || abs($server_now_ms - $geo_ts) > 120000)) {
        $error = 'Location check expired. Please refresh your location and try again.';
    } elseif ($geo_required && $geo_accuracy !== null && $geo_accuracy > 2000) {
        $error = 'Location accuracy is too low. Move to a better signal and try again.';
    } elseif ($enforce_geofence) {
        $distance_m = eventify_haversine_m($geo_lat, $geo_lng, $event_lat, $event_lng);
        if ($distance_m > $geo_radius_m) {
            $error = 'You are too far from the event location to confirm attendance. Please move closer to the venue and try again.';
        }
    } else {
        // Ensure device-lock table exists (graceful auto-setup)
        $conn->query("
            CREATE TABLE IF NOT EXISTS event_checkin_device_locks (
              id INT AUTO_INCREMENT PRIMARY KEY,
              event_id INT NOT NULL,
              user_id INT NOT NULL,
              device_hash VARCHAR(128) NOT NULL,
              first_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              last_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              last_lat DECIMAL(10,7) NULL DEFAULT NULL,
              last_lng DECIMAL(10,7) NULL DEFAULT NULL,
              last_accuracy FLOAT NULL DEFAULT NULL,
              last_geo_at DATETIME NULL DEFAULT NULL,
              UNIQUE KEY uniq_event_device (event_id, device_hash),
              KEY idx_event_user (event_id, user_id),
              CONSTRAINT fk_checkin_lock_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
              CONSTRAINT fk_checkin_lock_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        try {
            $conn->begin_transaction();

            // Prevent same device from checking in multiple different accounts on the same event
            $lockSel = $conn->prepare("SELECT user_id FROM event_checkin_device_locks WHERE event_id = ? AND device_hash = ? LIMIT 1 FOR UPDATE");
            if (!$lockSel) {
                throw new Exception('Security lock check failed.');
            }
            $lockSel->bind_param("is", $event_id, $device_hash);
            $lockSel->execute();
            $lockRes = $lockSel->get_result();
            $lockRow = $lockRes ? $lockRes->fetch_assoc() : null;
            $lockSel->close();

            if ($lockRow && (int)$lockRow['user_id'] !== $user_id) {
                throw new Exception('This device already checked in another account for this event.');
            }

            if ($lockRow) {
                $updLock = $conn->prepare("UPDATE event_checkin_device_locks SET last_seen_at = NOW(), last_lat = ?, last_lng = ?, last_accuracy = ?, last_geo_at = NOW() WHERE event_id = ? AND device_hash = ? AND user_id = ?");
                if (!$updLock) {
                    throw new Exception('Could not update device lock.');
                }
                $updLock->bind_param("dddisi", $geo_lat, $geo_lng, $geo_accuracy, $event_id, $device_hash, $user_id);
                $updLock->execute();
                $updLock->close();
            } else {
                $insLock = $conn->prepare("INSERT INTO event_checkin_device_locks (event_id, user_id, device_hash, last_lat, last_lng, last_accuracy, last_geo_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                if (!$insLock) {
                    throw new Exception('Could not create device lock.');
                }
                $insLock->bind_param("iisddd", $event_id, $user_id, $device_hash, $geo_lat, $geo_lng, $geo_accuracy);
                $insLock->execute();
                $insLock->close();
            }

            $stmt = $conn->prepare("INSERT INTO registrations (user_id, event_id, status, time_in) VALUES (?, ?, 'present', NOW()) ON DUPLICATE KEY UPDATE status = 'present', time_in = NOW()");
            if (!$stmt) {
                throw new Exception('Could not record attendance.');
            }
            $stmt->bind_param("ii", $user_id, $event_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $confirmed = true;
        } catch (Throwable $txe) {
            try { $conn->rollback(); } catch (Throwable $e2) {}
            if ($txe->getMessage() === 'This device already checked in another account for this event.') {
                $error = 'Security check blocked this action: this device is already used by another account for this event.';
            } else {
                $error = 'Could not record attendance. Please try again.';
            }
        }
    }
    }
}

$conn->close();

$pageTitle = $event ? htmlspecialchars($event['title']) : 'Event Check-in';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Check-in | EVENTIFY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1rem; }
        .checkin-card { max-width: 420px; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
        .checkin-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 1.5rem; border-radius: 16px 16px 0 0; text-align: center; }
        .checkin-body { padding: 1.5rem; background: #fff; border-radius: 0 0 16px 16px; }
        .event-meta { color: #6c757d; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .btn-confirm { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; padding: 0.75rem 1.5rem; font-weight: 600; border-radius: 10px; }
        .btn-confirm:hover { color: #fff; opacity: 0.95; }
    </style>
</head>
<body>
    <div class="checkin-card card border-0 overflow-hidden">
        <div class="checkin-header">
            <h1 class="h4 mb-0"><i class="fas fa-qrcode me-2"></i>EVENTIFY Check-in</h1>
        </div>
        <div class="checkin-body">
            <?php if ($error): ?>
                <p class="text-danger mb-0"><?= htmlspecialchars($error) ?></p>
                <a href="<?= BASE_URL ?>" class="btn btn-outline-primary mt-3" target="_top">Go to home</a>
            <?php elseif ($confirmed): ?>
                <p class="text-success mb-2"><i class="fas fa-check-circle me-2"></i><strong>Attendance confirmed.</strong></p>
                <p class="text-muted small mb-0">You have been marked present for this event.</p>
                <a href="<?= BASE_URL ?>/backend/auth/dashboard_student.php" class="btn btn-primary mt-3" target="_top">Back to dashboard</a>
            <?php elseif ($already_done): ?>
                <p class="text-success mb-2"><i class="fas fa-check-circle me-2"></i><strong>You're done with attendance already.</strong></p>
                <p class="text-muted small mb-0">You have already confirmed your attendance for this event. No need to check in again.</p>
                <a href="<?= BASE_URL ?>/backend/auth/dashboard_student.php" class="btn btn-primary mt-3" target="_top">Back to dashboard</a>
            <?php elseif ($event): ?>
                <h5 class="mb-3"><?= htmlspecialchars($event['title']) ?></h5>
                <div class="event-meta">
                    <?php if (!empty($event['date'])): ?>
                        <div><i class="fas fa-calendar-day me-2"></i><?= date('l, M j, Y', strtotime($event['date'])) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($event['location'])): ?>
                        <div><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($event['location']) ?></div>
                    <?php endif; ?>
                </div>
                <p class="small text-muted mb-2">Scan this QR at the event to confirm your attendance. Only students can check in.</p>
                <p class="small text-muted mb-3">Check-in security is currently using account/device checks only. Live location is temporarily disabled.</p>
                <form method="POST" id="checkinForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="geo_lat" id="geo_lat" value="">
                    <input type="hidden" name="geo_lng" id="geo_lng" value="">
                    <input type="hidden" name="geo_accuracy" id="geo_accuracy" value="">
                    <input type="hidden" name="geo_ts" id="geo_ts" value="">
                    <input type="hidden" name="device_hash" id="device_hash" value="">
                    <button type="submit" name="confirm" value="1" class="btn btn-confirm w-100" id="confirmBtn">
                        <i class="fas fa-check-double me-2"></i>Confirm my attendance
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <script>
      (function() {
        var form = document.getElementById('checkinForm');
        if (!form) return;
        var fLat = document.getElementById('geo_lat');
        var fLng = document.getElementById('geo_lng');
        var fAcc = document.getElementById('geo_accuracy');
        var fTs = document.getElementById('geo_ts');
        var fHash = document.getElementById('device_hash');
        var confirmBtn = document.getElementById('confirmBtn');
        var geoRequired = <?= json_encode($geo_required) ?>;

        function setCanConfirm(canConfirm) {
          if (!confirmBtn) return;
          confirmBtn.disabled = !canConfirm;
        }

        async function buildDeviceHash() {
          var fpRaw = [
            navigator.userAgent || '',
            navigator.platform || '',
            navigator.language || '',
            (screen && screen.width ? screen.width : 0) + 'x' + (screen && screen.height ? screen.height : 0),
            Intl.DateTimeFormat().resolvedOptions().timeZone || '',
            String(navigator.hardwareConcurrency || 0),
            String(navigator.maxTouchPoints || 0)
          ].join('|');
          try {
            var enc = new TextEncoder();
            var data = enc.encode(fpRaw);
            var hashBuf = await crypto.subtle.digest('SHA-256', data);
            var hashArr = Array.from(new Uint8Array(hashBuf));
            return hashArr.map(function(b) { return b.toString(16).padStart(2, '0'); }).join('');
          } catch (e) {
            return btoa(unescape(encodeURIComponent(fpRaw))).slice(0, 96);
          }
        }

        function requestLocation() {
          if (!geoRequired) {
            setCanConfirm(true);
            return;
          }
          if (!navigator.geolocation) {
            setCanConfirm(false);
            return;
          }
          setCanConfirm(false);
          navigator.geolocation.getCurrentPosition(function(pos) {
            var c = pos.coords || {};
            fLat.value = String(c.latitude || '');
            fLng.value = String(c.longitude || '');
            fAcc.value = String(c.accuracy || '');
            fTs.value = String(Date.now());
            setCanConfirm(true);
          }, function(err) {
            setCanConfirm(false);
          }, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
          });
        }

        buildDeviceHash().then(function(h) {
          fHash.value = h || '';
          if (!fHash.value) {
            setCanConfirm(false);
          }
        });
        setCanConfirm(true);

        form.addEventListener('submit', function(e) {
          if (!fHash.value) {
            e.preventDefault();
            return;
          }
          if (geoRequired && (!fLat.value || !fLng.value)) {
            e.preventDefault();
            requestLocation();
            return;
          }
        });
      })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
