<?php
if (!defined('EVENTIFY_SUPERADMIN_DASHBOARD_LOADED')) {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/school_events') . '/views/login.php');
    exit;
}
$superadmin_name = $superadmin_name ?? 'Super Admin';
$users = $users ?? [];
$logs = $logs ?? [];
$pendingEvents = $pendingEvents ?? [];
$allEvents = $allEvents ?? [];
$saUserRoleLabels = $saUserRoleLabels ?? ['Super Admin', 'Admin', 'Organizer', 'Multimedia', 'Student'];
$saUserRoleCounts = $saUserRoleCounts ?? [0,0,0,0,0];
$saEventStatusLabels = $saEventStatusLabels ?? ['Pending', 'Active', 'Rejected', 'Closed'];
$saEventStatusCounts = $saEventStatusCounts ?? [0,0,0,0];
$usersPage = isset($usersPage) ? (int) $usersPage : 1;
$usersTotalPages = isset($usersTotalPages) ? (int) $usersTotalPages : 1;
$eventsPage = isset($eventsPage) ? (int) $eventsPage : 1;
$eventsTotalPages = isset($eventsTotalPages) ? (int) $eventsTotalPages : 1;
$success = $success ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - EVENTIFY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            color: #1e293b;
        }
        .sa-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            background: rgba(10, 10, 10, 0.85);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }
        .sa-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 800;
            font-size: 1.35rem;
            color: #e7e7e7;
            letter-spacing: -0.02em;
        }
        .sa-brand i { color: #00bfff; }
        .sa-user {
            color: #94a3b8;
            font-size: 0.95rem;
            font-weight: 500;
            margin-right: 1rem;
        }
        .sa-logout {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 600;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.2s;
        }
        .sa-logout:hover { color: #00bfff; border-color: #00bfff; background: rgba(0, 191, 255, 0.1); }
        .sa-main {
            padding: 2rem;
        }
        .sa-layout {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            gap: 1.5rem;
        }
        .sa-sidebar {
            width: 260px;
            background: rgba(15, 23, 42, 0.9);
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.35);
            color: #e5e7eb;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .sa-sidebar-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }
        .sa-sidebar-avatar {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: linear-gradient(135deg, #0ea5e9, #22c55e);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #0b1120;
        }
        .sa-sidebar-name {
            font-size: 0.95rem;
            font-weight: 600;
        }
        .sa-sidebar-role {
            font-size: 0.75rem;
            color: #9ca3af;
        }
        .sa-nav-group-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            margin-top: 0.75rem;
            margin-bottom: 0.25rem;
        }
        .sa-nav-btn {
            width: 100%;
            border-radius: 10px;
            padding: 0.55rem 0.75rem;
            border: 1px solid rgba(148, 163, 184, 0.3);
            background: rgba(15, 23, 42, 0.9);
            color: #e5e7eb;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.18s ease;
        }
        .sa-nav-btn i {
            font-size: 0.9rem;
        }
        .sa-nav-btn span {
            flex: 1;
            text-align: left;
        }
        .sa-nav-btn:hover {
            border-color: #38bdf8;
            background: rgba(15, 23, 42, 0.98);
            color: #e0f2fe;
            box-shadow: 0 0 0 1px rgba(56, 189, 248, 0.35);
        }
        .sa-nav-btn.primary {
            border-color: #22c55e;
            background: rgba(22, 163, 74, 0.08);
        }
        .sa-nav-btn.primary:hover {
            background: rgba(22, 163, 74, 0.2);
            box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.4);
        }
        .sa-nav-footer {
            margin-top: auto;
            font-size: 0.75rem;
            color: #64748b;
        }
        .sa-nav-footer strong {
            color: #e5e7eb;
        }
        .sa-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .sa-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            padding: 1.25rem 1.5rem 1.5rem;
        }
        .sa-stat-card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 0.9rem 1rem;
            background: #f8fafc;
        }
        .sa-stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .sa-stat-value {
            font-size: 1.4rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.1rem;
        }
        .sa-stat-sub {
            font-size: 0.8rem;
            color: #6b7280;
        }
        .sa-charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
            padding: 0 1.5rem 1.5rem;
        }
        .sa-chart-card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #fff;
            padding: 0.9rem;
        }
        .sa-chart-title {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #64748b;
            margin-bottom: 0.5rem;
        }
        .sa-chart-wrap { position: relative; height: 220px; }
        .sa-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }
        .sa-card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
        }
        .sa-card-header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sa-card-header h1 i { color: #00bfff; }
        .sa-alert {
            margin: 0 2rem 1.5rem;
            border-radius: 12px;
            padding: 1rem 1.25rem;
        }
        .sa-table-wrap { padding: 0 1.5rem 1.5rem; overflow-x: auto; }
        .sa-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .sa-table thead tr {
            background: #f1f5f9;
            border-bottom: 2px solid #e2e8f0;
        }
        .sa-table th {
            padding: 1rem 1.25rem;
            text-align: left;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.75rem;
        }
        .sa-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        .sa-table tbody tr:hover { background: #f8fafc; }
        .sa-table tbody tr:last-child td { border-bottom: none; }
        .sa-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .sa-badge-active { background: #dcfce7; color: #166534; }
        .sa-badge-inactive { background: #f1f5f9; color: #64748b; }
        .sa-badge-pending { background: #fef3c7; color: #92400e; }
        .sa-badge-rejected { background: #fee2e2; color: #b91c1c; }
        .sa-badge-closed { background: #e2e8f0; color: #475569; }
        .sa-badge-dept { background: #eff6ff; color: #1d4ed8; }
        .sa-actions { display: flex; flex-wrap: wrap; gap: 0.35rem; }
        .sa-btn-react {
            padding: 0.4rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            background: #10b981;
            color: #fff;
            cursor: pointer;
            transition: all 0.2s;
        }
        .sa-btn-react:hover { background: #059669; color: #fff; }
        .sa-empty {
            text-align: center;
            padding: 3rem 2rem;
            color: #64748b;
        }
        .sa-empty i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }
        @media (max-width: 768px) {
            .sa-navbar { padding: 0.875rem 1rem; flex-wrap: wrap; gap: 0.5rem; }
            .sa-main { padding: 1rem; }
            .sa-layout { flex-direction: column; }
            .sa-sidebar { width: 100%; }
            .sa-card-header, .sa-table th, .sa-table td { padding: 0.75rem 1rem; }
            .sa-table { font-size: 0.85rem; }
            .modal-dialog {
                margin: 0.5rem;
            }
            .sa-stats-grid {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
            .sa-charts-grid {
                grid-template-columns: 1fr;
                padding: 0 1rem 1rem;
            }
            .sa-table-wrap {
                padding: 0 0.75rem 1rem;
            }
            .sa-actions {
                gap: 0.25rem;
            }
        }
    </style>
</head>
<body>

<nav class="sa-navbar">
    <div class="sa-brand">
        <i class="fas fa-shield-alt"></i>
        <span>EVENTIFY</span>
    </div>
    <div class="d-flex align-items-center">
        <span class="sa-user"><i class="fas fa-user-shield me-1"></i> <?= htmlspecialchars($superadmin_name) ?></span>
        <a href="#" class="sa-logout" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<main class="sa-main">
    <div class="sa-layout">
        <!-- Sidebar -->
        <aside class="sa-sidebar">
            <div class="sa-sidebar-header">
                <div class="sa-sidebar-avatar">
                    <?= strtoupper(substr($superadmin_name, 0, 1)) ?>
                </div>
                <div>
                    <div class="sa-sidebar-name"><?= htmlspecialchars($superadmin_name) ?></div>
                    <div class="sa-sidebar-role">Super Admin</div>
                </div>
            </div>

            <div>
                <div class="sa-nav-group-label">Management</div>
                <button
                    type="button"
                    class="sa-nav-btn primary mb-1"
                    data-bs-toggle="modal"
                    data-bs-target="#usersModal"
                >
                    <span><i class="fas fa-users me-2"></i>All Users</span>
                    <i class="fas fa-chevron-right"></i>
                </button>
                <button
                    type="button"
                    class="sa-nav-btn mb-1"
                    data-bs-toggle="modal"
                    data-bs-target="#activityModal"
                >
                    <span><i class="fas fa-clipboard-list me-2"></i>Recent Activity</span>
                    <i class="fas fa-chevron-right"></i>
                </button>
                <button
                    type="button"
                    class="sa-nav-btn mb-1"
                    data-bs-toggle="modal"
                    data-bs-target="#pendingEventsModal"
                >
                    <span><i class="fas fa-calendar-check me-2"></i>Pending Events</span>
                    <i class="fas fa-chevron-right"></i>
                </button>
                <button
                    type="button"
                    class="sa-nav-btn mb-1"
                    data-bs-toggle="modal"
                    data-bs-target="#allEventsModal"
                >
                    <span><i class="fas fa-calendar-day me-2"></i>All Events</span>
                    <i class="fas fa-chevron-right"></i>
                </button>
                <button
                    type="button"
                    class="sa-nav-btn mb-1"
                    data-bs-toggle="modal"
                    data-bs-target="#calendarModal"
                >
                    <span><i class="fas fa-calendar-alt me-2"></i>Calendar</span>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <div class="sa-nav-footer">
                <div>Signed in as <strong><?= htmlspecialchars($superadmin_name) ?></strong></div>
                <div class="mt-1">Use the menu to manage users, approvals, and view logs.</div>
            </div>
        </aside>

        <!-- Main content -->
        <section class="sa-content">
            <div class="sa-card">
                <div class="sa-card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h1 class="mb-0"><i class="fas fa-gauge-high"></i> Super Admin Dashboard</h1>
                    <span class="text-muted small">
                        <i class="fas fa-user-shield me-1"></i>Logged in as <strong><?= htmlspecialchars($superadmin_name) ?></strong>
                    </span>
                </div>
                <?php if ($success): ?>
                    <div class="sa-table-wrap">
                        <div class="alert alert-success alert-dismissible fade show sa-alert mt-3" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="sa-stats-grid">
                    <div class="sa-stat-card">
                        <div class="sa-stat-label">
                            <i class="fas fa-users"></i><span>Total Users</span>
                        </div>
                        <div class="sa-stat-value"><?= (int)($userStats['total'] ?? 0) ?></div>
                        <div class="sa-stat-sub">
                            Active: <?= (int)($userStats['active'] ?? 0) ?> · Inactive: <?= (int)($userStats['inactive'] ?? 0) ?>
                        </div>
                    </div>
                    <div class="sa-stat-card">
                        <div class="sa-stat-label">
                            <i class="fas fa-user-tie"></i><span>Admins &amp; Organizers</span>
                        </div>
                        <div class="sa-stat-value">
                            <?= (int)(($userStats['admin'] ?? 0) + ($userStats['organizer'] ?? 0)) ?>
                        </div>
                        <div class="sa-stat-sub">
                            Admin: <?= (int)($userStats['admin'] ?? 0) ?> · Organizer: <?= (int)($userStats['organizer'] ?? 0) ?>
                        </div>
                    </div>
                    <div class="sa-stat-card">
                        <div class="sa-stat-label">
                            <i class="fas fa-calendar-alt"></i><span>Events</span>
                        </div>
                        <div class="sa-stat-value"><?= (int)($eventStats['total'] ?? 0) ?></div>
                        <div class="sa-stat-sub">
                            Pending: <?= (int)($eventStats['pending'] ?? 0) ?> · Active: <?= (int)($eventStats['active'] ?? 0) ?>
                        </div>
                    </div>
                    <div class="sa-stat-card">
                        <div class="sa-stat-label">
                            <i class="fas fa-right-to-bracket"></i><span>Logins Today</span>
                        </div>
                        <div class="sa-stat-value"><?= (int)($loginTodayCount ?? 0) ?></div>
                        <div class="sa-stat-sub">
                            Successful sign-ins recorded for <?= htmlspecialchars(date('Y-m-d')) ?>
                        </div>
                    </div>
                </div>
                <div class="sa-charts-grid">
                    <div class="sa-chart-card">
                        <div class="sa-chart-title">Users by role</div>
                        <div class="sa-chart-wrap"><canvas id="saUsersChart"></canvas></div>
                    </div>
                    <div class="sa-chart-card">
                        <div class="sa-chart-title">Events by status</div>
                        <div class="sa-chart-wrap"><canvas id="saEventsChart"></canvas></div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- All Users Modal -->
<div class="modal fade" id="usersModal" tabindex="-1" aria-labelledby="usersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="usersModalLabel">
                    <i class="fas fa-users me-2"></i>All Users
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="sa-table-wrap">
                    <?php if (empty($users)): ?>
                        <div class="sa-empty">
                            <i class="fas fa-users-slash"></i>
                            <p class="mb-0">No users found.</p>
                        </div>
                    <?php else: ?>
                        <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                            <input
                                type="text"
                                id="userSearch"
                                class="form-control form-control-sm"
                                placeholder="Search name or email"
                                style="max-width: 220px;"
                            >
                            <select id="roleFilter" class="form-select form-select-sm" style="max-width: 180px;">
                                <option value="">All Roles</option>
                                <option value="super_admin">Super Admin</option>
                                <option value="admin">Admin</option>
                                <option value="organizer">Organizer</option>
                                <option value="multimedia">Multimedia</option>
                                <option value="student">Student</option>
                            </select>
                            <select id="statusFilter" class="form-select form-select-sm" style="max-width: 160px;">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <table class="sa-table" id="usersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <?php
                                        $uid   = (int)$user['id'];
                                        $role  = $user['role'] ?? '';
                                        $status = $user['status'] ?? '';
                                        $failedAttempts = (int)($user['failed_attempts'] ?? 0);
                                        $isLockedAccount = $failedAttempts >= 5;
                                    ?>
                                    <tr
                                        data-role="<?= htmlspecialchars($role) ?>"
                                        data-status="<?= htmlspecialchars($status) ?>"
                                    >
                                        <td><span class="text-muted">#<?= $uid ?></span></td>
                                        <td><strong><?= htmlspecialchars($user['name']) ?></strong></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <form method="POST" action="<?= BASE_URL ?>/backend/super_admin/update_user_role.php" class="d-flex gap-1 align-items-center">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="user_id" value="<?= $uid ?>">
                                                <select name="new_role" class="form-select form-select-sm" style="min-width: 140px;">
                                                    <?php
                                                        $allRoles = ['super_admin','admin','organizer','multimedia','student'];
                                                        foreach ($allRoles as $r):
                                                    ?>
                                                        <option value="<?= $r ?>" <?= $role === $r ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $r))) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-rotate"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <?php if ($status === 'active'): ?>
                                                <span class="sa-badge sa-badge-active">Active</span>
                                            <?php else: ?>
                                                <span class="sa-badge sa-badge-inactive"><?= ucfirst($status) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php if ($status === 'inactive'): ?>
                                                    <?php if ($isLockedAccount): ?>
                                                        <small class="text-muted d-block mb-1">Locked account: send OTP first via Reactivate.</small>
                                                        <button type="button"
                                                                class="sa-btn-react"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#reactivateConfirmModal"
                                                                data-user-id="<?= $uid ?>">
                                                            <i class="fas fa-user-check me-1"></i> Reactivate
                                                        </button>
                                                    <?php else: ?>
                                                        <small class="text-muted d-block mb-1">New/pending account: use Activate.</small>
                                                        <form method="POST" action="<?= BASE_URL ?>/backend/super_admin/activate_user.php" class="d-inline" onsubmit="return confirm('Activate this pending account?');">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="id" value="<?= $uid ?>">
                                                            <button type="submit" class="sa-btn-react">
                                                                <i class="fas fa-user-check me-1"></i> Activate
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php if ($role !== 'super_admin'): ?>
                                                        <form method="POST" action="<?= BASE_URL ?>/backend/super_admin/deactivate_user.php" class="d-inline" onsubmit="return confirm('Deactivate this user account?');">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="id" value="<?= $uid ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="fas fa-user-slash"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">Page <?= (int)$usersPage ?> of <?= (int)$usersTotalPages ?></small>
                            <div class="btn-group btn-group-sm">
                                <?php $prevUsersPage = max(1, $usersPage - 1); $nextUsersPage = min($usersTotalPages, $usersPage + 1); ?>
                                <a class="btn btn-outline-secondary <?= $usersPage <= 1 ? 'disabled' : '' ?>" href="<?= BASE_URL ?>/backend/super_admin/dashboardsuperadmin.php?users_page=<?= $prevUsersPage ?>&events_page=<?= (int)$eventsPage ?>">Prev</a>
                                <a class="btn btn-outline-secondary <?= $usersPage >= $usersTotalPages ? 'disabled' : '' ?>" href="<?= BASE_URL ?>/backend/super_admin/dashboardsuperadmin.php?users_page=<?= $nextUsersPage ?>&events_page=<?= (int)$eventsPage ?>">Next</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Modal -->
<div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityModalLabel">
                    <i class="fas fa-clipboard-list me-2"></i>Recent Activity
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="sa-table-wrap">
                    <?php if (empty($logs)): ?>
                        <div class="sa-empty">
                            <i class="fas fa-clipboard-check"></i>
                            <p class="mb-0">No recent activity recorded.</p>
                        </div>
                    <?php else: ?>
                        <table class="sa-table">
                            <thead>
                                <tr>
                                    <th>When</th>
                                    <th>Actor</th>
                                    <th>Action</th>
                                    <th>Target</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td>
                                            <span class="text-muted small">
                                                <?= htmlspecialchars(date('Y-m-d H:i', strtotime($log['created_at'] ?? 'now'))) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($log['actor_name'] ?? 'System') ?></strong>
                                            <?php if (!empty($log['actor_role'])): ?>
                                                <div class="small text-muted"><?= htmlspecialchars($log['actor_role']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars(str_replace('_', ' ', $log['action'] ?? '')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($log['target_type'])): ?>
                                                <span class="small text-muted">
                                                    <?= htmlspecialchars(ucfirst($log['target_type'])) ?>
                                                    <?php if (!empty($log['target_id'])): ?>
                                                        #<?= (int)$log['target_id'] ?>
                                                    <?php endif; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="small">
                                                <?= htmlspecialchars($log['details'] ?? '') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Events Modal -->
<div class="modal fade" id="pendingEventsModal" tabindex="-1" aria-labelledby="pendingEventsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pendingEventsModalLabel">
                    <i class="fas fa-calendar-check me-2"></i>Pending Event Approvals
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="sa-table-wrap">
                    <?php if (empty($pendingEvents)): ?>
                        <div class="sa-empty">
                            <i class="fas fa-calendar-times"></i>
                            <p class="mb-0">No events are currently waiting for approval.</p>
                        </div>
                    <?php else: ?>
                        <table class="sa-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Event</th>
                                    <th>Date &amp; Location</th>
                                    <th>Organizer</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingEvents as $event): ?>
                                    <tr>
                                        <td><span class="text-muted">#<?= (int)$event['id'] ?></span></td>
                                        <td>
                                            <strong><?= htmlspecialchars($event['title'] ?? 'Untitled') ?></strong>
                                            <?php if (!empty($event['description'])): ?>
                                                <div class="text-muted small mt-1">
                                                    <?= nl2br(htmlspecialchars(mb_strimwidth($event['description'], 0, 120, '...'))) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <i class="fas fa-calendar-day me-1 text-primary"></i>
                                                <?= htmlspecialchars($event['date'] ?? 'TBA') ?>
                                            </div>
                                            <div class="small mt-1">
                                                <i class="fas fa-location-dot me-1 text-secondary"></i>
                                                <?= htmlspecialchars($event['location'] ?? 'TBA') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold">
                                                <i class="fas fa-user me-1 text-secondary"></i>
                                                <?= htmlspecialchars($event['organizer_name'] ?? 'Unknown') ?>
                                            </div>
                                            <?php if (!empty($event['organizer_email'])): ?>
                                                <div class="small text-muted">
                                                    <?= htmlspecialchars($event['organizer_email']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="sa-badge sa-badge-dept">
                                                <?= htmlspecialchars(($event['department'] ?? 'ALL') === 'ALL' ? 'All Departments' : ($event['department'] ?? 'ALL')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="sa-badge sa-badge-pending">
                                                <i class="fas fa-hourglass-half me-1"></i> Pending
                                            </span>
                                        </td>
                                        <td>
                                            <div class="sa-actions">
                                                <a href="<?= BASE_URL ?>/event_attendance.php?id=<?= (int)$event['id'] ?>" class="sa-btn sa-btn-outline btn btn-sm" target="_blank" rel="noopener" title="View who attended"><i class="fas fa-clipboard-check me-1"></i>Attendance</a>
                                                <form method="POST" action="<?= BASE_URL ?>/backend/super_admin/update_event_status.php" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="sa-btn-approve">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <button type="button" class="sa-btn-reject" data-bs-toggle="modal" data-bs-target="#rejectEventModal" data-event-id="<?= (int)$event['id'] ?>" data-event-title="<?= htmlspecialchars($event['title'] ?? '') ?>" data-return-to="dashboard">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- All Events Modal (Event Management) -->
<div class="modal fade" id="allEventsModal" tabindex="-1" aria-labelledby="allEventsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="allEventsModalLabel">
                    <i class="fas fa-calendar-day me-2"></i>All Events
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                    <input type="text" id="eventSearch" class="form-control form-control-sm" placeholder="Search title or location" style="max-width: 220px;">
                    <select id="allEventsStatusFilter" class="form-select form-select-sm" style="max-width: 160px;">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="rejected">Rejected</option>
                        <option value="closed">Closed</option>
                    </select>
                    <select id="allEventsDeptFilter" class="form-select form-select-sm" style="max-width: 200px;">
                        <option value="">All Departments</option>
                        <option value="ALL">All Departments</option>
                        <option value="BSIT">BSIT</option>
                        <option value="BSHM">BSHM</option>
                        <option value="CONAHS">CONAHS</option>
                        <option value="Senior High">Senior High</option>
                        <option value="High school department">High School Department</option>
                        <option value="College of Communication, Information and Technology">CCIT</option>
                        <option value="College of Accountancy and Business">CAB</option>
                        <option value="School of Law and Political Science">SLPS</option>
                        <option value="College of Education">Education</option>
                        <option value="College of Nursing and Allied health sciences">CONAHS</option>
                        <option value="College of Hospitality Management">CHM</option>
                    </select>
                </div>
                <div class="sa-table-wrap">
                    <?php if (empty($allEvents)): ?>
                        <div class="sa-empty">
                            <i class="fas fa-calendar-times"></i>
                            <p class="mb-0">No events found.</p>
                        </div>
                    <?php else: ?>
                        <table class="sa-table" id="allEventsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Event</th>
                                    <th>Date &amp; Location</th>
                                    <th>Organizer</th>
                                    <th>Dept</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allEvents as $ev): ?>
                                    <?php
                                    $eid = (int)$ev['id'];
                                    $estatus = $ev['status'] ?? '';
                                    $edept = $ev['department'] ?? 'ALL';
                                    ?>
                                    <tr data-status="<?= htmlspecialchars($estatus) ?>" data-dept="<?= htmlspecialchars($edept) ?>">
                                        <td><span class="text-muted">#<?= $eid ?></span></td>
                                        <td>
                                            <strong><?= htmlspecialchars($ev['title'] ?? 'Untitled') ?></strong>
                                            <?php if (!empty($ev['description'])): ?>
                                                <div class="text-muted small mt-1"><?= nl2br(htmlspecialchars(mb_strimwidth($ev['description'], 0, 100, '...'))) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="small"><?= htmlspecialchars($ev['date'] ?? 'TBA') ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($ev['location'] ?? '') ?></div>
                                        </td>
                                        <td>
                                            <div class="small"><?= htmlspecialchars($ev['organizer_name'] ?? '') ?></div>
                                        </td>
                                        <td>
                                            <span class="sa-badge sa-badge-dept"><?= htmlspecialchars($edept === 'ALL' ? 'All' : $edept) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($estatus === 'pending'): ?>
                                                <span class="sa-badge sa-badge-pending">Pending</span>
                                            <?php elseif ($estatus === 'active'): ?>
                                                <span class="sa-badge sa-badge-active">Active</span>
                                            <?php elseif ($estatus === 'rejected'): ?>
                                                <span class="sa-badge sa-badge-rejected">Rejected</span>
                                            <?php else: ?>
                                                <span class="sa-badge sa-badge-closed">Closed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="sa-actions">
                                                <a href="<?= BASE_URL ?>/event_attendance.php?id=<?= $eid ?>" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" title="View who attended"><i class="fas fa-clipboard-check me-1"></i>Attendance</a>
                                                <?php if ($estatus === 'pending'): ?>
                                                    <form method="POST" action="<?= BASE_URL ?>/backend/super_admin/update_event_status.php" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="event_id" value="<?= $eid ?>">
                                                        <input type="hidden" name="action" value="approve">
                                                        <button type="submit" class="sa-btn-approve btn btn-sm"><i class="fas fa-check"></i> Approve</button>
                                                    </form>
                                                    <button type="button" class="sa-btn-reject btn btn-sm" data-bs-toggle="modal" data-bs-target="#rejectEventModal" data-event-id="<?= $eid ?>" data-event-title="<?= htmlspecialchars($ev['title'] ?? '') ?>" data-return-to="dashboard">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                <?php elseif ($estatus === 'active'): ?>
                                                    <form method="POST" action="<?= BASE_URL ?>/backend/super_admin/update_event_status.php" class="d-inline" onsubmit="return confirm('Close this event?');">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="event_id" value="<?= $eid ?>">
                                                        <input type="hidden" name="action" value="close">
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-archive"></i> Close</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted small">—</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">Page <?= (int)$eventsPage ?> of <?= (int)$eventsTotalPages ?></small>
                            <div class="btn-group btn-group-sm">
                                <?php $prevEventsPage = max(1, $eventsPage - 1); $nextEventsPage = min($eventsTotalPages, $eventsPage + 1); ?>
                                <a class="btn btn-outline-secondary <?= $eventsPage <= 1 ? 'disabled' : '' ?>" href="<?= BASE_URL ?>/backend/super_admin/dashboardsuperadmin.php?events_page=<?= $prevEventsPage ?>&users_page=<?= (int)$usersPage ?>">Prev</a>
                                <a class="btn btn-outline-secondary <?= $eventsPage >= $eventsTotalPages ? 'disabled' : '' ?>" href="<?= BASE_URL ?>/backend/super_admin/dashboardsuperadmin.php?events_page=<?= $nextEventsPage ?>&users_page=<?= (int)$usersPage ?>">Next</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
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

<!-- Calendar Modal -->
<div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calendarModalLabel">
                    <i class="fas fa-calendar-alt me-2"></i>Events Calendar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div id="saCalendar" style="min-height: 420px; width: 100%;"></div>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

<!-- Reactivate Account Confirmation Modal -->
<div class="modal fade" id="reactivateConfirmModal" tabindex="-1" aria-labelledby="reactivateConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reactivateConfirmModalLabel"><i class="fas fa-user-check me-2"></i>Confirm Reactivate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to reactivate this account?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="<?= BASE_URL ?>/backend/super_admin/reactivate_user.php" class="d-inline" id="reactivateConfirmForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="reactivateUserId" value="">
                    <button type="submit" class="btn btn-success" id="reactivateConfirmBtn"><i class="fas fa-check me-1"></i> Yes, Reactivate</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
window.BASE_URL = <?= json_encode(BASE_URL ?? '/school_events') ?>;
window.saUserRoles = <?= json_encode(['labels' => $saUserRoleLabels, 'counts' => $saUserRoleCounts]) ?>;
window.saEventStatus = <?= json_encode(['labels' => $saEventStatusLabels, 'counts' => $saEventStatusCounts]) ?>;
window.saAllEventsJson = <?= json_encode(array_values(array_filter(array_map(function($e) {
    $date = trim($e['date'] ?? '');
    if ($date === '') return null;
    $start = trim($date . ' ' . ($e['start_time'] ?? ''));
    $end   = isset($e['end_time']) && $e['end_time'] !== null
        ? trim($date . ' ' . $e['end_time'])
        : null;
    return [
        'id' => (int)$e['id'],
        'title' => $e['title'] ?? 'Untitled',
        'start' => $start,
        'end'   => $end,
        'extendedProps' => [
            'location' => $e['location'] ?? '',
            'department' => $e['department'] ?? 'ALL',
            'status' => $e['status'] ?? '',
            'organizer_name' => $e['organizer_name'] ?? '',
            'start_time' => $e['start_time'] ?? null,
            'end_time'   => $e['end_time'] ?? null,
        ],
    ];
}, $allEvents))), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

document.getElementById('reactivateConfirmModal').addEventListener('show.bs.modal', function(e) {
    var btn = e.relatedTarget;
    var userId = btn && btn.getAttribute ? btn.getAttribute('data-user-id') : '';
    var input = document.getElementById('reactivateUserId');
    if (input) input.value = userId || '';
});

if (typeof Chart !== 'undefined') {
    var ur = window.saUserRoles || { labels: [], counts: [] };
    var es = window.saEventStatus || { labels: [], counts: [] };
    var uCtx = document.getElementById('saUsersChart');
    if (uCtx) {
        new Chart(uCtx, {
            type: 'bar',
            data: { labels: ur.labels || [], datasets: [{ data: ur.counts || [], backgroundColor: 'rgba(56,189,248,0.65)', borderColor: 'rgba(56,189,248,1)', borderWidth: 1 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }
    var eCtx = document.getElementById('saEventsChart');
    if (eCtx) {
        new Chart(eCtx, {
            type: 'doughnut',
            data: { labels: es.labels || [], datasets: [{ data: es.counts || [], backgroundColor: ['rgba(234,179,8,.85)','rgba(16,185,129,.85)','rgba(239,68,68,.85)','rgba(100,116,139,.85)'] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }
}

var rejectModalEl = document.getElementById('rejectEventModal');
if (rejectModalEl) {
    rejectModalEl.addEventListener('show.bs.modal', function(e) {
        var btn = e.relatedTarget;
        if (btn && btn.getAttribute('data-event-id')) {
            document.getElementById('rejectEventId').value = btn.getAttribute('data-event-id') || '';
            document.getElementById('rejectReturnTo').value = btn.getAttribute('data-return-to') || 'dashboard';
            var title = btn.getAttribute('data-event-title') || 'this event';
            document.getElementById('rejectEventTitleText').textContent = 'Reject "' + title + '"? Optionally give a reason so the organizer knows what to fix.';
            document.getElementById('rejectReasonInput').value = '';
        }
    });
}

// Simple client-side filtering for All Users table
(function() {
    var searchInput = document.getElementById('userSearch');
    var roleFilter  = document.getElementById('roleFilter');
    var statusFilter = document.getElementById('statusFilter');
    var table = document.getElementById('usersTable');
    if (!table) return;

    var rows = Array.prototype.slice.call(table.querySelectorAll('tbody tr'));

    function applyFilters() {
        var query = (searchInput && searchInput.value ? searchInput.value.toLowerCase() : '').trim();
        var role  = roleFilter ? roleFilter.value : '';
        var status = statusFilter ? statusFilter.value : '';

        rows.forEach(function(row) {
            var nameCell  = row.cells[1] ? row.cells[1].innerText.toLowerCase() : '';
            var emailCell = row.cells[2] ? row.cells[2].innerText.toLowerCase() : '';
            var rowRole   = row.getAttribute('data-role') || '';
            var rowStatus = row.getAttribute('data-status') || '';

            var matchesSearch = !query || nameCell.indexOf(query) !== -1 || emailCell.indexOf(query) !== -1;
            var matchesRole   = !role || rowRole === role;
            var matchesStatus = !status || rowStatus === status;

            row.style.display = (matchesSearch && matchesRole && matchesStatus) ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (roleFilter) roleFilter.addEventListener('change', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
})();

// All Events table filters
(function() {
    var searchInput = document.getElementById('eventSearch');
    var statusFilter = document.getElementById('allEventsStatusFilter');
    var deptFilter = document.getElementById('allEventsDeptFilter');
    var table = document.getElementById('allEventsTable');
    if (!table) return;
    var rows = Array.prototype.slice.call(table.querySelectorAll('tbody tr'));
    function applyEventFilters() {
        var q = (searchInput && searchInput.value ? searchInput.value.toLowerCase() : '').trim();
        var status = statusFilter ? statusFilter.value : '';
        var dept = deptFilter ? deptFilter.value : '';
        rows.forEach(function(row) {
            var titleCell = row.cells[1] ? row.cells[1].innerText.toLowerCase() : '';
            var locCell = row.cells[2] ? row.cells[2].innerText.toLowerCase() : '';
            var rowStatus = row.getAttribute('data-status') || '';
            var rowDept = row.getAttribute('data-dept') || '';
            var matchSearch = !q || titleCell.indexOf(q) !== -1 || locCell.indexOf(q) !== -1;
            var matchStatus = !status || rowStatus === status;
            var matchDept = !dept || rowDept === dept;
            row.style.display = (matchSearch && matchStatus && matchDept) ? '' : 'none';
        });
    }
    if (searchInput) searchInput.addEventListener('input', applyEventFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyEventFilters);
    if (deptFilter) deptFilter.addEventListener('change', applyEventFilters);
})();

// Calendar modal: init FullCalendar when modal is fully shown (so dimensions are correct)
var saCalendarInstance = null;
var calendarModalEl = document.getElementById('calendarModal');
if (calendarModalEl) {
    calendarModalEl.addEventListener('shown.bs.modal', function() {
        var el = document.getElementById('saCalendar');
        if (!el) return;
        if (saCalendarInstance) {
            try { saCalendarInstance.destroy(); } catch (err) {}
            saCalendarInstance = null;
        }
        if (typeof FullCalendar === 'undefined') {
            console.warn('FullCalendar not loaded');
            el.innerHTML = '<p class="text-muted p-3">Calendar could not load. Check console.</p>';
            return;
        }
        var events = window.saAllEventsJson;
        if (!Array.isArray(events)) events = [];
        saCalendarInstance = new FullCalendar.Calendar(el, {
            initialView: 'dayGridMonth',
            headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek' },
            events: events,
            eventDisplay: 'block',
            height: 400,
            eventDidMount: function(info) {
                var status = (info.event.extendedProps && info.event.extendedProps.status) ? info.event.extendedProps.status : '';
                if (status === 'pending') info.el.style.backgroundColor = '#d97706';
                else if (status === 'rejected') info.el.style.backgroundColor = '#b91c1c';
                else if (status === 'closed') info.el.style.backgroundColor = '#64748b';
            },
        });
        saCalendarInstance.render();
    });
    calendarModalEl.addEventListener('hidden.bs.modal', function() {
        if (saCalendarInstance) {
            try { saCalendarInstance.destroy(); } catch (err) {}
            saCalendarInstance = null;
        }
    });
}
</script>
</body>
</html>
