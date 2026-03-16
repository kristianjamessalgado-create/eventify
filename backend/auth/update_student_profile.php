<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/csrf.php';

// Only allow logged-in students
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? null) !== 'student') {
    header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode("Access denied"));
    exit();
}

$studentId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode("Invalid request. Please try again."));
        exit();
    }
    $name = trim($_POST['name'] ?? '');
    $error = '';
    $profilePicturePath = null;

    // Validate name
    if ($name === '') {
        $error = "Full name is required.";
    } elseif (strlen($name) > 100) {
        $error = "Full name must be 100 characters or less.";
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            $error = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
        } elseif ($file['size'] > $maxSize) {
            $error = "File size exceeds 5MB limit.";
        } else {
            // Create uploads directory if it doesn't exist
            $baseDir = dirname(__DIR__, 2);
            $uploadsDir = $baseDir . '/uploads/profile_pictures';
            if (!is_dir($uploadsDir)) {
                @mkdir($uploadsDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $studentId . '_' . time() . '_' . uniqid() . '.' . $extension;
            $targetPath = $uploadsDir . '/' . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $profilePicturePath = 'uploads/profile_pictures/' . $filename;

                // Delete old profile picture if exists
                $stmtOld = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
                $stmtOld->bind_param("i", $studentId);
                $stmtOld->execute();
                $resultOld = $stmtOld->get_result();
                if ($oldPic = $resultOld->fetch_assoc()) {
                    if (!empty($oldPic['profile_picture'])) {
                        $oldPath = $baseDir . '/' . $oldPic['profile_picture'];
                        if (is_file($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                }
                $stmtOld->close();
            } else {
                $error = "Failed to upload profile picture. Please try again.";
            }
        }
    } elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $error = "Error uploading file. Please try again.";
    }

    if ($error !== '') {
        header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode($error));
        exit();
    }

    // Update student profile
    if ($profilePicturePath !== null) {
        // Update both name and profile picture
        $stmt = $conn->prepare("UPDATE users SET name = ?, profile_picture = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $profilePicturePath, $studentId);
    } else {
        // Update name only
        $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $studentId);
    }

    if ($stmt) {
        $stmt->execute();
        $stmt->close();

        // Keep session name in sync
        $_SESSION['name'] = $name;

        $successMsg = "Profile updated successfully.";
        if ($profilePicturePath !== null) {
            $successMsg = "Profile and picture updated successfully.";
        }
        header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode($successMsg));
        exit();
    } else {
        header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode("Failed to update profile."));
        exit();
    }
}

// Fallback for non-POST access
header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php");
exit();

