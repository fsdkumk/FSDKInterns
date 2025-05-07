<?php
include("db_connect.php"); // Ensure database connection

// CSV IMPORT HANDLING
// ------------------------------
if (isset($_POST['import_csv'])) {
    $fileName = $_FILES['csv_file']['tmp_name'];

    // Check if file exists and is not empty
    if (isset($fileName) && $_FILES['csv_file']['size'] > 0) {
        $file = fopen($fileName, "r");

        // Skip header row if CSV has headers
        fgetcsv($file);

        // Read each row of the CSV file
        while (($row = fgetcsv($file, 10000, ",")) !== false) {
            $name     = $row[0] ?? '';
            $matrix   = $row[1] ?? '';
            $email    = $row[2] ?? '';
            $lecturer = $row[3] ?? '';
            $semester = $row[4] ?? ''; // NEW - Get Semester from CSV
        
            $sql = "INSERT INTO students (name, matrix, email, lecturer, semester) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $name, $matrix, $email, $lecturer, $semester); // Add semester binding
            if (!$stmt->execute()) {
                // If there's an error, output it (for debugging purposes)
                echo "Database Insert Error: " . $stmt->error;
            }
        }
        fclose($file);

        // Redirect to refresh the page and show updated table
        header("Location: admin_list.php");
        exit;
    }
}

$lecturers = [];
$lecturer_sql = "SELECT name FROM users WHERE role = 'lecturer'";
$lecturer_result = $conn->query($lecturer_sql);
if ($lecturer_result->num_rows > 0) {
    while ($row = $lecturer_result->fetch_assoc()) {
        $lecturers[] = $row['name'];
    }
}

// DELETE FUNCTIONALITY
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Delete the record
    $sql = "DELETE FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        // Drop and recreate the id column to reset AUTO_INCREMENT
        $conn->query("ALTER TABLE students DROP COLUMN id;");
        $conn->query("ALTER TABLE students ADD COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;");

        echo "Record deleted and IDs reset successfully.";
    } else {
        echo "Error deleting record.";
    }

    $stmt->close();
    $conn->close();
    exit;
}

// ADD (INSERT) FUNCTIONALITY
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['delete_id'])) {
    $name     = $_POST['name']     ?? '';
    $matrix   = $_POST['matrix']   ?? '';
    $lecturer = $_POST['lecturer'] ?? ''; 
    $semester = $_POST['semester'] ?? '';

    $name     = trim($name);
    $matrix   = trim($matrix);
    $lecturer = trim($lecturer);

    // Check if matric number already exists
    $check_sql = "SELECT id FROM students WHERE matrix = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $matrix);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "This student already exists in the table."]);
        exit();
    }
    $stmt->close();

    // Insert into database (Now Including Lecturer)
    $stmt = $conn->prepare("INSERT INTO students (name, matrix, lecturer, semester) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $matrix, $lecturer, $semester);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Student added successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error. Please try again."]);
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admin | student list</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQ5BtjmQVKynWj8fPqKflEUVb+6ugvcgO/nr36M9pqE8s6rHuX4r3xIM+" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Add Bootstrap JavaScript -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<style>
/* General Body Styles */
body {
    background-color: #e0f7f5;
    margin: 0;
    font-family: Arial, sans-serif;
    display: flex;
    overflow: hidden;
}

/* Top Navbar */
.navbar {
    background: linear-gradient(145deg, #33d1c9, #1da2a0);
    color: #ffffff;
    width: 100%; /* Full width */
    padding: 5px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    position: fixed; /* Fixed positioning */
    top: 0; /* Stick to the top of the viewport */
    left: 0; /* Ensure it starts from the viewport's very left */
    z-index: 1000; /* Ensure it stays above other elements */
    transition: transform 0.3s ease; /* Smooth transition */
}

/* Optional: Reduce Gap Between Logo and Button */
.navbar .logo {
    display: flex;
    align-items: center;
    gap: 10px; /* Reduce gap to save space */
}

.navbar .logo-img {
    width: 70px; /* Increased width */
    height: 80px; /* Increased height */
    margin-right: 10px;
}

.navbar .logo p {
    font-size: 25px;
    font-weight: bold;
    color: #ffffff;
    margin: 0;
    text-transform: uppercase;
}

.navbar .logo p.faculty-title {
    font-size: 16px;
    font-weight: normal;
    color: #ffffff;
    opacity: 0.8;
    margin: 0;
    text-transform: capitalize;
}

/* Adjust Menu Position */
.navbar .menu {
    list-style: none;
    margin: 0; /* Remove unnecessary margin */
    padding: 0;
    display: flex;
    align-items: center; /* Align items in the center */
}

/* Logout Button Styling */
.logout-btn {
    background: #e0f7f5; /* Red background */
    color: black;
    padding: 8px 20px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    transition: background 0.3s ease, transform 0.2s ease;
    border: none;
    cursor: pointer;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1); /* Soft shadow */
}

