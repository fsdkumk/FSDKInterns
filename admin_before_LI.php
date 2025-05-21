<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true) {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $due_date = isset($_POST['time_submission_due']) ? trim($_POST['time_submission_due']) : '';
    $require_upload = isset($_POST['require_upload']) ? $_POST['require_upload'] : 'No';
    $redirect_url = isset($_POST['redirect_url']) ? trim($_POST['redirect_url']) : '';
    $button_name = isset($_POST['button_name']) ? trim($_POST['button_name']) : ''; // Ensure this is set

    if (empty($due_date)) {
        echo json_encode(["status" => "error", "message" => "Due Date is required."]);
        exit();
    }

    // Handle file upload
    $file_path = null;
    $form_name = null;
    if (isset($_FILES['form_file']) && $_FILES['form_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['form_file']['tmp_name'];
        $form_name = pathinfo($_FILES['form_file']['name'], PATHINFO_FILENAME);
        $file_name = "form_" . uniqid() . "_" . $_FILES['form_file']['name'];
        $upload_dir = "uploads/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $file_path = $upload_dir . $file_name;
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to upload file."]);
            exit();
        }
    }

    // Ensure `button_name` is required only if `require_upload` is "Yes"
    if ($require_upload === "Yes" && empty($button_name)) {
        echo json_encode(["status" => "error", "message" => "Button name is required if Require Upload is Yes."]);
        exit();
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO before_li (form_name, due_date, button_name, file_path, redirect_url, require_upload, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssss", $form_name, $due_date, $button_name, $file_path, $redirect_url, $require_upload);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Form added successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error inserting data: " . $stmt->error]);
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
    <title>admin | Before LI</title>
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

.add-form-btn {
    background: rgba(255, 255, 255, 0.25);
    color: #0d6efd;
    border: 2px solid #0d6efd;
    padding: 10px 18px;
    font-weight: bold;
    border-radius: 12px;
    font-size: 16px;
    backdrop-filter: blur(5px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.add-form-btn:hover {
    background: #0d6efd;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(13, 110, 253, 0.4);
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
    justify-content: center;
    align-items: center;
    width: 100%;
    margin-top: 50px
}

/* FORM CONTAINER */
.form-container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #e0f7f5;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

/* FORM CARD */
.form-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    border-radius: 10px;
    padding: 12px 20px;
    margin-bottom: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: 0.3s ease-in-out;
}

.form-card:hover {
    transform: scale(1.02);
}

/* FORM DETAILS */
.form-details {
    display: flex;
    flex-direction: column;
}

/* FORM LINK */
.form-link {
    font-weight: bold;
    font-size: 16px;
    color: #20b2aa;
    text-decoration: none;
    transition: 0.3s;
}

.form-link:hover {
    text-decoration: underline;
}

/* DUE DATE */
.due-date {
    font-size: 14px;
    color: #666;
    margin-top: 4px;
}

/* DELETE BUTTON */
.delete-btn {
    background: #ff4d4d;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: 0.3s;
}

.delete-btn i {
    margin-right: 5px;
}

.delete-btn:hover {
    background: #cc0000;
}

/* NO FORMS MESSAGE */
.no-forms {
    text-align: center;
    font-size: 16px;
    color: #555;
    margin-top: 20px;
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

.download-btn {
    background: linear-gradient(to right, #20c997, #17a2b8); /* teal & turquoise */
    color: white;
    border: none;
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 25px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.download-btn:hover {
    background: linear-gradient(to right, #17a2b8, #20c997);
    transform: scale(1.05);
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

@media (max-width: 768px) {
    .sidebar {
        width: 220px;
        transform: translateX(-100%);
        z-index: 1001;
    }

    .sidebar.active {
        transform: translateX(0);
    }

    .main-content {
        width: 100% !important;
        margin-left: 0 !important;
        padding: 15px;
    }

    .form-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .form-link {
        font-size: 15px;
    }

    .due-date {
        font-size: 13px;
    }

    .delete-btn {
        align-self: flex-end;
        font-size: 13px;
        padding: 6px 10px;
    }

    header {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }

    header h2 {
        font-size: 20px;
    }

    .logout-btn {
        font-size: 14px;
        padding: 6px 16px;
    }

    .modal-content {
        width: 95%;
        margin: auto;
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
                <p>Industrial Attachment Management System</p>
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
        <a href="usermanual/User_Manual_Admin.pdf" download><i class="fas fa-book"></i> User Manual</a>
    </div>

<!-- Main Content -->
<div class="main-content">
<header class="d-flex justify-content-between align-items-center">
  <h2>IMPLEMENTATION INDUSTRIAL TRAINING</h2>
  <button id="addFormButton" class="logout-btn" style="background-color: white; color: #20b2aa; border: 2px solid #20b2aa;" data-bs-toggle="modal" data-bs-target="#addFormModal">
    <i class="fas fa-plus"></i> Add New Form
  </button>
</header>


        <!-- Add Form Modal -->
        <div class="modal fade" id="addFormModal" tabindex="-1" aria-labelledby="addFormModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addFormModalLabel">Upload New Form</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    <form id="uploadForm" action="admin_before_LI.php" method="POST" enctype="multipart/form-data">

            <div class="mb-3">
                <label for="timeSubmissionDue" class="form-label">Time Submission Due</label>
                <input type="datetime-local" class="form-control" id="timeSubmissionDue" name="time_submission_due" required>
            </div>

            <div class="mb-3">
                <label for="formFile" class="form-label">Upload File</label>
                <input type="file" class="form-control" id="formFile" name="form_file" required>
            </div>

            <div class="mb-3">
            <label class="form-label">Require Button?</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="require_upload" id="requireNo" value="No" checked>
                    <label class="form-check-label" for="requireNo">No</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="require_upload" id="requireYes" value="Yes">
                    <label class="form-check-label" for="requireYes">Yes</label>
                </div>
                <!-- Button Name Field -->
                <div class="mb-3" id="buttonNameField">
                    <label for="buttonName" class="form-label">Button Name</label>
                    <input type="text" class="form-control" id="buttonName" name="button_name">
                </div>
                <!-- Redirect URL Field (Hidden by Default) -->
                <div class="mb-3" id="redirectUrlField" style="display: none;">
                    <label for="redirectUrl" class="form-label">Redirect URL</label>
                    <input type="url" class="form-control" id="redirectUrl" name="redirect_url" placeholder="https://example.com">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>


                    <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        const requireYes = document.getElementById("requireYes");
                        const requireNo = document.getElementById("requireNo");
                        const buttonNameField = document.getElementById("buttonNameField");
                        const buttonNameInput = document.getElementById("buttonName");

                        function toggleButtonNameInput() {
                            if (requireYes.checked) {
                                buttonNameInput.disabled = false; // Enable input
                            } else {
                                buttonNameInput.disabled = true; // Disable input
                                buttonNameInput.value = ""; // Clear input field
                            }
                        }

                        // Initial check
                        toggleButtonNameInput();

                        // Event Listeners
                        requireYes.addEventListener("change", toggleButtonNameInput);
                        requireNo.addEventListener("change", toggleButtonNameInput);
                    });
                    </script>
            </div>
        </div>
    </div>
</div>

    <!-- Section 2: Semasa LI -->
    <section class="student-table">
        <h3>Forms</h3>
        <div id="formsList">
            <?php include 'fetch_forms.php'; ?>
        </div>
    </section>

<form method="post" action="export_form_data.php">
    <button type="submit" class="download-btn">Download Data Form 1</button>
</form>

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

// Function to update admin page based on student selection
function checkStatusUpdate() {
    const studentId = 1; // Replace with dynamic student ID if needed

    fetch(`fetch_status.php?student_id=${studentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                document.querySelectorAll(".status-btn").forEach((btn) => {
                    if (btn.dataset.activeText === data.status) {
                        btn.classList.add("green");
                        btn.classList.remove("red");
                    } else {
                        btn.classList.remove("green");
                        btn.classList.add("red");
                    }
                });
            }
        })
        .catch(error => console.error("Error fetching status:", error));
}

// Check for updates every 5 seconds
setInterval(checkStatusUpdate, 5000);

// Also listen for localStorage changes for real-time updates
window.addEventListener("storage", function(event) {
    if (event.key === "latestStatus") {
        checkStatusUpdate();
    }
});

// Initial load to set the correct button state
document.addEventListener("DOMContentLoaded", checkStatusUpdate);


document.addEventListener("DOMContentLoaded", function () {
    const addForm = document.getElementById("addForm");
    const modalElement = document.getElementById("addFormModal");
    const modal = new bootstrap.Modal(modalElement); // Ensure Bootstrap modal is loaded

    if (addForm) {
        addForm.addEventListener("submit", function (e) {
            e.preventDefault(); // Prevent default form submission

            console.log("Submitting form..."); // ✅ Debugging log

            const submitButton = addForm.querySelector("button[type='submit']");
            submitButton.disabled = true; // Disable to prevent multiple clicks

            const formData = new FormData(addForm);

            fetch("admin_before_LI.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text()) // Read response as text
            .then(text => {
                console.log("Raw response:", text); // ✅ Debugging log
                try {
                    return JSON.parse(text); // Try parsing as JSON
                } catch (e) {
                    console.error("Invalid JSON response:", text);
                    throw new Error("Invalid JSON response: " + text);
                }
            })
            .then(data => {
                console.log("Parsed response:", data); // ✅ Debugging log

                if (data.status === "success") {
                    alert("Form added successfully!");
                    loadForms(); // Reload the forms list
                    addForm.reset(); // Reset form fields
                    modal.hide(); // Close the modal
                } else {
                    alert("Error: " + data.message);
                }

                submitButton.disabled = false;
            })
            .catch(error => {
                console.error("Error submitting form:", error);
                alert("Submission failed! Check console for details.");
                submitButton.disabled = false;
            });
        });
    }
});


// Function to fetch and display forms dynamically
function loadForms() {
    fetch("fetch_forms.php")
        .then(response => response.text())
        .then(data => {
            const formsList = document.getElementById("formsList");
            formsList.innerHTML = data;
        })
        .catch(error => console.error("Error fetching forms:", error));
}

// Load forms when the page is loaded
document.addEventListener("DOMContentLoaded", loadForms);
document.addEventListener("DOMContentLoaded", function () {
    const uploadForm = document.getElementById("uploadForm");

    uploadForm.addEventListener("submit", function (e) {
        e.preventDefault(); // Prevent default submission

        const formData = new FormData(uploadForm);
        const submitButton = uploadForm.querySelector("button[type='submit']");
        submitButton.disabled = true; // Disable to prevent multiple clicks

        fetch("admin_before_LI.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text()) // Read as text for debugging
        .then(text => {
            console.log("Raw Response:", text);
            return JSON.parse(text);
        })
        .then(data => {
            console.log("Parsed Response:", data);
            if (data.status === "success") {
                // Show SweetAlert2 Success Message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Form uploaded successfully!',
                    confirmButtonColor: '#6c5ce7',
                    confirmButtonText: 'OK'
                }).then(() => {
                    uploadForm.reset(); // Reset form fields
                    location.reload(); // Refresh to show new files
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message,
                    confirmButtonColor: '#d33'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Upload Failed!',
                text: 'Something went wrong. Please try again.',
                confirmButtonColor: '#d33'
            });
            console.error("Error uploading form:", error);
        })
        .finally(() => {
            submitButton.disabled = false; // Re-enable button
        });
    });
});


document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("formsList").addEventListener("click", function(event) {
        if (event.target.classList.contains("delete-btn")) {
            const formId = event.target.getAttribute("data-id");

            if (!formId) {
                console.error("Form ID is missing in button.");
                return;
            }

            // Show SweetAlert2 Confirmation Popup
            Swal.fire({
                icon: 'warning',
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("delete_form.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: new URLSearchParams({ id: formId }),
                        credentials: "include"
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "success") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'The form has been deleted.',
                                showConfirmButton: false,
                                timer: 1500
                            });

                            document.getElementById("form_" + formId).remove(); // Remove from UI
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to delete. Please try again.'
                        });
                        console.error("Error deleting form:", error);
                    });
                }
            });
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const requireYes = document.getElementById("requireYes");
    const requireNo = document.getElementById("requireNo");
    const buttonNameField = document.getElementById("buttonNameField");
    const buttonNameInput = document.getElementById("buttonName");
    const redirectUrlField = document.getElementById("redirectUrlField");
    const redirectUrlInput = document.getElementById("redirectUrl");

    function toggleFields() {
        if (requireYes.checked) {
            buttonNameField.style.display = "block";
            redirectUrlField.style.display = "block";
        } else {
            buttonNameField.style.display = "none";
            buttonNameInput.value = "";
            redirectUrlField.style.display = "none";
            redirectUrlInput.value = "";
        }
    }

    // Initial check
    toggleFields();

    // Event Listeners
    requireYes.addEventListener("change", toggleFields);
    requireNo.addEventListener("change", toggleFields);
});
</script>

</body>
</html>