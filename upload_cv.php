<?php
// upload_cv.php — saves uploaded CV to /uploads folder

header('Content-Type: application/json');

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Allowed types
$allowedExt = ['pdf', 'doc', 'docx'];
$maxSize    = 5 * 1024 * 1024; // 5 MB

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_FILES['cv']) || $_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['cv'];

// Validate size
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File too large (max 5MB)']);
    exit;
}

// Validate extension
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt)) {
    echo json_encode(['success' => false, 'message' => 'Only PDF, DOC, DOCX allowed']);
    exit;
}

// Optional: validate MIME
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
$allowedMime = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];
if (!in_array($mime, $allowedMime)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit;
}

// Build a safe unique filename
$baseName = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
$safeName = $baseName . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
$target   = $uploadDir . $safeName;

if (move_uploaded_file($file['tmp_name'], $target)) {
    echo json_encode([
        'success' => true,
        'message' => 'CV uploaded successfully',
        'file'    => $safeName,
        'url'     => 'view_cv.php?file=' . urlencode($safeName)
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
}