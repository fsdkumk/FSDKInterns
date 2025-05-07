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
    die(json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]));
}

// Check if student is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit();
}

// Get form_name from request
$form_name = isset($_POST['form_name']) ? trim($_POST['form_name']) : '';

if (empty($form_name)) {
    echo json_encode(["status" => "error", "message" => "Invalid form name"]);
    exit();
}

// Update clicked_count in before_li table
$stmt = $conn->prepare("UPDATE before_li SET clicked_count = clicked_count + 1 WHERE form_name = ?");
$stmt->bind_param("s", $form_name);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Click recorded"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error updating click count: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
