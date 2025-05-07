<?php
session_start(); // Ensure session is active

$uploadDirectory = "uploads/";

if (!isset($_GET['file']) || empty($_GET['file'])) {
    die("Error: No file specified.");
}

$storedFileName = basename($_GET['file']); // Server-stored filename (e.g., form_67fca90cad18e_1.doc)
$originalName = isset($_GET['name']) ? basename($_GET['name']) : $storedFileName; // Original name to display

$filePath = $uploadDirectory . $storedFileName;

if (!file_exists($filePath)) {
    die("Error: File not found.");
}

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"" . $originalName . "\"");
header("Content-Length: " . filesize($filePath));
header("Pragma: public");
header("Cache-Control: must-revalidate");

readfile($filePath);
exit();
?>
