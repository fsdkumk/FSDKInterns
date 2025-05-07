<?php
session_start();

// 1) Ensure only students can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
  header("Location: student_dashboard.php");
  exit();
}

// 2) Grab & then clear any flash messages
$error   = $_SESSION['error']   ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// 3) Connect to database
include 'db_connect.php';

// 4) Fetch full student info (name + email)
$email = $_SESSION['email'];
$student_name = "Student"; // fallback
$student_email = $email;   // default

$stmt = $conn->prepare("SELECT name, email FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($name, $fetched_email);
if ($stmt->fetch()) {
  $student_name = $name;
  $student_email = $fetched_email;
  $_SESSION['student_name'] = $student_name;
}

$stmt->close();
// Reconnect to database to fetch forms
$conn = new mysqli("localhost", "root", "", "fsdk");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$submitted = [];

// Combine submitted forms from both tables
// 1. From student_uploads
$stmt = $conn->prepare("SELECT form_name FROM student_uploads WHERE student_email = ?");
$stmt->bind_param("s", $student_email);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $submitted[] = $row['form_name'];
}
$stmt->close();

// 2. From form table (for Form 1)
$stmt = $conn->prepare("SELECT which_form FROM form WHERE email = ?");
$stmt->bind_param("s", $student_email);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $submitted[] = $row['which_form'];
}
$stmt->close();

// Now get tasks that are not submitted
$tasks = [];

if (count($submitted) > 0) {
    $placeholders = implode(',', array_fill(0, count($submitted), '?'));
    $types = str_repeat('s', count($submitted));
    $query = "SELECT form_name, due_date FROM before_li WHERE form_name NOT IN ($placeholders) ORDER BY due_date ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$submitted);
} else {
    $stmt = $conn->prepare("SELECT form_name, due_date FROM before_li ORDER BY due_date ASC");
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

// 1. Get total tasks
$total_tasks = 0;
$stmt = $conn->prepare("SELECT COUNT(*) FROM before_li");
$stmt->execute();
$stmt->bind_result($total_tasks);
$stmt->fetch();
$stmt->close();

// 2. Get completed task count
$completed_tasks = 0;

// Count from student_uploads
$stmt = $conn->prepare("SELECT COUNT(DISTINCT form_name) FROM student_uploads WHERE student_email = ?");
$stmt->bind_param("s", $student_email);
$stmt->execute();
$stmt->bind_result($completed_uploads);
$stmt->fetch();
$stmt->close();

// Count from form table
$stmt = $conn->prepare("SELECT COUNT(DISTINCT which_form) FROM form WHERE email = ?");
$stmt->bind_param("s", $student_email);
$stmt->execute();
$stmt->bind_result($completed_forms);
$stmt->fetch();
$stmt->close();

$completed_tasks = $completed_uploads + $completed_forms;

// 3. Calculate progress
$progress_percent = ($total_tasks > 0) ? round(($completed_tasks / $total_tasks) * 100) : 0;

$quote = "Loading...";

// Suppress warning with @ and add fallback
$response = @file_get_contents("https://zenquotes.io/api/today");
if ($response !== false) {
    $data = json_decode($response, true);
    if (isset($data[0]['q']) && isset($data[0]['a'])) {
        $quote = '"' . $data[0]['q'] . '" — ' . $data[0]['a'];
    }
} else {
    $quote = '"Strive for progress, not perfection." — Unknown'; // fallback quote
}

$announcement = null;
$announcement_query = "SELECT title, message, date FROM announcements ORDER BY created_at DESC LIMIT 1";
$result_announcement = $conn->query($announcement_query);
if ($result_announcement && $result_announcement->num_rows > 0) {
    $announcement = $result_announcement->fetch_assoc();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student | Home</title>
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
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: flex-end;
  align-items: center;
  min-width: 0;
}

@media (max-width: 768px) {
  .student-name,
  .logout-btn {
    padding: 6px 14px;
    font-size: 14px;
  }

  .navbar .logo-img {
    width: 50px;
    height: 60px;
  }

  .navbar .logo p {
    font-size: 18px;
  }

  .navbar .logo p.faculty-title {
    font-size: 14px;
  }
}

.hero-image-section {
  position: relative;
  height: 690px;
  background: 
    linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)),
    url('image/student.jpg') center center no-repeat;
  background-size: cover;
  width: 100vw;
  margin: 0;
  padding: 0;
  z-index: 1;
}

.hero-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: #fff;
  z-index: 2;
}

.hero-overlay h1 {
  font-size: 5rem;
  font-family: 'Roboto Slab', serif;
  font-weight: 700;
  margin: 0;
}

.hero-overlay p {
  font-size: 1.5rem;
  margin-top: 10px;
}

.dashboard-section {
  padding: 40px 20px;
  background-color: #f5ffff;
  margin-top: -20px;
}
 
.dashboard-grid .card {
  flex: 1 1 280px; /* Grow/shrink within a minimum width */
  max-width: 300px;
}

