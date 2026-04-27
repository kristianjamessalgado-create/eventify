<?php
session_start();
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'multimedia') {
    header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode("Access denied"));
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$base_dir = dirname(__DIR__, 2); // project root

const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif'];

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['event_id'])) {
    header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode("Invalid request."));
    exit();
}
if (!csrf_validate()) {
    header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode("Invalid request. Please try again."));
    exit();
}

$event_id = (int) $_POST['event_id'];
if ($event_id <= 0) {
    header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode("Invalid event."));
    exit();
}

// Check event exists and get department (for folder)
$stmt = $conn->prepare("SELECT id, department FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($ev_id, $ev_department);
if (!$stmt->fetch()) {
    $stmt->close();
    header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode("Event not found."));
    exit();
}
$stmt->close();

// Build department-based folder: uploads/events/<department_slug>/ (ALL -> all)
$dept = is_string($ev_department) ? trim($ev_department) : '';
$deptFolder = ($dept === '' || strtoupper($dept) === 'ALL') ? 'all' : $dept;
$deptFolder = strtolower($deptFolder);
$deptFolder = preg_replace('/[^a-z0-9]+/', '_', $deptFolder);
$deptFolder = trim($deptFolder, '_');
if ($deptFolder === '') $deptFolder = 'all';

$uploads_base = $base_dir . '/uploads/events/' . $deptFolder;
$relative_base = 'uploads/events/' . $deptFolder . '/';

// Collect uploaded files (support both single and multiple)
$files = [];
if (!empty($_FILES['photos']['name'])) {
    $names = $_FILES['photos']['name'];
    $tmp = $_FILES['photos']['tmp_name'];
    $errors = $_FILES['photos']['error'];
    $sizes = $_FILES['photos']['size'];
    $types = $_FILES['photos']['type'];
    if (!is_array($names)) {
        $names = [$names];
        $tmp = [$tmp];
        $errors = [$errors];
        $sizes = [$sizes];
        $types = [$types];
    }
    foreach ($names as $i => $name) {
        if ($errors[$i] !== UPLOAD_ERR_OK || empty($name)) continue;
        if ($sizes[$i] > MAX_FILE_SIZE) {
            $error = "File too large: " . htmlspecialchars($name) . " (max 5MB).";
            break;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp[$i]);
        finfo_close($finfo);
        if (!in_array($mime, ALLOWED_TYPES, true)) {
            $error = "Invalid file type: " . htmlspecialchars($name) . " (only JPG, PNG, GIF).";
            break;
        }
        $files[] = [
            'name' => $name,
            'tmp_name' => $tmp[$i],
            'type' => $mime
        ];
    }
}

if (!empty($error)) {
    header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode($error));
    exit();
}

if (empty($files)) {
    header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode("Please select at least one image."));
    exit();
}

// Ensure the department uploads folder exists
if (!is_dir($uploads_base)) {
    if (!@mkdir($uploads_base, 0755, true)) {
        header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode("Could not create upload folder."));
        exit();
    }
}

// mysqli can throw on invalid SQL; check columns before prepare (same pattern as dashboard_multimedia.php).
$photoStatusEnabled = false;
try {
    $chkCol = $conn->query("SHOW COLUMNS FROM event_photos LIKE 'status'");
    $photoStatusEnabled = (bool) ($chkCol && $chkCol->num_rows > 0);
} catch (Throwable $e) {
    $photoStatusEnabled = false;
}

if ($photoStatusEnabled) {
    $insert = $conn->prepare("INSERT INTO event_photos (event_id, uploaded_by, file_path, status, published_at) VALUES (?, ?, ?, 'draft', NULL)");
} else {
    $insert = $conn->prepare("INSERT INTO event_photos (event_id, uploaded_by, file_path) VALUES (?, ?, ?)");
}
if (!$insert) {
    header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode("Database error: could not save photo record."));
    exit();
}
$uploaded = 0;

foreach ($files as $f) {
    $ext = pathinfo($f['name'], PATHINFO_EXTENSION) ?: 'jpg';
    $safe_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $f['name']);
    $filename = date('Ymd_His') . '_' . uniqid() . '_' . $safe_name;
    if (!pathinfo($filename, PATHINFO_EXTENSION)) {
        $filename .= '.' . (strtolower($ext) ?: 'jpg');
    }
    $dest = $uploads_base . '/' . $filename;
    if (!move_uploaded_file($f['tmp_name'], $dest)) continue;
    $file_path = $relative_base . $filename;
    $insert->bind_param("iis", $event_id, $user_id, $file_path);
    if ($insert->execute()) {
        $uploaded++;
    }
}

$insert->close();
$conn->close();

$msg = $uploaded > 0
    ? $uploaded . " photo(s) uploaded successfully."
    : "Upload failed. Please try again.";
header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode($msg));
exit();
