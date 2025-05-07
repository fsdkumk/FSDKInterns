<?php
session_start();
header("Content-Type: application/json");

// Database connection
include 'db_connect.php';

// Ensure a file was uploaded
if (!isset($_FILES['form_file']) || $_FILES['form_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "message" => "File upload failed."]);
    exit();
}

// Get form submission time
$due_date = isset($_POST['time_submission_due']) ? trim($_POST['time_submission_due']) : '';
if (empty($due_date)) {
    echo json_encode(["status" => "error", "message" => "Due date is required."]);
    exit();
}

// Set upload directory
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
}

// Get original file name
$originalFileName = $_FILES['form_file']['name'];
$fileExt = pathinfo($originalFileName, PATHINFO_EXTENSION);
$uniqueFileName = uniqid("form_", true) . "." . $fileExt; // Unique file name
$filePath = $uploadDir . $uniqueFileName;

// Move file to uploads folder
if (!move_uploaded_file($_FILES['form_file']['tmp_name'], $filePath)) {
    echo json_encode(["status" => "error", "message" => "Failed to save uploaded file."]);
    exit();
}

// Store the original file name in the database instead of the unique one
$created_at = date("Y-m-d H:i:s");
$stmt = $conn->prepare("INSERT INTO before_li (form_name, due_date, created_at, file_path) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $originalFileName, $due_date, $created_at, $uniqueFileName);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Form uploaded successfully!", "file" => $filePath]);
} else {
    echo json_encode(["status" => "error", "message" => "Error inserting data: " . $stmt->error]);
}

$stmt->close();
$conn->close();
exit();
?>
