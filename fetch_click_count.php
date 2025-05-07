<?php
header("Content-Type: application/json");

// Database connection
include 'db_connect.php';

// Get form name from request
$form_name = isset($_GET['form_name']) ? trim($_GET['form_name']) : '';

if (empty($form_name)) {
    echo json_encode(["status" => "error", "message" => "Invalid form name"]);
    exit();
}

// Get total students count
$total_students_query = "SELECT COUNT(*) AS total FROM students";
$total_students_result = $conn->query($total_students_query);
$total_students_row = $total_students_result->fetch_assoc();
$total_students = $total_students_row['total'];

// Get click count from before_li
$stmt = $conn->prepare("SELECT clicked_count FROM before_li WHERE form_name = ?");
$stmt->bind_param("s", $form_name);
$stmt->execute();
$stmt->bind_result($clicked_count);
$stmt->fetch();
$stmt->close();

echo json_encode([
    "clicked_count" => $clicked_count ?? 0, // Default to 0 if NULL
    "total_students" => $total_students
]);

$conn->close();
?>
