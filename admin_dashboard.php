<?php
session_start();
include("db_connect.php");

if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get total students
$query_total_students = "SELECT COUNT(*) as total FROM students";
$result_total = $conn->query($query_total_students);
if ($result_total && $result_total->num_rows > 0) {
    $row_total      = $result_total->fetch_assoc();
    $total_students = $row_total['total'];
} else {
    $total_students = 0;
}

// Fetch forms from before_li
$query_forms  = "SELECT id, form_name, due_date FROM before_li ORDER BY id DESC";
$result_forms = $conn->query($query_forms);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $message = $_POST['message'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("INSERT INTO announcements (title, message, date, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $title, $message, $date);    

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?success=1");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admin | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQ5BtjmQVKynWj8fPqKflEUVb+6ugvcgO/nr36M9pqE8s6rHuX4r3xIM+" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Add Bootstrap JavaScript -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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

/* Top Navbar */
.navbar {
    background: linear-gradient(145deg, #33d1c9, #1da2a0);
    color: #ffffff;
    width: 100%;
    padding: 5px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    transition: transform 0.3s ease;
}

.navbar .logo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.navbar .logo-img {
    width: 70px;
    height: 80px;
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

.navbar .menu {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    align-items: center;
}

/* Before:  .navbar .menu li {...} */
.navbar .menu li {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    /* Remove or comment these out if you don’t want <li> to look like a button */
    /* background: #e0f7f5; */
    /* border-radius: 5px; */
    /* box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); */
    /* padding: 10px 20px; */
    /* Also reduce or remove large margins if not needed: */
    margin-left: 0;
    margin-right: 0;

    color: #333333;
    font-size: 14px;
    font-weight: bold;
    text-transform: capitalize;
    cursor: pointer;
    transition: color 0.3s ease;
}

.navbar .menu li:hover {
    /* Remove the background change on hover if you like */
    background: transparent;
    color: #20b2aa;
}

.form-names {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 20px;
}

.form-names li {
    display: flex;
    align-items: center;
    justify-content: space-between; /* Pushes icon to the right */
    padding: 12px;
    background: #f0f9ff;
    border: 1px solid #d1e9ff;
    border-radius: 8px;
    transition: all 0.3s ease;
    
}

.form-names li:hover {
    background: #d1e9ff;
}

.form-text {
    font-size: 16px;
    font-weight: bold;
    color: #333;
}

.no-forms {
    font-size: 14px;
    color: red;
}

.form-link {
    font-size: 16px;
    font-weight: bold;
    color: #17a2b8;
    text-decoration: none;
    transition: transform 0.3s ease, font-size 0.3s ease;
}

.form-link:hover {
    font-size: 18px;
    transform: scale(1.1);
    color: #138496;
}

/* Form List Container */
.list-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 20px;
}

/* Individual Form Item */
.list-group-item {
    background: linear-gradient(135deg,rgb(255, 255, 255),rgb(255, 255, 255)); /* Soft gradient */
    border-radius: 12px;
    padding: 15px 20px;
    border: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    position: relative;
    cursor: pointer;
    overflow: hidden;
}

/* Hover Effect */
.list-group-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    background: linear-gradient(135deg, #d1e9ff, #e3f2fd);
}

/* Form Name Text */
.form-link {
    font-size: 18px;
    font-weight: bold;
    color: #33d1c9;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: transform 0.3s ease, color 0.3s ease;
}

/* Hover Effect for Text */
.form-link:hover {
    color:rgb(47, 186, 179);
    transform: scale(1.02);
}

/* Fancy Left Icon */
.list-group-item i {
    font-size: 22px;
    color: #33d1c9;
    background: white;
    border-radius: 50%;
    padding: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Animated Right Arrow */
.list-group-item::after {
    content: '\f054'; /* Font Awesome right arrow */
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    font-size: 16px;
    color: #33d1c9;
    transition: transform 0.3s ease;
}

.list-group-item:hover::after {
    transform: translateX(8px);
}

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
    overflow-y: auto;
    margin-top: 90px;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
}

.sidebar.active {
    transform: translateX(0);
}

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
    margin-left: 0;
    transition: margin-left 0.3s ease;
    padding: 20px;
    width: calc(100% - 250px);
    background-color: #e0f7f5;
    min-height: 100%;
    overflow-y: auto;
    margin-top: 95px;
}

.sidebar.active ~ .main-content {
    margin-left: 250px;
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

.application-status {
    text-align: center;
    margin-bottom: 20px;
}

.application-status h2 {
    font-size: 28px;
    color: #20b2aa;
    margin: 0;
    position: relative;
    display: inline-block;
}

.application-status h2::after {
    content: "";
    display: block;
    width: 100%;
    height: 2px;
    background-color: #20b2aa;
    margin-top: 5px;
}

.status-squares {
    display: flex;
    justify-content: center;
    gap: 50px;
    margin-top: 40px;
    perspective: 1000px;
}

.square {
    width: 200px;
    height: 200px;
    color: #ffffff;
    font-size: 36px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 20px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
    transition: transform 0.9s ease, box-shadow 0.4s ease;
    transform-style: preserve-3d;
    cursor: pointer;
}

/* First Square - Not Got Any LI Yet */
#status1 {
    background: linear-gradient(145deg, #ff4c4c, #d63030); /* Red shades */
}

/* Second Square - At Least Got 1 LI */
#status2 {
    background: linear-gradient(145deg, #ffb347, #ff8c00); /* Orange shades */
}

/* Third Square - Already Confirmed / Decided */
#status3 {
    background: linear-gradient(145deg, #4caf50, #2e7d32); /* Green shades */
}

.square:hover {
    transform: translateY(-10px) rotateX(15deg) rotateY(15deg);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
}

.square::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 300%;
    height: 300%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.2), transparent);
    transform: translate(-50%, -50%) scale(0);
    transition: transform 0.4s ease;
    z-index: -1;
}

