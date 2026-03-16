<?php
$user = $user ?? ['name' => $user_name ?? 'Organizer', 'profile_picture' => null];
$msg = $msg ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard - EVENTIFY</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/dashboardorganizer.css">
</head>
<body>

<!-- Top Navigation Bar -->
<nav class="top-navbar">
    <div class="navbar-left">
        <div class="brand-logo">
            <i class="fas fa-calendar-alt"></i>
            <span>EVENTIFY</span>
        </div>
    </div>
    <div class="navbar-right">
        <button
            type="button"
            class="nav-btn create-btn"
            title="Create Event"
            data-bs-toggle="modal"
            data-bs-target="#createEventModal"
        >
            <i class="fas fa-plus"></i>
        </button>
        <button class="nav-btn" type="button" title="Calendar">
            <i class="fas fa-calendar"></i>
        </button>
        <?php $org_notifications = $organizer_notifications ?? []; ?>
        <div class="dropdown">
            <button class="nav-btn position-relative dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                <i class="fas fa-bell"></i>
                <?php if (count($org_notifications) > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.55rem;"><?= count($org_notifications) ?></span>
                <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 320px; max-width: 90vw;">
                <li class="px-3 py-2 border-bottom">
                    <strong><i class="fas fa-bell me-2"></i>Notifications</strong>
                </li>
                <?php if (empty($org_notifications)): ?>
                    <li class="px-3 py-4 text-muted small text-center">No new notifications.</li>
                <?php else: ?>
                    <?php foreach ($org_notifications as $n): ?>
                        <li>
                            <a class="dropdown-item py-2 text-decoration-none" href="<?= BASE_URL ?>/backend/auth/mark_notification_read.php?id=<?= (int)$n['id'] ?>">
                                <div class="d-flex w-100">
                                    <span class="me-2"><?= (($n['type'] ?? '') === 'event_approved') ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>'; ?></span>
                                    <div class="flex-grow-1 small">
                                        <div class="fw-semibold"><?= htmlspecialchars($n['title'] ?? '') ?></div>
                                        <?php if (!empty($n['message'])): ?>
                                            <div class="text-muted"><?= htmlspecialchars(mb_strimwidth($n['message'], 0, 80, '...')) ?></div>
                                        <?php endif; ?>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= date('M j, g:i A', strtotime($n['created_at'] ?? 'now')) ?></div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-0"></li>
                    <?php endforeach; ?>
                    <li>
                        <a class="dropdown-item small text-center py-2" href="<?= BASE_URL ?>/backend/auth/mark_notification_read.php?mark_all=1"><i class="fas fa-check-double me-1"></i> Mark all as read</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
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
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#organizerProfileModal">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#eventsModal">
                        <i class="fas fa-list me-2"></i> My Events
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
    <!-- Left Sidebar -->
    <aside class="sidebar">
        <!-- Mini Calendar -->
        <div class="mini-calendar-widget">
            <div class="mini-calendar-header">
                <button class="mini-cal-nav" id="miniCalPrev"><i class="fas fa-chevron-left"></i></button>
                <span class="mini-cal-month" id="miniCalMonth">September 2026</span>
                <button class="mini-cal-nav" id="miniCalNext"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="mini-calendar-grid" id="miniCalendar"></div>
        </div>

        <!-- Calendars/Departments List -->
        <div class="calendars-section">
            <h3 class="calendars-title">DEPARTMENTS</h3>
            <div class="calendars-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search" id="calendarSearch">
            </div>
            <div class="calendars-list" id="calendarsList">
                <div class="calendar-item active" data-dept="ALL">
                    <div class="calendar-avatar" style="background: #7c3aed;">A</div>
                    <span class="calendar-name">All Departments</span>
                    <i class="fas fa-check"></i>
                </div>
                <div class="calendar-item" data-dept="High school department">
                    <div class="calendar-avatar" style="background: #3b82f6;">H</div>
                    <span class="calendar-name">High School Department</span>
                </div>
                <div class="calendar-item" data-dept="College of Communication, Information and Technology">
                    <div class="calendar-avatar" style="background: #10b981;">C</div>
                    <span class="calendar-name">College of Communication, Information and Technology</span>
                </div>
                <div class="calendar-item" data-dept="College of Accountancy and Business">
                    <div class="calendar-avatar" style="background: #f59e0b;">A</div>
                    <span class="calendar-name">College of Accountancy and Business</span>
                </div>
                <div class="calendar-item" data-dept="School of Law and Political Science">
                    <div class="calendar-avatar" style="background: #ef4444;">L</div>
                    <span class="calendar-name">School of Law and Political Science</span>
                </div>
                <div class="calendar-item" data-dept="College of Education">
                    <div class="calendar-avatar" style="background: #6366f1;">E</div>
                    <span class="calendar-name">College of Education</span>
                </div>
                <div class="calendar-item" data-dept="College of Nursing and Allied health sciences">
                    <div class="calendar-avatar" style="background: #14b8a6;">N</div>
                    <span class="calendar-name">College of Nursing and Allied health sciences</span>
                </div>
                <div class="calendar-item" data-dept="College of Hospitality Management">
                    <div class="calendar-avatar" style="background: #f97316;">H</div>
                    <span class="calendar-name">College of Hospitality Management</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3 class="section-title">QUICK ACTIONS</h3>
            <a href="#" class="action-btn" data-bs-toggle="modal" data-bs-target="#organizerProfileModal">
                <i class="fas fa-user"></i>
                <span>Edit profile</span>
            </a>
            <a href="#" class="action-btn" data-bs-toggle="modal" data-bs-target="#eventsModal">
                <i class="fas fa-list"></i>
                <span>My Events</span>
            </a>
            <a href="#" class="action-btn" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <?php if ($msg): ?>
            <div class="alert alert-dismissible fade show <?= strpos($msg, 'success') !== false || strpos($msg, 'updated') !== false ? 'alert-success' : 'alert-warning' ?>" role="alert">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Calendar Controls (center calendar on dashboard again) -->
        <div class="calendar-controls">
            <div class="controls-left">
                <button class="control-nav" id="calPrev"><i class="fas fa-chevron-left"></i></button>
                <h2 class="calendar-title" id="calendarTitle">My Events Calendar</h2>
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
        <div class="calendar-container mb-4">
            <div id="calendar"></div>
        </div>

        <!-- (Stats and lists removed; calendar is main focus) -->
    </main>
</div>

<!-- Events List Modal -->
<div class="modal fade" id="eventsModal" tabindex="-1" aria-labelledby="eventsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventsModalLabel">My Events</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php
        $eventsSorted = $events ?? [];
        if (!empty($eventsSorted)) {
            usort($eventsSorted, function($a, $b) {
                return strtotime($a['date'] ?? '') <=> strtotime($b['date'] ?? '');
            });
        }
        ?>

        <?php if (!empty($eventsSorted)): ?>
          <div class="events-list">
            <?php foreach ($eventsSorted as $event): ?>
              <div class="event-item">
                <div class="event-date-badge">
                  <span class="event-month"><?= htmlspecialchars(date('M', strtotime($event['date']))) ?></span>
                  <span class="event-day"><?= htmlspecialchars(date('d', strtotime($event['date']))) ?></span>
                </div>
                <div class="event-details">
                  <h4 class="event-title"><?= htmlspecialchars($event['title'] ?? 'Untitled') ?></h4>
                  <p class="event-meta">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= htmlspecialchars($event['location'] ?? 'TBA') ?>
                  </p>
                  <p class="event-meta">
                    <i class="fas fa-users"></i>
                    <?= htmlspecialchars(($event['department'] ?? 'ALL') === 'ALL' ? 'All Departments' : ($event['department'] ?? 'ALL')) ?>
                  </p>
                  <?php
                  $evStatus = $event['status'] ?? '';
                  $evRejectReason = trim($event['reject_reason'] ?? '');
                  ?>
                  <?php if ($evStatus === 'rejected' && $evRejectReason !== ''): ?>
                    <p class="event-meta text-danger small mb-1"><i class="fas fa-info-circle"></i> <strong>Rejection reason:</strong> <?= htmlspecialchars($evRejectReason) ?></p>
                  <?php endif; ?>
                  <div class="event-actions d-flex gap-1 flex-wrap">
                    <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/backend/auth/edit_event.php?id=<?= urlencode($event['id']) ?>">Edit</a>
                    <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/event_qr.php?id=<?= urlencode($event['id']) ?>" target="_blank" rel="noopener" title="Show QR for check-in"><i class="fas fa-qrcode"></i> QR</a>
                    <a class="btn btn-sm btn-outline-info" href="<?= BASE_URL ?>/event_attendance.php?id=<?= urlencode($event['id']) ?>" target="_blank" rel="noopener" title="View who attended"><i class="fas fa-clipboard-check"></i> Attendance</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="no-events">No events found yet. Click the <strong>+</strong> button to create one.</p>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="openCreateEventFromMyEvents">
          <i class="fas fa-plus"></i> Create Event
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Organizer Profile Modal -->
<div class="modal fade" id="organizerProfileModal" tabindex="-1" aria-labelledby="organizerProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="organizerProfileModalLabel">Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="organizerProfileForm" action="<?= BASE_URL ?>/backend/auth/update_organizer_profile.php" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault(); confirmOrganizerProfileChanges(this);">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="organizer-profile-picture-container mb-3">
            <?php if (!empty($user['profile_picture'])): ?>
              <img src="<?= BASE_URL ?>/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile" id="organizerProfilePicturePreview" class="organizer-profile-picture-preview" title="Click to view full screen">
            <?php else: ?>
              <div class="organizer-profile-picture-placeholder" id="organizerProfilePicturePreview">
                <i class="fas fa-user"></i>
              </div>
            <?php endif; ?>
          </div>
          <div class="mb-3">
            <label class="form-label" for="organizerProfilePictureInput">Profile Picture</label>
            <input type="file" class="form-control" id="organizerProfilePictureInput" name="profile_picture" accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewOrganizerProfilePicture(this)">
            <small class="text-muted">JPG, PNG, GIF, or WEBP (max 5MB)</small>
          </div>
          <div class="mb-3">
            <label class="form-label" for="organizerFullName">Full Name</label>
            <input type="text" class="form-control" id="organizerFullName" name="name" value="<?= htmlspecialchars($user['name'] ?? $user_name) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Role</label>
            <input type="text" class="form-control" value="Organizer" readonly>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Confirm Organizer Profile Save Modal -->
<div class="modal fade" id="confirmOrganizerProfileModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Save profile changes?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="confirmOrganizerProfileMessage" class="mb-0"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmOrganizerProfileBtn">Save</button>
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
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

<!-- Help Modal (placeholder) -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="helpModalLabel">Help</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="mb-0">
          <li>Click a date to create an event.</li>
          <li>Click an event to view details.</li>
          <li>Use “My Events” to view/edit all events.</li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

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

<!-- Create Event Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createEventModalLabel">
          <i class="fas fa-calendar-plus me-2"></i>Create New Event
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="<?= BASE_URL ?>/backend/auth/createevent.php">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="mb-3">
            <label for="ceTitle" class="form-label">Event Title <span class="text-danger">*</span></label>
            <input type="text" name="title" id="ceTitle" class="form-control" maxlength="150" required>
          </div>
          <div class="mb-3">
            <label for="ceDescription" class="form-label">Description</label>
            <textarea name="description" id="ceDescription" class="form-control" rows="3" maxlength="1000"></textarea>
          </div>
          <div class="row g-3">
            <div class="col-md-4">
              <label for="ceDate" class="form-label">Event Date <span class="text-danger">*</span></label>
              <input type="date" name="date" id="ceDate" class="form-control" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-4">
              <label for="ceStartTime" class="form-label">Start Time <span class="text-danger">*</span></label>
              <input type="time" name="start_time" id="ceStartTime" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label for="ceEndTime" class="form-label">End Time</label>
              <input type="time" name="end_time" id="ceEndTime" class="form-control">
              <small class="text-muted">Optional — leave blank if not fixed.</small>
            </div>
          </div>
          <div class="mb-3 mt-3">
            <label for="ceLocation" class="form-label">Location <span class="text-danger">*</span></label>
            <input type="text" name="location" id="ceLocation" class="form-control" maxlength="100" required>
          </div>
          <div class="mb-3">
            <label for="ceDepartment" class="form-label">Department / Audience <span class="text-danger">*</span></label>
            <select id="ceDepartment" name="department" class="form-select" required>
              <option value="ALL">All Departments</option>
              <option value="High school department">High School Department</option>
              <option value="College of Communication, Information and Technology">College of Communication, Information and Technology</option>
              <option value="College of Accountancy and Business">College of Accountancy and Business</option>
              <option value="School of Law and Political Science">School of Law and Political Science</option>
              <option value="College of Education">College of Education</option>
              <option value="College of Nursing and Allied health sciences">College of Nursing and Allied health sciences</option>
              <option value="College of Hospitality Management">College of Hospitality Management</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-check me-1"></i>Submit for approval
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Pass PHP events to JS -->
<script>
window.BASE_URL = <?= json_encode(BASE_URL) ?>;
window.eventsData = <?= json_encode(array_map(function($e) use ($user_name) {
    return [
        'id'    => $e['id'],
        'title' => $e['title'],
        // Combine date + time so FullCalendar can show proper times
        'start' => trim(($e['date'] ?? '') . ' ' . ($e['start_time'] ?? '')),
        'end'   => isset($e['end_time']) && $e['end_time'] !== null
            ? trim(($e['date'] ?? '') . ' ' . $e['end_time'])
            : null,
        'extendedProps' => [
            'description'   => $e['description'],
            'location'      => $e['location'],
            'created_at'    => $e['created_at'],
            'status'        => $e['status'],
            'reject_reason' => $e['reject_reason'] ?? null,
            'start_time'    => $e['start_time'] ?? null,
            'end_time'      => $e['end_time'] ?? null,
            'editUrl'       => 'edit_event.php?id=' . $e['id'],
            'organizer'     => $user_name,
            'department'    => $e['department'] ?? 'ALL',
        ],
    ];
}, $events), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;

window.currentUser = {
    name: <?= json_encode($user_name) ?>,
    id: <?= json_encode($_SESSION['user_id'] ?? 0) ?>
};
</script>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventDetailsLabel">Event Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h5 id="eventTitle" class="mb-2"></h5>
        <p class="mb-1"><strong>Date:</strong> <span id="eventDate"></span></p>
        <p class="mb-1"><strong>Location:</strong> <span id="eventLocation"></span></p>
        <p class="mb-1"><strong>Status:</strong> <span id="eventStatus" class="badge bg-success"></span></p>
        <p class="mb-1" id="eventRejectReasonWrap" style="display:none;"><strong>Rejection reason:</strong> <span id="eventRejectReason" class="text-danger"></span></p>
        <p class="mb-1"><strong>Target Department:</strong> <span id="eventDepartment"></span></p>
        <p class="mb-1"><strong>Created by:</strong> <span id="eventOrganizer"></span></p>
        <p class="mt-3 mb-1"><strong>Description:</strong></p>
        <p id="eventDescription" class="mb-2 text-muted"></p>
        <p class="mb-0"><small><strong>Created at:</strong> <span id="eventCreatedAt"></span></small></p>
      </div>
      <div class="modal-footer">
        <a href="#" id="eventEditLink" class="btn btn-primary">Edit Event</a>
        <a href="#" id="eventQrLink" class="btn btn-outline-secondary" target="_blank" rel="noopener" style="display:none;"><i class="fas fa-qrcode me-1"></i> Show QR</a>
        <a href="#" id="eventAttendanceLink" class="btn btn-outline-info" target="_blank" rel="noopener" style="display:none;"><i class="fas fa-clipboard-check me-1"></i> Attendance</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Dashboard Scripts -->
<script src="<?= BASE_URL ?>/assets/js/dashboardorganizer.js"></script>

<script>
// Open create-event modal from "My Events" footer button
document.addEventListener('DOMContentLoaded', function () {
  var openFromMyEvents = document.getElementById('openCreateEventFromMyEvents');
  var eventsModal = document.getElementById('eventsModal');
  var createModal = document.getElementById('createEventModal');

  if (openFromMyEvents && eventsModal && createModal) {
    openFromMyEvents.addEventListener('click', function () {
      var eventsInstance = bootstrap.Modal.getInstance(eventsModal);
      if (eventsInstance) {
        eventsInstance.hide();
      }
      setTimeout(function () {
        bootstrap.Modal.getOrCreateInstance(createModal).show();
      }, 300);
    });
  }
});
</script>


</body>
</html>
