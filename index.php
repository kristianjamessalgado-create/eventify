<?php
session_start();
if (!defined('BASE_URL')) define('BASE_URL','/school_events');

$checkin_token = trim($_GET['t'] ?? '');
$auth_modal = trim((string)($_GET['auth_modal'] ?? ''));
$auth_error = trim((string)($_GET['auth_error'] ?? ''));
$auth_success = trim((string)($_GET['auth_success'] ?? ''));

// Public landing: show upcoming active events on a calendar (login required to view details/RSVP)
$publicCalendarEvents = [];
$publicUpcomingList = [];
$publicPastList = [];
$publicAllList = [];
try {
    include __DIR__ . '/config/db.php';
    include __DIR__ . '/config/config.php';
    include __DIR__ . '/config/csrf.php';
    $today = date('Y-m-d');
    $stmtPub = $conn->prepare("
        SELECT id, title, description, date, start_time, end_time, location, department
        FROM events
        WHERE status = 'active'
        ORDER BY date DESC, start_time DESC, id DESC
        LIMIT 400
    ");
    if ($stmtPub) {
        if ($stmtPub->execute()) {
            $res = $stmtPub->get_result();
            while ($row = $res->fetch_assoc()) {
                $date = trim($row['date'] ?? '');
                if ($date === '') continue;
                $startTime = trim((string)($row['start_time'] ?? ''));
                $endTime = trim((string)($row['end_time'] ?? ''));
                $start = $startTime !== '' ? ($date . 'T' . $startTime) : $date;
                $end = $endTime !== '' ? ($date . 'T' . $endTime) : null;
                $publicAllList[] = [
                    'id' => (int)($row['id'] ?? 0),
                    'title' => (string)($row['title'] ?? 'Untitled'),
                    'description' => (string)($row['description'] ?? ''),
                    'date' => $date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'location' => (string)($row['location'] ?? ''),
                    'department' => (string)($row['department'] ?? 'ALL'),
                ];
                $publicCalendarEvents[] = [
                    'id' => (int)($row['id'] ?? 0),
                    'title' => $row['title'] ?? 'Untitled',
                    'start' => $start,
                    'end' => $end,
                    'allDay' => ($startTime === ''),
                    'extendedProps' => [
                        'location' => $row['location'] ?? '',
                        'department' => $row['department'] ?? 'ALL',
                    ],
                ];
            }
        }
        $stmtPub->close();
    }
    if (isset($conn) && $conn) {
        $conn->close();
    }
} catch (Throwable $e) {
    $publicCalendarEvents = [];
    $publicUpcomingList = [];
    $publicPastList = [];
    $publicAllList = [];
}

// Split for display
try {
    $publicUpcomingList = array_values(array_filter($publicAllList, function ($e) use ($today) {
        $d = (string)($e['date'] ?? '');
        return $d !== '' && $d >= $today;
    }));
    $publicPastList = array_values(array_filter($publicAllList, function ($e) use ($today) {
        $d = (string)($e['date'] ?? '');
        return $d !== '' && $d < $today;
    }));
} catch (Throwable $e) {
    $publicPastList = [];
}

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    // Enforce password change before dashboard access if flagged.
    try {
        include __DIR__ . '/config/db.php';
        $uid = (int)($_SESSION['user_id'] ?? 0);
        if ($uid > 0) {
            $cp = $conn->prepare("SELECT must_change_password FROM users WHERE id = ? LIMIT 1");
            if ($cp) {
                $cp->bind_param("i", $uid);
                $cp->execute();
                $cpRow = $cp->get_result()->fetch_assoc();
                $cp->close();
                if ((int)($cpRow['must_change_password'] ?? 0) === 1) {
                    $next = BASE_URL . "/index.php";
                    if ($_SESSION['role'] === 'super_admin') $next = BASE_URL . "/backend/super_admin/dashboardsuperadmin.php";
                    elseif ($_SESSION['role'] === 'admin') $next = BASE_URL . "/backend/admin/dashboard.php";
                    elseif ($_SESSION['role'] === 'organizer') $next = BASE_URL . "/backend/auth/dashboardorganizer.php";
                    elseif ($_SESSION['role'] === 'student') $next = BASE_URL . "/backend/auth/dashboard_student.php";
                    elseif ($_SESSION['role'] === 'multimedia') $next = BASE_URL . "/backend/auth/dashboard_multimedia.php";
                    header("Location: " . BASE_URL . "/views/change_password.php?from=required&next=" . urlencode($next));
                    exit();
                }
            }
        }
    } catch (Throwable $e) {
        // ignore and continue default routing
    }
    // Logged-in student with check-in token: send straight to check-in
    if ($_SESSION['role'] === 'student' && $checkin_token !== '') {
        header("Location: " . BASE_URL . "/checkin.php?t=" . urlencode($checkin_token));
        exit();
    }
    switch ($_SESSION['role']) {
        case 'super_admin':
            header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php");
            exit();
        case 'admin':
            header("Location: " . BASE_URL . "/backend/admin/dashboard.php");
            exit();
        case 'organizer':
            header("Location: " . BASE_URL . "/backend/auth/dashboardorganizer.php");
            exit();
        case 'student':
            header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php");
            exit();
        case 'multimedia':
            header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php");
            exit();
    }
}

