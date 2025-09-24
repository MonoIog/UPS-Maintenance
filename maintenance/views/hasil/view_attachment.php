<?php
// Increase memory limit to handle large images
@ini_set('memory_limit', '256M');

// Turn off all error reporting for this endpoint to ensure clean JSON output.
error_reporting(0);
ini_set('display_errors', 0);

// Set header immediately to prevent any accidental non-JSON output
header('Content-Type: application/json');

// Centralized error handler to ensure JSON response
function fail_and_exit_json($message, $code = 500)
{
    http_response_code($code);
    // Ensure the output is a JSON string
    echo json_encode(['error' => $message]);
    exit;
}

try {
    // 1. Check if a file was requested and is not empty.
    if (!isset($_GET['file']) || empty($_GET['file'])) {
        fail_and_exit_json('File not specified.', 400);
    }

    $file_path_from_get = $_GET['file'];

    // 2. Security: Prevent directory traversal attacks.
    // Replace backslashes and remove any parent directory indicators.
    $sanitized_path = str_replace('..', '', $file_path_from_get);
    if (strpos($sanitized_path, 'uploads/report_attachments/') !== 0) {
        fail_and_exit_json('Invalid file path specified.', 403);
    }

    $base_path = realpath(__DIR__ . '/../../');
    $file_path_absolute = $base_path . '/' . $sanitized_path;

    // 3. Security: Double-check that the final resolved path is within the allowed directory.
    $allowed_dir_absolute = realpath($base_path . '/uploads/report_attachments');
    if (!$allowed_dir_absolute || strpos(realpath($file_path_absolute), $allowed_dir_absolute) !== 0) {
        fail_and_exit_json('Access denied to the specified file path.', 403);
    }

    if (!file_exists($file_path_absolute) || !is_readable($file_path_absolute)) {
        fail_and_exit_json('File not found or is not readable.', 404);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) {
        fail_and_exit_json('Failed to open fileinfo database.');
    }
    $mime_type = finfo_file($finfo, $file_path_absolute);
    finfo_close($finfo);

    if ($mime_type === false) {
        fail_and_exit_json('Could not determine file MIME type.');
    }

    // Force correct MIME type for JPEG files which can sometimes be misidentified.
    $file_extension = strtolower(pathinfo($file_path_absolute, PATHINFO_EXTENSION));
    if ($file_extension === 'jpg' || $file_extension === 'jpeg') {
        $mime_type = 'image/jpeg';
    }

    $file_content = file_get_contents($file_path_absolute);
    if ($file_content === false) {
        fail_and_exit_json('Could not read file content.');
    }

    $base64_data = base64_encode($file_content);

    echo json_encode([
        'data' => $base64_data,
        'mimeType' => $mime_type
    ]);
    exit;
} catch (Exception $e) {
    // Catch any other unexpected errors and return a clean JSON response.
    fail_and_exit_json('An unexpected server error occurred: ' . $e->getMessage());
}
