<?php
session_start();
// In production, keep errors out of the browser (log them instead)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Include DB, config, CSRF, and activity logger
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php'; // For BASE_URL
include __DIR__ . '/../../config/csrf.php';
include __DIR__ . '/../lib/activity_logger.php';

// Allowed departments for students (must match form options)
$allowedDepartments = [
    'High school department',
    'College of Communication, Information and Technology',
    'College of Accountancy and Business',
    'School of Law and Political Science',
    'College of Education',
    'College of Nursing and Allied health sciences',
    'College of Hospitality Management',
];

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (!csrf_validate()) {
        header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode("Invalid request. Please try again.") . "&form=" . ($action === 'register' ? 'register' : 'login'));
        exit();
    }

    if ($action === 'register') {
        // --- REGISTRATION ---
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role     = $_POST['role'] ?? '';
        $department = isset($_POST['department']) ? trim($_POST['department']) : null;

        $error = '';

        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (!preg_match('/[A-Z]/', $password)
            || !preg_match('/[\W_]/', $password)
            || strlen($password) < 8) {
            $error = "Password must contain at least 1 uppercase letter, 1 special character, and be at least 8 characters long.";
        } elseif (!in_array($role, ['student', 'organizer', 'multimedia', 'admin'])) {
            $error = "Invalid role selected.";
        } elseif ($role === 'student' && empty($department)) {
            $error = "Department is required for students.";
        } elseif ($role === 'student' && !in_array($department, $allowedDepartments, true)) {
            $error = "Invalid department selected.";
        } elseif (strlen($name) < 1 || strlen($name) > 100) {
            $error = "Name must be between 1 and 100 characters.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Email already registered.";
            } else {
                // Use bcrypt for password hashing (secure, salted)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                if ($role === 'multimedia') {
                    $prefix = 'MUL';
                } elseif ($role === 'super_admin') {
                    $prefix = 'SA';
                } elseif ($role === 'admin') {
                    $prefix = 'ADM';
                } else {
                    $prefix = strtoupper(substr($role, 0, 3));
                }
                $user_id = $prefix . '-' . rand(100, 999);
                // Multimedia and admin accounts must be approved by super admin first
                $status = in_array($role, ['multimedia', 'admin'], true) ? 'inactive' : 'active';

                $insert = $conn->prepare(
                    "INSERT INTO users (user_id, name, email, password, role, department, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $insert->bind_param("sssssss", $user_id, $name, $email, $hashed_password, $role, $department, $status);

                if ($insert->execute()) {
                    if ($role === 'multimedia' || $role === 'admin') {
                        $success = "Registration submitted. Please wait for super admin approval before logging in.";
                    } else {
                        $success = "Registration successful! You can now login.";
                    }
                    header("Location: " . BASE_URL . "/views/login.php?success=" . urlencode($success) . "&form=register");
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                }

                $insert->close();
            }

            $stmt->close();
        }

        if (!empty($error)) {
            header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode($error) . "&form=register");
            exit();
        }

    } elseif ($action === 'login') {

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            $error = "Invalid email or password";
        } else {
            $user = $result->fetch_assoc();

            if ($user['status'] !== 'active') {
                $error = "Account is inactive. Contact admin.";
            } else {
                $stored = $user['password'];
                $password_ok = false;

                // Support both bcrypt (new) and legacy SHA-256 hashes
                if (password_verify($password, $stored)) {
                    $password_ok = true;
                } elseif (strlen($stored) === 64 && ctype_xdigit($stored) && hash_equals($stored, hash('sha256', $password))) {
                    $password_ok = true;
                    // Upgrade: rehash to bcrypt and update DB
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upgrade = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $upgrade->bind_param("si", $newHash, $user['id']);
                    $upgrade->execute();
                    $upgrade->close();
                }

                if (!$password_ok) {
                    // Handle failed attempts
                    $attempts = $user['failed_attempts'] + 1;

                    if ($attempts >= 5) {
                        $update = $conn->prepare("UPDATE users SET failed_attempts=?, status='inactive' WHERE id=?");
                        $update->bind_param("ii", $attempts, $user['id']);
                        $update->execute();

                        $error = "Account locked due to multiple failed attempts.";
                    } else {
                        $update = $conn->prepare("UPDATE users SET failed_attempts=? WHERE id=?");
                        $update->bind_param("ii", $attempts, $user['id']);
                        $update->execute();

                        $remaining = 5 - $attempts;
                        $error = "Incorrect password. $remaining attempts left.";
                    }
                } else {
                    // Success: reset failed attempts
                    $update = $conn->prepare("UPDATE users SET failed_attempts=0 WHERE id=?");
                    $update->bind_param("i", $user['id']);
                    $update->execute();
                    $update->close();

                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);

                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['name'] = $user['name'];

                    // Log successful login
                    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                    $details = "Successful login from IP {$ip}" . ($agent ? " | UA: {$agent}" : '');
                    log_activity($conn, $user['id'], $user['role'], 'login_success', 'user', $user['id'], $details);

                    // Decide redirect URL based on role (students may return to check-in page)
                    $redirectUrl = '';
                    switch ($user['role']) {
                        case 'super_admin':
                            $redirectUrl = BASE_URL . "/backend/super_admin/dashboardsuperadmin.php";
                            break;
                        case 'admin':
                            $redirectUrl = BASE_URL . "/backend/admin/dashboard.php";
                            break;
                        case 'organizer':
                            $redirectUrl = BASE_URL . "/backend/auth/dashboardorganizer.php";
                            break;
                        case 'student':
                            $redirectUrl = BASE_URL . "/backend/auth/dashboard_student.php";
                            break;
                        case 'multimedia':
                            $redirectUrl = BASE_URL . "/backend/auth/dashboard_multimedia.php";
                            break;
                        default:
                            $error = "Invalid role";
                            break;
                    }
                    // Only allow same-origin redirect (prevent open redirect)
                    if ($user['role'] === 'student' && !empty($_POST['redirect'])) {
                        $redirect = trim($_POST['redirect']);
                        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                        $host = $_SERVER['HTTP_HOST'] ?? '';
                        $allowed_prefix = $scheme . '://' . $host . BASE_URL;
                        $same_origin = (strpos($redirect, $allowed_prefix) === 0);
                        $relative_safe = (strpos($redirect, '/') === 0 && strpos($redirect, BASE_URL) === 0);
                        if ($same_origin || $relative_safe) {
                            $redirectUrl = $redirect;
                        }
                    }

                    if (!empty($error)) {
                        header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode($error) . "&form=login");
                        exit();
                    }

                    // Use server-side redirect when we have a custom URL (e.g. check-in page) so session cookie is sent on mobile
                    if ($user['role'] === 'student' && $redirectUrl !== '' && $redirectUrl !== (BASE_URL . "/backend/auth/dashboard_student.php")) {
                        $loc = $redirectUrl;
                        if (strpos($loc, 'http') !== 0) {
                            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                            $host = $_SERVER['HTTP_HOST'] ?? '';
                            $loc = $scheme . '://' . $host . (strpos($loc, '/') === 0 ? $loc : BASE_URL . '/' . $loc);
                        }
                        header('Location: ' . $loc);
                        exit();
                    }

                    // Break out of iframe (modal) and redirect full page for default dashboard
                    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Redirecting...</title></head><body>';
                    echo '<script>';
                    echo 'window.top.location.href = ' . json_encode($redirectUrl) . ';';
                    echo '</script>';
                    echo '</body></html>';
                    exit();
                }
            }
        }

        // Redirect with error if login failed
        if (!empty($error)) {
            header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode($error) . "&form=login");
            exit();
        }
    }
} else {
    // Redirect if accessed directly
    header("Location: " . BASE_URL . "/views/login.php");
    include __DIR__ . '/../../views/login.php';

    exit();
}
