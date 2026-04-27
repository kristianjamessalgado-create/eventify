<?php

if (!defined('BASE_URL')) {
    define('BASE_URL', '/school_events');
}

// Fallbacks ni if ang variables wa na set sa controller or if ang data wa na pasa
$user_name = $user_name ?? 'Student';
$user      = $user ?? ['name' => 'Student', 'user_id' => 'N/A', 'department' => null, 'student_course' => null, 'student_year_level' => null, 'student_academic_year' => null];
$events    = $events ?? []; // always an array
$msg       = $msg ?? '';
$error     = $error ?? '';
$department = $user['department'] ?? null;
$registered_event_ids   = $registered_event_ids ?? [];
$reg_count_by_event     = $reg_count_by_event ?? [];
$feedback_submitted_ids = $feedback_submitted_ids ?? [];
$pending_urgent_feedback_events = $pending_urgent_feedback_events ?? [];
$student_notifications  = $student_notifications ?? [];
$unread_notif_count     = isset($unread_notif_count) ? (int) $unread_notif_count : 0;
$attendance_records = $attendance_records ?? [];
$attended_event_ids = $attended_event_ids ?? [];
$openModal = strtolower((string)($_GET['open_modal'] ?? ''));
$today = date('Y-m-d');
$upcoming_events = array_values(array_filter($events ?? [], function ($e) use ($today) {
    $d = $e['date'] ?? '';
    return $d !== '' && $d >= $today;
}));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - EVENTIFY</title>

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

   
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/dashboard_student.css">
</head>
<body>


