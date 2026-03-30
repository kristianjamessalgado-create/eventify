<?php
session_start();
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';
include __DIR__ . '/../../config/departments.php';

// Check if user is logged in as organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: " . BASE_URL . "/views/login.php?error=Access denied");
    exit();
}

$session_user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$eventsHasGeo = false;
try {
    $geoColCheck = $conn->query("SHOW COLUMNS FROM events WHERE Field IN ('latitude','longitude')");
    if ($geoColCheck && $geoColCheck->num_rows >= 2) {
        $eventsHasGeo = true;
    }
} catch (Throwable $e) {
    $eventsHasGeo = false;
}

$eventsHasMaxCapacity = false;
try {
    $mcCol = $conn->query("SHOW COLUMNS FROM events WHERE Field = 'max_capacity'");
    if ($mcCol && $mcCol->num_rows >= 1) {
        $eventsHasMaxCapacity = true;
    }
} catch (Throwable $e) {
    $eventsHasMaxCapacity = false;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        header("Location: " . BASE_URL . "/backend/auth/dashboardorganizer.php?msg=" . urlencode("Invalid request. Please try again."));
        exit();
    }
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = $_POST['date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $department = $_POST['department'] ?? 'ALL';
    $max_capacity_raw = trim($_POST['max_capacity'] ?? '');
    $maxCapVal = null;
    if ($max_capacity_raw !== '' && ctype_digit($max_capacity_raw)) {
        $v = (int) $max_capacity_raw;
        if ($v > 0) {
            $maxCapVal = $v;
        }
    }
    
    // Validation
    if (empty($title)) {
        $error = "Title is required.";
    } elseif (empty($date)) {
        $error = "Date is required.";
    } elseif (empty($start_time)) {
        $error = "Start time is required.";
    } elseif (empty($location)) {
        $error = "Location is required.";
    } elseif (strlen($title) > 150) {
        $error = "Title must be 150 characters or less.";
    } elseif (strlen($location) > 255) {
        $error = "Location must be 255 characters or less.";
    } else {
        // Validate date format
        $dateObj = DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
            $error = "Invalid date format.";
        } else {
            // Validate time format (HH:MM)
            $startTimeObj = DateTime::createFromFormat('H:i', $start_time);
            if (!$startTimeObj || $startTimeObj->format('H:i') !== $start_time) {
                $error = "Invalid start time format.";
            }

            $endTimeObj = null;
            if ($end_time !== '') {
                $endTimeObj = DateTime::createFromFormat('H:i', $end_time);
                if (!$endTimeObj || $endTimeObj->format('H:i') !== $end_time) {
                    $error = "Invalid end time format.";
                } elseif ($endTimeObj <= $startTimeObj) {
                    $error = "End time must be after start time.";
                }
            }

            // Check if date is in the past
            $today = new DateTime();
            $today->setTime(0, 0, 0);
            $eventDate = new DateTime($date);
            $eventDate->setTime(0, 0, 0);
            
            if ($eventDate < $today) {
                $error = "Event date cannot be in the past.";
            }

            if (!$error) {
                $department = eventify_normalize_department($department);

                $latVal = null;
                $lngVal = null;
                if ($eventsHasGeo) {
                    $latRaw = trim($_POST['event_latitude'] ?? '');
                    $lngRaw = trim($_POST['event_longitude'] ?? '');
                    if ($latRaw === '' || $lngRaw === '' || !is_numeric($latRaw) || !is_numeric($lngRaw)) {
                        $error = 'Please set the venue on the map, use “Use my location”, or search and pick a result.';
                    } else {
                        $latVal = (float) $latRaw;
                        $lngVal = (float) $lngRaw;
                        if ($latVal < -90 || $latVal > 90 || $lngVal < -180 || $lngVal > 180) {
                            $error = 'Invalid map coordinates.';
                        }
                    }
                }

                if (!$error) {
                    $checkin_token = bin2hex(random_bytes(16));
                    $start_time_param = $start_time ?: null;
                    $end_time_param = $end_time !== '' ? $end_time : null;
                    $executed = false;

                    if ($eventsHasGeo && $latVal !== null && $lngVal !== null) {
                        if ($eventsHasMaxCapacity) {
                            $stmt = $conn->prepare("INSERT INTO events (title, description, date, start_time, end_time, location, latitude, longitude, max_capacity, organizer_id, department, status, checkin_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
                            if ($stmt) {
                                $stmt->bind_param("ssssssddiiss", $title, $description, $date, $start_time_param, $end_time_param, $location, $latVal, $lngVal, $maxCapVal, $session_user_id, $department, $checkin_token);
                                if ($stmt->execute()) {
                                    $executed = true;
                                }
                                $stmt->close();
                            }
                        }
                        if (!$executed) {
                            $stmt = $conn->prepare("INSERT INTO events (title, description, date, start_time, end_time, location, latitude, longitude, organizer_id, department, status, checkin_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
                            if ($stmt) {
                                $stmt->bind_param("ssssssddiss", $title, $description, $date, $start_time_param, $end_time_param, $location, $latVal, $lngVal, $session_user_id, $department, $checkin_token);
                                if ($stmt->execute()) {
                                    $executed = true;
                                }
                                $stmt->close();
                            }
                        }
                    }

                    if (!$executed && !$eventsHasGeo) {
                        if ($eventsHasMaxCapacity) {
                            $stmt = $conn->prepare("INSERT INTO events (title, description, date, start_time, end_time, location, max_capacity, organizer_id, department, status, checkin_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
                            if ($stmt) {
                                $stmt->bind_param("ssssssiiss", $title, $description, $date, $start_time_param, $end_time_param, $location, $maxCapVal, $session_user_id, $department, $checkin_token);
                                if ($stmt->execute()) {
                                    $executed = true;
                                }
                                $stmt->close();
                            }
                        }
                        if (!$executed) {
                            $stmt = $conn->prepare("INSERT INTO events (title, description, date, start_time, end_time, location, organizer_id, department, status, checkin_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
                            if ($stmt) {
                                $stmt->bind_param("ssssssiss", $title, $description, $date, $start_time_param, $end_time_param, $location, $session_user_id, $department, $checkin_token);
                                if ($stmt->execute()) {
                                    $executed = true;
                                }
                                $stmt->close();
                            }
                        }
                    }

                    if ($executed) {
                        $newEventId = (int) $conn->insert_id;
                        require_once __DIR__ . '/../lib/activity_logger.php';
                        log_activity(
                            $conn,
                            (int) $session_user_id,
                            'organizer',
                            'event_submitted_pending',
                            'event',
                            $newEventId,
                            'Submitted for admin approval: ' . $title
                        );
                        try {
                            $who = $conn->query("SELECT id FROM users WHERE role IN ('admin','super_admin') AND status = 'active'");
                            if ($who) {
                                $notifTitle = 'New event pending approval';
                                $notifMsg = 'Organizer submitted "' . $title . '" for approval.';
                                $insNotif = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, event_id) VALUES (?, 'event_pending_review', ?, ?, ?)");
                                if ($insNotif) {
                                    while ($adm = $who->fetch_assoc()) {
                                        $adminId = (int) ($adm['id'] ?? 0);
                                        if ($adminId > 0) {
                                            $insNotif->bind_param("issi", $adminId, $notifTitle, $notifMsg, $newEventId);
                                            $insNotif->execute();
                                        }
                                    }
                                    $insNotif->close();
                                }
                            }
                        } catch (Throwable $e) {
                            // ignore
                        }
                        $success = "Event submitted successfully and is now pending approval from the administrator.";
                        header("Location: " . BASE_URL . "/backend/auth/dashboardorganizer.php?msg=" . urlencode($success));
                        exit();
                    }

                    if ($eventsHasGeo) {
                        $error = 'Could not save event with map location. Check database migration (latitude/longitude columns).';
                    } else {
                        $error = "Failed to create event. Please try again.";
                    }
                }
            }
        }
    }
}

// Get pre-filled date from URL parameter (from calendar click)
$prefilled_date = $_GET['date'] ?? '';

// Fetch user info for display
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $session_user_id);
$stmt->execute();
$stmt->bind_result($db_name);
$stmt->fetch();
$user_name = $db_name ?? 'Organizer';
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - EVENTIFY</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Google Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .create-event-container {
            max-width: 700px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            animation: slideUp 0.4s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .create-event-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .create-event-header h1 {
            font-size: 28px;
            font-weight: 500;
            margin: 0;
        }
        
        .create-event-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        
        .create-event-body {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #3c4043;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group label .required {
            color: #ea4335;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #dadce0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Google Sans', sans-serif;
            transition: all 0.2s ease;
            background: #fff;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-control::placeholder {
            color: #9aa0a6;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        
        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            flex: 1;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #5f6368;
            border: 1px solid #dadce0;
        }
        
        .btn-secondary:hover {
            background: #e8eaed;
        }
        
        .alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-danger {
            background: #fce8e6;
            color: #c5221f;
            border: 1px solid #f28b82;
        }
        
        .alert-success {
            background: #e6f4ea;
            color: #137333;
            border: 1px solid #81c995;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 20px;
            transition: color 0.2s ease;
        }
        
        .back-link:hover {
            color: #764ba2;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa0a6;
        }
        
        .input-icon .form-control {
            padding-left: 45px;
        }

        .event-location-map {
            height: 280px;
            border-radius: 8px;
            border: 1px solid #dadce0;
            overflow: hidden;
            z-index: 0;
        }

        .event-loc-results {
            max-height: 160px;
            overflow-y: auto;
            border-radius: 8px;
            z-index: 2;
        }
        
        @media (max-width: 768px) {
            .create-event-body {
                padding: 24px;
            }
            
            .create-event-header {
                padding: 24px;
            }
            
            .create-event-header h1 {
                font-size: 24px;
            }
            
            .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="create-event-container">
        <div class="create-event-header">
            <h1><i class="fas fa-calendar-plus"></i> Create New Event</h1>
            <p>Fill in the details below to create your event</p>
        </div>
        
        <div class="create-event-body">
            <a href="<?= BASE_URL ?>/backend/auth/dashboardorganizer.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="createEventForm" data-require-geo="<?= $eventsHasGeo ? '1' : '0' ?>">
                <div class="form-group">
                    <label for="title">
                        Event Title <span class="required">*</span>
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-heading"></i>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="form-control" 
                            placeholder="Enter event title"
                            value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                            required
                            maxlength="150"
                        >
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">
                        Description
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-control" 
                        placeholder="Enter event description (optional)"
                        maxlength="1000"
                    ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="date">
                        Event Date <span class="required">*</span>
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-calendar-alt"></i>
                        <input 
                            type="date" 
                            id="date" 
                            name="date" 
                            class="form-control" 
                            value="<?= htmlspecialchars($prefilled_date ?: ($_POST['date'] ?? '')) ?>"
                            required
                            min="<?= date('Y-m-d') ?>"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="start_time">
                        Start time <span class="required">*</span>
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-clock"></i>
                        <input
                            type="time"
                            id="start_time"
                            name="start_time"
                            class="form-control"
                            value="<?= htmlspecialchars($_POST['start_time'] ?? '') ?>"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="end_time">End time</label>
                    <div class="input-icon">
                        <i class="fas fa-clock"></i>
                        <input
                            type="time"
                            id="end_time"
                            name="end_time"
                            class="form-control"
                            value="<?= htmlspecialchars($_POST['end_time'] ?? '') ?>"
                        >
                    </div>
                    <small class="text-muted d-block mt-1">Optional — leave blank if the event has no fixed end time.</small>
                </div>
                
                <div class="form-group">
                    <label for="location">
                        Location <span class="required">*</span>
                    </label>
                    <p class="text-muted" style="font-size: 13px; margin-bottom: 10px;">
                        Search OpenStreetMap, click the map, or use your current location. The pin sets GPS coordinates for the venue.
                    </p>
                    <input type="hidden" name="event_latitude" id="event_latitude" value="<?= htmlspecialchars($_POST['event_latitude'] ?? '') ?>">
                    <input type="hidden" name="event_longitude" id="event_longitude" value="<?= htmlspecialchars($_POST['event_longitude'] ?? '') ?>">
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 10px;">
                        <input type="search" id="eventLocSearch" class="form-control" style="flex: 1; min-width: 200px;" placeholder="Search place or address" autocomplete="off">
                        <button type="button" class="btn btn-secondary" id="eventLocSearchBtn" style="white-space: nowrap;">Search</button>
                        <button type="button" class="btn btn-primary" id="eventLocUseGps" style="white-space: nowrap;"><i class="fas fa-location-crosshairs"></i> Use my location</button>
                    </div>
                    <div id="eventLocResults" class="list-group event-loc-results mb-2" style="display: none;"></div>
                    <div id="eventLocationMap" class="event-location-map mb-2"></div>
                    <label for="location" style="font-size: 14px; font-weight: 500; margin-bottom: 6px; display: block;">Venue name / address (shown to attendees)</label>
                    <div class="input-icon">
                        <i class="fas fa-map-marker-alt"></i>
                        <input 
                            type="text"
                            id="location"
                            name="location"
                            class="form-control"
                            placeholder="e.g. Main campus gym"
                            value="<?= htmlspecialchars($_POST['location'] ?? '') ?>"
                            required
                            maxlength="255"
                            autocomplete="off"
                        >
                    </div>
                    <?php if ($eventsHasGeo): ?>
                    <small class="text-muted d-block mt-1">After migrating the database, a map position is required to submit.</small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="max_capacity">Max attendees (RSVP cap)</label>
                    <input
                        type="number"
                        id="max_capacity"
                        name="max_capacity"
                        class="form-control"
                        min="1"
                        max="50000"
                        placeholder="Leave empty for unlimited"
                        value="<?= htmlspecialchars($_POST['max_capacity'] ?? '') ?>"
                    >
                    <small class="text-muted d-block mt-1">Students cannot register once this limit is reached. Requires database migration <code>school_events_high_value_features.sql</code> for the column.</small>
                </div>
                
                <div class="form-group">
                    <label for="department">
                        Department / Audience <span class="required">*</span>
                    </label>
                    <select id="department" name="department" class="form-control" required>
                        <?php $selectedDept = $_POST['department'] ?? 'ALL'; ?>
                        <option value="ALL" <?= $selectedDept === 'ALL' ? 'selected' : '' ?>>All Departments</option>
                        <option value="High school department" <?= $selectedDept === 'High school department' ? 'selected' : '' ?>>High School Department</option>
                        <option value="College of Communication, Information and Technology" <?= $selectedDept === 'College of Communication, Information and Technology' ? 'selected' : '' ?>>College of Communication, Information and Technology</option>
                        <option value="College of Accountancy and Business" <?= $selectedDept === 'College of Accountancy and Business' ? 'selected' : '' ?>>College of Accountancy and Business</option>
                        <option value="School of Law and Political Science" <?= $selectedDept === 'School of Law and Political Science' ? 'selected' : '' ?>>School of Law and Political Science</option>
                        <option value="College of Education" <?= $selectedDept === 'College of Education' ? 'selected' : '' ?>>College of Education</option>
                        <option value="College of Nursing and Allied health sciences" <?= $selectedDept === 'College of Nursing and Allied health sciences' ? 'selected' : '' ?>>College of Nursing and Allied health sciences</option>
                        <option value="College of Hospitality Management" <?= $selectedDept === 'College of Hospitality Management' ? 'selected' : '' ?>>College of Hospitality Management</option>
                    </select>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Create Event
                    </button>
                    <a href="<?= BASE_URL ?>/backend/auth/dashboardorganizer.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Message Modal (replaces alert) -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel"><i class="fas fa-exclamation-circle me-2"></i>Please fix the following</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="messageModalBody" class="mb-0"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="<?= htmlspecialchars(BASE_URL) ?>/assets/js/event_location_picker.js"></script>

    <script>
        function showMessageModal(msg) {
            var el = document.getElementById('messageModalBody');
            if (el) el.textContent = msg;
            var modal = new bootstrap.Modal(document.getElementById('messageModal'));
            modal.show();
        }

        var EVENTIFY_GEOCODE_URL = <?= json_encode(BASE_URL . '/backend/auth/geocode_proxy.php') ?>;

        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.initEventLocationPicker === 'function' && window.L) {
                window.initEventLocationPicker({
                    mapElId: 'eventLocationMap',
                    latInputId: 'event_latitude',
                    lngInputId: 'event_longitude',
                    addressInputId: 'location',
                    searchInputId: 'eventLocSearch',
                    searchBtnId: 'eventLocSearchBtn',
                    useLocationBtnId: 'eventLocUseGps',
                    resultsElId: 'eventLocResults',
                    geocodeBase: EVENTIFY_GEOCODE_URL
                });
            }
        });

        document.getElementById('createEventForm').addEventListener('submit', function(e) {
            const form = e.target;
            const title = document.getElementById('title').value.trim();
            const date = document.getElementById('date').value;
            const startTime = (document.getElementById('start_time') || {}).value;
            const location = document.getElementById('location').value.trim();
            const requireGeo = form.getAttribute('data-require-geo') === '1';
            const latEl = document.getElementById('event_latitude');
            const lngEl = document.getElementById('event_longitude');
            
            if (!title) {
                e.preventDefault();
                showMessageModal('Please enter an event title.');
                document.getElementById('title').focus();
                return false;
            }
            
            if (!date) {
                e.preventDefault();
                showMessageModal('Please select an event date.');
                document.getElementById('date').focus();
                return false;
            }

            if (!startTime) {
                e.preventDefault();
                showMessageModal('Please select a start time.');
                var st = document.getElementById('start_time');
                if (st) st.focus();
                return false;
            }
            
            if (!location) {
                e.preventDefault();
                showMessageModal('Please enter a venue name or address.');
                document.getElementById('location').focus();
                return false;
            }

            if (requireGeo && latEl && lngEl) {
                var lat = latEl.value.trim();
                var lng = lngEl.value.trim();
                if (!lat || !lng || isNaN(parseFloat(lat)) || isNaN(parseFloat(lng))) {
                    e.preventDefault();
                    showMessageModal('Please set the venue on the map, search and pick a result, or use your location.');
                    return false;
                }
            }
            
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const eventDate = new Date(date);
            
            if (eventDate < today) {
                e.preventDefault();
                showMessageModal('Event date cannot be in the past.');
                document.getElementById('date').focus();
                return false;
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>
