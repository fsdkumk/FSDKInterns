<?php
header('Content-Type: application/json');

// Debugging: Print GET and POST request data
error_log("🔍 Debugging Received GET Data: " . json_encode($_GET));
error_log("🔍 Debugging Received POST Data: " . json_encode($_POST));

if (!isset($_POST['form_name']) || empty($_POST['form_name'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Form Name is missing",
        "debug_received" => $_POST
    ]);
    exit();
}

$form_name = trim($_POST['form_name']);

include 'db_connect.php';

// Debugging: Print received form_name
error_log("🔍 Debugging form_name received: " . $form_name);

// Ensure special characters do not break SQL
$query = "SELECT DISTINCT s.name FROM students s
          JOIN before_li b ON s.name = b.student_name
          WHERE b.form_name = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $form_name);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row['name'];
}

$stmt->close();
$conn->close();

echo json_encode(["status" => "success", "students" => $students]);
?>
