<?php
session_start();

// 1) Ensure only students can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
  header("Location: student_letter.php");
  exit();
}

// 2) Grab & then clear any flash messages
$error   = $_SESSION['error']   ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// 3) Connect to database
$conn = new mysqli("localhost", "root", "", "fsdk");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// 4) Fetch the student’s display name
$email = $_SESSION['email'];
$student_name = "Student";  // fallback
$stmt = $conn->prepare("SELECT name FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($name);
if ($stmt->fetch()) {
  $student_name = $name;
  $_SESSION['student_name'] = $student_name;
}
$stmt->close();

// 5) Close the connection
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
    z-index: 9999; /* Ensure it stays above other elements */
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
    height: calc(100vh - 90px);
    position: fixed;
    top: 90px;
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
    flex-grow: 1;
    padding: 30px 20px 20px;
    margin-left: 0; /* ✨ REMOVE this 250px margin */
    display: flex; 
    flex-direction: column;
    align-items: center; /* ✨ Center all children */
    justify-content: start;
    width: 100%;
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
  margin-top: 100px; /* push content below the fixed navbar */
  }

  /* Header Section */
  header {
  background-color: #20b2aa;
  color: #ffffff;
  padding: 16px 30px;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  margin-bottom: 20px;
  width: 100%;
  text-align: center;
  justify-content: center;
}

header h2 {
  font-size: 24px;
  margin: 0;
  color: #ffffff;
  font-weight: bold;
}

  .text-teal {
  color: #20b2aa !important;
}

.letter-header {
  background-color: #20b2aa;
  color: #ffffff;
  padding: 25px 30px;
  border-radius: 15px;
  box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
  width: 100%;
  max-width: 1000px; /* ✅ limit maximum size */
  margin: 0 auto; /* ✅ center it */
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-sizing: border-box;
}

@media (max-width: 768px) {
  .letter-header {
    flex-direction: column;
    gap: 15px;
    padding: 20px;
    text-align: center;
  }
}

.letter-header h2 {
  font-size: 24px;
  margin: 0;
  font-weight: bold;
  color: #ffffff;
}