.logout-btn i {
    font-size: 18px;
}

/* Hover Effect */
.logout-btn:hover {
    background: #e0f7f5; /* Darker red */
    color: #20b2aa;
    transform: scale(1.05); /* Slight zoom effect */
}

/* Active (Click) Effect */
.logout-btn:active {
    transform: scale(0.95); /* Button presses slightly */
}

/* Sidebar */
.sidebar {
    background-color: #20b2aa;
    color: #ffffff;
    width: 250px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    display: flex;
    flex-direction: column;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    overflow-y: auto; /* Allow scrolling if content overflows */
    margin-top: 90px;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
}

/* Sidebar Active */
.sidebar.active {
    transform: translateX(0);
}

/* Menu Toggle Button */
.menu-toggle {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    margin-right: 10px;
}

/* Force-disable hover effects on the toggle button */
.menu-toggle:hover,
.menu-toggle:focus,
.menu-toggle:active {
    background: none !important;
    color: inherit !important;
    transform: none !important;
    transition: none !important;
    box-shadow: none !important;
}

.sidebar a {
    color: #ffffff;
    text-decoration: none;
    padding: 10px 20px;
    display: block;
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 5px;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.sidebar a:hover {
    background-color: #1a8d8a;
}

.sidebar .collapse-item {
    padding-left: 40px;
    font-size: 14px;
}

.main-content {
    margin-left: 0; /* Default when the sidebar is hidden */
    transition: margin-left 0.3s ease;
    padding: 20px;
    width: calc(100% - 100px);
    background-color: #e0f7f5;
    min-height: 100%; /* Ensures content height scales */
    overflow-y: auto; /* Allows scrolling if content overflows */
   margin-top: -70px;
}

/* Main Content When Sidebar is Open */
.sidebar.active ~ .main-content {
    margin-left: 250px; /* Match the sidebar width */
}

.container {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 100%;
    margin-top: 70px
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.table th,
.table td {
    border: 1px solid #ddd;
    padding: 8px;
}

.table th {
    background-color: #2d523a;
    color: white;
    text-align: left;
}

.button-container {
    margin-bottom: 20px;
}

/* Start of admin2.css content */
.student-table h3 {
    margin: 0;
    margin-top: 90px;
    padding-bottom: 10px;
    font-size: 20px;
    color: #20b2aa;
    border-bottom: 2px solid #20b2aa;
    display: inline-block;
}

.student-table table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    background-color: #ffffff; /* White table background */
    color: #1e353f; /* Dark Teal text */
}

.student-table table th,
.student-table table td {
    padding: 15px;
    border: 1px solid #cdeeea; /* Lightened Sea Green border */
    font-size: 16px;
}

.student-table table th {
    background-color: #20b2aa; /* Table header in Light Sea Green */
    font-weight: bold;
    text-align: center;
    color: #ffffff;
    text-transform: uppercase;
}

.student-table table td {
    text-align: center;
    color: #1e353f; /* Dark Teal text */
}

.student-table table tr:hover {
    background-color: #cdeeea; /* Lightened Sea Green hover */
    transition: all 0.2s ease;
}

/* Button container to align the button */
.button-container {
    text-align: right; /* Align the button to the right */
    margin-bottom: 15px; /* Add space below the button */
}

/* Sort Button Styling */
button {
    padding: 10px 20px;
    background-color: #20b2aa; /* Match sidebar color */
    color: #ffffff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #2ac2b0; /* Hover effect for button */
}

@media (max-width: 480px) {
    .sidebar {
        display: none;
    }

    .main-content {
        width: 100%;
        padding: 10px;
    }

    header h2 {
        font-size: 18px;
    }

    header p {
        font-size: 12px;
    }

    /* Adjust Name and Matrix column widths */
    .student-table table th:nth-child(2), /* Name header */
    .student-table table td:nth-child(2) { /* Name data */
        width: 40%; /* Wider column for Name */
    }

    .student-table table th:nth-child(3), /* Matrix header */
    .student-table table td:nth-child(3) { /* Narrower column for Matrix */
        width: 20%; /* Narrower column */
    }


    button {
        font-size: 12px;
        padding: 8px 16px;
    }
}

/* Modal Styling */
.modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 10;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4); /* Background overlay */   
    align-items: center;
    justify-content: center;
    overflow: hidden; /* Prevent any overflow issues */
}

