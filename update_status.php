<?php
session_start();
header("Content-Type: application/json");

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fsdk";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Get the email and new status from the AJAX request
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$new_status = isset($_POST['status']) ? trim($_POST['status']) : '';

// Validate input
if (empty($email) || empty($new_status)) {
    echo json_encode(["status" => "error", "message" => "Invalid email or status."]);
    exit();
}

// Update the student's status in the database
$stmt = $conn->prepare("UPDATE students SET status = ? WHERE email = ?");
$stmt->bind_param("ss", $new_status, $email);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error updating status: " . $stmt->error]);
}

$stmt->close();
$conn->close();
exit();