.letter-box {
  background-color: #20b2aa;
  color: #ffffff;
  padding: 16px 24px;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  width: 100%;
  max-width: 700px;
  margin: 0 auto 20px auto; /* Center horizontally */
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.letter-box h2 {
  font-size: 24px;
  font-weight: bold;
  margin: 0;
  color: #fff;
}

@media (max-width: 768px) {
  .student-name,
  .logout-btn {
    font-size: 14px;
    padding: 6px 14px;
  }

  .navbar p {
    font-size: 20px !important;
  }

  .navbar p + p {
    font-size: 13px !important;
  }
}

.letter-list {
  width: 100%;
  max-width: 850px;
  padding: 0 15px; /* Add padding on mobile */
}

@media (max-width: 768px) {
  .letter-list {
    max-width: 100%;
    padding: 0 10px;
  }
  
  .letter-item {
    flex-direction: column;
    align-items: flex-start !important;
  }

  .letter-item button {
    margin-top: 10px;
    align-self: flex-end;
  }
}

/* ───────────────────────────────────────────
   Change‑Password Popup (exact copy)
───────────────────────────────────────────── */

/* make the parent positioned for the popup */
.position-relative { position: relative; }

.popup-form {
  position: absolute;
  top: 85%; /* Instead of calc(100% + 8px) */
  right: 10%;
  width: 270px;
  background: #ffffff;
  border: 1px solid #e2e2e2;
  border-radius: 0.75rem;
  box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  padding: 1rem;
  z-index: 1100;
  animation: fadeInDrop 200ms ease-out;
}

/* arrow stays the same */
.popup-form::before {
  content: "";
  position: absolute;
  top: -8px;
  right: 30px; /* move arrow slightly left */
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
  color: #004080;
  display: flex;
  align-items: center;
  justify-content: center;
}
.popup-form h6 i {
  margin-right: 0.5rem;
  color: #20b2aa;
}

/* form layout */
.popup-form form {
  display: flex;
  flex-direction: column;
  align-items: center;
  max-width: 260px;
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
  margin-left: 80px;
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
   <nav class="navbar d-flex flex-nowrap justify-content-between align-items-center" style="background: linear-gradient(145deg,#33d1c9,#1da2a0); padding:5px 20px; position:fixed; top:0; width:100%; z-index:999;">
  <!-- Left side: Menu + Logo + Text -->
  <div class="d-flex align-items-center gap-2">
    <button id="sidebarToggle" class="menu-toggle btn btn-link text-white p-0"><i class="fas fa-bars fa-lg"></i></button>
    <img src="image/logoumk.png" alt="UMK Logo" style="width:70px;height:80px;">
    <div class="d-flex flex-column justify-content-center">
      <p style="margin:0;font-size:22px;font-weight:bold;color:#fff;text-transform:uppercase;">UNIVERSITI MALAYSIA KELANTAN</p>
      <p style="margin:0;font-size:14px;color:#fff;opacity:.8;text-transform:capitalize;">FACULTY OF DATA SCIENCE & COMPUTING</p>
    </div>
  </div>

  <!-- Right side: Student name + Logout -->
  <div class="d-flex align-items-center gap-2 flex-shrink-0">
<!-- Student Name -->
<a href="#" id="userName" class="student-name position-relative">
  <i class="fas fa-user"></i> <?= htmlspecialchars($student_name) ?>
</a>

<!-- Change Password Popup (following your given CSS) -->
<div id="changePasswordPopup" class="popup-form d-none">
  <h6><i class="fas fa-key"></i> Change Password</h6>
  <form id="changePasswordForm" action="change_password.php" method="POST">

    <!-- Current Password -->
    <label class="form-label">Current</label>
    <div class="input-group">
      <span class="input-group-text"><i class="fas fa-lock"></i></span>
      <input type="password" name="current_password" class="form-control" required>
      <button type="button" class="btn toggle-pass"><i class="fas fa-eye"></i></button>
    </div>

    <!-- New Password -->
    <label class="form-label">New</label>
    <div class="input-group">
      <span class="input-group-text"><i class="fas fa-lock-open"></i></span>
      <input type="password" name="new_password" class="form-control" required>
      <button type="button" class="btn toggle-pass"><i class="fas fa-eye"></i></button>
    </div>

    <!-- Confirm Password -->
    <label class="form-label">Confirm</label>
    <div class="input-group">
      <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
      <input type="password" name="confirm_password" class="form-control" required>
      <button type="button" class="btn toggle-pass"><i class="fas fa-eye"></i></button>
    </div>

    <!-- Buttons -->
    <div class="d-flex justify-content-end gap-2">
      <button type="button" id="cancelPopup" class="btn btn-light btn-sm">Cancel</button>
      <button type="submit" class="btn btn-primary btn-sm">Save</button>
    </div>

  </form>
</div>


    <a href="logout.php" class="logout-btn">
      <i class="fas fa-sign-out-alt"></i> Log Out
    </a>
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
    <a href="student_before_LI.php" class="collapse-item"><i class="fas fa-angle-right"></i>Implementation IT</a>
    <a href="student_letter.php" class="collapse-item"><i class="fas fa-angle-right"></i> Letter</a>
  </div>
</div>

  <!-- Main Content -->
  <div class="main-content">
  <div class="letter-header">
    <h2 class="m-0">LETTER</h2>   
  </div>

<!-- Letters List  -->
<div class="letter-list mt-4">
<?php
// Connect again to database
$conn = new mysqli("localhost", "root", "", "fsdk");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch letters
$sql = "SELECT * FROM letters ORDER BY id ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $counter = 1;
    while($row = $result->fetch_assoc()) {
        echo '<div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-white rounded shadow-sm" style="border-left: 6px solid #20b2aa;">';
        echo '<div>';
        echo '<h6 class="fw-bold text-teal mb-1">';
        echo $counter . '. <a href="' . htmlspecialchars($row['file_path']) . '" download style="text-decoration: none; color: #20b2aa;">' . htmlspecialchars($row['letter_name']) . '</a>';
        echo '</h6>';
        echo '<small class="text-muted">' . htmlspecialchars($row['description']) . '</small>';
        echo '</div>';
        echo '</div>';
        $counter++;
    }
} else {
    echo "<p>No letters found.</p>";
}
$conn->close();
?>
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
const toggleIcon = sidebarToggle.querySelector("i");

sidebarToggle.addEventListener("click", () => {
    sidebar.classList.toggle("active");

    if (sidebar.classList.contains("active")) {
        toggleIcon.classList.remove("fa-bars");
        toggleIcon.classList.add("fa-times");
    } else {
        toggleIcon.classList.remove("fa-times");
        toggleIcon.classList.add("fa-bars");
    }
});

// Toggle popup open/close (already existing)
const userNameBtn = document.getElementById("userName");
const passwordPopup = document.getElementById("changePasswordPopup");
const cancelPopup = document.getElementById("cancelPopup");

userNameBtn.addEventListener("click", (e) => {
  e.preventDefault();
  passwordPopup.classList.toggle("d-none");
});

cancelPopup.addEventListener("click", () => {
  passwordPopup.classList.add("d-none");
});

// Toggle Eye Icon (already existing)
document.querySelectorAll('.toggle-pass').forEach(button => {
  button.addEventListener('click', function() {
    const input = this.previousElementSibling;
    if (input.type === "password") {
      input.type = "text";
      this.querySelector('i').classList.remove('fa-eye');
      this.querySelector('i').classList.add('fa-eye-slash');
    } else {
      input.type = "password";
      this.querySelector('i').classList.remove('fa-eye-slash');
      this.querySelector('i').classList.add('fa-eye');
    }
  });
});

// 🚀 Handle form submission via AJAX (NO PAGE RELOAD)
document.getElementById("changePasswordForm").addEventListener("submit", function(e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch("change_password.php", {
    method: "POST",
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === "success") {
      Swal.fire({
        icon: 'success',
        title: 'Password Changed',
        text: data.message,
        showConfirmButton: false,
        timer: 2000
      });
      // Close the popup after success
      passwordPopup.classList.add("d-none");
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
  });
});
</script>