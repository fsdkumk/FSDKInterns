<?php
header('Content-Type: application/json; charset=UTF-8');
session_start();

// only students or lecturers allowed
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['student','lecturer'])) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized.']);
    exit;
}

$email   = $_SESSION['email'];
$current = $_POST['current_password'] ?? '';
$new     = $_POST['new_password']     ?? '';
$confirm = $_POST['confirm_password'] ?? '';

// 2) Check new vs confirm
if ($new !== $confirm) {
    echo json_encode(['status'=>'error','message'=>'New & confirmation do not match.']);
    exit;
}

// 3) Connect to database
// ✅ Use centralized DB connection
include("db_connect.php"); // <-- this links to your db_connect.php

// 4) Fetch stored password
$stmt = $conn->prepare("
  SELECT password
    FROM users
   WHERE email = ?
     AND role  = ?
");
$stmt->bind_param("ss",$email,$_SESSION['role']);
$stmt->execute();
$stmt->bind_result($dbpass);
if (!$stmt->fetch()) {
    echo json_encode(['status'=>'error','message'=>'User not found.']);
    exit;
}
$stmt->close();

// 5) Verify current password
if ($current !== $dbpass) {
    echo json_encode(['status'=>'error','message'=>'Current password incorrect.']);
    exit;
}

// 6) Update to the new password
$upd = $conn->prepare("
  UPDATE users
     SET password = ?
   WHERE email = ?
     AND role  = ?
");
$upd->bind_param("sss",$new,$email,$_SESSION['role']);
if ($upd->execute()) {
    echo json_encode(['status'=>'success','message'=>'Password updated.']);
} else {
    echo json_encode(['status'=>'error','message'=>'Update failed.']);
}
$upd->close();
$conn->close();
