<?php
session_start();
if ($_SESSION['role'] != 'lecturer') {
  header("Location: login.php");
  exit();
}

// ---[ STEP 1: DATABASE CONNECTION ]---
include("db_connect.php");

$lecturerName = $_SESSION['name'];

// Use a prepared statement for security
$stmt = $conn->prepare("SELECT id, name, matrix, email, semester FROM students WHERE lecturer = ?");
$stmt->bind_param("s", $lecturerName);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lecturer | Student List</title>

  <!-- Bootstrap & Font Awesome CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <!-- SweetAlert2 for popups -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="student.js"></script>

<style>
/* ----------------------
 General Page Styling
-------------------------*/
body {
  background-color: #e0f7f5;
  margin: 0;
  font-family: Arial, sans-serif;
  display: flex;
  overflow: hidden;
}

/* ----------------------
 Navbar Styling
-------------------------*/
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

.navbar .menu li {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  margin: 0;
  color: #333333;
  font-size: 14px;
  font-weight: bold;
  text-transform: capitalize;
  cursor: pointer;
  transition: color 0.3s ease;
}

.navbar .menu li:hover {
  background: transparent;
  color: #20b2aa;
}

/* ----------------------
 Sidebar Styling
-------------------------*/
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

/* Shift main content when sidebar is visible (desktop view) */
.sidebar.active ~ .main-content {
  margin-left: 250px;
}

/* ----------------------
 Main Content Styling
-------------------------*/
.main-content {
  margin-left: 0;
  transition: margin-left 0.3s ease;
  padding: 20px;
  width: 100%;
  background-color: #e0f7f5;
  min-height: 100%;
  overflow-y: auto;
  margin-top: 10px; /* Changed from 95px to 60px */
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* ----------------------
Logout Button Styling
-------------------------*/
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
  margin-left: 20px;
}

.logout-btn i {
  font-size: 18px;
}

.logout-btn:hover {
  background: #e0f7f5;
  color: #20b2aa;
  transform: scale(1.05);
}

.logout-btn:active {
  transform: scale(0.95);
}

/* ----------------------
Student Table Styling
-------------------------*/
.student-table h3 {
  margin: 0;
  margin-top: 90px;
  padding-bottom: 10px;
  font-size: 26px !important;
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
  background-color: #ffffff;
  color: #1e353f;
}

.student-table table th,
.student-table table td {
  padding: 15px;
  border: 1px solid #cdeeea;
  font-size: 18px;
}

.student-table table th {
  background-color: #20b2aa;
  font-weight: bold;
  text-align: center;
  color: #ffffff;
  text-transform: uppercase;
}

.student-table table td {
  text-align: center;
  color: #1e353f;
}

.student-table table tr:hover {
  background-color: #cdeeea;
  transition: all 0.2s ease;
}

/* Buttons & Search Container */
.button-search-container {
  text-align: right;
  margin-bottom: 15px;
}

button {
  padding: 10px 20px;
  background-color: #20b2aa;
  color: #ffffff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  font-weight: bold;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #2ac2b0;
}