.card {
  background: #ffffff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 3px 15px rgba(0,0,0,0.05);
}

.card h4 {
  margin-bottom: 12px;
  font-weight: 700;
  font-size: 1.1rem;
}

.task-list,
.quick-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.task-list li,
.quick-links li {
  margin-bottom: 8px;
  display: flex;
  justify-content: space-between;
  font-size: 0.95rem;
}

.due {
  color: #009999;
  font-weight: 600;
  font-size: 0.85rem;
}

.progress-bar-bg {
  background: #e4e4e4;
  height: 10px;
  border-radius: 5px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: #20b2aa;
  border-radius: 5px;
}

.badge-new {
  background-color: #00cc99;
  color: white;
  font-size: 0.75rem;
  padding: 2px 6px;
  border-radius: 4px;
  margin-right: 5px;
}

blockquote {
  font-style: italic;
  color: #555;
  font-size: 0.95rem;
  margin: 0;
}

.support-link {
  color: #009999;
  font-weight: 600;
  text-decoration: none;
}

.card {
  background: #ffffff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 3px 15px rgba(0,0,0,0.05);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Zoom on hover */
.card:hover {
  transform: scale(1.03);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
}

.card h4 i {
  color: #20b2aa; /* Light sea green (matches your theme) */
  margin-right: 8px;
  font-size: 1.1rem;
  vertical-align: middle;
}

.styled-task-list {
  list-style: disc;
  padding-left: 20px;
  margin: 0;
}

.styled-task-list li {
  margin-bottom: 16px;
  font-size: 0.95rem;
  color: #000;
}

.styled-task-list .due {
  font-size: 0.85rem;
  color: #009999;
  font-weight: 500;
  margin-left: 1rem;
  margin-top: 4px;
}

.announcements-card {
  min-height: 100px; /* or increase this value as needed */
  display: flex;
  flex-direction: column;
  justify-content: flex-start;

}

.quick-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.quick-links li {
  display: flex;
  align-items: center;
  justify-content: flex-start; /* ✅ Left-align everything */
  gap: 8px;
  font-size: 0.95rem;
  color: #00796b;
  margin-bottom: 8px;
}

.quick-links li i {
  color: #00796b;
}

.quick-links a {
  color: #00796b;
  text-decoration: none;
}

.quick-links a:hover {
  text-decoration: underline;
}

.task-entry {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  margin-bottom: 12px;
  padding-left: 0.5rem;
}

.task-title {
  font-weight: 500;
  font-size: 0.95rem;
  color: #000;
}

.due {
  font-size: 0.85rem;
  color: #009999;
  font-weight: 500;
  margin-top: 4px;
}

.scroll-down-btn {
  position: absolute;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 2rem;
  color: white;
  animation: bounce 2s infinite;
  z-index: 3;
  text-decoration: none;
}

.scroll-down-btn:hover {
  color: #1a8d8a;
}

@keyframes bounce {
  0%, 100% {
    transform: translateX(-50%) translateY(0);
  }
  50% {
    transform: translateX(-50%) translateY(10px);
  }
}

.short-card {
  height: 280px; /* adjust this value as needed */
  overflow-y: auto;
}


.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  max-width: 1200px;
  margin: auto;
  align-items: start;
}

/* Assign each card to exact position */
.card-profile      { grid-column: 1; grid-row: 1; }
.card-progress     { grid-column: 1; grid-row: 2; }

.card-links        { grid-column: 2; grid-row: 1; }
.card-motivation   { grid-column: 2; grid-row: 2; }

.card-announcements{ grid-column: 3; grid-row: 1; }
.card-help         { grid-column: 3; grid-row: 2; }

.card-tasks        { grid-column: 4; grid-row: 1 / span 2; }

.card-tasks {
  height: 580px;  /* ✅ Adjust this until it hits the red line exactly */
  overflow-y: auto; /* Enable scroll if content exceeds */
}

@media (max-width: 1200px) {
  .dashboard-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .card-profile      { grid-column: 1; grid-row: 1; }
  .card-progress     { grid-column: 1; grid-row: 2; }
  .card-links        { grid-column: 2; grid-row: 1; }
  .card-motivation   { grid-column: 2; grid-row: 2; }
  .card-announcements{ grid-column: 1; grid-row: 3; }
  .card-help         { grid-column: 1; grid-row: 4; }
  .card-tasks        { grid-column: 2; grid-row: 3 / span 2; }
}

@media (max-width: 768px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
  }

  .card-profile,
  .card-progress,
  .card-links,
  .card-motivation,
  .card-announcements,
  .card-help,
  .card-tasks {
    grid-column: 1;
    grid-row: auto;
  }

  .card-tasks {
    height: auto;
  }
}

</style>

