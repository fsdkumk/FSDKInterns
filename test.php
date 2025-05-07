<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
include("db_connect.php");

file_put_contents('error_log.txt', "Error: " . $conn->error . "\n", FILE_APPEND);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect data from form
    $form_name = isset($_POST['form_name']) ? $_POST['form_name'] : '';
    $due_date = isset($_POST['time_submission_due']) ? $_POST['time_submission_due'] : '';

    // Ensure form inputs are not empty
    if (!empty($form_name) && !empty($due_date)) {
        $status = "Pending"; // Default status value
        $created_at = date("Y-m-d H:i:s"); // Current timestamp

        // Prepare SQL query
        $stmt = $conn->prepare("INSERT INTO before_li (status, form_name, due_date, created_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $status, $form_name, $due_date, $created_at);

        // Execute the query
        if ($stmt->execute()) {
            echo "New form added successfully!";
        } else {
            echo "Error executing query: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: Form Name or Time Submission Due is empty.";
    }
} else {
    echo "Error: Invalid request method.";
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admin| Before LI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQ5BtjmQVKynWj8fPqKflEUVb+6ugvcgO/nr36M9pqE8s6rHuX4r3xIM+" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Add Bootstrap JavaScript -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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

/* Adjust Log Out Button Styling */
.navbar .menu li {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    background: #e0f7f5;
    color: #333333;
    border-radius: 5px;
    font-size: 14px;
    font-weight: bold;
    text-transform: capitalize;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    margin-left: 30px; /* Add slight left margin if needed */
    margin-right: 40px;
}

.navbar .menu li:hover {
    background: #e0f7f5;
    color: #20b2aa;
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
    width: calc(100% - 250px);
    background-color: #e0f7f5;
    min-height: 100%; /* Ensures content height scales */
    overflow-y: auto; /* Allows scrolling if content overflows */
    margin-top: 95px; /* Adjust to the height of the navbar */
}

/* Main Content When Sidebar is Open */
.sidebar.active ~ .main-content {
    margin-left: 250px; /* Match the sidebar width */
}

.container {
    flex: 1;
    display: flex;
    flex-direction: column;
}

/* Header Section */
header {
    background-color: #20b2aa;
    color: #ffffff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header h2 {
    margin: 0;
    font-size: 24px;
    color: #ffffff;
}

header p {
    margin: 0;
    font-size: 14px;
    opacity: 0.9;
}

.student-table h3 {
    margin: 0;
    margin-bottom: 10px;
    padding-bottom: 10px;
    font-size: 20px;
    color: #20b2aa; /* Light Sea Green */
    border-bottom: 2px solid #20b2aa;
    display: inline-block;
}

/* Make "Daftar Untuk Menjalani LI" Text Black */
.student-table ul li a {
    color: black; /* Set text color to black */
    text-decoration: none; /* Remove underline */
    font-weight: bold; /* Optional: Make it bold */
}

.student-table ul li a:hover {
    color: #20b2aa; /* Optional: Change color on hover for better UX */
    text-decoration: underline; /* Optional: Add underline on hover */
}

/* Status Buttons */
.status-btn {
    border-radius: 50px;
    color: white;
    font-weight: bold;
    border: none;
    padding: 5px 15px;
    margin-bottom: 40px;
}

.status-btn.red {
    background-color: #d9534f;
}

.status-btn.green {
    background-color: #5cb85c;
}

/* Status Button Container */
.status-btn-container {
    display: flex;
    justify-content: flex-start;
    gap: 20px;
    margin-top: 10px;
}

/* Add Button */
.add-button {
    margin-left: auto;
    color: #20b2aa;
    background-color: #e0f7f5;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    cursor: pointer;
}

/* Modal Content */
.modal-content {
    border-radius: 15px;
    overflow: hidden;
}

.modal-header {
    background-color: #20b2aa;
    color: white;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    background-color: #e0f7f5;
    border-top: 1px solid #ddd;
}

.modal .btn {
    background-color: #20b2aa;
    color: #fff; /* Make text white for contrast */
    border: none;
    border-radius: 5px;
    padding: 10px 15px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.modal .btn:hover {
    background-color: #1a8d8a; /* Slightly darker shade for hover */
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
                <p class="faculty-title">FAKULTI SAINS DATA & KOMPUTERAN</p>
            </div>
        </div>
        <ul class="menu">
            <li><i class="fas fa-sign-out-alt"></i> Log out</li>
        </ul>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="#dashboard"><i class="fas fa-home"></i> Dashboard</a>
        <a href="#" data-bs-toggle="collapse" data-bs-target="#studentSubmenu" aria-expanded="false" aria-controls="studentSubmenu">
            <i class="fas fa-user-graduate"></i> Student
        </a>
        <div class="collapse" id="studentSubmenu">
            <a href="#student1" class="collapse-item">Student 1</a>
            <a href="#student2" class="collapse-item">Student 2</a>
            <a href="#student3" class="collapse-item">Student 3</a>
        </div>
        <a href="#" data-bs-toggle="collapse" data-bs-target="#staffSubmenu" aria-expanded="false" aria-controls="staffSubmenu">
            <i class="fas fa-users"></i> Staff
        </a>
        <div class="collapse" id="staffSubmenu">
            <a href="#staff1" class="collapse-item">Staff 1</a>
            <a href="#staff2" class="collapse-item">Staff 2</a>
            <a href="#staff3" class="collapse-item">Staff 3</a>
        </div>
    </div>

<!-- Main Content -->
<div class="main-content">
    <header>
    <h2>Implementation Before LI </h2>
        <button id="addFormButton" class="add-button" data-bs-toggle="modal" data-bs-target="#addFormModal">+</button>
    </header>

    <!-- Section 1: Perlaksanaan Sebelum LI -->
    <section class="student-table">
        <h3 style="align-items: center;">
            Application Status
        </h3>

        <div class="container text-center">
            <p style="text-align: left; margin-bottom: 10px;">Status Undergoing Industrial Training:</p>
            <div class="status-btn-container">
                <button id="statusButton1" class="status-btn red" data-active-text="Haven't got any LI yet">Haven't got any LI yet</button>
                <button id="statusButton2" class="status-btn red" data-active-text="At least get 1 LI">At least get 1 LI</button>
                <button id="statusButton3" class="status-btn red" data-active-text="Already confirmed / decided">Already confirmed / decided</button>
            </div>
        </div>
    </section>

    <!-- Add Form Modal -->
    <div class="modal fade" id="addFormModal" tabindex="-1" aria-labelledby="addFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFormModalLabel">Add New Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <form id="addForm" action="admin_before_LI.php" method="POST">
                    <div class="mb-3">
                        <label for="formName" class="form-label">Form Name</label>
                        <input type="text" class="form-control" id="formName" name="form_name" placeholder="Enter form name" required>
                    </div>
                    <div class="mb-3">
                        <label for="timeSubmissionDue" class="form-label">Time Submission Due</label>
                        <input type="datetime-local" class="form-control" id="timeSubmissionDue" name="time_submission_due" required>
                    </div>
                    <button type="submit" class="btn" style="background-color: #20b2aa; color: white;">Submit</button>
                </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Semasa LI -->
    <section class="student-table">
        <h3>Forms</h3>
        <ul>
            <!-- New list items will be added here dynamically -->
        </ul>
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
    </div>
   
<script>

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

// Handle Status Button Clicks
document.querySelectorAll(".status-btn").forEach((button) => {
    button.addEventListener("click", function () {
        // Deactivate all buttons
        document.querySelectorAll(".status-btn").forEach((btn) => {
            btn.classList.remove("green"); // Remove active class
            btn.classList.add("red"); // Add inactive class
            btn.textContent = btn.dataset.originalText; // Reset to original text
        });

        // Activate the clicked button
        this.classList.remove("red"); // Remove inactive class
        this.classList.add("green"); // Add active class
        this.textContent = this.dataset.activeText; // Set active text

        // Get the selected status
        const selectedStatus = this.dataset.activeText;

        // Optional: Alert the selected status
        alert(`Selected Status: ${selectedStatus}`);
    });
});

// Initialize button texts with data attributes
document.querySelectorAll(".status-btn").forEach((button) => {
    if (!button.dataset.originalText) {
        button.dataset.originalText = button.textContent; // Save original button text
    }
    if (!button.dataset.activeText) {
        button.dataset.activeText = button.textContent; // Default active text
    }
});

document.addEventListener("DOMContentLoaded", () => {
    const addForm = document.getElementById("addForm");
    const formsList = document.querySelector(".student-table ul"); // Target the <ul> under the "Forms" section

    if (addForm) {
        addForm.addEventListener("submit", (e) => {
            e.preventDefault(); // Prevent the default form submission behavior

            // Get form values
            const formName = document.getElementById("formName").value;
            const timeSubmissionDue = document.getElementById("timeSubmissionDue").value;

            // Create a new list item
            const listItem = document.createElement("li");
            listItem.innerHTML = `
                <strong>${formName}</strong>
                <strong>-</strong> ${new Date(timeSubmissionDue).toLocaleString()}
            `;

            // Append the new list item to the forms list
            formsList.appendChild(listItem);

            // Clear the form fields
            addForm.reset();

            // Close the modal (if applicable)
            const modal = bootstrap.Modal.getInstance(document.getElementById("addFormModal"));
            if (modal) modal.hide();

            alert("Form added successfully!");
        });
    }
});
    </script>

</body>
</html>