.export-btn {
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

.export-btn:hover{
  background-color: #2ac2b0;
}

.student-link {
  text-decoration: underline;  /* ✅ Make it underlined */
  color: black;
  font-weight: bold;
}

.student-link:hover {
  color: rgb(122, 118, 118); /* Optional: darken on hover */
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

/* ----------------------
Responsive Design
-------------------------*/
@media (max-width: 991px) {
.navbar .logo-img {
  width: 50px;
  height: auto;
}
.navbar .logo p {
  font-size: 18px;
}
.navbar .logo p.faculty-title {
  font-size: 12px;
}
.logout-btn {
  font-size: 12px;
  padding: 5px 12px;
}
.sidebar {
  width: 220px;
  margin-top: 85px;
}
  }

@media (max-width: 768px) {
.navbar {
  flex-wrap: wrap;
  padding: 8px;
}
.navbar .menu {
  flex-direction: column;
  align-items: flex-start;
}
.navbar .menu li {
  font-size: 12px;
}
.sidebar {
  width: 200px;
  margin-top: 70px;
}
/* For mobile, sidebar slides in from left */
@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    top: 15px; /* 🔥 exactly same height as navbar */
    left: 0;
    width: 200px;
    height: calc(100vh - 87px); /* 🔥 full height minus navbar */
    background: linear-gradient(180deg, #20b2aa, #1da2a0);
    overflow-y: auto;
    z-index: 2000;
    transform: translateX(-100%); /* hidden by default */
    transition: transform 0.3s ease;
  }

  .sidebar.active {
    transform: translateX(0);
  }

  .main-content {
  margin-top: 90px;
  margin-left: 0;
  width: 100%;
  overflow-x: hidden;
  overflow-y: auto;
}
}

.navbar {
  z-index: 3000;

}

body.sidebar-open::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  z-index: 1500;
}
  }

@media (max-width: 480px) {
.sidebar {
  display: none;
}
.main-content {
  width: 100%;
  padding: 10px;
}
.student-table table th:nth-child(2),
.student-table table td:nth-child(2) {
  width: 40%;
}
.student-table table th:nth-child(3),
.student-table table td:nth-child(3) {
  width: 20%;
}
button {
  font-size: 12px;
  padding: 8px 16px;
}
  }