.square:hover::before {
    transform: translate(-50%, -50%) scale(1);
}

.square::after {
    content: attr(data-back);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(145deg, #1da2a0, #33d1c9);
    color: #ffffff;
    font-size: 20px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 20px;
    backface-visibility: hidden;
    transform: rotateY(180deg);
}

.square span {
    position: absolute;
    backface-visibility: hidden;
}

.watchers {
    margin-left: 10px; /* Adjust the value for the desired spacing */
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

/* Logout Button Styling */
.logout-btn {
    background: #e0f7f5; 
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
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);

}

.logout-btn {
    /* existing styles */
    margin-left: 20px !important; /* nudge to the right */
}

.logout-btn i {
    font-size: 18px;
}

.logout-btn:hover {
    background: #e0f7f5; 
    color: #20b2aa;
    transform: scale(1.05);
}

/* Active (Click) Effect */
.logout-btn:active {
    transform: scale(0.95); /* Button presses slightly */
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

/* Accordion Container */
.form-accordion {
  width: 100%;
  max-width: 100%; /* ensure it can go full width */
}

/* Accordion Items (Cards) */
.form-accordion .accordion-item {
  border: none;             /* remove default border */
  border-radius: 10px;      /* smooth corners */
  overflow: hidden;         /* so corners remain rounded */
  background-color: #fff;   /* white card background */
}

/* Header Button */
.form-accordion .accordion-button {
  border: none;
  border-radius: 10px;             /* match parent radius */
  background-color: #fff;          /* white header */
  color: #333;                     /* dark text */
  padding: 1rem 1.5rem;
  transition: background 0.3s ease, transform 0.2s ease;
  box-shadow: none;
}

.form-accordion .accordion-button i {
  font-size: 1.25rem;
}

.form-accordion .accordion-button:hover {
  background-color: #f2f2f2;       /* subtle hover */
}

/* Expanded (Active) State */
.form-accordion .accordion-button:not(.collapsed) {
  background-color: #e8f7f6;       /* light teal/green accent when open */
  color: #008b8b;                  /* darker teal text for contrast */
  box-shadow: inset 0 -1px 0 #ddd; /* subtle bottom border */
}

/* Accordion Body */
.form-accordion .accordion-body {
  background-color: #fff;          /* white body */
  padding: 1.25rem 1.5rem;
  color: #444;
  border-top: none;                /* remove top border line */
  border-radius: 0 0 10px 10px;    /* match corners */
}

/* Student List in Body */
.form-accordion .accordion-body ul {
  list-style-type: disc;
  margin-left: 1.5rem;
  padding-left: 0;
}

.form-accordion .accordion-body li {
  margin-bottom: 0.5rem;
  color: #555;
}

/* Badge for Due Date */
.form-accordion .badge {
  font-size: 0.9rem;
  font-weight: 500;
  opacity: 0.9;
}

.status-label {
    margin-top: 8px;
    font-weight: bold;
    text-align: center;
}

.red-label { color: #d63030; }
.orange-label { color: #ff8c00; }
.green-label { color: #2e7d32; }

/* Make Application Status Responsive */
@media (max-width: 992px) {
    .status-squares {
        flex-direction: column;
        align-items: center;
        gap: 30px;
    }

    .square {
        width: 160px;
        height: 160px;
        font-size: 28px;
    }
}

@media (max-width: 576px) {
    .square {
        width: 140px;
        height: 140px;
        font-size: 24px;
    }

    .application-status h2 {
        font-size: 24px;
    }

    .status-label {
        font-size: 14px;
    }
}

/* Default: show all status squares in one row */
.status-squares {
    display: flex;
    justify-content: center;
    gap: 50px;
    margin-top: 40px;
    flex-wrap: nowrap; /* Keep in a row unless screen is small */
}

.status-squares > div {
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* When minimized or small screen (below 768px), stack vertically */
@media (max-width: 768px) {
    .status-squares {
        flex-direction: column;
        align-items: center;
        gap: 30px;
        flex-wrap: wrap;
    }
}

/* Make Forms Section Responsive */
@media (max-width: 768px) {
    .form-accordion .accordion-button {
        font-size: 14px;
        padding: 0.75rem 1rem;
        flex-wrap: wrap; /* Allow text and badges to wrap properly */
    }

    .form-accordion .accordion-button .badge {
        font-size: 0.75rem;
        margin-top: 5px;
        
    }

    .accordion-header i {
        font-size: 16px !important;
    }

    .accordion-item {
        margin: 0 10px; /* slight side margin for spacing */
    }
    
}

/* Modal Styling */
.modal-content.rounded-4 {
    border-radius: 20px;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    background: #ffffff;
}

.modal-header.bg-info {
    background: linear-gradient(145deg, #33d1c9, #1da2a0);
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
    padding: 20px;
    border-bottom: none;
}

.modal-title {
    font-weight: bold;
    font-size: 20px;
    color: white;
}

.modal-body {
    padding: 25px;
}

.modal-body .form-label {
    font-weight: bold;
    color: #20b2aa;
}

.modal-body input,
.modal-body textarea {
    border-radius: 12px;
    border: 1px solid #ccc;
    padding: 10px 15px;
    transition: border-color 0.3s ease;
}

.modal-body input:focus,
.modal-body textarea:focus {
    border-color: #20b2aa;
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(32, 178, 170, 0.25);
}

.modal-footer {
    background-color: #f8f9fa;
    border-bottom-left-radius: 20px;
    border-bottom-right-radius: 20px;
    padding: 15px 25px;
    border-top: none;
}

.modal-footer .btn-success {
    background-color: #20b2aa;
    border: none;
    padding: 8px 20px;
    font-weight: bold;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: background 0.3s ease;
}

.modal-footer .btn-success:hover {
    background-color: #1a8d8a;
}

.modal-footer .btn-secondary {
    border-radius: 12px;
    font-weight: bold;
    padding: 8px 20px;
}

.btn-close-white {
    filter: brightness(0) invert(1);
}

/* Shrink logo and text gracefully */
@media (max-width: 768px) {
    .navbar .logo-img {
        width: 41px;
        height: auto;
    }

    .navbar .logo p {
        font-size: 16px;
    }

    .navbar .logo p.faculty-title {
        font-size: 13px;
    }

    .logout-btn {
        padding: 6px 10px;
        font-size: 15px;
    }

    .navbar {
        justify-content: space-between;
    }

    .navbar .menu {
        justify-content: center;
    }
}
@media (max-width: 768px) {
    /* Adjust the navbar height, if needed */
    .navbar {
        height: 70px;
        padding: 5px 10px; /* update as needed */
    }
    /* Align the sidebar to the reduced navbar height */
    .sidebar {
        margin-top: 70px;
    }
    /* Also adjust main content's margin-top */
    .main-content {
        margin-top: 70px;
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
        <button class="logout-btn" data-bs-toggle="modal" data-bs-target="#announcementModal" style="margin-right: 10px;">
        <i class="fas fa-plus-circle"></i> Add Announcement
        </button>
    </li>
    <li>
        <a href="logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Log Out
        </a>
    </li>
    </ul>
</div>

<!-- Announcement Modal -->
<div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4">
      <form action="" method="POST"> <!-- Moved here -->
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="announcementModalLabel"><i class="fas fa-bullhorn"></i> Add Announcement</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="announcement_title" class="form-label">Title</label>
            <input type="text" class="form-control" id="announcement_title" name="title" required>
          </div>
          <div class="mb-3">
            <label for="announcement_body" class="form-label">Message</label>
            <textarea class="form-control" id="announcement_body" name="message" rows="4" required></textarea>
          </div>
          <div class="mb-3">
            <label for="announcement_date" class="form-label">Date</label>
            <input type="date" class="form-control" id="announcement_date" name="date" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form> <!-- Closed here -->
    </div>
  </div>
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

<!-- Main Content -->
<div class="main-content">

<div class="application-status">
    <h2>Application Status</h2>
</div>

<!-- Status Count Display -->
<div class="status-squares">
    <div style="text-align:center;">
        <div class="square" id="status1">0</div>
        <p style="margin-top:8px; color:#d63030; font-weight:bold;">Haven't got any LI yet</p>
    </div>
    <div style="text-align:center;">
        <div class="square" id="status2">0</div>
        <p style="margin-top:8px; color:#ff8c00; font-weight:bold;">At least get 1 LI</p>
    </div>
    <div style="text-align:center;">
        <div class="square" id="status3">0</div>
        <p style="margin-top:8px; color:#2e7d32; font-weight:bold;">Already confirmed / decided</p>
    </div>
</div>


     <!-- FORMS SECTION -->
     <div class="application-status mt-5">
            <h2>Forms</h2>
            <p style="font-size: 16px; color: #444; margin-top: 10px;">
                List of students who haven't submitted:
            </p>
            <div class="accordion form-accordion" id="formsAccordion" style="margin-top: 30px;">
            <?php 
if ($result_forms && $result_forms->num_rows > 0):
    while ($row = $result_forms->fetch_assoc()):
        $formId   = $row['id'];
        $formName = $row['form_name'];
        $dueDate  = $row['due_date'];

        // Fetch all students
        $studentsQuery = "SELECT name, email FROM students";
        $studentsResult = $conn->query($studentsQuery);

        $students = [];
        while ($student = $studentsResult->fetch_assoc()) {
            $students[$student['email']] = $student['name'];
        }

        // Fetch who already submitted
        if (strpos($formName, 'MAKLUM BALAS INDUSTRI') !== false) {
            // form1, match by email
            $stmtSubmitted = $conn->prepare("SELECT email FROM form WHERE which_form = ?");
        } else {
            // other forms, match by student_email
            $stmtSubmitted = $conn->prepare("SELECT student_email FROM student_uploads WHERE form_name = ?");
        }
        $stmtSubmitted->bind_param("s", $formName);
        $stmtSubmitted->execute();
        $submittedResult = $stmtSubmitted->get_result();

        $submitted = [];
        while ($r = $submittedResult->fetch_assoc()) {
            if (isset($r['email'])) {
                $submitted[] = $r['email'];
            } else if (isset($r['student_email'])) {
                $submitted[] = $r['student_email'];
            }
        }
        $stmtSubmitted->close();

        // Calculate not submitted
        $notSubmittedNames = [];
        foreach ($students as $email => $name) {
            if (!in_array($email, $submitted)) {
                $notSubmittedNames[] = $name;
            }
        }
        $notCount = count($notSubmittedNames);
        $submittedCount = count($students) - $notCount;
?>
    <div class="accordion-item mb-3 border-0 shadow-sm rounded">
        <h2 class="accordion-header" id="heading-<?php echo $formId; ?>">
            <button class="accordion-button collapsed bg-white text-dark fw-bold" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#collapse-<?php echo $formId; ?>" 
                    aria-expanded="false" 
                    aria-controls="collapse-<?php echo $formId; ?>">
                <i class="fas fa-file-alt me-2 text-primary"></i>
                <?php echo htmlspecialchars($formName); ?>
                <?php if (!empty($dueDate)): ?>
                    <span class="badge bg-danger ms-3">
                        Due: <?php echo htmlspecialchars($dueDate); ?>
                    </span>
                <?php endif; ?>
                <span class="badge bg-success ms-3">
                    <?php echo $notCount . " / " . count($students); ?>
                </span>
            </button>
        </h2>
        <div id="collapse-<?php echo $formId; ?>" class="accordion-collapse collapse" 
             aria-labelledby="heading-<?php echo $formId; ?>" 
             data-bs-parent="#formsAccordion">
            <div class="accordion-body">
                <?php if ($notCount > 0): ?>
                    <ul>
                        <?php foreach ($notSubmittedNames as $name): ?>
                            <li><?php echo htmlspecialchars($name); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-success mb-0">All students have submitted this form ✅</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php 
    endwhile;
else:
    echo "<p>No forms available.</p>";
endif;
?>

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

// Load status counts
function loadStatusCounts() {
        fetch("fetch_status_count.php")
            .then(response => response.json())
            .then(data => {
                document.getElementById("status1").textContent = data["Haven't got any LI yet"];
                document.getElementById("status2").textContent = data["At least get 1 LI"];
                document.getElementById("status3").textContent = data["Already confirmed / decided"];
            })
            .catch(error => console.error("Error fetching status counts:", error));
    }

    document.addEventListener("DOMContentLoaded", function() {
        loadStatusCounts();
        setInterval(loadStatusCounts, 5000);
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>