.modal-content {
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    width: 100%;
    max-width: 500px;
    text-align: center; /* Align text to the left */
    position: relative; /* For positioning the close button */
    box-sizing: border-box; /* Include padding in width/height */
    overflow: hidden; /* Prevent content overflow */
}

.modal-content h3 {
    font-size: 20px;
    margin: 0 0 20px 0; /* Add space below */
    color: #20b2aa; /* Light Sea Green */
}

.modal-content form {
    display: flex;
    flex-direction: column; /* Stack inputs and button vertically */
    gap: 15px; /* Add spacing between elements */
    align-items: stretch; /* Make all elements stretch to full width */
}

.modal-content input {
    width: 100%; /* Full width for inputs */
    margin: 0 0 15px 0; /* Space between inputs */
    padding: 10px;
    font-size: 14px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-sizing: border-box;
}

.modal-content button {
    padding: 10px 20px;
    background-color: #20b2aa;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin: 20px 0 0 0; /* Add space above button */
}

.modal-content button:hover {
    background-color: #2ac2b0;
}

/* Close Button Styling */
.close {
    position: absolute;
    top: 10px;
    right: 15px;
    color: #000;
    font-size: 24px; /* Larger size */
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
}

.close:hover {
    color: #2ac2b0; /* Change to black on hover */
}

#addStudentModal {
    border: 2px solid #2ac2b0;
}
table {
    border: 2px solid blue;
}

.button-search-container {
    display: flex;
    justify-content: space-between; /* Align items in a row */
    align-items: center; /* Vertically center */
    margin-bottom: 15px;
}

.search-bar {
    width: 250px; /* Set search bar width */
    padding: 9px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
    margin-right: 10px; /* Add spacing between search bar and buttons */
}

.button-container {
    display: flex;
    gap: 10px; /* Space between buttons */
    margin-bottom: 2px;
}

/* Footer Styling */
html, body {
    height: 100%;
    margin: 0;
    display: flex;
    flex-direction: column;
    overflow-y: auto; /* Allow vertical scrolling */
}

.footer {
    background: linear-gradient(145deg, #33d1c9, #1da2a0); /* Light Sea Green gradient */
    color: #ffffff; /* White text */
    text-align: center; /* Center content */
    padding: 10px 0; /* Adjust padding as needed */
    box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.2); /* Subtle shadow */
    margin-top: 20px;
}

.footer-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px; /* Space between logo and text */
    flex-wrap: wrap; /* Wrap content on smaller screens */
}

.footer-logo {
    width: 50px;
    height: auto;
}

.footer p {
    margin: 0;
    font-size: 14px;
    font-weight: normal;
    opacity: 0.9; /* Slight transparency for the text */
}

.button-search-container {
    display: flex;
    justify-content: flex-end; /* Align everything to the right */
    align-items: center;
    margin-bottom: 15px;
}

/* Right controls: Export, Search Bar, Add Student, and Sort Button */
.right-controls {
    display: flex;
    align-items: center;
    gap: 10px; /* Space between elements */
}

/* Search Bar */
.search-bar {
    width: 250px;
    padding: 9px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}

.export-btn, .import-btn, .add-btn, .sort-btn {
    background-color: #20b2aa;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.export-btn:hover, .import-btn:hover, .add-btn:hover, .sort-btn:hover {
    background-color: #2ac2b0;
}

.student-link {
    text-decoration: underline;  /* ✅ Add underline */
    color: black;
    font-weight: bold;
}

