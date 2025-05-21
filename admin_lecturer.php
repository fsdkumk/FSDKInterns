<?php
include("db_connect.php");

// Retrieve the lecturer name from the URL query string
$lecturerName = isset($_GET['lecturer']) ? $_GET['lecturer'] : 'Unknown Lecturer';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>admin | lecturer</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-rbsA2VBKQ5BtjmQVKynWj8fPqKflEUVb+6ugvcgO/nr36M9pqE8s6rHuX4r3xIM+"
    crossorigin="anonymous"
  />
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    rel="stylesheet"
  />

  <!-- Add Bootstrap JavaScript -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

/>

</head>

<style>
   * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

  /* General Body Styles */
    body {
      background-color: #e0f7f5;
      font-family: Arial, sans-serif;
      min-height: 100vh;
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
    height: 90px;  
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
    top: 0px;       
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
    margin-left: 140px;  /* Default when the sidebar is hidden */
    transition: margin-left 0.3s ease;
    padding: 0px;
    width: calc(100% - 250px);
    background-color: #e0f7f5;
    min-height: calc(100vh - 90px);/* Ensures content height scales */
    overflow-y: auto; /* Allows scrolling if content overflows */
    margin-top: 90px;   /* Adjust to the height of the navbar */
    /* FLEX settings to center items horizontally */
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    justify-content: flex-start; /* Start at the top (instead of center) */
    padding: 20px;              /* Some padding around the content */
  }

  /* Main Content When Sidebar is Open */
  .sidebar.active ~ .main-content {
    margin-left: 250px; /* Match the sidebar width */
  }

  .container {
    justify-content: center;
    align-items: center;
    width: 100%;
    margin-top: 30px;
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
    justify-content: center; /* Center the content */
    align-items: center;
  }

  header h2 {
    margin: 0;
    font-size: 24px;
    color: #ffffff;
  }

  .lecturer-title {
  font-family: 'Playfair Display', serif; /* Elegant font */
  font-size: 34px;
  background: linear-gradient(135deg, #ff7e5f, #feb47b); /* Bold gradient */
  color: #ffffff;
  padding: 20px 40px;
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  text-transform: uppercase;
  letter-spacing: 2px;
  text-align: center;
  position: relative;
  overflow: hidden;
  margin: 10px 0;  /* Adjust top/bottom spacing */
}

/* Animated light sweep effect */
.lecturer-title::before {
  content: "";
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.2);
  transform: skewX(-20deg);
  animation: slide 3s infinite;
}

@keyframes slide {
  0% {
    left: -100%;
  }
  50% {
    left: 100%;
  }
  100% {
    left: 100%;
  }
}

/* Decorative underline */
.lecturer-title::after {
  content: "";
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 120px;
  height: 6px;
  background: #ffffff;
  border-radius: 3px;
}

/* Table Styling */
.student-table {
  margin: 20px auto;
  text-align: center;
  width: 90%;
}

.student-table h3 {
  margin-bottom: 15px;
  font-family: 'Playfair Display', serif;
  font-size: 28px;
  color: #333;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.student-table table {
  width: 100%;
  border-collapse: collapse;
  border-spacing: 0;
  margin: 0 auto;
  background: #fff;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: transform 0.3s ease;
}

.student-table table:hover {
  transform: scale(1.02);
}

