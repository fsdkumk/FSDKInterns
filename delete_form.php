<?php
session_start(); // Ensure session starts
include("db_connect.php");

// Check if the user is an admin
if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized action."]);
    exit();
}

// Check if form ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(["status" => "error", "message" => "Form ID is required."]);
    exit();
}

$formId = intval($_POST['id']); // Convert to integer to prevent SQL injection

// Prepare the delete statement
$stmt = $conn->prepare("DELETE FROM before_li WHERE id = ?");
$stmt->bind_param("i", $formId);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Form deleted successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete form."]);
}

// Close connection
$stmt->close();
$conn->close();
exit();
?>
