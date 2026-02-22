<?php
session_start();
if (!defined('BASE_URL')) define('BASE_URL','/school_events');

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EVENTIFY</title>
<link rel="stylesheet" href="/school_events/assets/css/index.css">
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
        <a onclick="goToSection('hero')">Home</a>
        <a onclick="goToSection('how-it-works')">How it works</a>
        <a onclick="goToSection('features')">Features</a>
        <a onclick="goToSection('roles')">Roles</a>
        <a onclick="goToSection('faq')">FAQ</a>
        <a onclick="openLoginModal()" class="btn">Get Started</a>
    </nav>
    <div class="mobile-nav-overlay" id="mobileNavOverlay" onclick="closeMobileNav()" aria-hidden="true"></div>
    <nav class="mobile-nav" id="mobileNav">
        <a onclick="goToSection('hero'); closeMobileNav();">Home</a>
        <a onclick="goToSection('how-it-works'); closeMobileNav();">How it works</a>
        <a onclick="goToSection('features'); closeMobileNav();">Features</a>
        <a onclick="goToSection('roles'); closeMobileNav();">Roles</a>
        <a onclick="goToSection('faq'); closeMobileNav();">FAQ</a>
        <a onclick="openLoginModal(); closeMobileNav();" class="btn">Get Started</a>
    </nav>
</header>

<!-- Sections -->
<section id="hero" class="active">
    <h1>Plan and track every school event in one place.</h1>
    <p>
        EVENTIFY is a web & app-based school events monitoring system that helps
        administrators, organizers, and students create, announce, and follow every
        activity without missing a thing.
    </p>
    <div class="hero-buttons">
        <a class="btn" onclick="openLoginModal()">Get Started</a>
        <a class="btn btn-outline" onclick="goToSection('how-it-works')">See how it works</a>
    </div>
</section>

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

<!-- Login Modal -->
<div id="loginModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeLoginModal()">&times;</span>
        <iframe src="<?= BASE_URL ?>/views/login.php"></iframe>
    </div>
  
</div>

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
<script src="/school_events/assets/js/index.js"></script>
</body>
</html>
