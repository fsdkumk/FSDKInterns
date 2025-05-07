<?php
session_start();

// 1) Check if the user is a student
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    // Return JSON error (because we use fetch in JavaScript)
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

// 2) Connect to your database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fsdk";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: '.$conn->connect_error]);
    exit();
}

// Use the student's name from session if it exists:
    $student_email = $_SESSION['email'] ?? '';
    $student_name  = $_SESSION['student_name'] ?? 'Unknown Student';

// 4) Check if form_id is sent from the modal form
if (!isset($_POST['form_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Form ID is missing.']);
    exit();
}
$form_id = (int)$_POST['form_id'];

// 5) Fetch the form_name from your forms table (e.g., `before_li`) or wherever you store it
$sqlForm = "SELECT form_name FROM before_li WHERE id = ?";
$stmt = $conn->prepare($sqlForm);
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows < 1) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid form ID.']);
    exit();
}
$row = $result->fetch_assoc();
$form_name = $row['form_name'] ?? '';

// 6) Check if a file was actually uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded or upload error.']);
    exit();
}

// 7) Handle the uploaded file
$fileTmpPath = $_FILES['file']['tmp_name'];
$fileName    = $_FILES['file']['name']; // original file name
$fileSize    = $_FILES['file']['size'];
$fileType    = $_FILES['file']['type'];

// You might want to sanitize the file name further, but for demo:
$uploadDir  = "uploads/"; // Make sure this folder exists and is writable
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Create the folder if it doesn't exist
}

// Create a unique file name to avoid collisions, e.g. time + original name
$newFileName = time() . "_" . $fileName;
$destPath    = $uploadDir . $newFileName;

// 8) Move file from temp location to your uploads folder
if (!move_uploaded_file($fileTmpPath, $destPath)) {
    echo json_encode(['status' => 'error', 'message' => 'Error moving uploaded file.']);
    exit();
}

// 9) Insert a record into your `student_uploads` table
$sqlInsert = "INSERT INTO student_uploads (student_name, student_email, form_name, file_path)
              VALUES (?, ?, ?, ?)";
$stmtInsert = $conn->prepare($sqlInsert);
$stmtInsert->bind_param("ssss", $student_name, $student_email, $form_name, $destPath);

if ($stmtInsert->execute()) {
    // Successfully inserted
    echo json_encode(['status' => 'success', 'message' => 'File uploaded and record saved!']);
} else {
    // If DB insert fails, remove the file from server to keep things clean
    unlink($destPath);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Database insert failed: ' . $stmtInsert->error
    ]);
}

$stmtInsert->close();
$conn->close();
?>
