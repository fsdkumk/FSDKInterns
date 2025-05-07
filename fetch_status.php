<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fsdk";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]));
}

if (isset($_GET["student_id"])) {
    $student_id = $_GET["student_id"];

    $stmt = $conn->prepare("SELECT status FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close();

    echo json_encode(["status" => $status]);
}

$conn->close();
?>
