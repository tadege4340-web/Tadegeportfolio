<?php
// download_cv.php — force download of a CV file

$uploadDir = __DIR__ . '/uploads/';

if (!isset($_GET['file'])) {
    http_response_code(400);
    exit('Missing file parameter');
}

$file = basename($_GET['file']); // prevent path traversal
$path = $uploadDir . $file;

if (!is_file($path)) {
    http_response_code(404);
    exit('File not found');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: must-revalidate');
readfile($path);
exit;