// Build login iframe URL: if we have a check-in token, pass redirect so after login they go to check-in
$login_src = BASE_URL . '/views/login.php';
if ($checkin_token !== '') {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $checkin_redirect = $scheme . '://' . $host . BASE_URL . '/checkin.php?t=' . urlencode($checkin_token);
    $login_src .= '?redirect=' . urlencode($checkin_redirect);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EVENTIFY</title>
<link rel="stylesheet" href="/school_events/assets/css/index.css">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
</head>
<body>

<!-- Background layers -->
<img src="/school_events/assets/img/gradient.png" alt="Background" class="bg-image">
<div class="layer-blur"></div>

<!-- Header -->
<header>
    <h2>EVENTIFY</h2>
    <button type="button" class="hamburger" id="hamburgerBtn" aria-label="Open menu">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <nav class="desktop-nav">
        <a onclick="goToSection('public-calendar')">Home</a>
        <a onclick="goToSection('how-it-works')">How it works</a>
        <a onclick="goToSection('features')">Features</a>
        <a onclick="goToSection('roles')">Roles</a>
        <a onclick="goToSection('faq')">FAQ</a>
        <a href="javascript:void(0)" class="btn login-trigger" id="navGetStarted" data-login-url="<?= htmlspecialchars($login_src) ?>">Get Started</a>
    </nav>
    <div class="mobile-nav-overlay" id="mobileNavOverlay" onclick="closeMobileNav()" aria-hidden="true"></div>
    <nav class="mobile-nav" id="mobileNav">
        <a onclick="goToSection('public-calendar'); closeMobileNav();">Home</a>
        <a onclick="goToSection('how-it-works'); closeMobileNav();">How it works</a>
        <a onclick="goToSection('features'); closeMobileNav();">Features</a>
        <a onclick="goToSection('roles'); closeMobileNav();">Roles</a>
        <a onclick="goToSection('faq'); closeMobileNav();">FAQ</a>
        <a href="javascript:void(0)" onclick="closeMobileNav()" class="btn login-trigger" data-login-url="<?= htmlspecialchars($login_src) ?>">Get Started</a>
    </nav>
</header>

<!-- Sections -->
<section id="public-calendar" class="active">
    <h1>Upcoming events calendar</h1>
    <p>Browse what’s coming up. To view full details and RSVP, you’ll be asked to log in.</p>

    <div class="public-upcoming-wrap">
        <h3 class="public-upcoming-title">Upcoming events</h3>
        <?php if (!empty($publicUpcomingList)): ?>
            <div class="public-upcoming-grid">
                <?php foreach (array_slice($publicUpcomingList, 0, 6) as $ev): ?>
                    <?php
                      $dateLabel = '';
                      try { $dateLabel = date('M d, Y', strtotime((string)($ev['date'] ?? ''))); } catch (Throwable $e) { $dateLabel = (string)($ev['date'] ?? ''); }
                      $timeLabel = '';
                      $st = trim((string)($ev['start_time'] ?? ''));
                      $et = trim((string)($ev['end_time'] ?? ''));
                      if ($st !== '' && $et !== '') $timeLabel = $st . ' - ' . $et;
                      elseif ($st !== '') $timeLabel = $st;
                      $deptLabel = trim((string)($ev['department'] ?? ''));
                      $locLabel = trim((string)($ev['location'] ?? ''));
                    ?>
                    <a class="public-upcoming-card login-trigger" href="javascript:void(0)" data-login-url="<?= htmlspecialchars($login_src) ?>" aria-label="Log in to view <?= htmlspecialchars((string)($ev['title'] ?? 'event')) ?>">
                        <div class="public-upcoming-card-top">
                            <div class="public-upcoming-card-title"><?= htmlspecialchars((string)($ev['title'] ?? 'Untitled')) ?></div>
                            <div class="public-upcoming-card-meta">
                                <span><?= htmlspecialchars($dateLabel) ?></span>
                                <?php if ($timeLabel !== ''): ?><span class="dot">•</span><span><?= htmlspecialchars($timeLabel) ?></span><?php endif; ?>
                            </div>
                        </div>
                        <div class="public-upcoming-card-bottom">
                            <?php if ($deptLabel !== ''): ?><span class="chip"><?= htmlspecialchars($deptLabel) ?></span><?php endif; ?>
                            <?php if ($locLabel !== ''): ?><span class="chip chip-muted"><?= htmlspecialchars($locLabel) ?></span><?php endif; ?>
                            <span class="chip chip-cta">Log in to view</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if (count($publicUpcomingList) > 6): ?>
                <div class="public-upcoming-more">
                    <button type="button" class="btn btn-outline login-trigger" data-login-url="<?= htmlspecialchars($login_src) ?>">View all events (login)</button>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="public-empty">
                No upcoming events posted yet.
            </div>
        <?php endif; ?>
    </div>

    <div class="public-upcoming-wrap">
        <h3 class="public-upcoming-title">Existing events</h3>
        <?php if (!empty($publicPastList)): ?>
            <div class="public-upcoming-grid">
                <?php foreach (array_slice($publicPastList, 0, 6) as $ev): ?>
                    <?php
                      $dateLabel = '';
                      try { $dateLabel = date('M d, Y', strtotime((string)($ev['date'] ?? ''))); } catch (Throwable $e) { $dateLabel = (string)($ev['date'] ?? ''); }
                      $timeLabel = '';
                      $st = trim((string)($ev['start_time'] ?? ''));
                      $et = trim((string)($ev['end_time'] ?? ''));
                      if ($st !== '' && $et !== '') $timeLabel = $st . ' - ' . $et;
                      elseif ($st !== '') $timeLabel = $st;
                      $deptLabel = trim((string)($ev['department'] ?? ''));
                      $locLabel = trim((string)($ev['location'] ?? ''));
                    ?>
                    <a class="public-upcoming-card login-trigger" href="javascript:void(0)" data-login-url="<?= htmlspecialchars($login_src) ?>" aria-label="Log in to view <?= htmlspecialchars((string)($ev['title'] ?? 'event')) ?>">
                        <div class="public-upcoming-card-top">
                            <div class="public-upcoming-card-title"><?= htmlspecialchars((string)($ev['title'] ?? 'Untitled')) ?></div>
                            <div class="public-upcoming-card-meta">
                                <span><?= htmlspecialchars($dateLabel) ?></span>
                                <?php if ($timeLabel !== ''): ?><span class="dot">•</span><span><?= htmlspecialchars($timeLabel) ?></span><?php endif; ?>
                            </div>
                        </div>
                        <div class="public-upcoming-card-bottom">
                            <?php if ($deptLabel !== ''): ?><span class="chip"><?= htmlspecialchars($deptLabel) ?></span><?php endif; ?>
                            <?php if ($locLabel !== ''): ?><span class="chip chip-muted"><?= htmlspecialchars($locLabel) ?></span><?php endif; ?>
                            <span class="chip chip-cta">Log in to view</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if (count($publicPastList) > 6): ?>
                <div class="public-upcoming-more">
                    <button type="button" class="btn btn-outline login-trigger" data-login-url="<?= htmlspecialchars($login_src) ?>">View all existing events (login)</button>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="public-empty">
                No existing events yet.
            </div>
        <?php endif; ?>
    </div>

    <div class="public-calendar-card">
        <div class="public-calendar-toolbar">
            <div class="public-calendar-note">
                <span class="pill">Public view</span>
                <span class="muted">Tap an event to log in</span>
                <span class="calendar-month-display" id="publicCalendarMonth"><?= date('F Y') ?></span>
            </div>
            <a class="btn btn-sm login-trigger" href="javascript:void(0)" data-login-url="<?= htmlspecialchars($login_src) ?>">Log in</a>
        </div>
        <div id="publicCalendar"></div>
    </div>
    <script>
      window.EVENTIFY_BASE_URL = <?= json_encode(BASE_URL) ?>;
      window.PUBLIC_LOGIN_URL = <?= json_encode($login_src) ?>;
      window.PUBLIC_CALENDAR_EVENTS = <?= json_encode($publicCalendarEvents, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;
      window.AUTH_MODAL = <?= json_encode($auth_modal) ?>;
      window.AUTH_ERROR = <?= json_encode($auth_error) ?>;
      window.AUTH_SUCCESS = <?= json_encode($auth_success) ?>;
    </script>
</section>

<section id="hero">
    <?php if ($checkin_token !== ''): ?>
    <p class="hero-checkin-notice" style="background: rgba(102, 126, 234, 0.2); color: #c7d2fe; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.95rem;">
        <strong>Event check-in.</strong> Log in with your student account to confirm your attendance.
    </p>
    <?php endif; ?>
    <h1>Plan and track every school event in one place.</h1>
    <p>
        EVENTIFY is a web & app-based school events monitoring system that helps
        administrators, organizers, and students create, announce, and follow every
        activity without missing a thing.
    </p>
    <div class="hero-buttons">
        <a class="btn login-trigger" href="javascript:void(0)" id="heroGetStarted" data-login-url="<?= htmlspecialchars($login_src) ?>"><?= $checkin_token !== '' ? 'Log in to check in' : 'Get Started' ?></a>
        <a class="btn btn-outline" onclick="goToSection('how-it-works')">See how it works</a>
    </div>
</section>

<!-- Auth Modals (desktop only; on mobile we redirect to full pages) -->
<div id="loginModal" class="modal auth-modal">
    <div class="modal-content auth-modal-content">
        <span class="close" onclick="closeLoginModal()" aria-label="Close">&times;</span>
        <div class="auth-form-box">
            <h3 class="auth-title">Login</h3>
            <p class="auth-subtitle">Welcome back. Sign in to continue.</p>
            <div class="auth-inline-message" id="loginModalMessage" style="display:none;"></div>
            <?php if ($auth_modal === 'login' && $auth_error !== ''): ?>
                <div class="auth-inline-message error" id="loginModalMessageServer"><?= htmlspecialchars($auth_error) ?></div>
            <?php elseif ($auth_modal === 'login' && $auth_success !== ''): ?>
                <div class="auth-inline-message success" id="loginModalMessageServer"><?= htmlspecialchars($auth_success) ?></div>
            <?php endif; ?>
            <form id="loginModalForm" action="<?= BASE_URL ?>/backend/auth/auth.php" method="POST" class="auth-form-wrap">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="login">
                <div class="auth-input-wrap">
                    <label class="auth-label" for="loginModalEmail">Email</label>
                    <input type="email" name="email" id="loginModalEmail" class="auth-input" placeholder="you@example.com" required>
                </div>
                <div class="auth-input-wrap auth-password-wrap">
                    <label class="auth-label" for="loginModalPassword">Password</label>
                    <input type="password" name="password" id="loginModalPassword" class="auth-input" placeholder="Enter password" required>
                    <button type="button" class="auth-eye-btn" id="toggleLoginModalPassword" aria-label="Show password" aria-pressed="false">👁</button>
                </div>
                <button type="submit" class="login-modal-action-btn primary auth-submit-btn">Login</button>
            </form>
        </div>
        <div class="login-modal-action-row">
            <button type="button" class="login-modal-action-btn secondary" onclick="openRegisterModal()">Register</button>
            <button type="button" class="login-modal-action-btn ghost" onclick="openVerifyModal()">Verify Reactivation OTP</button>
        </div>
    </div>
</div>

<div id="registerModal" class="modal auth-modal">
    <div class="modal-content auth-modal-content">
        <span class="close" onclick="closeRegisterModal()" aria-label="Close">&times;</span>
        <div class="auth-form-box">
            <h3 class="auth-title">Register</h3>
            <p class="auth-subtitle">Create your account to use EVENTIFY.</p>
            <div class="auth-inline-message" id="registerModalMessage" style="display:none;"></div>
            <?php if ($auth_modal === 'register' && $auth_error !== ''): ?>
                <div class="auth-inline-message error" id="registerModalMessageServer"><?= htmlspecialchars($auth_error) ?></div>
            <?php endif; ?>
            <form id="registerModalForm" action="<?= BASE_URL ?>/backend/auth/auth.php" method="POST" class="auth-form-wrap">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="register">
                <div class="auth-input-wrap">
                    <label class="auth-label" for="registerModalName">Username</label>
                    <input type="text" name="name" id="registerModalName" class="auth-input" placeholder="Your name" required>
                </div>
                <div class="auth-input-wrap">
                    <label class="auth-label" for="registerModalEmail">Email</label>
                    <input type="email" name="email" id="registerModalEmail" class="auth-input" placeholder="you@example.com" required>
                </div>
                <div class="auth-input-wrap auth-password-wrap">
                    <label class="auth-label" for="registerModalPassword">Password</label>
                    <input type="password" name="password" id="registerModalPassword" class="auth-input" placeholder="Password" required>
                    <button type="button" class="auth-eye-btn" id="toggleRegisterModalPassword" aria-label="Show password" aria-pressed="false">👁</button>
                </div>
                <div class="auth-input-wrap auth-password-wrap">
                    <label class="auth-label" for="registerModalConfirmPassword">Confirm Password</label>
                    <input type="password" name="confirm_password" id="registerModalConfirmPassword" class="auth-input" placeholder="Confirm Password" required>
                    <button type="button" class="auth-eye-btn" id="toggleRegisterModalConfirmPassword" aria-label="Show password" aria-pressed="false">👁</button>
                </div>
                <div class="auth-input-wrap">
                    <label class="auth-label" for="registerRoleSelectModal">Role</label>
                    <select name="role" id="registerRoleSelectModal" class="auth-input" required>
                        <option value="">Select Role</option>
                        <option value="student">Student</option>
                        <option value="organizer">Organizer</option>
                        <option value="multimedia">Multimedia</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="auth-input-wrap" id="registerDepartmentWrapModal" style="display:none;">
                    <label class="auth-label" for="registerDepartmentSelectModal">Department</label>
                    <select name="department" id="registerDepartmentSelectModal" class="auth-input">
                        <option value="">Select Department</option>
                        <option value="High school department">High School Department</option>
                        <option value="College of Communication, Information and Technology">College of Communication, Information and Technology</option>
                        <option value="College of Accountancy and Business">College of Accountancy and Business</option>
                        <option value="School of Law and Political Science">School of Law and Political Science</option>
                        <option value="College of Education">College of Education</option>
                        <option value="College of Nursing and Allied health sciences">College of Nursing and Allied health sciences</option>
                        <option value="College of Hospitality Management">College of Hospitality Management</option>
                    </select>
                </div>
                <button type="submit" class="login-modal-action-btn primary auth-submit-btn">Register</button>
            </form>
        </div>
        <div class="login-modal-action-row">
            <button type="button" class="login-modal-action-btn primary" onclick="openLoginModal()">Back to Login</button>
        </div>
    </div>
</div>

<div id="verifyModal" class="modal auth-modal">
    <div class="modal-content auth-modal-content">
        <span class="close" onclick="closeVerifyModal()" aria-label="Close">&times;</span>
        <div class="auth-form-box">
            <h3 class="auth-title">Verify Reactivation OTP</h3>
            <p class="auth-subtitle">Enter the code sent to your registered email.</p>
            <div class="auth-inline-message" id="verifyModalMessage" style="display:none;"></div>
            <form id="verifyModalForm" action="<?= BASE_URL ?>/backend/auth/verify_account_otp.php" method="POST" class="auth-form-wrap">
                <?= csrf_field() ?>
                <input type="hidden" name="purpose" value="reactivate">
                <div class="auth-input-wrap">
                    <label class="auth-label" for="verifyModalEmail">Registered Email</label>
                    <input type="email" name="email" id="verifyModalEmail" class="auth-input" placeholder="Registered Email" required>
                </div>
                <div class="auth-input-wrap">
                    <label class="auth-label" for="verifyOtpCode">6-digit OTP</label>
                    <input type="text" name="otp_code" id="verifyOtpCode" class="auth-input" placeholder="Enter code" required maxlength="6" pattern="\d{6}">
                </div>
                <button type="submit" class="login-modal-action-btn primary auth-submit-btn">Verify OTP</button>
            </form>
        </div>
        <div class="login-modal-action-row">
            <button type="button" class="login-modal-action-btn primary" onclick="openLoginModal()">Back to Login</button>
        </div>
    </div>
</div>

<section id="how-it-works">
    <h1>How EVENTIFY works</h1>
    <div class="grid">
        <div class="card">
            <h3 style="margin-bottom: 8px; font-size: 1.2rem;">1. Plan</h3>
            <p style="font-size: 0.95rem; color: #cbd5e1;">
                Organizers create events, choose which departments can see them, and set dates
                and locations in a few clicks.
            </p>
        </div>
        <div class="card">
            <h3 style="margin-bottom: 8px; font-size: 1.2rem;">2. Announce & track</h3>
            <p style="font-size: 0.95rem; color: #cbd5e1;">
                Students see upcoming events on a Google-style calendar filtered to their
                department, while admins and organizers review and update events in one
                centralized place.
            </p>
        </div>
    </div>
</section>

<section id="features">
    <h1>Powerful features for your campus</h1>
    <div class="grid">
        <div class="card">
            <h3 style="margin-bottom: 10px; font-size: 1.3rem;">📅 Smart event creation</h3>
            <p style="font-size: 0.95rem; color: #cbd5e1;">
                Create events in a few clicks, set dates and locations, and target the right
                departments or the whole school.
            </p>
        </div>
        <div class="card">
            <h3 style="margin-bottom: 10px; font-size: 1.3rem;">🗓 Google-style calendars</h3>
            <p style="font-size: 0.95rem; color: #cbd5e1;">
                Organizer and student dashboards use a clean month/week/day calendar view so
                everyone can see what is happening at a glance.
            </p>
        </div>
        <div class="card">
            <h3 style="margin-bottom: 10px; font-size: 1.3rem;">🎯 Department-based visibility</h3>
            <p style="font-size: 0.95rem; color: #cbd5e1;">
                Events can be exclusive to BSIT, BSHM, CONAHS, Senior High, or visible to all —
                students only see what is relevant to them.
            </p>
        </div>
        <div class="card">
            <h3 style="margin-bottom: 10px; font-size: 1.3rem;">👤 Personalized dashboards</h3>
            <p style="font-size: 0.95rem; color: #cbd5e1;">
                Students can update their profile and view events in one place, while organizers
                manage and edit their events easily.
            </p>
        </div>
    </div>
</section>

<section id="roles">
    <h1>Built for your whole school</h1>
    <div class="grid">
        <div class="card">
            <h3 style="margin-bottom: 10px; font-size: 1.3rem;">👨‍💼 Administrators</h3>
            <p style="font-size: 0.95rem; color: #cbd5e1;">
                Get a clear view of all upcoming events, departments involved, and organizers
                responsible for each activity.
            </p>
        </div>
        <div class="card">
            <h3 style="margin-bottom: 10px; font-size: 1.3rem;">🎯 Organizers</h3>
            <p style="font-size: 0.95rem; color: #cbd5e1;">
                Create, edit, and manage events from a modern Google-like calendar, and see
                exactly which students you are targeting.
            </p>
        </div>
        <div class="card">
            <h3 style="margin-bottom: 10px; font-size: 1.3rem;">🎓 Students</h3>
            <p style="font-size: 0.95rem; color: #cbd5e1;">
                View upcoming events for your department, avoid schedule conflicts, and keep
                track of your participation throughout the school year.
            </p>
        </div>
    </div>
</section>

<section id="faq">
    <h1>Frequently asked questions</h1>
    <div class="grid">
        <div class="card" style="text-align:left;">
            <h3 style="margin-bottom: 6px; font-size: 1.1rem;">Who can create events?</h3>
            <p style="font-size: 0.95rem; color: #cbd5e1;">
                Only users with an organizer or admin account can create and manage events from
                their dashboard.
            </p>
        </div>
        <div class="card" style="text-align:left;">
            <h3 style="margin-bottom: 6px; font-size: 1.1rem;">What do students see?</h3>
            <p style="font-size: 0.95rem; color: #cbd5e1;">
                Students see events that are tagged for their department or for all departments,
                displayed in a clean calendar view.
            </p>
        </div>
    </div>
</section>

<script type="module" src="https://unpkg.com/@splinetool/viewer@1.12.39/build/spline-viewer.js"></script>
<div class="spline-wrap">
    <spline-viewer url="https://prod.spline.design/QKWcuhuYDwcet-bm/scene.splinecode"></spline-viewer>
</div>

<!-- Footer -->
<footer>
    <div class="footer-inner">
        <div class="footer-left">
            <span class="footer-brand">EVENTIFY</span>
            <span class="footer-text">Web & App-Based School Events Monitoring System</span>
        </div>
        <div class="footer-links">
            <a href="javascript:void(0)" onclick="goToSection('features')">Features</a>
            <a href="javascript:void(0)" onclick="goToSection('roles')">Roles</a>
            <a href="mailto:youremail@example.com">Contact</a>
        </div>
    </div>
</footer>


<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="/school_events/assets/js/index.js"></script>
</body>
</html>
