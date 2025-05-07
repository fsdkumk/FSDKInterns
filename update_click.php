<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fsdk";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

if (!isset($_SESSION['student_id'])) {
    die(json_encode(["status" => "error", "message" => "User not logged in"]));
}

$student_id = $_SESSION['student_id'];
$form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;

if ($form_id > 0) {
    // Increment the click count for this student
    $stmt = $conn->prepare("UPDATE students SET click_count = click_count + 1 WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->close();

    // Get the updated count
    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE click_count > 0");
    $count_stmt->execute();
    $count_stmt->bind_result($click_count);
    $count_stmt->fetch();
    $count_stmt->close();

    echo json_encode(["status" => "success", "count" => $click_count]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid form ID"]);
}

$conn->close();
?>
