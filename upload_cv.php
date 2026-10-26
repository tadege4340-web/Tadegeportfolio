<?php
// upload_cv.php — saves file to folder + inserts into Aiven database

header('Content-Type: application/json');
require __DIR__ . '/db.php'; // Connect to the database

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

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

// Validate MIME
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

if (!move_uploaded_file($file['tmp_name'], $target)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file on server']);
    exit;
}

// Insert metadata into DB
try {
    $stmt = $pdo->prepare(
        "INSERT INTO cv_uploads
            (file_name, original_name, file_size, mime_type, ip_address)
         VALUES (:fn, :on, :fs, :mt, :ip)"
    );
    $stmt->execute([
        ':fn' => $safeName,
        ':on' => $file['name'],
        ':fs' => $file['size'],
        ':mt' => $mime,
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
} catch (Exception $e) {
    // If DB insert fails, delete the file so it doesn't orphan
    @unlink($target);
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'CV uploaded successfully',
    'file'    => $safeName
]);
