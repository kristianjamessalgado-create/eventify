<?php
$superadmin_name = $superadmin_name ?? 'Super Admin';
$users = $users ?? [];
$success = $success ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - EVENTIFY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
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
            .sa-card-header, .sa-table th, .sa-table td { padding: 0.75rem 1rem; }
            .sa-table { font-size: 0.85rem; }
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
        <a href="<?= BASE_URL ?>/backend/auth/logout.php" class="sa-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<main class="sa-main">
    <div class="sa-card">
        <div class="sa-card-header">
            <h1><i class="fas fa-users"></i> All Users</h1>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show sa-alert" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="sa-table-wrap">
            <?php if (empty($users)): ?>
                <div class="sa-empty">
                    <i class="fas fa-users-slash"></i>
                    <p class="mb-0">No users found.</p>
                </div>
            <?php else: ?>
                <table class="sa-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><span class="text-muted">#<?= (int)$user['id'] ?></span></td>
                                <td><strong><?= htmlspecialchars($user['name']) ?></strong></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($user['role']) ?></span></td>
                                <td>
                                    <?php if ($user['status'] === 'active'): ?>
                                        <span class="sa-badge sa-badge-active">Active</span>
                                    <?php else: ?>
                                        <span class="sa-badge sa-badge-inactive"><?= ucfirst($user['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['status'] === 'inactive'): ?>
                                        <button type="button"
                                                class="sa-btn-react"
                                                data-bs-toggle="modal"
                                                data-bs-target="#reactivateConfirmModal"
                                                data-reactivate-url="reactivate_user.php?id=<?= (int)$user['id'] ?>">
                                            <i class="fas fa-user-check me-1"></i> Reactivate
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</main>

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
                <a href="#" class="btn btn-success" id="reactivateConfirmBtn"><i class="fas fa-check me-1"></i> Yes, Reactivate</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('reactivateConfirmModal').addEventListener('show.bs.modal', function(e) {
    var btn = e.relatedTarget;
    var url = btn && btn.getAttribute ? btn.getAttribute('data-reactivate-url') : '';
    var link = document.getElementById('reactivateConfirmBtn');
    if (link) link.href = url || '#';
});
</script>
</body>
</html>
