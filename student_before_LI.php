<?php
// Start session
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: student_before_LI.php"); // Redirect if not a student
    exit();
}

// Database connection
include("db_connect.php");

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get student email from session
$email = $_SESSION['email'];
$student_name = "Student"; // Default name
$status = "Haven't got any LI yet"; // Default status

// Fetch student name & status from the database
$stmt = $conn->prepare("SELECT name, status FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($name, $db_status);
if ($stmt->fetch()) {
    $student_name = $name;
    $status = $db_status;
    // Store the name in the session for later use:
    $_SESSION['student_name'] = $name;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student | Before LI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQ5BtjmQVKynWj8fPqKflEUVb+6ugvcgO/nr36M9pqE8s6rHuX4r3xIM+" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Add Bootstrap JavaScript -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="student.js"></script>

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

.form-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    border: 1px solid #ddd;
    margin-bottom: 10px;
    border-radius: 5px;
    background: #f9f9f9;
}

.dynamic-btn {
    display: inline-block; /* Ensure it's visible */
    padding: 8px 12px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-left: auto; /* Align to right */
}

.dynamic-btn:hover {
    background: #0056b3;
}

/* Navbar menu */
.menu {
    background: none !important; /* Remove background */
    padding: 0;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 15px; /* Spacing between items */
}

/* Student Name Styling */
.student-name {
    background: white;
    color: #004080;
    padding: 8px 20px;
    border-radius: 25px;
    font-weight: bold;
    display: flex;
    align-items: center;
    font-size: 16px;
    text-decoration: none;
    transition: background 0.3s ease, transform 0.2s ease;
    cursor: pointer;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1); /* Soft shadow */
    border: 2px solid #004080;
}

.student-name i {
    margin-right: 8px;
    color: #004080;
}

/* Hover Effect */
.student-name:hover {
    background: #004080;
    color: white;
    transform: scale(1.05); /* Slight zoom effect */
}

/* Click Effect */
.student-name:active {
    transform: scale(0.95); /* Slight shrink effect */
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

/* Menu Toggle Button */
.menu-toggle {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    margin-right: 10px;
}

/* === STUDENT SIDEBAR (Improved Look) === */
.sidebar {
    background: linear-gradient(180deg, #20b2aa, #1da2a0);
    color: #ffffff;
    width: 250px;
    height: calc(100vh - 50px);
    position: fixed;
    top: 85px;
    left: 0;
    display: flex;
    flex-direction: column;
    padding-top: 10px;
    box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    overflow-y: auto;
}

/* Show sidebar when active */
.sidebar.active {
    transform: translateX(0);
}

/* Section Titles */
.sidebar .section-title {
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 12px 20px 4px;
    margin: 0;
    color: #cce7e6;
    font-weight: 600;
    opacity: 0.85;
    border-bottom: 1px solid rgba(255,255,255,0.15);
}

/* Sidebar Links */
.sidebar a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #ffffff;
    font-size: 15px;
    text-decoration: none;
    transition: background 0.3s ease, padding-left 0.2s ease;
    border-left: 4px solid transparent;
}

.sidebar a i {
    margin-right: 12px;
    font-size: 17px;
}

/* Hover and Active Effect */
.sidebar a:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-left: 4px solid #ffffff;
    padding-left: 24px;
}

.sidebar a.active {
    background-color: rgba(255, 255, 255, 0.15);
    border-left: 4px solid #ffffff;
    font-weight: bold;
}

/* Collapsible Inner Items */
.sidebar .collapse {
    margin-left: 10px;
    padding-left: 10px; 
    transition: all 0.3s;
}

.sidebar .collapse-item {
    font-size: 14px;
    padding: 8px 5px;
    color: #e0f7f5;
    display: block;
    text-decoration: none;
    transition: all 0.3s ease;

}

