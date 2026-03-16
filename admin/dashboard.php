<?php
if (!defined('EVENTIFY_ADMIN_DASHBOARD_LOADED')) {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/school_events') . '/views/login.php');
    exit;
}
$admin_name    = $admin_name    ?? 'Admin';
$admin_email   = $admin_email   ?? '';
$events        = $events        ?? [];
$pendingCount  = $pendingCount  ?? 0;
$pendingEvents = $pendingEvents ?? [];
$eventStats    = $eventStats    ?? ['total' => 0, 'pending' => 0, 'active' => 0];
$success       = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EVENTIFY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/dashboard_student.css">
    <style>
        .adm-navbar { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem; background: rgba(10, 10, 10, 0.9); border-bottom: 1px solid rgba(255,255,255,0.08); }
        .adm-brand { display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1.25rem; color: #e5e7eb; text-decoration: none; }
        .adm-brand i { color: #0ea5e9; }
        .adm-user { color: #94a3b8; font-size: 0.9rem; margin-right: 0.75rem; }
        .adm-logout { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; color: #94a3b8; text-decoration: none; font-weight: 600; border-radius: 8px; border: 1px solid rgba(255,255,255,0.15); }
        .adm-logout:hover { color: #0ea5e9; border-color: #0ea5e9; background: rgba(14, 165, 233, 0.1); }
        .adm-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; padding: 1.25rem 1.5rem; }
        .adm-stat-card { border-radius: 12px; border: 1px solid #e2e8f0; padding: 1rem; background: #f8fafc; }
        .adm-stat-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; margin-bottom: 0.25rem; }
        .adm-stat-value { font-size: 1.5rem; font-weight: 800; color: #0f172a; }
        .adm-badge-pending { background: #fef3c7; color: #92400e; }
        .adm-badge-dept { background: #eff6ff; color: #1d4ed8; }
        .adm-actions { display: flex; flex-wrap: wrap; gap: 0.35rem; }
        .adm-btn-approve { background: #10b981; color: #fff; border: none; padding: 0.35rem 0.7rem; border-radius: 8px; font-size: 0.8rem; font-weight: 600; cursor: pointer; }
        .adm-btn-reject { background: #fee2e2; color: #b91c1c; border: none; padding: 0.35rem 0.7rem; border-radius: 8px; font-size: 0.8rem; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>

<nav class="adm-navbar">
    <a href="<?= BASE_URL ?>/backend/admin/dashboard.php" class="adm-brand">
        <i class="fas fa-calendar-alt"></i>
        <span>EVENTIFY</span>
    </a>
    <div class="d-flex align-items-center">
        <span class="adm-user"><i class="fas fa-user-shield me-1"></i> <?= htmlspecialchars($admin_name) ?></span>
        <a href="#" class="adm-logout" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="user-info-card">
            <div class="user-avatar-large"><?= strtoupper(substr($admin_name, 0, 1)) ?></div>
            <h3 class="user-name"><?= htmlspecialchars($admin_name) ?></h3>
            <p class="user-id">Role: Admin</p>
            <span class="user-dept-badge">System-wide</span>
        </div>
        <div class="mini-calendar-widget">
            <div class="mini-calendar-header">
                <button class="mini-cal-nav" id="miniCalPrev"><i class="fas fa-chevron-left"></i></button>
                <span class="mini-cal-month" id="miniCalMonth">Month</span>
                <button class="mini-cal-nav" id="miniCalNext"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="mini-calendar-grid" id="miniCalendar"></div>
        </div>
        <div class="quick-actions">
            <h3 class="section-title">QUICK ACTIONS</h3>
            <button type="button" class="action-btn w-100 text-start border-0 bg-transparent" data-bs-toggle="modal" data-bs-target="#pendingEventsModal">
                <i class="fas fa-inbox"></i>
                <span>Pending Events <?= $pendingCount > 0 ? '(' . (int)$pendingCount . ')' : '' ?></span>
                <i class="fas fa-chevron-right ms-auto"></i>
            </button>
            <a href="<?= BASE_URL ?>/upcoming_events.php" class="action-btn">
                <i class="fas fa-calendar-check"></i>
                <span>Upcoming Events</span>
            </a>
            <a href="#" class="action-btn logout-btn" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <div class="calendar-controls">
            <div class="controls-left">
                <button class="control-nav" id="calPrev"><i class="fas fa-chevron-left"></i></button>
                <h2 class="calendar-title" id="calendarTitle">Calendar</h2>
                <button class="control-nav" id="calNext"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="controls-right">
                <span class="text-muted small me-2"><i class="fas fa-user-shield me-1"></i> <?= htmlspecialchars($admin_name) ?></span>
                <button class="view-btn active" data-view="dayGridMonth">Month</button>
                <button class="view-btn" data-view="timeGridWeek">Week</button>
                <button class="view-btn" data-view="timeGridDay">Day</button>
                <button class="view-btn" data-view="today">Today</button>
            </div>
        </div>
        <div class="adm-stats">
            <div class="adm-stat-card">
                <div class="adm-stat-label">Pending approval</div>
                <div class="adm-stat-value"><?= (int)$eventStats['pending'] ?></div>
            </div>
            <div class="adm-stat-card">
                <div class="adm-stat-label">Active events</div>
                <div class="adm-stat-value"><?= (int)$eventStats['active'] ?></div>
            </div>
            <div class="adm-stat-card">
                <div class="adm-stat-label">Total events</div>
                <div class="adm-stat-value"><?= (int)$eventStats['total'] ?></div>
            </div>
        </div>
        <div class="calendar-container">
            <div id="calendar"></div>
        </div>
    </main>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutModalLabel"><i class="fas fa-sign-out-alt me-2"></i>Confirm Logout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Are you sure you want to log out?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a href="<?= BASE_URL ?>/backend/auth/logout.php" class="btn btn-danger">Yes, Log out</a>
      </div>
    </div>
  </div>
</div>

<!-- Pending Events Modal -->
<div class="modal fade" id="pendingEventsModal" tabindex="-1" aria-labelledby="pendingEventsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pendingEventsModalLabel"><i class="fas fa-inbox me-2"></i>Pending Event Approvals</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php if (empty($pendingEvents)): ?>
          <p class="text-muted mb-0">No events waiting for approval.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Event</th>
                  <th>Date &amp; Location</th>
                  <th>Organizer</th>
                  <th>Dept</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pendingEvents as $ev): ?>
                  <tr>
                    <td class="text-muted">#<?= (int)$ev['id'] ?></td>
                    <td>
                      <strong><?= htmlspecialchars($ev['title'] ?? 'Untitled') ?></strong>
                      <?php if (!empty($ev['description'])): ?>
                        <div class="text-muted small"><?= htmlspecialchars(mb_strimwidth($ev['description'], 0, 100, '...')) ?></div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="small"><?= htmlspecialchars($ev['date'] ?? '') ?></div>
                      <div class="small text-muted"><?= htmlspecialchars($ev['location'] ?? '') ?></div>
                    </td>
                    <td class="small"><?= htmlspecialchars($ev['organizer_name'] ?? '') ?></td>
                    <td><span class="badge adm-badge-dept"><?= htmlspecialchars(($ev['department'] ?? 'ALL') === 'ALL' ? 'All' : $ev['department']) ?></span></td>
                    <td>
                      <div class="adm-actions">
                        <a href="<?= BASE_URL ?>/event_qr.php?id=<?= (int)$ev['id'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" title="Show QR for event check-in"><i class="fas fa-qrcode me-1"></i>QR</a>
                        <a href="<?= BASE_URL ?>/event_attendance.php?id=<?= (int)$ev['id'] ?>" class="btn btn-sm btn-outline-info" target="_blank" rel="noopener" title="View who attended"><i class="fas fa-clipboard-check me-1"></i>Attendance</a>
                        <form method="POST" action="<?= BASE_URL ?>/backend/super_admin/update_event_status.php" class="d-inline">
                          <?= csrf_field() ?>
                          <input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>">
                          <input type="hidden" name="action" value="approve">
                          <input type="hidden" name="return_to" value="dashboard">
                          <button type="submit" class="adm-btn-approve"><i class="fas fa-check me-1"></i>Approve</button>
                        </form>
                        <button type="button" class="adm-btn-reject" data-bs-toggle="modal" data-bs-target="#rejectEventModal" data-event-id="<?= (int)$ev['id'] ?>" data-return-to="dashboard" data-event-title="<?= htmlspecialchars($ev['title'] ?? '') ?>">
                          <i class="fas fa-times me-1"></i>Reject
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <a href="<?= BASE_URL ?>/backend/super_admin/manage_events.php" class="btn btn-outline-primary btn-sm"><i class="fas fa-external-link-alt me-1"></i> Open full list</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Reject event modal (with optional reason) -->
<div class="modal fade" id="rejectEventModal" tabindex="-1" aria-labelledby="rejectEventModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= BASE_URL ?>/backend/super_admin/update_event_status.php" id="rejectEventForm">
        <?= csrf_field() ?>
        <input type="hidden" name="event_id" id="rejectEventId" value="">
        <input type="hidden" name="action" value="reject">
        <input type="hidden" name="return_to" id="rejectReturnTo" value="dashboard">
        <div class="modal-header">
          <h5 class="modal-title" id="rejectEventModalLabel"><i class="fas fa-times-circle me-2 text-danger"></i>Reject event</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2" id="rejectEventTitleText">Optionally give a reason so the organizer knows what to fix.</p>
          <label for="rejectReasonInput" class="form-label small text-muted">Reason (optional)</label>
          <textarea class="form-control" id="rejectReasonInput" name="reject_reason" rows="3" placeholder="e.g. Please add a clearer description or change the date."></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i>Reject event</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventDetailsLabel">Event Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <h5 id="eventTitle" class="mb-2"></h5>
        <p class="mb-1"><strong>Date:</strong> <span id="eventDate"></span></p>
        <p class="mb-1"><strong>Location:</strong> <span id="eventLocation"></span></p>
        <p class="mb-1"><strong>Status:</strong> <span id="eventStatus" class="badge bg-success"></span></p>
        <p class="mb-1"><strong>Target Department:</strong> <span id="eventDepartment"></span></p>
        <p class="mb-1"><strong>Organizer:</strong> <span id="eventOrganizer"></span></p>
        <p class="mt-3 mb-1"><strong>Description:</strong></p>
        <p id="eventDescription" class="mb-2 text-muted"></p>
        <p class="mb-0"><small><strong>Created at:</strong> <span id="eventCreatedAt"></span></small></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="admOpenPendingBtn"><i class="fas fa-inbox me-1"></i> Pending Events</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var openPendingBtn = document.getElementById('admOpenPendingBtn');
  var eventModal = document.getElementById('eventDetailsModal');
  var pendingModal = document.getElementById('pendingEventsModal');
  if (openPendingBtn && eventModal && pendingModal) {
    openPendingBtn.addEventListener('click', function() {
      bootstrap.Modal.getInstance(eventModal).hide();
      setTimeout(function() { bootstrap.Modal.getOrCreateInstance(pendingModal).show(); }, 300);
    });
  }
  // Reject modal: set event_id and return_to from trigger button
  var rejectModal = document.getElementById('rejectEventModal');
  if (rejectModal) {
    rejectModal.addEventListener('show.bs.modal', function(e) {
      var btn = e.relatedTarget;
      if (btn && btn.getAttribute('data-event-id')) {
        document.getElementById('rejectEventId').value = btn.getAttribute('data-event-id');
        document.getElementById('rejectReturnTo').value = btn.getAttribute('data-return-to') || '';
        var title = btn.getAttribute('data-event-title') || 'this event';
        document.getElementById('rejectEventTitleText').textContent = 'Reject “‘ + title + '”? Optionally give a reason so the organizer knows what to fix.';
        document.getElementById('rejectReasonInput').value = '';
      }
    });
  }
});
</script>
<script>
window.BASE_URL = <?= json_encode(BASE_URL) ?>;
window.eventsData = <?= json_encode(array_map(function($e) {
    return [
        'id'    => $e['id'],
        'title' => $e['title'],
        'start' => trim(($e['date'] ?? '') . ' ' . ($e['start_time'] ?? '')),
        'end'   => isset($e['end_time']) && $e['end_time'] !== null
            ? trim(($e['date'] ?? '') . ' ' . $e['end_time'])
            : null,
        'extendedProps' => [
            'description' => $e['description'],
            'location'    => $e['location'],
            'created_at'  => $e['created_at'],
            'status'      => $e['status'],
            'start_time'  => $e['start_time'] ?? null,
            'end_time'    => $e['end_time'] ?? null,
            'department'  => $e['department'] ?? 'ALL',
            'organizer'   => $e['organizer_name'] ?? 'Organizer',
        ],
    ];
}, $events), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/dashboardorganizer.js"></script>
</body>
</html>
