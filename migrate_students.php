<?php
include("db_connect.php"); // Ensure database connection is included

// Fetch all emails from students table
$sql = "SELECT DISTINCT email FROM students WHERE email IS NOT NULL AND email <> ''";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $email = $row['email'];
        $password = "123456"; // Default password, you can modify this
        $role = "student";

        // Check if email already exists in users table
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows == 0) { // If email does not exist, insert it
            $insert_sql = "INSERT INTO users (email, password, role) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sss", $email, $password, $role);

            if ($insert_stmt->execute()) {
                echo "Inserted: " . $email . "<br>";
            } else {
                echo "Error inserting: " . $email . " - " . $conn->error . "<br>";
            }
            $insert_stmt->close();
        } else {
            echo "Skipped (already exists): " . $email . "<br>";
        }
        $check_stmt->close();
    }
} else {
    echo "No student emails found.";
}

$conn->close();
?>