<body>
<div class="container">
    <nav class="navbar" style="background: linear-gradient(145deg, #33d1c9, #1da2a0); padding:5px 20px; position:fixed; top:0; width:100%; z-index:999;">
  <div class="container-fluid d-flex align-items-center justify-content-between flex-nowrap">

    <!-- Left: Sidebar Toggle + Logo -->
    <div class="d-flex align-items-center flex-nowrap gap-2" style="min-width: 0;">
      <button id="sidebarToggle" class="menu-toggle btn btn-link text-white p-0 me-2">
        <i class="fas fa-bars fa-lg"></i>
      </button>
      <img src="image/logoumk.png" alt="UMK Logo" style="width:65px;height:75px;flex-shrink:0;">
      <div style="flex-shrink:1;">
        <p style="margin:0;font-size:22px;font-weight:bold;color:#fff;text-transform:uppercase;white-space:nowrap;">UNIVERSITI MALAYSIA KELANTAN</p>
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

<!-- Hero Section -->
<div class="hero-image-section">
  <div class="hero-overlay">
    <h1>Industrial Training</h1>
    <p>COMPUTER SCIENCE | FSDK</p>
  </div>
<!-- Scroll Button at the Bottom of Dashboard -->
<a href="#scroll-target" class="scroll-down-btn" style="bottom: 30px;">
  <i class="fas fa-chevron-down"></i>
</a>
</div>

<section class="dashboard-section" id="scroll-target">
  <div class="dashboard-grid">

    <!-- My Profile -->
    <div class="card short-card card-profile">
      <h4><i class="fas fa-user"></i> My Profile</h4>
      <p><strong>Name</strong><br> <?= htmlspecialchars($student_name) ?></p>
      <p><strong>Email</strong><br> <?= htmlspecialchars($student_email) ?></p>
      <p><strong>Program</strong><br> Bachelor of Computer Science</p>
    </div>

<!-- Quick Links -->
<div class="card short-card card-links">
  <h4><i class="fas fa-book-open"></i> Quick Links</h4>
  <ul class="quick-links">
    <li><i class="fas fa-angle-right"></i><a href="student_before_LI.php">Implementation IT</a></li>
    <li><i class="fas fa-angle-right"></i><a href="student_letter.php">Letter</a></li>
  </ul>
</div>

<!-- Announcements -->
<div class="card short-card card-announcements">
  <h4><i class="fas fa-bullhorn"></i> Announcements</h4>
  <?php if ($announcement): ?>
    <p>
      <span class="badge-new">NEW</span>
      <?= htmlspecialchars($announcement['title']) ?><br>
      <?= date('l, d F', strtotime($announcement['date'])) ?>.<br>
      <?= htmlspecialchars($announcement['message']) ?>
    </p>
  <?php else: ?>
    <p>No announcements at the moment.</p>
  <?php endif; ?>
</div>

<!-- Upcoming Tasks -->
<div class="card card-tasks">
  <h4><i class="fas fa-tasks"></i> Upcoming Tasks</h4>
  <ul class="styled-task-list">
    <?php if (!empty($tasks)): ?>
      <?php foreach ($tasks as $task): ?>
        <li>
          <div class="task-entry">
            <span class="task-title"><?= htmlspecialchars($task['form_name']) ?></span>
            <span class="due">Due <?= date('d M Y', strtotime($task['due_date'])) ?></span>
          </div>
        </li>
      <?php endforeach; ?>
    <?php else: ?>
      <li>No upcoming tasks available.</li>
    <?php endif; ?>
  </ul>
</div>

<!-- Progress Tracker (⬆ Move directly after My Profile) -->
<div class="card short-card card-progress">
  <h4><i class="fas fa-chart-line"></i> Progress Tracker</h4>
  <div class="progress-bar-bg">
    <div class="progress-bar-fill" style="width: <?= $progress_percent ?>%;"></div>
  </div>
</div>

<!-- Motivation for Today (⬆ Move directly after Quick Links) -->
<div class="card short-card card-motivation"> 
  <h4><i class="fas fa-lightbulb"></i> Motivation for Today</h4>
  <blockquote><?= htmlspecialchars($quote) ?></blockquote>
</div>

<!-- Need Help (⬆ Move directly after Announcements) -->
<div class="card short-card card-help">
  <h4><i class="fas fa-headset"></i> Need Help?</h4>
  <p>If you have any problems, please contact:</p>
  <p><a href="mailto:fsdk@umk.edu.my" class="support-link">fsdk@umk.edu.my</a></p>
  <p><i class="fas fa-phone"></i> +09-771 7179</p>
</div>

  </div>
</section>


<!-- Sidebar -->
<div class="sidebar">
    <div class="section-title">Main</div>
    <a href="student_dashboard.php"><i class="fas fa-home"></i> Home</a>

    <div class="section-title">Student Area</div>
    <a href="#" data-bs-toggle="collapse" data-bs-target="#studentSubmenu" aria-expanded="true" aria-controls="studentSubmenu">
        <i class="fas fa-user-graduate"></i> Student
    </a>
    <div class="collapse show" id="studentSubmenu">
    <a href="student_before_LI.php" class="collapse-item"><i class="fas fa-angle-right"></i> Implementation IT</a>
    <a href="student_letter.php" class="collapse-item"><i class="fas fa-angle-right"></i> Letter</a>
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