.student-link:hover {
    color: rgb(122, 118, 118);  /* Optional: slightly darker on hover */
}

#semesterFilter {
    padding: 8px;
    border-radius: 5px;
    font-size: 14px;
}

@media (max-width: 768px) {
    .sidebar {
        width: 220px;
        transform: translateX(-100%);
        position: fixed;
        z-index: 1001;
        transition: transform 0.3s ease;
    }

    .sidebar.active {
        transform: translateX(0);
    }

    .main-content {
        width: 100% !important;
        margin-left: 0 !important;
        padding: 10px;
    }

    .button-search-container,
    .right-controls {
        flex-direction: column;
        align-items: stretch;
        margin-top: 10px;
    }

    .search-bar,
    .export-btn,
    .import-btn,
    .add-btn,
    .sort-btn,
    #semesterFilter {
        width: 100% !important;
        margin: 5px 0 !important;
    }

    .student-table table {
        font-size: 14px;
        overflow-x: auto;
        display: block;
        white-space: nowrap;
    }
}

@media (max-width: 480px) {
    .student-table table th,
    .student-table table td {
        padding: 10px;
        font-size: 12px;
    }

    .navbar .logo p {
        font-size: 18px;
    }

    .navbar .logo p.faculty-title {
        font-size: 12px;
    }
}

</style>

