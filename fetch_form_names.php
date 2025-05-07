<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
include 'db_connect.php';

// Query to fetch form_name only
$query = "SELECT form_name FROM before_li ORDER BY id DESC";
$result = $conn->query($query);

// Display form names with icons on the right
if ($result->num_rows > 0) {
    echo "<ul class='form-names'>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>
                <span class='form-text'>" . htmlspecialchars($row['form_name']) . "</span>
                <span class='eye-icon'><i class='fas fa-eye'></i></span> 
              </li>";
    }
    echo "</ul>";
} else {
    echo "<p class='no-forms'>No forms available.</p>";
}

$conn->close();
?>
