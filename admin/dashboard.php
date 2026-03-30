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
$eventStats    = $eventStats    ?? ['total' => 0, 'pending' => 0, 'active' => 0, 'rejected' => 0, 'closed' => 0];
$auditLogs     = $auditLogs     ?? [];
$chartDeptLabels = $chartDeptLabels ?? [];
$chartDeptCounts = $chartDeptCounts ?? [];
$chartStatusLabels = $chartStatusLabels ?? ['Pending', 'Active', 'Rejected', 'Closed'];
$chartStatusCounts = $chartStatusCounts ?? [0, 0, 0, 0];
$upcomingAdminEvents = $upcomingAdminEvents ?? [];
$upcomingAdminCount  = isset($upcomingAdminCount) ? (int) $upcomingAdminCount : count($upcomingAdminEvents);
$admin_notifications = $admin_notifications ?? [];
$admin_unread_count = isset($admin_unread_count) ? (int) $admin_unread_count : 0;
$feedbackStats = $feedbackStats ?? ['total_feedback' => 0, 'avg_rating' => 0, 'rating_labels' => ['1★','2★','3★','4★','5★'], 'rating_counts' => [0,0,0,0,0]];
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
        #auditLogModal .modal-body { max-height: min(70vh, 520px); overflow-y: auto; }
        #auditLogModal .table { font-size: 0.8rem; }
        #auditLogModal .table thead th { position: sticky; top: 0; background: #f8fafc; z-index: 1; }
        .adm-charts { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; padding: 0 1.5rem 1rem; }
        .adm-chart-card { border-radius: 12px; border: 1px solid #e2e8f0; padding: 1rem; background: #fff; }
        .adm-chart-card h6 { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 0.75rem; }
        .adm-chart-wrap { position: relative; height: 220px; }
        .adm-chart-empty { display: flex; align-items: center; justify-content: center; height: 100%; color: #64748b; font-size: 0.85rem; text-align: center; }
        /* Admin uses cards + charts + calendar on one page; allow vertical scroll instead of clipping calendar rows. */
        .main-content { overflow-y: auto; overflow-x: hidden; }
        .main-content .calendar-container { flex: 0 0 auto; min-height: 760px; }
        .main-content #calendar { min-height: 700px; }
        @media (max-width: 768px) {
            .adm-navbar {
                padding: 0.75rem 1rem;
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            .dashboard-layout {
                flex-direction: column;
                margin-top: 0;
                height: auto;
                min-height: calc(100vh - 64px);
            }
            /* Override student.css mobile off-canvas sidebar behavior for admin */
            .sidebar {
                position: static !important;
                top: auto !important;
                left: auto !important;
                bottom: auto !important;
                transform: none !important;
                width: 100% !important;
                max-width: 100% !important;
                height: auto;
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
                box-shadow: none;
                padding-top: 1rem;
            }
            .main-content {
                min-width: 0;
            }
            .calendar-controls {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.65rem;
            }
            .controls-right {
                width: 100%;
                flex-wrap: wrap;
                gap: 0.4rem;
            }
            .view-btn {
                flex: 1 1 auto;
                min-width: 78px;
            }
            .main-content .calendar-container { min-height: 620px; }
            .main-content #calendar { min-height: 560px; }
        }
    </style>
</head>
<body>

<nav class="adm-navbar">
    <a href="<?= BASE_URL ?>/backend/admin/dashboard.php" class="adm-brand">
        <i class="fas fa-calendar-alt"></i>
        <span>EVENTIFY</span>
    </a>
    <div class="d-flex align-items-center">
        <button class="nav-btn position-relative me-2" type="button" title="Notifications" data-bs-toggle="modal" data-bs-target="#adminNotificationsModal">
            <i class="fas fa-bell"></i>
            <?php if ($admin_unread_count > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.55rem;">
                    <?= $admin_unread_count > 99 ? '99+' : $admin_unread_count ?>
                </span>
            <?php endif; ?>
        </button>
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
                <span class="mini-cal-month" id="miniCalMonth"><?= date('F Y') ?></span>
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
            <button type="button" class="action-btn w-100 text-start border-0 bg-transparent" data-bs-toggle="modal" data-bs-target="#adminUpcomingEventsModal">
                <i class="fas fa-calendar-check"></i>
                <span>Upcoming Events<?= $upcomingAdminCount > 0 ? ' (' . $upcomingAdminCount . ')' : '' ?></span>
                <i class="fas fa-chevron-right ms-auto"></i>
            </button>
            <button type="button" class="action-btn w-100 text-start border-0 bg-transparent" data-bs-toggle="modal" data-bs-target="#auditLogModal">
                <i class="fas fa-clipboard-list"></i>
                <span>Audit log</span>
                <i class="fas fa-chevron-right ms-auto"></i>
            </button>
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
            <div class="adm-stat-card">
                <div class="adm-stat-label">Feedback avg</div>
                <div class="adm-stat-value"><?= number_format((float)($feedbackStats['avg_rating'] ?? 0), 2) ?></div>
            </div>
        </div>
        <div class="adm-charts">
            <div class="adm-chart-card">
                <h6 class="mb-0">Events by department</h6>
                <div class="adm-chart-wrap">
                    <canvas id="adminChartDept" aria-label="Bar chart of events by department"></canvas>
                </div>
            </div>
            <div class="adm-chart-card">
                <h6 class="mb-0">Events by status</h6>
                <div class="adm-chart-wrap">
                    <canvas id="adminChartStatus" aria-label="Doughnut chart of events by status"></canvas>
                </div>
            </div>
            <div class="adm-chart-card">
                <h6 class="mb-0">Feedback ratings distribution</h6>
                <div class="adm-chart-wrap">
                    <canvas id="adminChartFeedback" aria-label="Bar chart of feedback ratings"></canvas>
                </div>
            </div>
        </div>
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
        <div class="calendar-container">
            <div id="calendar"></div>
        </div>
    </main>
</div>

<!-- Admin Notifications Modal -->
<div class="modal fade" id="adminNotificationsModal" tabindex="-1" aria-labelledby="adminNotificationsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="adminNotificationsModalLabel"><i class="fas fa-bell me-2"></i>Notifications</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if (empty($admin_notifications)): ?>
          <p class="text-muted mb-0">No notifications yet.</p>
        <?php else: ?>
          <div class="list-group">
            <?php foreach ($admin_notifications as $n): ?>
              <a href="<?= BASE_URL ?>/backend/auth/mark_notification_read.php?id=<?= (int)($n['id'] ?? 0) ?>" class="list-group-item list-group-item-action <?= empty($n['read_at']) ? 'fw-semibold list-group-item-light' : '' ?>">
                <div class="d-flex w-100 justify-content-between align-items-start gap-2">
                  <div>
                    <div><?= htmlspecialchars($n['title'] ?? 'Notification') ?></div>
                    <?php if (!empty($n['message'])): ?>
                      <div class="small text-muted"><?= nl2br(htmlspecialchars($n['message'])) ?></div>
                    <?php endif; ?>
                  </div>
                  <small class="text-muted text-nowrap"><?= htmlspecialchars($n['created_at'] ?? '') ?></small>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <a href="<?= BASE_URL ?>/backend/auth/mark_notification_read.php?mark_all=1" class="btn btn-outline-secondary btn-sm"><i class="fas fa-check-double me-1"></i>Mark all read</a>
        <a href="<?= BASE_URL ?>/backend/auth/mark_notification_read.php?clear_all=1" class="btn btn-outline-danger btn-sm" onclick="return confirm('Clear all notifications? This cannot be undone.');"><i class="fas fa-trash me-1"></i>Clear all</a>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
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
        <?php if (empty($otpTableReady) || empty($usersHasOtpContactColumns)): ?>
          <div class="alert alert-warning py-2">
            OTP approval requires database migration: <code>school_events_event_approval_otp.sql</code>.
          </div>
        <?php endif; ?>
        <?php if (empty($pendingEvents)): ?>
          <p class="text-muted mb-0">No events waiting for approval.</p>
        <?php else: ?>
          <form method="POST" action="<?= BASE_URL ?>/backend/super_admin/update_event_status_bulk.php" class="mb-2" id="bulkEventStatusForm">
            <?= csrf_field() ?>
            <input type="hidden" name="reject_reason" id="bulkRejectReasonInput" value="">
            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
              <button type="button" class="btn btn-sm btn-outline-secondary" id="bulkSelectAllPending"><i class="fas fa-check-square me-1"></i>Select all</button>
              <button type="button" class="btn btn-sm btn-success" disabled title="OTP is required per event"><i class="fas fa-check me-1"></i>Approve selected</button>
              <button type="button" class="btn btn-sm btn-danger" id="bulkRejectBtn"><i class="fas fa-times me-1"></i>Reject selected</button>
              <small class="text-muted">Bulk actions apply to selected pending events only.</small>
            </div>
          </form>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th><input type="checkbox" id="pendingHeadCheck"></th>
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
                    <td><input type="checkbox" class="pending-event-checkbox" form="bulkEventStatusForm" name="event_ids[]" value="<?= (int)$ev['id'] ?>"></td>
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
                        <?php
                          $orgEmail = trim((string)($ev['organizer_contact_email'] ?? ''));
                          if ($orgEmail === '') { $orgEmail = trim((string)($ev['organizer_email'] ?? '')); }
                          $orgPhone = trim((string)($ev['organizer_phone'] ?? ''));
                          $prefMethod = (string)($ev['organizer_contact_method'] ?? 'email');
                          $canSendOtp = !empty($otpTableReady) && !empty($usersHasOtpContactColumns) && (($prefMethod === 'phone' && $orgPhone !== '') || $orgEmail !== '');
                          $otpMethod = ($prefMethod === 'phone' && $orgPhone !== '') ? 'phone' : 'email';
                          if ($otpMethod === 'phone') {
                              $digits = preg_replace('/\D+/', '', $orgPhone);
                              $maskedTarget = strlen($digits) >= 4
                                  ? str_repeat('*', max(0, strlen($digits) - 4)) . substr($digits, -4)
                                  : '***';
                          } else {
                              $parts = explode('@', (string)$orgEmail, 2);
                              if (count($parts) === 2) {
                                  $local = $parts[0];
                                  $domain = $parts[1];
                                  $maskedLocal = strlen($local) <= 2
                                      ? substr($local, 0, 1) . '*'
                                      : substr($local, 0, 2) . str_repeat('*', max(1, strlen($local) - 2));
                                  $maskedTarget = $maskedLocal . '@' . $domain;
                              } else {
                                  $maskedTarget = '***';
                              }
                          }
                          $otpConfirmMsg = "Are you sure you want to request OTP to the organizer's {$otpMethod}: {$maskedTarget}?";
                        ?>
                        <form method="POST" action="<?= BASE_URL ?>/backend/super_admin/update_event_status.php" class="d-inline js-confirm-otp-request" data-confirm-message="<?= htmlspecialchars($otpConfirmMsg, ENT_QUOTES, 'UTF-8') ?>">
                          <?= csrf_field() ?>
                          <input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>">
                          <input type="hidden" name="action" value="send_otp">
                          <input type="hidden" name="return_to" value="dashboard">
                          <button type="submit" class="btn btn-sm btn-outline-primary" <?= $canSendOtp ? '' : 'disabled' ?>><i class="fas fa-paper-plane me-1"></i>Request OTP</button>
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

<!-- OTP request confirmation modal -->
<div class="modal fade" id="otpRequestConfirmModal" tabindex="-1" aria-labelledby="otpRequestConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="otpRequestConfirmModalLabel"><i class="fas fa-shield-alt me-2 text-primary"></i>Confirm OTP Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="otpRequestConfirmText" class="mb-0">Are you sure you want to request OTP?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="otpRequestConfirmBtn"><i class="fas fa-paper-plane me-1"></i>Yes, request OTP</button>
      </div>
    </div>
  </div>
</div>

<!-- Audit log modal -->
<div class="modal fade" id="auditLogModal" tabindex="-1" aria-labelledby="auditLogModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="auditLogModalLabel"><i class="fas fa-clipboard-list me-2"></i>Audit log</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-0">
        <p class="text-muted small mb-2">Latest <?= count($auditLogs) ?> entries (newest first). Use search to filter this list.</p>
        <div class="mb-3">
          <label for="auditLogSearch" class="form-label small mb-1">Search</label>
          <input type="search" id="auditLogSearch" class="form-control form-control-sm" placeholder="Filter by date, user, action, details…" autocomplete="off">
        </div>
        <div class="table-responsive border rounded">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>When</th>
                <th>Actor</th>
                <th>Role</th>
                <th>Action</th>
                <th>Target</th>
                <th>Details</th>
              </tr>
            </thead>
            <tbody id="auditLogTableBody">
              <?php if (empty($auditLogs)): ?>
                <tr><td colspan="6" class="text-muted text-center py-4">No log entries yet.</td></tr>
              <?php else: ?>
                <?php foreach ($auditLogs as $row): ?>
                  <tr class="audit-log-row">
                    <td class="text-nowrap small"><?= htmlspecialchars($row['created_at'] ?? '') ?></td>
                    <td class="small"><?= htmlspecialchars($row['actor_name'] ?? '—') ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($row['actor_role'] ?? '—') ?></span></td>
                    <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row['action'] ?? '') ?></span></td>
                    <td class="small text-muted">
                      <?php if (!empty($row['target_type'])): ?>
                        <?= htmlspecialchars($row['target_type']) ?>
                        <?php if ($row['target_id'] !== null && $row['target_id'] !== ''): ?>
                          #<?= (int)$row['target_id'] ?>
                        <?php endif; ?>
                      <?php else: ?>
                        —
                      <?php endif; ?>
                    </td>
                    <td class="small"><?= htmlspecialchars($row['details'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
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

<!-- Upcoming events (opens only when you choose Quick action — no auto-popup) -->
<div class="modal fade" id="adminUpcomingEventsModal" tabindex="-1" aria-labelledby="adminUpcomingEventsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="adminUpcomingEventsModalLabel">
          <i class="fas fa-calendar-check me-2"></i>Upcoming events
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted mb-3 mb-md-2">Active and pending events from today onward (system-wide). Tap an event to see full details on the calendar.</p>
        <?php if (!empty($upcomingAdminEvents)): ?>
          <div class="list-group">
            <?php foreach ($upcomingAdminEvents as $ev): ?>
              <?php
                $eid = (int) ($ev['id'] ?? 0);
                $st = strtolower((string) ($ev['status'] ?? ''));
              ?>
              <button type="button" class="list-group-item list-group-item-action text-start admin-upcoming-event-link" data-event-id="<?= $eid ?>" data-event-date="<?= htmlspecialchars($ev['date'] ?? '') ?>" data-bs-dismiss="modal">
                <div class="d-flex w-100 justify-content-between align-items-start gap-2">
                  <div>
                    <h6 class="mb-1">
                      <i class="fas fa-calendar-day me-1 text-primary"></i>
                      <?= htmlspecialchars($ev['title'] ?? 'Untitled') ?>
                    </h6>
                    <div class="small text-muted">
                      <?php if (!empty($ev['date'])): ?>
                        <?= date('M j, Y', strtotime($ev['date'])) ?>
                        <?php if (!empty($ev['start_time'])): ?>
                          · <?= htmlspecialchars(substr($ev['start_time'], 0, 5)) ?>
                        <?php endif; ?>
                      <?php endif; ?>
                      <?php if (!empty($ev['location'])): ?>
                        <br><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($ev['location']) ?>
                      <?php endif; ?>
                    </div>
                  </div>
                  <span class="badge <?= $st === 'active' ? 'bg-success' : 'bg-warning text-dark' ?> flex-shrink-0">
                    <?= $st === 'active' ? 'Active' : 'Pending' ?>
                  </span>
                </div>
                <?php if (!empty($ev['description'])): ?>
                  <p class="mb-0 small mt-2 text-muted"><?= htmlspecialchars(mb_strimwidth((string) $ev['description'], 0, 160, '…')) ?></p>
                <?php endif; ?>
                <div class="small text-muted mt-1"><i class="fas fa-user me-1"></i><?= htmlspecialchars($ev['organizer_name'] ?? '') ?></div>
              </button>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-muted mb-0">No upcoming active or pending events.</p>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <a href="<?= BASE_URL ?>/upcoming_events.php" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">
          <i class="fas fa-external-link-alt me-1"></i> Open full page
        </a>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
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
        <a href="#" id="eventQrLink" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener" style="display:none;"><i class="fas fa-qrcode me-1"></i> QR</a>
        <a href="#" id="eventAttendanceLink" class="btn btn-outline-info btn-sm" target="_blank" rel="noopener" style="display:none;"><i class="fas fa-clipboard-check me-1"></i> Attendance</a>
        <button type="button" class="btn btn-primary btn-sm" id="admOpenPendingBtn" title="Open pending event approvals">
          <i class="fas fa-inbox me-1"></i> Pending Approvals
        </button>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
window.__adminChartDept = <?= json_encode(['labels' => $chartDeptLabels, 'counts' => $chartDeptCounts]) ?>;
window.__adminChartStatus = <?= json_encode(['labels' => $chartStatusLabels, 'counts' => $chartStatusCounts]) ?>;
window.__adminChartFeedback = <?= json_encode(['labels' => $feedbackStats['rating_labels'] ?? ['1★','2★','3★','4★','5★'], 'counts' => $feedbackStats['rating_counts'] ?? [0,0,0,0,0]]) ?>;
document.addEventListener('DOMContentLoaded', function() {
  var otpReqModalEl = document.getElementById('otpRequestConfirmModal');
  var otpReqMsgEl = document.getElementById('otpRequestConfirmText');
  var otpReqConfirmBtn = document.getElementById('otpRequestConfirmBtn');
  var otpReqModal = otpReqModalEl ? bootstrap.Modal.getOrCreateInstance(otpReqModalEl) : null;
  var otpPendingForm = null;

  document.querySelectorAll('form.js-confirm-otp-request').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      e.preventDefault();
      otpPendingForm = f;
      if (otpReqMsgEl) {
        otpReqMsgEl.textContent = f.getAttribute('data-confirm-message') || 'Are you sure you want to request OTP?';
      }
      if (otpReqModal) {
        otpReqModal.show();
      }
    });
  });
  if (otpReqConfirmBtn) {
    otpReqConfirmBtn.addEventListener('click', function () {
      if (!otpPendingForm) return;
      if (otpReqModal) otpReqModal.hide();
      otpPendingForm.submit();
      otpPendingForm = null;
    });
  }
  if (otpReqModalEl) {
    otpReqModalEl.addEventListener('hidden.bs.modal', function () {
      otpPendingForm = null;
    });
  }

  var deptData = window.__adminChartDept || { labels: [], counts: [] };
  var stData = window.__adminChartStatus || { labels: [], counts: [] };
  var deptLabels = deptData.labels && deptData.labels.length ? deptData.labels : ['No events'];
  var deptCounts = deptData.counts && deptData.counts.length ? deptData.counts : [0];
  function showEmptyChartMessage(canvasId, msg) {
    var el = document.getElementById(canvasId);
    if (!el || !el.parentElement) return;
    el.parentElement.innerHTML = '<div class="adm-chart-empty">' + msg + '</div>';
  }
  if (typeof Chart === 'undefined') {
    showEmptyChartMessage('adminChartDept', 'Charts unavailable (Chart.js failed to load).');
    showEmptyChartMessage('adminChartStatus', 'Charts unavailable (Chart.js failed to load).');
  }
  var cdept = document.getElementById('adminChartDept');
  if (cdept && typeof Chart !== 'undefined' && deptData.counts && deptData.counts.length) {
    new Chart(cdept, {
      type: 'bar',
      data: {
        labels: deptLabels,
        datasets: [{
          label: 'Events',
          data: deptCounts,
          backgroundColor: 'rgba(14, 165, 233, 0.55)',
          borderColor: 'rgba(14, 165, 233, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { precision: 0 } },
          x: { ticks: { maxRotation: 45, minRotation: 0 } }
        }
      }
    });
  } else if (cdept && typeof Chart !== 'undefined') {
    showEmptyChartMessage('adminChartDept', 'No events yet for department chart.');
  }
  var cst = document.getElementById('adminChartStatus');
  if (cst && typeof Chart !== 'undefined' && stData.counts && stData.counts.length) {
    new Chart(cst, {
      type: 'doughnut',
      data: {
        labels: stData.labels || [],
        datasets: [{
          data: stData.counts || [],
          backgroundColor: [
            'rgba(234, 179, 8, 0.85)',
            'rgba(16, 185, 129, 0.85)',
            'rgba(239, 68, 68, 0.85)',
            'rgba(100, 116, 139, 0.85)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
      }
    });
  } else if (cst && typeof Chart !== 'undefined') {
    showEmptyChartMessage('adminChartStatus', 'No events yet for status chart.');
  }
  var fData = window.__adminChartFeedback || { labels: ['1★','2★','3★','4★','5★'], counts: [0,0,0,0,0] };
  var cfb = document.getElementById('adminChartFeedback');
  if (cfb && typeof Chart !== 'undefined' && fData.counts && fData.counts.length) {
    new Chart(cfb, {
      type: 'bar',
      data: {
        labels: fData.labels || [],
        datasets: [{
          label: 'Feedback',
          data: fData.counts || [],
          backgroundColor: 'rgba(99, 102, 241, 0.6)',
          borderColor: 'rgba(99, 102, 241, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
      }
    });
  }

  var openPendingBtn = document.getElementById('admOpenPendingBtn');
  var eventModal = document.getElementById('eventDetailsModal');
  var pendingModal = document.getElementById('pendingEventsModal');
  if (openPendingBtn && eventModal && pendingModal) {
    openPendingBtn.addEventListener('click', function() {
      bootstrap.Modal.getInstance(eventModal).hide();
      setTimeout(function() { bootstrap.Modal.getOrCreateInstance(pendingModal).show(); }, 300);
    });
  }
  var auditSearch = document.getElementById('auditLogSearch');
  if (auditSearch) {
    auditSearch.addEventListener('input', function() {
      var q = (auditSearch.value || '').toLowerCase().trim();
      document.querySelectorAll('#auditLogTableBody tr.audit-log-row').forEach(function(tr) {
        tr.style.display = !q || tr.innerText.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
      });
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
        document.getElementById('rejectEventTitleText').textContent = 'Reject "' + title + '"? Optionally give a reason so the organizer knows what to fix.';
        document.getElementById('rejectReasonInput').value = '';
      }
    });
  }

  var headCheck = document.getElementById('pendingHeadCheck');
  var selectAllBtn = document.getElementById('bulkSelectAllPending');
  var bulkRejectBtn = document.getElementById('bulkRejectBtn');
  var bulkForm = document.getElementById('bulkEventStatusForm');
  function getPendingChecks() {
    return Array.prototype.slice.call(document.querySelectorAll('.pending-event-checkbox'));
  }
  function setAllPendingChecks(v) {
    getPendingChecks().forEach(function(c) { c.checked = !!v; });
    if (headCheck) headCheck.checked = !!v;
  }
  if (headCheck) {
    headCheck.addEventListener('change', function() { setAllPendingChecks(headCheck.checked); });
  }
  if (selectAllBtn) {
    selectAllBtn.addEventListener('click', function() {
      var checks = getPendingChecks();
      var allChecked = checks.length > 0 && checks.every(function(c){ return c.checked; });
      setAllPendingChecks(!allChecked);
    });
  }
  if (bulkRejectBtn && bulkForm) {
    bulkRejectBtn.addEventListener('click', function() {
      var selected = getPendingChecks().some(function(c){ return c.checked; });
      if (!selected) {
        alert('Select at least one event first.');
        return;
      }
      var reason = prompt('Optional rejection reason for selected events:', '');
      var input = document.getElementById('bulkRejectReasonInput');
      if (input) input.value = reason || '';
      var hiddenAction = document.createElement('input');
      hiddenAction.type = 'hidden';
      hiddenAction.name = 'action';
      hiddenAction.value = 'reject';
      bulkForm.appendChild(hiddenAction);
      bulkForm.submit();
    });
  }

});
</script>
<script>
window.BASE_URL = <?= json_encode(BASE_URL) ?>;
window.currentRole = 'admin';
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
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.admin-upcoming-event-link').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = this.getAttribute('data-event-id');
      var dateStr = this.getAttribute('data-event-date') || '';
      setTimeout(function () {
        var opened = typeof window.eventifyOpenEventDetailsById === 'function' && id && window.eventifyOpenEventDetailsById(id);
        if (!opened && window.eventifyCalendar && dateStr) {
          try {
            window.eventifyCalendar.gotoDate(dateStr);
          } catch (e) { /* ignore */ }
        }
      }, 320);
    });
  });
});
</script>
</body>
</html>