<body>
<div class="container">
    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">
            <button id="sidebarToggle" class="menu-toggle">
                <i class="fas fa-bars"></i> <!-- Default Icon -->
            </button>
            <img src="image/logoumk.png" alt="UMK Logo" class="logo-img">
            <div>
                <p>UNIVERSITI MALAYSIA KELANTAN</p>
                <p class="faculty-title">FACULTY OF DATA SCIENCE & COMPUTING</p>
            </div>
        </div>
        <ul class="menu">
        <li>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </li>
        </ul>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="#" data-bs-toggle="collapse" data-bs-target="#studentSubmenu" aria-expanded="false" aria-controls="studentSubmenu">
            <i class="fas fa-user-graduate"></i> Student
        </a>
        <div class="collapse" id="studentSubmenu">
            <a href="admin_list.php" class="collapse-item">Student List</a>
            <a href="admin_letter.php" class="collapse-item">Letter</a>
            <a href="admin_before_LI.php" class="collapse-item">Implementation IT</a>
        </div>
        <a href="#" data-bs-toggle="collapse" data-bs-target="#staffSubmenu" aria-expanded="false" aria-controls="staffSubmenu">
            <i class="fas fa-users"></i> Lecturer
        </a>
        <div class="collapse" id="staffSubmenu">
        <a href="admin_lecturer_list.php" class="collapse-item">Lecturer List</a>
        </div>
    </div>

    <div class="main-content" id="main-content">
        <section class="student-table">
            <h3>Student</h3>
            
    <div class="button-search-container">
        <!-- Buttons on the Right -->
        <div class="right-controls">
            <button type="button" class="btn btn-warning" id="importUsersBtn">Import Users</button>
            <button id="exportCSV" class="btn export-btn">Export</button>
            <button id="importCSVBtn" class="btn import-btn" style="margin-left:5px;">Import</button>
            <input type="text" id="searchInput" placeholder="Search by name..." class="form-control search-bar">
            <button id="addStudentBtn" class="btn add-btn">Add Student</button>
            <select id="semesterFilter" class="form-control" style="width: 150px; margin-left: 10px;">
                <option value="">All Semesters</option>
                <option value="Feb 24/25">Feb 24/25</option>
                <option value="Sep 25/26">Sep 25/26</option>
            </select>
        </div>
    </div>

    <!-- NEW MODAL FOR "Import Users" -->
    <div id="importUsersModal2" class="modal">
    <div class="modal-content">
        <!-- Close (X) -->
        <span class="close">&times;</span>
        
        <!-- Title -->
        <h3>Import Users CSV</h3>

        <!-- Form: you can POST to the same admin_list.php or a new file -->
        <form id="importUsersForm" action="import_users.php" method="POST" enctype="multipart/form-data">
        <input 
            type="file" 
            name="csv_file" 
            id="usersCsvFile" 
            accept=".csv" 
            required 
            style="margin-bottom:15px;"
        >
        <button 
            type="submit" 
            name="import_csv" 
            class="btn" 
            style="background-color: #20b2aa; color: #fff;"
        >
            Upload
        </button>
        </form>
    </div>
    </div>

    <!-- Import CSV Modal -->
    <div id="importModal" class="modal">
        <div class="modal-content">
          <span class="close">&times;</span>
          <h3>Upload CSV File</h3>
          <form id="importCSVForm" method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" id="csvFile" accept=".csv" required style="margin-bottom:15px;">
            <button type="submit" name="import_csv" class="btn" style="background-color: #20b2aa; color: #fff;">
              Upload
            </button>
          </form>
        </div>
    </div>

    <div id="addStudentModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Add New Student</h3>
            <form id="addStudentForm">
                <input type="text" id="studentName" placeholder="Enter Name" required>
                <input type="text" id="studentMatrix" placeholder="Enter Matrix" required>
                <select id="studentLecturer" required style="padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                    <option value="">Select Lecturer</option>
                    <?php foreach ($lecturers as $lecturer): ?>
                        <option value="<?php echo htmlspecialchars($lecturer); ?>"><?php echo htmlspecialchars($lecturer); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="studentSemester" required style="padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                    <option value="">Select Semester</option>
                    <option value="Feb 24/25">Feb 24/25</option>
                    <option value="Sep 25/26">Sep 25/26</option>
                </select>
                <button type="submit">Add Student</button>
            </form>
        </div>
    </div>

            <table id="studentTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th class="name-column">Name</th>
                        <th class="matrix-column">Matric</th>
                        <th class="email-column">Email</th>
                        <th class="lecturer-column">Lecturer</th>
                        <th class="semester-column">Semester</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $sql = "SELECT id, name, matrix, email, lecturer, semester FROM students ORDER BY id ASC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        $counter = 1;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $counter++ . "</td>"; // Serial number
                            // Student name clickable
                            echo "<td><a href='student_profile.php?id=" . $row['id'] . "' class='student-link'>" 
                                 . htmlspecialchars($row['name']) 
                                 . "</a></td>";
                            echo "<td>" . htmlspecialchars($row['matrix']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            
                            // Lecturer name clickable
                            echo "<td>
                            <a href='admin_lecturer.php?lecturer=" . urlencode($row['lecturer']) . "' 
                               class='student-link'>" 
                               . htmlspecialchars($row['lecturer']) . 
                            "</a>
                          </td>";  
                          echo "<td>" . htmlspecialchars($row['semester']) . "</td>";

                        }
                    }                   
                    ?>
                </tbody>
            </table>
        </section>
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <img src="image/logoumk.png" alt="UMK Logo" class="footer-logo">
        <p>&copy; 2021 Universiti Malaysia Kelantan | Entrepreneur University. All Rights Reserved.</p>
    </div>
</footer>

<script>
  document.addEventListener("DOMContentLoaded", () => {
  const importUsersBtn     = document.getElementById("importUsersBtn");
  const importUsersModal2  = document.getElementById("importUsersModal2");
  const closeUsersModal2   = importUsersModal2.querySelector(".close");

  // OPEN the new modal on button click
  importUsersBtn.addEventListener("click", () => {
    importUsersModal2.style.display = "flex"; 
  });

  // CLOSE the new modal when clicking the (X)
  closeUsersModal2.addEventListener("click", () => {
    importUsersModal2.style.display = "none";
  });

  // CLOSE the new modal if user clicks outside the modal content
  window.addEventListener("click", (e) => {
    if (e.target === importUsersModal2) {
      importUsersModal2.style.display = "none";
    }
  });
});

// Modal functionality for Import CSV
document.addEventListener("DOMContentLoaded", () => {
  const importCSVBtn = document.getElementById("importCSVBtn");
  const importModal = document.getElementById("importModal");
  const importClose = importModal.querySelector(".close");

  // Open only on button click
  importCSVBtn.addEventListener("click", () => {
  importModal.style.display = "flex";
  });

  // Close on X
  importClose.addEventListener("click", () => {
  importModal.style.display = "none";
  });

  // Close when clicking outside
  window.addEventListener("click", (e) => {
    if (e.target === importModal) {
      importModal.style.display = "none";
    }
  });
});