.student-table thead {
  background: linear-gradient(45deg, #ff6b6b, #f06595);
  color: #fff;
}

.student-table thead th {
  padding: 15px;
  font-size: 16px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  border-bottom: 2px solid rgba(255, 255, 255, 0.3);
}

.student-table tbody tr {
  transition: background 0.3s ease;
}

.student-table tbody tr:nth-child(even) {
  background: #f9f9f9;
}

.student-table tbody tr:nth-child(odd) {
  background: #fff;
}

.student-table tbody tr:hover {
  background: rgba(240, 101, 149, 0.1);
}

.student-table tbody td {
  padding: 15px;
  font-size: 15px;
  color: #555;
  border-bottom: 1px solid #eee;
}

.student-table tbody tr:last-child td {
  border-bottom: none;
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
    position: static; 
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

/* ─────────────── Responsive Design ─────────────── */
@media (max-width: 992px) {
  .main-content {
    margin-left: 0;
    width: 100%;
    padding: 10px;
  }

  .lecturer-title {
    font-size: 26px;
    padding: 15px 30px;
  }

  .student-table h3 {
    font-size: 20px;
    text-align: center;
  }

  .student-table table {
    font-size: 14px;
    box-shadow: none;
    transform: none;
  }

  .student-table thead th,
  .student-table tbody td {
    padding: 10px;
    font-size: 13px;
  }
}

@media (max-width: 576px) {
  .navbar .logo p {
    font-size: 18px;
  }

  .navbar .logo-img {
    width: 50px;
    height: 60px;
  }

  .navbar .logo p.faculty-title {
    font-size: 12px;
  }

  .lecturer-title {
    font-size: 20px;
    padding: 12px 20px;
    letter-spacing: 1px;
  }

  .student-table {
    width: 100%;
    overflow-x: auto;
  }

  .student-table table {
    display: block;
    width: 100%;
    overflow-x: auto;
  }

  .student-table thead,
  .student-table tbody,
  .student-table tr,
  .student-table td,
  .student-table th {
    display: block;
    text-align: left;
    width: 100%;
  }

  .student-table tr {
    margin-bottom: 10px;
  }

  .student-table td, .student-table th {
    padding: 10px;
    border-bottom: 1px solid #ccc;
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
        <img src="image/logoumk.png" alt="UMK Logo" class="logo-img" />
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
      <a
        href="#"
        data-bs-toggle="collapse"
        data-bs-target="#studentSubmenu"
        aria-expanded="false"
        aria-controls="studentSubmenu"
      >
        <i class="fas fa-user-graduate"></i> Student
      </a>
      <div class="collapse" id="studentSubmenu">
        <a href="admin_list.php" class="collapse-item">Student List</a>
        <a href="admin_letter.php" class="collapse-item">Letter</a>
        <a href="admin_before_LI.php" class="collapse-item">Implementation IT</a>
      </div>
      <a
        href="#"
        data-bs-toggle="collapse"
        data-bs-target="#staffSubmenu"
        aria-expanded="false"
        aria-controls="staffSubmenu"
      >
        <i class="fas fa-users"></i> Lecturer
      </a>
      <div class="collapse" id="staffSubmenu">
      <a href="admin_lecturer_list.php" class="collapse-item">Lecturer List</a>
      </div>
    </div>

   <!-- Main Content -->
   <div class="main-content">
      <!-- Lecturer Title -->
      <h2 class="lecturer-title"><?php echo htmlspecialchars($lecturerName); ?></h2>

      <!-- Students List Under the Lecturer -->
      <div class="student-table">
        <h3>Students under <?php echo htmlspecialchars($lecturerName); ?></h3>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Name</th>
              <th>Matric</th>
              <th>Email</th>
            </tr>
          </thead>
          <tbody>
            <?php
              // Prepare a statement to fetch students for this lecturer
              $stmt = $conn->prepare("SELECT name, matrix, email FROM students WHERE lecturer = ?");
              $stmt->bind_param("s", $lecturerName);
              $stmt->execute();
              $result = $stmt->get_result();

              if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['matrix']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                  echo "</tr>";
                }
              } else {
                echo "<tr><td colspan='3'>No students found under this lecturer.</td></tr>";
              }
              $stmt->close();
              $conn->close();
            ?>
          </tbody>
        </table>
      </div> <!-- .student-table -->
    </div> <!-- .main-content -->
  </div> <!-- .container -->

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
  </script>
</body>
</html>
