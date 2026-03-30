<?php

if (!defined('BASE_URL')) {
    define('BASE_URL', '/school_events');
}

// Fallbacks ni if ang variables wa na set sa controller or if ang data wa na pasa
$user_name = $user_name ?? 'Student';
$user      = $user ?? ['name' => 'Student', 'user_id' => 'N/A', 'department' => null];
$events    = $events ?? []; // always an array
$msg       = $msg ?? '';
$department = $user['department'] ?? null;
$registered_event_ids   = $registered_event_ids ?? [];
$reg_count_by_event     = $reg_count_by_event ?? [];
$feedback_submitted_ids = $feedback_submitted_ids ?? [];
$student_notifications  = $student_notifications ?? [];
$unread_notif_count     = isset($unread_notif_count) ? (int) $unread_notif_count : 0;
$attendance_records = $attendance_records ?? [];
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
        <button class="nav-btn" title="Calendar">
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

            <button type="submit" class="btn btn-primary w-100">Save Info</button>
        </form>
    </div>
</div>

<!-- Pass PHP events to JS -->
<script>
window.BASE_URL = <?= json_encode(BASE_URL) ?>;
window.csrfToken = <?= json_encode(function_exists('csrf_token') ? csrf_token() : '') ?>;
window.studentEvents = <?= json_encode(array_map(function ($e) use ($registered_event_ids, $reg_count_by_event, $feedback_submitted_ids) {
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
            'max_capacity'        => $maxCap,
            'registration_count'  => $regCount,
            'is_registered'       => $isRegistered,
            'has_feedback'        => $hasFeedback,
        ],
    ];
}, $events), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;

window.currentUser = {
    name: <?= json_encode($user_name) ?>,
    id: <?= json_encode($_SESSION['user_id'] ?? 0) ?>,
    department: <?= json_encode($department) ?>
};
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

<!-- Settings Modal (placeholder) -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="settingsModalLabel">Settings</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0 text-muted">Coming soon.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
          <li>Click Profile to update your info.</li>
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
                      <?= htmlspecialchars($event['department'] === 'ALL' ? 'All Departments' : $event['department']) ?>
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
                      <?= htmlspecialchars($event['department'] === 'ALL' ? 'All Departments' : $event['department']) ?>
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