// Toggle Sidebar and Main Content
const sidebarToggle = document.getElementById("sidebarToggle");
const sidebar = document.querySelector(".sidebar");
const mainContent = document.querySelector(".main-content");
const toggleIcon = sidebarToggle.querySelector("i");

sidebarToggle.addEventListener("click", () => {
    // Toggle the sidebar
    sidebar.classList.toggle("active");

    // Adjust the icon
    if (sidebar.classList.contains("active")) {
        toggleIcon.classList.remove("fa-bars");
        toggleIcon.classList.add("fa-times");
    } else {
        toggleIcon.classList.remove("fa-times");
        toggleIcon.classList.add("fa-bars");
    }
});

    const addStudentBtn = document.getElementById('addStudentBtn');
    const addStudentModal = document.getElementById('addStudentModal');
    const closeModal = document.querySelector('.modal .close');

    addStudentBtn.addEventListener('click', () => {
        addStudentModal.style.display = 'flex';
    });

    closeModal.addEventListener('click', () => {
        addStudentModal.style.display = 'none';
    });

    window.onclick = (event) => {
        if (event.target === addStudentModal) {
            addStudentModal.style.display = 'none';
        }
    };

document.addEventListener("DOMContentLoaded", () => {
    console.log("Script initialized, DOM fully loaded");

    // Select required elements
    const modal = document.getElementById("addStudentModal");
    const addStudentBtn = document.getElementById("addStudentBtn");
    const closeBtn = document.querySelector("#addStudentModal .close");
    const addStudentForm = document.getElementById("addStudentForm");
    const tableBody = document.querySelector("#studentTable tbody");

    // Ensure modal is hidden by default
    modal.style.display = "none";

    // Open the modal when clicking the "Add Student" button
    addStudentBtn.addEventListener("click", () => {
        console.log("Opening modal...");
        modal.style.display = "flex";
    });

    // Close the modal when clicking the close button
    closeBtn.addEventListener("click", () => {
        console.log("Closing modal...");
        modal.style.display = "none";
    });

    // Close modal when clicking outside the modal content
    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            console.log("Closing modal by clicking outside...");
            modal.style.display = "none";
        }
    });

    // Add student on form submit
    addStudentForm.addEventListener("submit", (event) => {
        event.preventDefault();
        console.log("Form submitted. Attempting to add student...");
        addStudent();
    });

    // Function to add a new student
    function addStudent() {
    const nameInput = document.getElementById("studentName").value.trim();
    const matrixInput = document.getElementById("studentMatrix").value.trim();

    // Create a new table row
    const newRow = document.createElement("tr");
    newRow.innerHTML = `
        <td>${document.querySelectorAll("#studentTable tbody tr").length + 1}</td>
        <td>${nameInput}</td>
        <td>${matrixInput}</td>
    `;

    // Append the row to the table body
    const tableBody = document.querySelector("#studentTable tbody");
    tableBody.appendChild(newRow);

    // Reset the form and close the Add Student modal
    document.getElementById("addStudentForm").reset();
    document.getElementById("addStudentModal").style.display = "none";

    // Show success popup modal
    const successModal = document.getElementById("successModal");
    successModal.style.display = "flex";

    // Add close button functionality for success modal
    document.getElementById("successCloseBtn").addEventListener("click", () => {
        successModal.style.display = "none";
    });

    console.log("Student added successfully.");
}

    // Sort table by Name column
    document.querySelector("button[onclick='sortTable()']").addEventListener("click", () => {
        sortTable();
    });

function sortTable() {
    const rows = Array.from(tableBody.rows);

        rows.sort((a, b) => {
            const nameA = a.cells[1].textContent.trim().toLowerCase();
            const nameB = b.cells[1].textContent.trim().toLowerCase();

            return nameA.localeCompare(nameB);
        });

        // Re-append sorted rows
        rows.forEach((row) => tableBody.appendChild(row));

        // Update row numbers
        rows.forEach((row, index) => {
            row.cells[0].textContent = index + 1;
        });

        console.log("Table sorted by Name column.");
    }
});