<nav class="top-navbar">
    <div class="navbar-left">
        <button type="button" class="nav-btn sidebar-toggle-mobile" id="sidebarToggleMobile" aria-label="Open menu" title="Menu">
            <i class="fas fa-bars"></i>
        </button>
        <div class="brand-logo">
            <i class="fas fa-calendar-alt"></i>
            <span>EVENTIFY</span>
        </div>
    </div>
    <div class="navbar-right">
        <button type="button" class="nav-btn" id="topCalendarShortcutBtn" title="Go to today">
            <i class="fas fa-calendar"></i>
        </button>
        <button
            class="nav-btn position-relative"
            title="Notifications"
            data-bs-toggle="modal"
            data-bs-target="#studentNotificationsModal"
        >
            <i class="fas fa-bell"></i>
            <?php if ($unread_notif_count > 0): ?>
                <span
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                    style="font-size: 0.55rem;"
                    title="Unread notifications"
                >
                    <?= $unread_notif_count > 99 ? '99+' : $unread_notif_count ?>
                </span>
            <?php endif; ?>
        </button>

        <!-- Profile dropdown -->
        <div class="dropdown">
            <button class="profile-avatar profile-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="<?= htmlspecialchars($user_name) ?>">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($user['profile_picture']) ?>" alt="<?= htmlspecialchars($user_name) ?>" class="profile-avatar-img">
                <?php else: ?>
                    <?= strtoupper(substr($user_name, 0, 1)) ?>
                <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end profile-menu">
                <li class="px-3 py-2">
                    <div class="small text-muted">Signed in as</div>
                    <div class="fw-semibold"><?= htmlspecialchars($user_name) ?></div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="#" onclick="openProfileModal(); return false;">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#settingsModal">
                        <i class="fas fa-cog me-2"></i> Settings
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#helpModal">
                        <i class="fas fa-circle-question me-2"></i> Help
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#studentAttendanceModal">
                        <i class="fas fa-clipboard-check me-2"></i> My attendance
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Layout -->
<div class="dashboard-layout">
    <div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>
    <!-- Left Sidebar (drawer on mobile) -->
    <aside class="sidebar" id="studentSidebar">
        <button type="button" class="sidebar-close-mobile" id="sidebarCloseMobile" aria-label="Close menu"><i class="fas fa-times"></i></button>
        <!-- User Info Card -->
        <div class="user-info-card">
            <div class="user-avatar-large">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($user['profile_picture']) ?>" alt="<?= htmlspecialchars($user_name) ?>" class="profile-picture-img">
                <?php else: ?>
                    <?= strtoupper(substr($user_name, 0, 1)) ?>
                <?php endif; ?>
            </div>
            <h3 class="user-name"><?= htmlspecialchars($user_name) ?></h3>
            <p class="user-id">ID: <?= htmlspecialchars($user['user_id'] ?? 'N/A') ?></p>
            <?php if ($department): ?>
                <span class="user-dept-badge"><?= htmlspecialchars($department) ?></span>
            <?php else: ?>
                <span class="user-dept-badge warning">No Department Set</span>
            <?php endif; ?>
        </div>

        <!-- Mini Calendar -->
        <div class="mini-calendar-widget">
            <div class="mini-calendar-header">
                <button class="mini-cal-nav" id="miniCalPrev"><i class="fas fa-chevron-left"></i></button>
                <span class="mini-cal-month" id="miniCalMonth"><?= date('F Y') ?></span>
                <button class="mini-cal-nav" id="miniCalNext"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="mini-calendar-grid" id="miniCalendar"></div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3 class="section-title">QUICK ACTIONS</h3>
            <button
                type="button"
                class="action-btn w-100 text-start border-0 bg-transparent"
                data-bs-toggle="modal"
                data-bs-target="#scanQRModal"
            >
                <i class="fas fa-qrcode"></i>
                <span>Scan QR</span>
            </button>
            <button
                type="button"
                class="action-btn w-100 text-start border-0 bg-transparent"
                data-bs-toggle="modal"
                data-bs-target="#studentUpcomingEventsModal"
            >
                <i class="fas fa-calendar-check"></i>
                <span>Upcoming Events</span>
            </button>
            <button
                type="button"
                class="action-btn w-100 text-start border-0 bg-transparent"
                data-bs-toggle="modal"
                data-bs-target="#studentAttendanceModal"
            >
                <i class="fas fa-clipboard-check"></i>
                <span>Attendance record</span>
                <?php if (count($attendance_records) > 0): ?>
                    <span class="badge bg-success ms-1"><?= count($attendance_records) ?></span>
                <?php endif; ?>
            </button>
            <button class="action-btn" onclick="openProfileModal()">
                <i class="fas fa-user-edit"></i>
                <span>Edit Profile</span>
            </button>
            <a href="#" class="action-btn logout-btn" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>

        <!-- My Department Info -->
        <?php if ($department): ?>
        <div class="department-info">
            <h3 class="section-title">MY DEPARTMENT</h3>
            <div class="department-badge-large" data-dept="<?= htmlspecialchars($department) ?>">
                <div class="dept-avatar"><?= strtoupper(substr($department, 0, 1)) ?></div>
                <span><?= htmlspecialchars($department) ?></span>
            </div>
            <p class="dept-note">You only see events open to your department or all departments.</p>
        </div>
        <?php endif; ?>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Success/Error Message -->
        <?php if ($msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong><?= htmlspecialchars($msg) ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><?= htmlspecialchars($error) ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Upcoming Events List (at top for quick scan) -->
        <div class="upcoming-events-section">
            <h3 class="section-heading">Upcoming Events</h3>
            <?php if (!empty($upcoming_events)): ?>
                <div class="events-list">
                    <?php foreach (array_slice($upcoming_events, 0, 5) as $event): ?>
                        <div class="event-item student-event-link" data-event-id="<?= isset($event['id']) ? (int)$event['id'] : '' ?>" role="button">
                            <div class="event-date-badge">
                                <span class="event-month"><?= date('M', strtotime($event['date'])) ?></span>
                                <span class="event-day"><?= date('d', strtotime($event['date'])) ?></span>
                            </div>
                            <div class="event-details">
                                <h4 class="event-title"><?= htmlspecialchars($event['title'] ?? 'Untitled') ?></h4>
                                <p class="event-meta">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($event['location'] ?? 'TBA') ?>
                                </p>
                                <?php if (!empty($event['description'])): ?>
                                    <p class="event-desc"><?= htmlspecialchars(substr($event['description'], 0, 100)) ?><?= strlen($event['description']) > 100 ? '...' : '' ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-events">No upcoming events for your department.</p>
            <?php endif; ?>
        </div>

        <!-- Calendar Controls -->
        <div class="calendar-controls">
            <div class="controls-left">
                <button class="control-nav" id="calPrev"><i class="fas fa-chevron-left"></i></button>
                <h2 class="calendar-title" id="calendarTitle">September, 2026</h2>
                <button class="control-nav" id="calNext"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="controls-right">
                <button class="view-btn active" data-view="dayGridMonth">Month</button>
                <button class="view-btn" data-view="timeGridWeek">Week</button>
                <button class="view-btn" data-view="timeGridDay">Day</button>
                <button class="view-btn" data-view="today">Today</button>
            </div>
        </div>
        <div class="student-calendar-legend" id="studentCalendarLegend">
            <span><i class="fas fa-circle text-success me-1"></i>Active</span>
            <span><i class="fas fa-circle text-warning me-1"></i>Upcoming</span>
            <span><i class="fas fa-circle text-secondary me-1"></i>Closed/Completed</span>
            <span><i class="fas fa-circle text-danger me-1"></i>Rejected</span>
        </div>

        <!-- FullCalendar Container -->
        <div class="calendar-container">
            <div id="student-calendar"></div>
        </div>
    </main>
</div>

<!-- Profile Modal -->
<div id="profileModal" class="profile-modal">
    <div class="profile-modal-content">
        <span class="profile-close" onclick="closeProfileModal()">&times;</span>
        <h2>My Information</h2>
        <form id="profileForm" action="<?= BASE_URL ?>/backend/auth/update_student_profile.php" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault(); confirmProfileChanges(this);">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="profilePictureModal">Profile Picture</label>
                <div class="profile-picture-preview-container">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Current profile picture" id="profilePicturePreview" class="profile-picture-preview profile-picture-clickable" onclick="openProfilePicFullscreen(this.src)" title="Click to view full screen">
                    <?php else: ?>
                        <div class="profile-picture-placeholder" id="profilePicturePreview">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <input
                    type="file"
                    id="profilePictureModal"
                    name="profile_picture"
                    accept="image/jpeg,image/png,image/gif,image/webp"
                    class="form-control-file"
                    onchange="previewProfilePicture(this)"
                >
                <small class="text-muted">JPG, PNG, GIF, or WEBP (max 5MB)</small>
            </div>

            <div class="form-group">
                <label for="fullNameModal">Full Name</label>
                <input
                    type="text"
                    id="fullNameModal"
                    name="name"
                    value="<?= htmlspecialchars($user['name'] ?? $user_name) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Student ID</label>
                <input
                    type="text"
                    value="<?= htmlspecialchars($user['user_id'] ?? 'N/A') ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="studentCourseModal">Course / program <span class="text-danger">*</span></label>
                <select id="studentCourseModal" name="student_course" required>
                    <?php
                    $courseOpts = eventify_student_course_program_options();
                    $storedCourse = trim((string) ($user['student_course'] ?? ''));
                    $selectedCourse = ($storedCourse !== '' && isset($courseOpts[$storedCourse]) && $storedCourse !== '')
                        ? $storedCourse
                        : '';
                    foreach ($courseOpts as $cv => $clab):
                        $sel = ((string) $cv === (string) $selectedCourse) ? ' selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars((string) $cv) ?>"<?= $sel ?>><?= htmlspecialchars($clab) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Required — shown on attendance lists. Choose the program that matches your enrollment.</small>
            </div>

            <div class="form-group">
                <label for="studentDepartmentModal">Department</label>
                <?php
                $deptFromCourse = function_exists('eventify_student_course_program_department')
                    ? eventify_student_course_program_department((string)($selectedCourse ?? ''))
                    : '';
                $displayDepartment = trim((string)($deptFromCourse !== '' ? $deptFromCourse : ($user['department'] ?? '')));
                ?>
                <input
                    type="text"
                    id="studentDepartmentModal"
                    value="<?= htmlspecialchars($displayDepartment !== '' ? $displayDepartment : 'Department will be set from selected course') ?>"
                    readonly
                >
                <small class="text-muted">Auto-assigned from your selected course / program.</small>
            </div>

            <div class="form-group">
                <label for="studentYearLevelModal">Year level</label>
                <select id="studentYearLevelModal" name="student_year_level">
                    <?php foreach (eventify_student_year_level_options() as $yv => $ylab): ?>
                        <option value="<?= htmlspecialchars($yv) ?>" <?= ((string) ($user['student_year_level'] ?? '') === (string) $yv) ? 'selected' : '' ?>><?= htmlspecialchars($ylab) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="studentAcademicYearModal">School year (AY)</label>
                <select id="studentAcademicYearModal" name="student_academic_year">
                    <?php foreach (eventify_student_academic_year_options() as $yv => $ylab): ?>
                        <option value="<?= htmlspecialchars($yv) ?>" <?= ((string) ($user['student_academic_year'] ?? '') === (string) $yv) ? 'selected' : '' ?>><?= htmlspecialchars($ylab) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Academic year (e.g. 2025-2026).</small>
            </div>

            <button type="submit" class="btn btn-primary w-100">Save Info</button>
        </form>
    </div>
</div>

<!-- Pass PHP events to JS -->
<script>
window.BASE_URL = <?= json_encode(BASE_URL) ?>;
window.csrfToken = <?= json_encode(function_exists('csrf_token') ? csrf_token() : '') ?>;
window.studentEvents = <?= json_encode(array_map(function ($e) use ($registered_event_ids, $reg_count_by_event, $feedback_submitted_ids, $attended_event_ids) {
    $date = trim($e['date'] ?? '');
    $startTime = isset($e['start_time']) ? trim($e['start_time']) : '';
    $endTime = isset($e['end_time']) && $e['end_time'] !== null && $e['end_time'] !== '' ? trim($e['end_time']) : '';
    $hasStartTime = $date !== '' && $startTime !== '';
    $hasEndTime = $date !== '' && $endTime !== '';
    // FullCalendar week/day views need ISO8601: date with "T" and time (e.g. 2025-03-15T09:00:00)
    if ($hasStartTime) {
        $start = $date . 'T' . $startTime;
        $end = $hasEndTime ? ($date . 'T' . $endTime) : null;
        if ($end === null) {
            $startDt = \DateTime::createFromFormat('Y-m-d H:i:s', $date . ' ' . $startTime);
            if (!$startDt) {
                $startDt = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $startTime);
            }
            $end = $startDt ? $startDt->modify('+1 hour')->format('Y-m-d\TH:i:s') : ($date . 'T23:59:59');
        }
        $allDay = false;
    } else {
        $start = $date;
        $end = $date;
        $allDay = true;
    }
    $eid = isset($e['id']) ? (int) $e['id'] : 0;
    $mc = $e['max_capacity'] ?? null;
    $maxCap = ($mc !== null && $mc !== '') ? (int) $mc : null;
    $regCount = $reg_count_by_event[$eid] ?? 0;
    $isRegistered = in_array($eid, $registered_event_ids, true);
    $hasFeedback = in_array($eid, $feedback_submitted_ids, true);
    $attended = in_array($eid, $attended_event_ids, true);

    return [
        'id'     => $e['id'] ?? null,
        'title'  => $e['title'] ?? 'Untitled',
        'start'  => $start,
        'end'    => $end,
        'allDay' => $allDay,
        'extendedProps' => [
            'event_id'            => $eid,
            'event_date_ymd'      => $date,
            'location'            => $e['location'] ?? '',
            'description'         => $e['description'] ?? '',
            'start_time'          => $e['start_time'] ?? null,
            'end_time'            => $e['end_time'] ?? null,
            'department'          => $e['department'] ?? 'ALL',
            'department_display'  => eventify_format_department_label((string) ($e['department'] ?? 'ALL')),
            'max_capacity'        => $maxCap,
            'registration_count'  => $regCount,
            'is_registered'       => $isRegistered,
            'has_feedback'        => $hasFeedback,
            'attended'            => $attended,
            'status'              => $e['status'] ?? '',
        ],
    ];
}, $events), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;

window.currentUser = {
    name: <?= json_encode($user_name) ?>,
    id: <?= json_encode($_SESSION['user_id'] ?? 0) ?>,
    department: <?= json_encode($department) ?>
};
window.__studentSettings = <?= json_encode($studentSettings ?? []) ?>;
window.__studentOpenModal = <?= json_encode($openModal) ?>;
window.__studentCourseDepartmentMap = <?= json_encode(function_exists('eventify_student_course_program_department_map') ? eventify_student_course_program_department_map() : [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;
window.__studentPendingUrgentFeedback = <?= json_encode($pending_urgent_feedback_events, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;
</script>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">Are you sure you want to logout?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a href="<?= BASE_URL ?>/backend/auth/logout.php" class="btn btn-danger">Logout</a>
      </div>
    </div>
  </div>
</div>

<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form id="studentSettingsForm" method="POST" action="<?= BASE_URL ?>/backend/auth/update_student_settings.php">
        <?= csrf_field() ?>
        <div class="modal-header">
          <h5 class="modal-title" id="settingsModalLabel"><i class="fas fa-cog me-2"></i>Student Settings</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="student-settings-section">
            <h6>Security</h6>
            <p class="small text-muted mb-2">Manage account security options.</p>
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#studentChangePasswordModal">
              <i class="fas fa-key me-1"></i>Change Password
            </button>
          </div>

          <div class="student-settings-section">
            <h6>Notifications</h6>
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="event_reminders" name="event_reminders" value="1" <?= !empty($studentSettings['event_reminders']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="event_reminders">Event reminders</label>
            </div>
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="rsvp_updates" name="rsvp_updates" value="1" <?= !empty($studentSettings['rsvp_updates']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="rsvp_updates">RSVP updates</label>
            </div>
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="announcement_notifications" name="announcement_notifications" value="1" <?= !empty($studentSettings['announcement_notifications']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="announcement_notifications">Announcements</label>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="notif_channel_email" name="notif_channel_email" value="1" <?= !empty($studentSettings['notif_channel_email']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="notif_channel_email">Enable email channel</label>
            </div>
          </div>

          <div class="student-settings-section">
            <h6>Calendar & Display</h6>
            <div class="row g-2">
              <div class="col-md-6">
                <label class="form-label small" for="default_calendar_view">Default Calendar View</label>
                <?php $stDefaultView = (string)($studentSettings['default_calendar_view'] ?? 'dayGridMonth'); ?>
                <select class="form-select" id="default_calendar_view" name="default_calendar_view">
                  <option value="dayGridMonth" <?= $stDefaultView === 'dayGridMonth' ? 'selected' : '' ?>>Month</option>
                  <option value="timeGridWeek" <?= $stDefaultView === 'timeGridWeek' ? 'selected' : '' ?>>Week</option>
                  <option value="timeGridDay" <?= $stDefaultView === 'timeGridDay' ? 'selected' : '' ?>>Day</option>
                </select>
              </div>
              <div class="col-md-6 d-flex align-items-end">
                <div class="form-check form-switch mb-2">
                  <input class="form-check-input" type="checkbox" id="show_calendar_legend" name="show_calendar_legend" value="1" <?= !empty($studentSettings['show_calendar_legend']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="show_calendar_legend">Show event legend</label>
                </div>
              </div>
            </div>
          </div>

          <div class="student-settings-section">
            <h6>RSVP Preferences</h6>
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="auto_add_rsvp_calendar" name="auto_add_rsvp_calendar" value="1" <?= !empty($studentSettings['auto_add_rsvp_calendar']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="auto_add_rsvp_calendar">Auto-add RSVP events to my calendar</label>
            </div>
            <div class="row g-2">
              <div class="col-md-6">
                <label class="form-label small" for="reminder_timing">Reminder timing</label>
                <?php $stReminderTiming = (string)($studentSettings['reminder_timing'] ?? '1_day'); ?>
                <select class="form-select" id="reminder_timing" name="reminder_timing">
                  <option value="1_day" <?= $stReminderTiming === '1_day' ? 'selected' : '' ?>>1 day before</option>
                  <option value="1_hour" <?= $stReminderTiming === '1_hour' ? 'selected' : '' ?>>1 hour before</option>
                  <option value="30_min" <?= $stReminderTiming === '30_min' ? 'selected' : '' ?>>30 minutes before</option>
                </select>
              </div>
              <div class="col-md-6 d-flex align-items-end">
                <div class="form-check form-switch mb-2">
                  <input class="form-check-input" type="checkbox" id="hide_past_rsvped" name="hide_past_rsvped" value="1" <?= !empty($studentSettings['hide_past_rsvped']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="hide_past_rsvped">Hide past RSVPed events</label>
                </div>
              </div>
            </div>
          </div>

          <div class="student-settings-section">
            <h6>Privacy</h6>
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="share_profile_with_organizers" name="share_profile_with_organizers" value="1" <?= !empty($studentSettings['share_profile_with_organizers']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="share_profile_with_organizers">Show my profile info to organizers in attendee lists</label>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="allow_photo_tagging" name="allow_photo_tagging" value="1" <?= !empty($studentSettings['allow_photo_tagging']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="allow_photo_tagging">Allow photo tagging consent</label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="studentSettingsUpdateBtn"><i class="fas fa-save me-1"></i>Update Settings</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Change Password Modal (Student) -->
<div class="modal fade" id="studentChangePasswordModal" tabindex="-1" aria-labelledby="studentChangePasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= BASE_URL ?>/backend/auth/change_password.php">
        <?= csrf_field() ?>
        <input type="hidden" name="return_to" value="student_dashboard">
        <div class="modal-header">
          <h5 class="modal-title" id="studentChangePasswordModalLabel"><i class="fas fa-key me-2"></i>Change Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label small" for="studentCurrentPassword">Current Password</label>
            <div class="password-input-wrap">
              <input type="password" class="form-control" id="studentCurrentPassword" name="current_password" required>
              <button type="button" class="password-toggle-btn" data-target="studentCurrentPassword" aria-label="Show current password">
                <i class="fas fa-eye"></i>
              </button>
            </div>
          </div>
          <div class="mb-2">
            <label class="form-label small" for="studentNewPassword">New Password</label>
            <div class="password-input-wrap">
              <input type="password" class="form-control" id="studentNewPassword" name="new_password" required>
              <button type="button" class="password-toggle-btn" data-target="studentNewPassword" aria-label="Show new password">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            <small class="text-muted">At least 8 chars, 1 uppercase, 1 special character.</small>
          </div>
          <div class="mb-0">
            <label class="form-label small" for="studentConfirmPassword">Confirm New Password</label>
            <div class="password-input-wrap">
              <input type="password" class="form-control" id="studentConfirmPassword" name="confirm_password" required>
              <button type="button" class="password-toggle-btn" data-target="studentConfirmPassword" aria-label="Show confirm password">
                <i class="fas fa-eye"></i>
              </button>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Password</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Confirm student settings update -->
<div class="modal fade" id="confirmStudentSettingsModal" tabindex="-1" aria-labelledby="confirmStudentSettingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmStudentSettingsModalLabel"><i class="fas fa-question-circle me-2 text-primary"></i>Confirm Update</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">Are you sure you want to update your settings?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <button type="button" class="btn btn-primary" id="confirmStudentSettingsYesBtn">Yes, Update</button>
      </div>
    </div>
  </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="helpModalLabel">Help</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <ul class="mb-0">
          <li>Use the mini calendar to jump to a date.</li>
          <li>Events shown are filtered by your department.</li>
          <li>Click Profile to update your info, course, year level, and school year (shown on event attendance sheets).</li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Notifications Modal -->
<div class="modal fade" id="studentNotificationsModal" tabindex="-1" aria-labelledby="studentNotificationsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="studentNotificationsModalLabel">
          <i class="fas fa-bell me-2"></i>Notifications
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="studentNotificationsModalBody">
        <h6 class="text-uppercase small text-muted mb-2">In-app messages</h6>
        <?php if (!empty($student_notifications)): ?>
          <div class="list-group mb-4">
            <?php foreach ($student_notifications as $n): ?>
              <?php
                $nid = (int) ($n['id'] ?? 0);
                $isUnread = empty($n['read_at']);
                $markUrl = BASE_URL . '/backend/auth/mark_notification_read.php?id=' . $nid;
              ?>
              <a href="<?= htmlspecialchars($markUrl) ?>" class="list-group-item list-group-item-action <?= $isUnread ? 'list-group-item-light fw-semibold' : '' ?>">
                <div class="d-flex w-100 justify-content-between align-items-start gap-2">
                  <div>
                    <div class="mb-1"><?= htmlspecialchars($n['title'] ?? 'Notification') ?></div>
                    <?php if (!empty($n['message'])): ?>
                      <div class="small text-muted"><?= nl2br(htmlspecialchars($n['message'])) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($n['created_at'])): ?>
                      <div class="small text-muted mt-1"><?= htmlspecialchars($n['created_at']) ?></div>
                    <?php endif; ?>
                  </div>
                  <?php if ($isUnread): ?>
                    <span class="badge bg-primary rounded-pill">New</span>
                  <?php endif; ?>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="small text-muted mb-4">No system notifications yet.</p>
        <?php endif; ?>

        <h6 class="text-uppercase small text-muted mb-2">Upcoming events (tap to open details)</h6>
        <?php if (!empty($upcoming_events)): ?>
          <div class="list-group">
            <?php foreach ($upcoming_events as $event): ?>
              <div class="list-group-item list-group-item-action student-event-link" data-event-id="<?= isset($event['id']) ? (int)$event['id'] : '' ?>" role="button" data-bs-dismiss="modal">
                <div class="d-flex w-100 justify-content-between">
                  <h6 class="mb-1">
                    <i class="fas fa-calendar-day me-1 text-primary"></i>
                    <?= htmlspecialchars($event['title'] ?? 'Untitled') ?>
                  </h6>
                  <?php if (!empty($event['date'])): ?>
                    <small class="text-muted">
                      <?= date('M d, Y', strtotime($event['date'])) ?>
                    </small>
                  <?php endif; ?>
                </div>
                <div class="mb-1 small text-muted">
                  <?php if (!empty($event['location'])): ?>
                    <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($event['location']) ?>
                  <?php endif; ?>
                  <?php if (!empty($event['department'])): ?>
                    <span class="badge bg-light text-dark ms-2">
                      <?= htmlspecialchars(eventify_format_department_label((string) $event['department'])) ?>
                    </span>
                  <?php endif; ?>
                </div>
                <?php if (!empty($event['description'])): ?>
                  <p class="mb-1 small">
                    <?= htmlspecialchars(mb_strimwidth($event['description'], 0, 140, '...')) ?>
                  </p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="mb-0 text-muted small">No upcoming events for your department right now.</p>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <a href="<?= BASE_URL ?>/backend/auth/mark_notification_read.php?mark_all=1" class="btn btn-outline-secondary btn-sm" id="studentNotificationsMarkAllLink">
          <i class="fas fa-check-double me-1"></i> Mark all read
        </a>
        <a href="<?= BASE_URL ?>/backend/auth/mark_notification_read.php?clear_all=1" class="btn btn-outline-danger btn-sm" onclick="return confirm('Clear all notifications? This cannot be undone.');">
          <i class="fas fa-trash me-1"></i> Clear all
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm" id="studentOpenUpcomingFromNotifBtn">
          <i class="fas fa-external-link-alt me-1"></i> View all upcoming events
        </button>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Upcoming Events Modal (same structure as Notifications: events first, then in-app messages) -->
<div class="modal fade" id="studentUpcomingEventsModal" tabindex="-1" aria-labelledby="studentUpcomingEventsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="studentUpcomingEventsModalLabel">
          <i class="fas fa-calendar-check me-2"></i>Upcoming Events
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="studentUpcomingEventsModalBody">
        <h6 class="text-uppercase small text-muted mb-2">Upcoming events <span class="fw-normal">(tap to open details)</span></h6>
        <?php if (!empty($upcoming_events)): ?>
          <div class="list-group mb-4">
            <?php foreach ($upcoming_events as $event): ?>
              <div class="list-group-item list-group-item-action student-event-link" data-event-id="<?= isset($event['id']) ? (int)$event['id'] : '' ?>" role="button" data-bs-dismiss="modal">
                <div class="d-flex w-100 justify-content-between">
                  <h6 class="mb-1">
                    <i class="fas fa-calendar-day me-1 text-primary"></i>
                    <?= htmlspecialchars($event['title'] ?? 'Untitled') ?>
                  </h6>
                  <?php if (!empty($event['date'])): ?>
                    <small class="text-muted">
                      <?= date('M d, Y', strtotime($event['date'])) ?>
                    </small>
                  <?php endif; ?>
                </div>
                <div class="mb-1 small text-muted">
                  <?php if (!empty($event['location'])): ?>
                    <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($event['location']) ?>
                  <?php endif; ?>
                  <?php if (!empty($event['department'])): ?>
                    <span class="badge bg-light text-dark ms-2">
                      <?= htmlspecialchars(eventify_format_department_label((string) $event['department'])) ?>
                    </span>
                  <?php endif; ?>
                </div>
                <?php if (!empty($event['description'])): ?>
                  <p class="mb-1 small">
                    <?= htmlspecialchars(mb_strimwidth($event['description'], 0, 180, '...')) ?>
                  </p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="small text-muted mb-4">No upcoming events for your department right now.</p>
        <?php endif; ?>

        <h6 class="text-uppercase small text-muted mb-2">In-app messages</h6>
        <?php if (!empty($student_notifications)): ?>
          <div class="list-group">
            <?php foreach ($student_notifications as $n): ?>
              <?php
                $nid = (int) ($n['id'] ?? 0);
                $isUnread = empty($n['read_at']);
                $markUrl = BASE_URL . '/backend/auth/mark_notification_read.php?id=' . $nid;
              ?>
              <a href="<?= htmlspecialchars($markUrl) ?>" class="list-group-item list-group-item-action <?= $isUnread ? 'list-group-item-light fw-semibold' : '' ?>">
                <div class="d-flex w-100 justify-content-between align-items-start gap-2">
                  <div>
                    <div class="mb-1"><?= htmlspecialchars($n['title'] ?? 'Notification') ?></div>
                    <?php if (!empty($n['message'])): ?>
                      <div class="small text-muted"><?= nl2br(htmlspecialchars($n['message'])) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($n['created_at'])): ?>
                      <div class="small text-muted mt-1"><?= htmlspecialchars($n['created_at']) ?></div>
                    <?php endif; ?>
                  </div>
                  <?php if ($isUnread): ?>
                    <span class="badge bg-primary rounded-pill">New</span>
                  <?php endif; ?>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="mb-0 small text-muted">No system notifications yet.</p>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <a href="<?= BASE_URL ?>/backend/auth/mark_notification_read.php?mark_all=1" class="btn btn-outline-secondary btn-sm">
          <i class="fas fa-check-double me-1"></i> Mark all read
        </a>
        <a href="<?= BASE_URL ?>/backend/auth/mark_notification_read.php?clear_all=1" class="btn btn-outline-danger btn-sm" onclick="return confirm('Clear all notifications? This cannot be undone.');">
          <i class="fas fa-trash me-1"></i> Clear all
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm" id="studentOpenNotificationsFromUpcomingBtn">
          <i class="fas fa-bell me-1"></i> Open notifications
        </button>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Scan QR for attendance -->
<div class="modal fade" id="scanQRModal" tabindex="-1" aria-labelledby="scanQRModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="scanQRModalLabel">
          <i class="fas fa-qrcode me-2"></i>Scan event QR for attendance
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="scanQRModalClose"></button>
      </div>
      <div class="modal-body">
        <div id="scanQRVideoContainer" class="position-relative bg-dark rounded overflow-hidden" style="min-height: 260px;">
          <video id="scanQRVideo" playsinline muted style="width:100%; height:auto; display:block;"></video>
          <canvas id="scanQRCanvas" style="display:none;"></canvas>
          <div id="scanQRPlaceholder" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white">
            <span><i class="fas fa-camera fa-2x mb-2 d-block"></i>Starting camera…</span>
          </div>
        </div>
        <p id="scanQRStatus" class="small text-muted mt-2 mb-0">Position the event QR code within the frame.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Attendance record (proof of check-in) -->
<div class="modal fade" id="studentAttendanceModal" tabindex="-1" aria-labelledby="studentAttendanceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="studentAttendanceModalLabel">
          <i class="fas fa-clipboard-check me-2"></i>My attendance
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted mb-2">Events you have checked into (QR scan). This is your record of attendance.</p>
        <?php if (!empty($attendance_records)): ?>
          <?php
          $total_attended = count($attendance_records);
          $first_checkin = $attendance_records[array_key_last($attendance_records)]['time_in'] ?? null;
          $first_year = $first_checkin ? date('Y', strtotime($first_checkin)) : null;
          $this_year = date('Y');
          $this_year_attended = 0;
          foreach ($attendance_records as $rec) {
              if (!empty($rec['time_in']) && date('Y', strtotime($rec['time_in'])) === $this_year) {
                  $this_year_attended++;
              }
          }
          ?>
          <div class="mb-3 small">
            <span class="badge bg-primary me-2">
              <i class="fas fa-check me-1"></i><?= $total_attended ?> total event<?= $total_attended === 1 ? '' : 's' ?> attended
            </span>
            <span class="badge bg-info text-dark">
              <i class="fas fa-calendar me-1"></i><?= $this_year_attended ?> this year
            </span>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Event</th>
                  <th>Event date</th>
                  <th>Location</th>
                  <th>Check-in time</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($attendance_records as $rec): ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($rec['event_title'] ?? 'Event') ?></strong></td>
                    <td><?= !empty($rec['event_date']) ? date('M j, Y', strtotime($rec['event_date'])) : '—' ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($rec['event_location'] ?? '—') ?></td>
                    <td>
                      <span class="badge bg-success">
                        <i class="fas fa-check me-1"></i>
                        <?= !empty($rec['time_in']) ? date('M j, Y g:i A', strtotime($rec['time_in'])) : '—' ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="mb-0 text-muted">You have not checked in to any event yet. Scan the event QR code at the venue to confirm your attendance.</p>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Urgent post-event feedback prompt -->
<div class="modal fade" id="studentUrgentFeedbackModal" tabindex="-1" aria-labelledby="studentUrgentFeedbackModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-warning" style="border-width: 2px;">
      <div class="modal-header text-bg-warning">
        <h5 class="modal-title" id="studentUrgentFeedbackModalLabel"><i class="fas fa-bullhorn me-2"></i>Feedback needed</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="studentUrgentFeedbackModalBody">
        <p class="mb-0 text-muted">Loading…</p>
      </div>
      <div class="modal-footer flex-wrap gap-2">
        <button type="button" class="btn btn-outline-secondary" id="studentUrgentFeedbackSnoozeBtn">Remind me in 4 hours</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventDetailsModalLabel"><i class="fas fa-calendar-alt me-2"></i>Event Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="eventDetailsModalBody">
        <p class="mb-0 text-muted">Loading...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Profile Changes Confirmation Modal -->
<div class="modal fade" id="confirmProfileChangesModal" tabindex="-1" aria-labelledby="confirmProfileChangesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmProfileChangesModalLabel">
                    <i class="fas fa-exclamation-circle me-2"></i>Confirm Changes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmProfileChangesMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="confirmProfileChangesBtn">
                    <i class="fas fa-check me-1"></i> Yes, Proceed
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Profile Picture Full-Screen Viewer -->
<div class="profile-pic-fullscreen" id="profilePicFullscreen" style="display:none;">
    <div class="profile-pic-fullscreen-overlay" onclick="closeProfilePicFullscreen()"></div>
    <button class="profile-pic-fullscreen-close" onclick="closeProfilePicFullscreen()" aria-label="Close">
        <i class="fas fa-times"></i>
    </button>
    <div class="profile-pic-fullscreen-content">
        <img id="profilePicFullscreenImg" src="" alt="Profile picture">
    </div>
</div>

<!-- jsQR for QR code decoding -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<!-- Dashboard Scripts -->
<script src="<?= BASE_URL ?>/assets/js/dashboard_student.js"></script>

<script>
// Profile picture preview function
function previewProfilePicture(input) {
    const preview = document.getElementById('profilePicturePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
                preview.classList.add('profile-picture-clickable');
                preview.onclick = function() { openProfilePicFullscreen(preview.src); };
            } else {
                const img = document.createElement('img');
                img.id = 'profilePicturePreview';
                img.className = 'profile-picture-preview profile-picture-clickable';
                img.src = e.target.result;
                img.alt = 'Profile picture preview';
                img.onclick = function() { openProfilePicFullscreen(img.src); };
                preview.parentNode.replaceChild(img, preview);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

var pendingFormSubmission = null;

function confirmProfileChanges(form) {
    var fileInput = form.querySelector('input[name="profile_picture"]');
    var hasNewPicture = fileInput && fileInput.files && fileInput.files.length > 0;
    var msg = hasNewPicture
        ? 'Are you sure you want to change your profile picture? Your current picture will be replaced.'
        : 'Are you sure you want to save your changes?';
    
    // Store the form for later submission
    pendingFormSubmission = form;
    
    // Set the message in the modal
    document.getElementById('confirmProfileChangesMessage').textContent = msg;
    
    // Show the modal using Bootstrap
    var modalEl = document.getElementById('confirmProfileChangesModal');
    var modal = new bootstrap.Modal(modalEl, {
        backdrop: true,
        keyboard: true
    });
    modal.show();
    
    // Ensure modal and backdrop have higher z-index after showing
    setTimeout(function() {
        var backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.style.zIndex = '1199';
        }
        modalEl.style.zIndex = '1200';
    }, 10);
}

// Handle confirm button click
document.addEventListener('DOMContentLoaded', function() {
    var confirmBtn = document.getElementById('confirmProfileChangesBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (pendingFormSubmission) {
                // Hide the modal first
                var modal = bootstrap.Modal.getInstance(document.getElementById('confirmProfileChangesModal'));
                if (modal) {
                    modal.hide();
                }
                // Submit the form
                pendingFormSubmission.submit();
                pendingFormSubmission = null;
            }
        });
    }
});

function openProfilePicFullscreen(src) {
    if (!src) return;
    var el = document.getElementById('profilePicFullscreen');
    var img = document.getElementById('profilePicFullscreenImg');
    if (el && img) {
        img.src = src;
        el.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeProfilePicFullscreen() {
    var el = document.getElementById('profilePicFullscreen');
    if (el) {
        el.style.display = 'none';
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeProfilePicFullscreen();
});

document.addEventListener('DOMContentLoaded', function () {
    var notifModal = document.getElementById('studentNotificationsModal');
    var openUpcomingFromNotifBtn = document.getElementById('studentOpenUpcomingFromNotifBtn');
    var openNotifFromUpcomingBtn = document.getElementById('studentOpenNotificationsFromUpcomingBtn');
    var upcomingModal = document.getElementById('studentUpcomingEventsModal');

    if (openUpcomingFromNotifBtn && notifModal && upcomingModal) {
        openUpcomingFromNotifBtn.addEventListener('click', function () {
            var notifInstance = bootstrap.Modal.getInstance(notifModal);
            if (notifInstance) {
                notifInstance.hide();
            }
            setTimeout(function () {
                bootstrap.Modal.getOrCreateInstance(upcomingModal).show();
            }, 300);
        });
    }

    if (openNotifFromUpcomingBtn && notifModal && upcomingModal) {
        openNotifFromUpcomingBtn.addEventListener('click', function () {
            var upInstance = bootstrap.Modal.getInstance(upcomingModal);
            if (upInstance) {
                upInstance.hide();
            }
            setTimeout(function () {
                bootstrap.Modal.getOrCreateInstance(notifModal).show();
            }, 300);
        });
    }
});
</script>

</body>
</html>