/* ───────────────────────────────────────────
       Change‑Password Popup CSS
    ───────────────────────────────────────────── */
    .position-relative { position: relative; }
    .popup-form {
      position: absolute;
      top: calc(100% + 8px);
      right: 0;
      width: 270px;
      background: #ffffff;
      border: 1px solid #e2e2e2;
      border-radius: 0.75rem;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      padding: 1rem;
      z-index: 1100;
      animation: fadeInDrop 200ms ease-out;
    }
    .popup-form::before {
      content: "";
      position: absolute;
      top: -8px;
      right: 24px;
      border-left: 8px solid transparent;
      border-right: 8px solid transparent;
      border-bottom: 8px solid #ffffff;
    }
    @keyframes fadeInDrop {
      from { opacity: 0; transform: translateY(-10px) scale(0.95); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .d-none { display: none !important; }
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
    .popup-form form {
    display: flex;
    flex-direction: column;
    align-items: flex-start;   /* ← left‑align children */
    max-width: 260px;
    margin: 0 auto;
    }

    .popup-form .form-label {
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
    text-align: left;          /* ← left‑align the label text */
    width: 100%;               /* ← span full width */
    margin-left: 30px;            /* ← remove any extra indent */
    }

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

  @media (max-width: 768px) {
  .main-content {
    margin-top: 10px; /* 🔥 when MINIMIZE, move content lower nicely */
  }
}

</style>
</head>

<body>
<!-- Navbar -->
<div class="navbar">
    <div class="logo">
      <button id="sidebarToggle" class="menu-toggle">
        <i class="fas fa-bars"></i>
      </button>
      <img src="image/logoumk.png" alt="UMK Logo" class="logo-img">
      <div>
        <p>Industrial Attachment Management System</p>
        <p class="faculty-title">FACULTY OF DATA SCIENCE & COMPUTING</p>
      </div>
    </div>
    <ul class="menu">
      <!-- Lecturer Name + Change-Password Popup -->
      <li>
        <div class="position-relative">
          <a href="#" id="userName" class="logout-btn" style="background-color: white; color: navy; border: 2px solid navy;">
            <i class="fas fa-user"></i> <?= htmlspecialchars($lecturerName) ?>
          </a>
          <div id="userPopup" class="popup-form d-none">
            <h6><i class="fas fa-key"></i>Change Password</h6>
            <form id="changePasswordForm" action="change_password.php" method="POST">
              <label class="form-label">Current</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" name="current_password" class="form-control" required>
                <button type="button" class="btn toggle-pass"><i class="fas fa-eye"></i></button>
              </div>
              <label class="form-label">New</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fas fa-lock-open"></i></span>
                <input type="password" name="new_password" class="form-control" required>
                <button type="button" class="btn toggle-pass"><i class="fas fa-eye"></i></button>
              </div>
              <label class="form-label">Confirm</label>
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                <input type="password" name="confirm_password" class="form-control" required>
                <button type="button" class="btn toggle-pass"><i class="fas fa-eye"></i></button>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="button" id="popupCancel" class="btn btn-light btn-sm">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
              </div>
            </form>
          </div>
        </div>
      </li>
      <!-- Logout -->
      <li>
        <a href="logout.php" class="logout-btn">
          <i class="fas fa-sign-out-alt"></i> Log Out
        </a>
      </li>
    </ul>
  </div>

<!-- Sidebar -->
<div class="sidebar">
    <a href="lecturer_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="#" data-bs-toggle="collapse" data-bs-target="#studentSubmenu" aria-expanded="false" aria-controls="studentSubmenu">
        <i class="fas fa-user-graduate"></i> Student
    </a>
    <div class="collapse" id="studentSubmenu">
        <a href="lecturer_student_list.php" class="collapse-item">Student List</a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content" id="main-content">
    <section class="student-table">
      <h3>Student</h3>
      <!-- Controls: Import, Export, Search, Add, Filter -->
      <div class="button-search-container">
        <!-- Controls: Export, Search, Semester Filter -->
        <div class="button-search-container d-flex justify-content-end align-items-center w-100" style="gap: 10px;">
        <button id="exportCSV" class="btn export-btn">Export</button>

        <!-- Search Input -->
        <input 
            type="text" 
            id="searchInput" 
            placeholder="Search by name..." 
            class="form-control" 
            style="width: auto;" 
        >
        <!-- Semester Filter to the right of Search -->
        <select 
            id="semesterFilter" 
            class="form-control" 
            style="width: 150px;"
        >
            <option value="">All Semesters</option>
            <option value="FEB 24/25">FEB 24/25</option>
            <option value="SEP 25/26">SEP 25/26</option>
        </select>
        </div>
      </div>

      <!-- Sample Table -->
      <table>
        <thead>
          <tr>
            <th>BIL</th>
            <th>NAME</th>
            <th>MATRIC</th>
            <th>EMAIL</th>
            <th>SEMESTER</th>
          </tr>
        </thead>
        <tbody>
        <?php
          if ($result->num_rows > 0) {
              $counter = 1;
              while ($row = $result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . $counter++ . "</td>";
                  echo "<td><a href='student_profile.php?id=" . $row['id'] . "' class='student-link'>" 
                      . htmlspecialchars($row['name']) . "</a></td>";
                  echo "<td>" . htmlspecialchars($row['matrix']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['semester']) . "</td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='5'>No student data found</td></tr>";
          }
          ?>

        </tbody>
      </table>
    </section>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <img src="image/logoumk.png" alt="UMK Logo" class="footer-logo">
        <p>&copy; 2021 Universiti Malaysia Kelantan | Entrepreneur University. All Rights Reserved.</p>
    </div>
</footer>

<!-- JavaScript to Toggle Sidebar -->
<script>
    const sidebarToggle = document.getElementById("sidebarToggle");
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");
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

// Listen for changes on the semester filter dropdown
document.getElementById("semesterFilter").addEventListener("change", function() {
    var selectedSemester = this.value; // Get the selected semester filter value
    var table = document.querySelector('.student-table table');
    var rows = table.querySelectorAll("tbody tr"); // Select all rows in tbody

    // Loop through each row to check if it matches the selected semester
    rows.forEach(function(row) {
      // Assuming semester information is in the 5th column (adjust if necessary)
      var semesterCell = row.querySelector("td:nth-child(5)");
      // Trim any extra whitespace from the cell value
      var cellValue = semesterCell.textContent.trim();

      // If no filter is selected or the cell value matches the selected semester, show the row; otherwise, hide it.
      if (selectedSemester === "" || cellValue === selectedSemester) {
        row.style.display = ""; // Show the row (default display)
      } else {
        row.style.display = "none"; // Hide the row
      }
    });
});

// Search by Name Functionality
document.getElementById("searchInput").addEventListener("input", function() {
    // Get the search term converted to lower case for case-insensitive comparison
    const searchTerm = this.value.toLowerCase();
    // Select all rows within tbody of your student table
    const rows = document.querySelectorAll('.student-table table tbody tr');

    // Loop through each row and check if the name cell (2nd cell) contains the search term
    rows.forEach(function(row) {
      // Get the 'Name' cell; index 1 is used because arrays are zero-indexed
      const nameCell = row.cells[1];
      if (nameCell) {
        // Compare lower case text content with the search term
        if (nameCell.textContent.toLowerCase().indexOf(searchTerm) > -1) {
          row.style.display = "";  // Show the row
        } else {
          row.style.display = "none";  // Hide the row if it doesn't match
        }
      }
    });
});

// Export to CSV functionality for the Export button
document.getElementById("exportCSV").addEventListener("click", function() {
    // Get the table element (you can change the selector if you add an id to the table)
    var table = document.querySelector('.student-table table');
    var csv = [];
    
    // Loop through each row of the table
    var rows = table.querySelectorAll("tr");
    rows.forEach(function(row) {
      // Get all cells (both th and td)
      var cols = row.querySelectorAll("th, td");
      var rowData = [];
      
      cols.forEach(function(col) {
        // Replace any commas in text with an alternate value or enclose the text in quotes for CSV format.
        // Here, we'll enclose the text in quotes.
        var cellText = col.textContent.trim();
        rowData.push('"' + cellText.replace(/"/g, '""') + '"');
      });
      
      csv.push(rowData.join(","));
    });
    
    // Combine into one CSV string
    var csvString = csv.join("\n");

    // Create a Blob object with MIME type text/csv
    var blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
    
    // Create a link and set the URL using createObjectURL on the Blob
    var link = document.createElement("a");
    if (link.download !== undefined) { // feature detection
      var url = URL.createObjectURL(blob);
      link.setAttribute("href", url);
      // Name the CSV file; adjust as needed
      link.setAttribute("download", "student_list.csv");
      link.style.visibility = 'hidden';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
});

// ───────────────────────────────────────────
    // Change‑Password Popup JS
    // ───────────────────────────────────────────
    const userName  = document.getElementById('userName'),
          userPopup = document.getElementById('userPopup'),
          cancelBtn = document.getElementById('popupCancel');

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

    document.querySelectorAll('.toggle-pass').forEach(btn => {
      btn.addEventListener('click', () => {
        const inp = btn.closest('.input-group').querySelector('input');
        inp.type = inp.type === 'password' ? 'text' : 'password';
        btn.querySelector('i').classList.toggle('fa-eye-slash');
      });
    });

    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = this.querySelector('button[type="submit"]');
      btn.disabled = true;

      fetch('change_password.php', {
        method: 'POST',
        body: new FormData(this)
      })
      .then(r => r.json())
      .then(data => {
        Swal.fire({
          icon: data.status === 'success' ? 'success' : 'error',
          title: data.status === 'success' ? 'Success' : 'Error',
          text: data.message,
          confirmButtonColor: data.status === 'success' ? '#20b2aa' : '#d33'
        }).then(() => {
          if (data.status === 'success') {
            userPopup.classList.add('d-none');
            this.reset();
          }
        });
      })
      .catch(() => {
        Swal.fire('Oops...', 'Something went wrong.', 'error');
      })
      .finally(() => btn.disabled = false);
    });
</script> 
 
  <!-- Bootstrap Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