.sidebar .collapse-item:hover {
    color: #ffffff;
    text-decoration: none;
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

button, .dynamic-btn, .upload-btn {
    text-decoration: none; /* Removes underline */
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

.my-custom-button {
  background-color: #20b2aa !important;  /* Light Sea Green */
  border: 1px solid #20b2aa !important;
  color: #fff !important;               /* Ensure the text is visible */
}

/* Optional: Change hover color */
.my-custom-button:hover {
  background-color: #1a8d8a !important; 
  border-color: #1a8d8a !important;
}

  /* ───────────────────────────────────────────
    Change‑Password Popup (exact copy)
  ───────────────────────────────────────────── */

  /* make the parent positioned for the popup */
  .position-relative { position: relative; }

  /* the popup card */
  .popup-form {
    position: absolute;
    top: calc(100% + 8px);       /* just below the trigger */
    right: 0;                    /* align to the right edge */
    width: 270px;                /* exact width */
    background: #ffffff;
    border: 1px solid #e2e2e2;
    border-radius: 0.75rem;      /* same rounding */
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    padding: 1rem;
    z-index: 1100;
    animation: fadeInDrop 200ms ease-out;
  }

  /* the little arrow on top */
  .popup-form::before {
    content: "";
    position: absolute;
    top: -8px;
    right: 24px;  /* same offset */
    border-left: 8px solid transparent;
    border-right:8px solid transparent;
    border-bottom:8px solid #ffffff;
  }

  /* fade+drop animation */
  @keyframes fadeInDrop {
    from {
      opacity: 0;
      transform: translateY(-10px) scale(0.95);
    }
    to {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }

  /* ensure hidden by default */
  .d-none { display: none !important; }

  /* Title + icon */
  .popup-form h6 {
    margin: 0 0 0.75rem;
    font-size: 1rem;
    font-weight: 600;
    color: #004080;               /* dark blue text */
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .popup-form h6 i {
    margin-right: 0.5rem;
    color: #20b2aa;               /* green key icon */
  }

  /* form layout */
  .popup-form form {
    display: flex;
    flex-direction: column;
    align-items: center;
    max-width: 260px;             /* just inside the 270px container */
    margin: 0 auto;
  }

  /* each input group */
  .popup-form .input-group {
    margin-bottom: 0.75rem;
    width: 100%;
  }
  .popup-form .input-group .form-control {
    border: 1px solid #d1d1d1;
    border-right: none;
    border-radius: 0.5rem 0 0 0.5rem;
    box-shadow: none;
  }
  .popup-form .input-group-text,
  .popup-form .toggle-pass {
    background: transparent;
    border: none;
    color: #20b2aa;
  }
  .popup-form .toggle-pass i {
    color: #666;
  }

  /* floating, centered labels */
  .popup-form .form-label {
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
    width: 100%;
    margin-left: 60px;
    color:rgb(0, 0, 0);
  }

  /* buttons */
  .popup-form .btn-light {
    border: 1px solid #d1d1d1;
    border-radius: 0.5rem;
    padding: 0.25rem 0.75rem;
  }
  .popup-form .btn-primary {
    background: linear-gradient(135deg, #20b2aa, #1da2a0);
    border: none;
    border-radius: 0.5rem;
    padding: 0.25rem 1rem;
  }

  .navbar-right {
  gap: 10px;
  min-width: 0;
}

@media (max-width: 768px) {
  .navbar-right {
    flex-direction: column;
    align-items: flex-end;
    width: auto;
    margin-top: 10px;
  }

  .student-name,
  .logout-btn {
    width: auto;
    justify-content: center;
    text-align: center;
    margin-left: 60px;
  }
}

@media (max-width: 768px) {
  .sidebar {
    width: 220px;
    height: calc(100vh - 90px); /* minus navbar height */
    top: 111px;                  /* start below navbar */
    left: -220px;                /* hide sidebar initially */
    z-index: 1200;
    background: linear-gradient(180deg, #20b2aa, #1da2a0);
    transition: left 0.3s ease;
    box-shadow: 3px 0 10px rgba(0, 0, 0, 0.2);
  }

  .sidebar.active {
    left: 0; /* show when active */
  }

  .main-content {
    width: 100%;
    margin-left: 0;
  }

  /* Navbar fix */
  .navbar .container-fluid {
    flex-wrap: nowrap;
  }

  /* Sidebar toggle button fix */
  .menu-toggle {
    margin-right: 10px;
  }
}

@media (max-width: 768px) {
  .main-content {
    margin-top: 120px; /* 🔥 move more down when minimize */
  }
}

@media (max-width: 768px) {
  .status-btn {
    border-radius: 999px;
    min-width: 140px;      /* 🔥 smaller width */
    padding: 8px 15px;      /* 🔥 smaller padding */
    font-size: 14px;        /* 🔥 smaller text */
    text-align: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    white-space: normal;   /* allow line break */
    word-break: break-word; /* break if too long */
  }

  .status-btn:hover {
    transform: scale(1.03);
  }
}
</style>

<body>
    <div class="container">
    <nav class="navbar" style="background: linear-gradient(145deg, #33d1c9, #1da2a0); padding:5px 20px; position:fixed; top:0; width:100%; z-index:999;">
  <div class="container-fluid d-flex align-items-center justify-content-between flex-nowrap">

    <!-- Left: Sidebar Toggle + Logo -->
    <div class="d-flex align-items-center flex-nowrap" style="gap:10px; min-width: 0;">
      <button id="sidebarToggle" class="menu-toggle btn btn-link text-white p-0 me-2">
        <i class="fas fa-bars fa-lg"></i>
      </button>
      <img src="image/iams.png" alt="UMK Logo" style="width:75px;height:80px;flex-shrink:0;">
      <div style="flex-shrink:1;">
        <p style="margin:0;font-size:22px;font-weight:bold;color:#fff;text-transform:uppercase;white-space:nowrap;">Industrial Attachment Management System</p>
        <p style="margin:0;font-size:15px;opacity:.8;color:#fff;text-transform:capitalize;white-space:nowrap;">FACULTY OF DATA SCIENCE & COMPUTING</p>
      </div>
    </div>

    <!-- Right: Student Name + Logout -->
    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end navbar-right">

      <!-- Student Name -->
      <div class="position-relative">
        <a href="#" id="userName" class="student-name" style="white-space:nowrap;">
          <i class="fas fa-user"></i> <?= htmlspecialchars($student_name) ?>
        </a>

        <!-- Change Password Popup -->
        <div id="userPopup" class="popup-form d-none">
  <h6><i class="fas fa-key"></i> Change Password</h6>
  
  <form id="changePasswordForm" action="change_password.php" method="POST">
    <!-- Current Password -->
    <label for="currentPassword" class="form-label">Current</label>
    <div class="input-group">
      <span class="input-group-text"><i class="fas fa-lock"></i></span>
      <input type="password" name="current_password" class="form-control" required>
      <button type="button" class="btn toggle-pass"><i class="fas fa-eye"></i></button>
    </div>

    <!-- New Password -->
    <label for="newPassword" class="form-label">New</label>
    <div class="input-group">
      <span class="input-group-text"><i class="fas fa-unlock"></i></span>
      <input type="password" name="new_password" class="form-control" required>
      <button type="button" class="btn toggle-pass"><i class="fas fa-eye"></i></button>
    </div>

    <!-- Confirm Password -->
    <label for="confirmPassword" class="form-label">Confirm</label>
    <div class="input-group">
      <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
      <input type="password" name="confirm_password" class="form-control" required>
      <button type="button" class="btn toggle-pass"><i class="fas fa-eye"></i></button>
    </div>

    <!-- Buttons -->
    <div class="d-flex justify-content-between mt-3">
      <button type="button" id="popupCancel" class="btn btn-light">Cancel</button>
      <button type="submit" class="btn btn-primary">Save</button>
    </div>
  </form>
</div>

      </div>

      <!-- Logout Button -->
      <a href="logout.php" class="logout-btn" style="white-space:nowrap;">
        <i class="fas fa-sign-out-alt"></i> Log Out
      </a>

    </div>

  </div>
</nav>

<!-- Sidebar -->
<div class="sidebar">
    <div class="section-title">Main</div>
    <a href="student_dashboard.php"><i class="fas fa-home"></i> Home</a>

    <div class="section-title">Student Area</div>
    <a href="#" data-bs-toggle="collapse" data-bs-target="#studentSubmenu" aria-expanded="true" aria-controls="studentSubmenu">
        <i class="fas fa-user-graduate"></i> Student
    </a>
    <div class="collapse show" id="studentSubmenu">
    <a href="student_before_LI.php" class="collapse-item active"><i class="fas fa-angle-right"></i> Implementation IT</a>
    <a href="student_letter.php" class="collapse-item"><i class="fas fa-angle-right"></i> Letter</a>
  </div>
  <a href="usermanual/User_Manual_Student.pdf" download>
    <i class="fas fa-book"></i> User Manual
  </a>
</div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h2>IMPLEMENTATION INDUSTRIAL TRAINING</h2>
            </header>

           <!-- Section 1: Perlaksanaan Sebelum LI -->
           <section class="student-table">
            <h3 style="align-items: center;">
                Application Status
            </h3>
            <div class="container text-center">
                <p style="text-align: left; margin-bottom: 10px;">Status Undergoing Industrial Training:</p>
                <div class="status-btn-container">
                    <button class="status-btn <?php echo ($status === "Haven't got any LI yet") ? 'green' : 'red'; ?>" data-active-text="Haven't got any LI yet">
                        Haven't got any LI yet
                    </button>
                    <button class="status-btn <?php echo ($status === 'At least get 1 LI') ? 'green' : 'red'; ?>" data-active-text="At least get 1 LI">
                        At least get 1 LI
                    </button>
                    <button class="status-btn <?php echo ($status === 'Already confirmed / decided') ? 'green' : 'red'; ?>" data-active-text="Already confirmed / decided">
                        Already confirmed / decided
                    </button>
                </div>
            </div>
        </section>

        <!-- Section 2: Semasa LI -->
        <section class="student-table">
            <h3>Forms</h3>
            <div id="formsList">
                <?php include 'fetch_forms.php'; ?>
            </div>
        </section>
    </div>

                    <!-- ✅ Corrected Upload Modal -->
                    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="uploadModalLabel">Upload Your File</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                <form id="uploadForm" enctype="multipart/form-data" method="POST">
                    <!-- We’ll populate these inputs dynamically via JavaScript -->
                    <input type="hidden" id="formId" name="form_id">
                    <div class="mb-3">
                        <label for="fileUpload" class="form-label">Choose File:</label>
                        <input type="file" class="form-control" id="fileUpload" name="file" required>
                    </div>
                    <!-- Example button from fetch_forms.php or similar -->
                    <button
                    class="dynamic-btn upload-btn my-custom-button"
                    data-bs-toggle="modal"
                    data-bs-target="#uploadModal"
                    data-id="<?php echo $row['id']; ?>"
                    data-form-name="<?php echo htmlspecialchars($row['form_name']); ?>"
                    >
                    Upload
                    </button>
                </form>
            </div>
        </div>
    </div>
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
    const currentPage = window.location.pathname.split("/").pop();
    document.querySelectorAll(".sidebar a, .collapse-item").forEach(link => {
        if (link.getAttribute("href") === currentPage) {
            link.classList.add("active");
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

// Handle Status Button Clicks
    document.addEventListener("DOMContentLoaded", function () {
        const statusButtons = document.querySelectorAll(".status-btn");

        statusButtons.forEach(button => {
            button.addEventListener("click", function () {
                const newStatus = this.getAttribute("data-active-text"); // Get selected status
                const studentEmail = "<?php echo $_SESSION['email']; ?>"; // Get logged-in student email

                if (!studentEmail) {
                    alert("Error: Student email not found in session.");
                    return;
                }

                // Send AJAX request to update the status in the database
                fetch("update_status.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `email=${encodeURIComponent(studentEmail)}&status=${encodeURIComponent(newStatus)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        alert("Status updated successfully!");

                        // Reset all buttons to red
                        statusButtons.forEach(btn => btn.classList.remove("green", "red"));
                        statusButtons.forEach(btn => btn.classList.add("red"));

                        // Make the clicked button green
                        this.classList.add("green");
                    } else {
                        alert("Error updating status: " + data.message);
                    }
                })
                .catch(error => console.error("Error:", error));
            });
        });
    });

// Function to fetch and display forms dynamically
function loadForms() {
    fetch("fetch_forms.php")
        .then(response => response.json())
        .then(data => {
            const formsList = document.querySelector("#formsList");
            formsList.innerHTML = ""; // Clear previous list

            data.forEach(form => {
                const listItem = document.createElement("li");
                listItem.innerHTML = `<strong>${form.form_name}</strong> - ${form.due_date}`;
                formsList.appendChild(listItem);
            });
        })
        .catch(error => console.error("Error fetching forms:", error));
}

document.addEventListener("DOMContentLoaded", function() {
  const uploadModal = document.getElementById("uploadModal");

  // Populate hidden inputs when the modal is shown
  uploadModal.addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget;
    const formId = button.getAttribute("data-id");
    const formName = button.getAttribute("data-form-name");

    document.getElementById("formId").value = formId;
    document.getElementById("formNameDisplay").textContent = formName;
  });

  // Handle the actual file upload
  const uploadForm = document.getElementById("uploadForm");
  uploadForm.addEventListener("submit", function(e) {
    e.preventDefault(); // stop normal form submission

    // Build FormData
    const formData = new FormData(uploadForm);

    // Send via fetch to upload_file.php
    fetch("upload_file.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if (data.status === "success") {
        // Optionally close the modal
        // Reload the page or just update the UI
        location.reload();
      }
    })
    .catch(err => console.error("Error uploading:", err));
  });
});

   // 1) toggle popup
const userName  = document.getElementById('userName');
const userPopup = document.getElementById('userPopup');
const cancelBtn = document.getElementById('popupCancel');

userName.addEventListener('click', e => {
  e.preventDefault();
  userPopup.classList.toggle('d-none');
});
cancelBtn.addEventListener('click', () => userPopup.classList.add('d-none'));
document.addEventListener('click', e => {
  if (!userPopup.contains(e.target) && !userName.contains(e.target)) {
    userPopup.classList.add('d-none');
  }
});

// 2) eye‑toggle password visibility
document.querySelectorAll('.toggle-pass').forEach(btn => {
  btn.addEventListener('click', () => {
    const inp = btn.closest('.input-group').querySelector('input');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    btn.querySelector('i').classList.toggle('fa-eye-slash');
  });
});

// 3) AJAX‑submit change_password.php
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const btn = this.querySelector('button[type="submit"]');
  btn.disabled = true;

  fetch("change_password.php", {
    method: "POST",
    body: new FormData(this)
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Server error');
    }
    return response.json();
  })
  .then(data => {
    if (data.status === "success") {
      Swal.fire({
        icon: 'success',
        title: 'Password Changed',
        text: data.message,
        showConfirmButton: false,
        timer: 2000
      });
      userPopup.classList.add("d-none");   // ✅ use correct ID (userPopup, not passwordPopup)
      this.reset();
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: data.message,
        showConfirmButton: true
      });
    }
})

  .catch(error => {
    console.error('Error:', error);
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: 'Something went wrong.',
      showConfirmButton: true
    });
  })
  .finally(() => btn.disabled = false);
});

</script>

</body>
</html>