<?php
include("db_connect.php"); // Make sure this file connects to your DB

if (isset($_POST['import_csv'])) {
    $fileName = $_FILES['csv_file']['tmp_name'];

    // Check if file is valid and not empty
    if (!empty($fileName) && $_FILES['csv_file']['size'] > 0) {
        if (($file = fopen($fileName, "r")) !== false) {
            // Skip header row (assuming CSV has headers)
            fgetcsv($file);

            while (($row = fgetcsv($file, 10000, ",")) !== false) {
                // Adjust indexes to match your CSV columns
                $name     = trim($row[0] ?? '');
                $email    = trim($row[1] ?? '');
                $phone    = trim($row[2] ?? '');
                $password = trim($row[3] ?? '');
                // Option A: read role from CSV
                // $role = trim($row[5] ?? 'student');

                // Option B: always set role to "student"
                $role = 'student';

                // If you want to store hashed passwords:
                // $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                // Otherwise, store plain text (not recommended for real apps):
                $hashedPassword = $password; // or do password_hash($password, PASSWORD_DEFAULT);

                // Insert into "users" table
                $sql = "INSERT INTO users (name, email, phone, password, role) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("sssss",
                        $name,
                        $email,
                        $phone,
                        $hashedPassword,
                        $role
                    );
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // For debugging
                    echo "Prepare failed: " . $conn->error;
                }
            }
            fclose($file);

            // Redirect or show success message
            header("Location: admin_list.php?import=success");
            exit;
        } else {
            echo "Error opening the file.";
        }
    } else {
        echo "No file uploaded or file is empty.";
    }
}
?>
