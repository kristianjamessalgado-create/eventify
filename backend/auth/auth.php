<?php
session_start();
// In production, keep errors out of the browser (log them instead)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Include DB and config
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php'; // For BASE_URL

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
        } elseif (!in_array($role, ['student', 'organizer', 'multimedia'])) {
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
                $prefix = $role === 'multimedia' ? 'MUL' : strtoupper(substr($role, 0, 3));
                $user_id = $prefix . '-' . rand(100, 999);
                // Multimedia accounts must be approved by admin first
                $status = ($role === 'multimedia') ? 'inactive' : 'active';

                $insert = $conn->prepare(
                    "INSERT INTO users (user_id, name, email, password, role, department, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $insert->bind_param("sssssss", $user_id, $name, $email, $hashed_password, $role, $department, $status);

                if ($insert->execute()) {
                    if ($role === 'multimedia') {
                        $success = "Registration submitted. Please wait for admin approval before logging in.";
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

                    // Decide redirect URL based on role
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

                    if (!empty($error)) {
                        header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode($error) . "&form=login");
                        exit();
                    }

                    // Break out of iframe (modal) and redirect full page
                    // Works whether called inside iframe or directly
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
