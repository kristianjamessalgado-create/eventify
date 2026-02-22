<?php
// Ensure BASE_URL is defined
if (!defined('BASE_URL')) {
    define('BASE_URL', '/school_events');
}

// Fallbacks in case the controller didn't pass data
$user_name = $user_name ?? 'Student';
$user      = $user ?? ['name' => 'Student', 'user_id' => 'N/A', 'department' => null];
$events    = $events ?? []; // always an array
$msg       = $msg ?? '';
$department = $user['department'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - EVENTIFY</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/dashboard_student.css">
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
        <button class="nav-btn" title="Calendar">
            <i class="fas fa-calendar"></i>
        </button>
        <button class="nav-btn" title="Notifications">
            <i class="fas fa-bell"></i>
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
                <span class="mini-cal-month" id="miniCalMonth">September 2026</span>
                <button class="mini-cal-nav" id="miniCalNext"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="mini-calendar-grid" id="miniCalendar"></div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3 class="section-title">QUICK ACTIONS</h3>
            <a href="<?= BASE_URL ?>/upcoming_events.php" class="action-btn">
                <i class="fas fa-calendar-check"></i>
                <span>Upcoming Events</span>
            </a>
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

        <!-- Upcoming Events List -->
        <div class="upcoming-events-section">
            <h3 class="section-heading">Upcoming Events</h3>
            <?php if (!empty($events)): ?>
                <div class="events-list">
                    <?php foreach (array_slice($events, 0, 5) as $event): ?>
                        <div class="event-item">
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
    </main>
</div>

<!-- Profile Modal -->
<div id="profileModal" class="profile-modal">
    <div class="profile-modal-content">
        <span class="profile-close" onclick="closeProfileModal()">&times;</span>
        <h2>My Information</h2>
        <form id="profileForm" action="<?= BASE_URL ?>/backend/auth/update_student_profile.php" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault(); confirmProfileChanges(this);">
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
window.studentEvents = <?= json_encode(array_map(function($e) {
    return [
        'id'    => $e['id'] ?? null,
        'title' => $e['title'] ?? 'Untitled',
        'start' => $e['date'] ?? null,
        'extendedProps' => [
            'location'    => $e['location'] ?? '',
            'description' => $e['description'] ?? '',
            'department'  => $e['department'] ?? 'ALL',
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
</script>

</body>
</html>
