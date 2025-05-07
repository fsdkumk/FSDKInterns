<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$email = $_SESSION['email'];
$password = $_POST['password'] ?? '';
$phone = $_POST['phone'] ?? '';

// Connect to DB
$conn = new mysqli("localhost", "root", "", "fsdk");
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit();
}

// Check existing phone/password
$stmt = $conn->prepare("SELECT phone, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($existing_phone, $existing_password);
$stmt->fetch();
$stmt->close();

$update_fields = [];
$params = [];
$types = "";

if (empty($existing_phone) && !empty($phone)) {
    $update_fields[] = "phone = ?";
    $params[] = $phone;
    $types .= "s";
}
if (empty($existing_password) && !empty($password)) {
    $update_fields[] = "password = ?";
    $params[] = $password;
    $types .= "s";
}

if (empty($update_fields)) {
    echo json_encode(['status' => 'error', 'message' => 'Nothing new to update.']);
    exit();
}

$params[] = $email;
$types .= "s";

$sql = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Profile info inserted successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to insert data.']);
}

$stmt->close();
$conn->close();
?>
