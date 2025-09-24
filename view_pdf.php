<?php
// Turn off all error reporting.
error_reporting(0);
ini_set('display_errors', 0);

// Define the absolute base path of the project for security.
$base_path = realpath(__DIR__ . '/../../');

function fail_and_exit_json($message) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['error' => $message]);
    exit;
}

// 1. Check if a file was requested and is not empty.
if (!isset($_GET['file']) || empty($_GET['file'])) {
    fail_and_exit_json('File not specified.');
}

$file_path_from_get = $_GET['file'];
$sanitized_path = str_replace('..', '', $file_path_from_get);

// 2. Security: Ensure the path starts with the expected directory.
if (strpos($sanitized_path, 'uploads/ups_technical_drawings/') !== 0) {
    fail_and_exit_json('Invalid file path.');
}
    
// 3. Construct the full, absolute server path to the file.
$file_path_absolute = $base_path . '/' . $sanitized_path;

// 4. Verify that the file actually exists and is readable.
if (!file_exists($file_path_absolute) || !is_readable($file_path_absolute)) {
    fail_and_exit_json('File not found or is not readable.');
}
    
// 5. Read the file content.
$pdf_content = file_get_contents($file_path_absolute);

if ($pdf_content === false) {
    fail_and_exit_json('Could not read file content.');
}

// 6. Encode the file content in Base64.
$base64_pdf = base64_encode($pdf_content);

// 7. Send the Base64 data back as a JSON response.
header('Content-Type: application/json');
echo json_encode(['pdfData' => $base64_pdf]);
exit;