// navbar
document.addEventListener("DOMContentLoaded", () => {
    console.log("Script initialized, DOM fully loaded");

    // Navbar scroll-hide functionality
    const navbar = document.querySelector(".navbar");
    let lastScrollTop = 0;

    window.addEventListener("scroll", () => {
        const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (currentScrollTop > lastScrollTop) {
            navbar.style.transform = "translateY(0)"; // Hide when scrolling down
        } else {
            navbar.style.transform = "translateY(0)"; // Show when scrolling up
        }

        lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop; // Prevent negative values
    });

});

document.addEventListener("DOMContentLoaded", () => {
    const backToNavbar = document.getElementById("backToNavbar");

    // Show or hide the button based on scroll position
    window.addEventListener("scroll", () => {
        if (window.scrollY > 200) { // Show after scrolling 200px
            backToNavbar.classList.add("show");
        } else {
            backToNavbar.classList.remove("show");
        }
    });

    // Smooth scroll to the top when the button is clicked
    backToNavbar.addEventListener("click", () => {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });
});

document.getElementById("addStudentForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const studentName = document.getElementById("studentName").value.trim();
    const studentMatrix = document.getElementById("studentMatrix").value.trim();
    const studentLecturer = document.getElementById("studentLecturer").value.trim(); 
    const studentSemester = document.getElementById("studentSemester").value.trim();

    if (!studentName || !studentMatrix || !studentLecturer) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'All fields are required.',
            confirmButtonColor: '#d33'
        });
        return;
    }

    const formData = new FormData();
    formData.append("name", studentName);
    formData.append("matrix", studentMatrix);
    formData.append("lecturer", studentLecturer); // Include Lecturer Data
    formData.append("semester", studentSemester);

    fetch("admin_list.php", { // Adjust to the correct PHP file handling insertion
        method: "POST",
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        console.log("Server Response:", data);

        if (data.status === "success") {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Student added successfully!',
                confirmButtonColor: '#6c5ce7',
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });

            document.getElementById("addStudentForm").reset();
            document.getElementById("addStudentModal").style.display = "none";
        } else if (data.status === "error" && data.message.includes("exists")) {
            Swal.fire({
                icon: 'warning',
                title: 'Duplicate Matric Number!',
                text: data.message,
                confirmButtonColor: '#8e44ad',
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to add student. Please try again.',
                confirmButtonColor: '#d33'
            });
        }
    })
    .catch(error => {
        console.error("Error:", error);
        Swal.fire({
            icon: 'error',
            title: 'Network Error!',
            text: 'Failed to connect to the server.',
            confirmButtonColor: '#d33'
        });
    });
});

// Function to filter students by name
document.getElementById("searchInput").addEventListener("keyup", function () {
    const searchValue = this.value.toLowerCase();
    const tableRows = document.querySelectorAll("#studentTable tbody tr");

    tableRows.forEach(row => {
        const nameCell = row.cells[1].textContent.toLowerCase(); // Get the Name column
        if (nameCell.includes(searchValue)) {
            row.style.display = ""; // Show row if it matches
        } else {
            row.style.display = "none"; // Hide row if it doesn't match
        }
    });
});

document.getElementById("exportCSV").addEventListener("click", function () {
    let csvContent = "data:text/csv;charset=utf-8,"; 
    let table = document.getElementById("studentTable");
    let rows = table.querySelectorAll("tr");

    // Remove the line that shows the import modal!
    // importModal.style.display = "flex";

    rows.forEach((row) => {
        let rowData = [];
        row.querySelectorAll("th, td").forEach((cell) => {
            rowData.push(cell.innerText);
        });
        csvContent += rowData.join(",") + "\n";
    });

    let encodedUri = encodeURI(csvContent);
    let link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "student_data.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});

document.getElementById("semesterFilter").addEventListener("change", function () {
    const selectedSemester = this.value.toLowerCase();
    const tableRows = document.querySelectorAll("#studentTable tbody tr");

    tableRows.forEach(row => {
        const semesterCell = row.cells[5].textContent.toLowerCase(); // Semester column (6th column = index 5)
        if (selectedSemester === "" || semesterCell.includes(selectedSemester)) {
            row.style.display = ""; // Show row if matches or if "All"
        } else {
            row.style.display = "none"; // Hide if doesn't match
        }
    });
});
</script>

</body>
</html>