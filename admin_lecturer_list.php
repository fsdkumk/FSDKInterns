<?php
include("db_connect.php");

// CSV IMPORT HANDLING for Lecturers
if (isset($_POST['import_csv_lecturer'])) {
  $fileName = $_FILES['csv_file']['tmp_name'];
  if (isset($fileName) && $_FILES['csv_file']['size'] > 0) {
      $file = fopen($fileName, "r");
      // Skip header row if needed
      fgetcsv($file);
      while (($row = fgetcsv($file, 10000, ",")) !== false) {
          // Adjust indices to match your CSV columns for lecturers (Name, Email)
          $name  = $row[0] ?? '';
          $email = $row[1] ?? '';
          $role  = "lecturer";
          $password = 'lecturer123'; // Default password
          $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
          $stmt->bind_param("ssss", $name, $email, $password, $role);
          
          if (!$stmt->execute()) {
              echo "Database Insert Error: " . $stmt->error;
          }
          $stmt->close();
      }
      fclose($file);
      header("Location: admin_lecturer_list.php");
      exit;
  }
}

// PROCESS POST SUBMISSION: Add Lecturer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['lecturerName']) && isset($_POST['lecturerEmail'])) {
    $name  = trim($_POST['lecturerName']);
    $email = trim($_POST['lecturerEmail']);
    $role  = "lecturer";

    // Insert into the users table
    $password = 'lecturer123'; // Default password
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Lecturer added successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error inserting lecturer"]);
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
    <title>admin | lecturer list</title>
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

/* Table Styles */
.lecturer-table h3 {
      margin: 0;
      margin-top: 20px;
      padding-bottom: 10px;
      font-size: 20px;
      color: #20b2aa;
      border-bottom: 2px solid #20b2aa;
      display: inline-block;
    }
    .lecturer-table table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      background-color: #ffffff;
      color: #1e353f;
    }
    .lecturer-table table th,
    .lecturer-table table td {
      padding: 15px;
      border: 1px solid #cdeeea;
      font-size: 16px;
    }
    .lecturer-table table th {
      background-color: #20b2aa;
      font-weight: bold;
      text-align: center;
      color: #ffffff;
      text-transform: uppercase;
    }
    .lecturer-table table td {
      text-align: center;
      color: #1e353f;
    }
    .lecturer-table table tr:hover {
      background-color: #cdeeea;
      transition: all 0.2s ease;
    }
    /* Button Styles */
    .button-search-container {
      display: flex;
      justify-content: flex-end; /* Changed from space-between to flex-end */
      align-items: center;
      margin-bottom: 15px;
    }
    .right-controls {
      display: flex;
      align-items: center;
      gap: 10px;
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

    .search-bar {
      width: 250px;
      padding: 9px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
    }
    /* Modal Styling */
    .modal {
      display: none;
      position: fixed;
      z-index: 10;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.4);
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }
    .modal-content {
      background-color: #f9f9f9;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 500px;
      text-align: center;
      position: relative;
      box-sizing: border-box;
    }
    .modal-content h3 {
      font-size: 20px;
      margin: 0 0 20px 0;
      color: #20b2aa;
    }
    .modal-content form {
      display: flex;
      flex-direction: column;
      gap: 15px;
      align-items: stretch;
    }
    .modal-content input {
      width: 100%;
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
      margin-top: 20px;
      transition: background-color 0.3s ease;
    }
    .modal-content button:hover {
      background-color: #2ac2b0;
    }
    .close {
      position: absolute;
      top: 10px;
      right: 15px;
      color: #000;
      font-size: 24px;
      font-weight: bold;
      cursor: pointer;
      transition: color 0.3s ease;
    }
    .close:hover {
      color: #2ac2b0;
    }

    .lecturer-link {
    text-decoration: underline;
    color: black;
    font-weight: bold;
  }

  .lecturer-link:hover {
    text-decoration: underline;
    color: rgb(122, 118, 118);
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

/* Make Lecturer Page Responsive */
@media (max-width: 768px) {
    .sidebar {
        width: 220px; /* Slightly narrower sidebar on small screens */
    }

    .main-content {
        width: 100%; /* Full width for content when minimized */
        margin-left: 0;
        padding: 10px; /* Smaller padding */
    }

    .button-search-container {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .right-controls {
        flex-wrap: wrap;
        gap: 10px;
    }

    .right-controls button, 
    .right-controls input, 
    .right-controls select {
        width: 100%; /* Make buttons and search bar full width */
    }

    .lecturer-table table {
        font-size: 14px;
    }

    .lecturer-table table th, 
    .lecturer-table table td {
        padding: 8px;
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .navbar .logo p {
        font-size: 18px;
    }

    .navbar .logo p.faculty-title {
        font-size: 12px;
    }

    .navbar .logo-img {
        width: 50px;
        height: 60px;
    }

    .sidebar {
        width: 60%;
    }

    .main-content {
        width: 100%;
        margin-left: 0;
        padding: 10px;
    }

    .button-search-container {
        flex-direction: column;
        align-items: stretch;
    }

    .right-controls {
        flex-direction: column;
        gap: 10px;
    }

    .lecturer-table table th, 
    .lecturer-table table td {
        padding: 6px;
        font-size: 13px;
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
    </div>
 

 <!-- Main Content -->
 <div class="main-content" id="main-content">
      <section class="lecturer-table">
        <h3 style="margin-bottom: 30px;">Lecturer</h3>
        
        <!-- Button & Search Container (aligned to the right) -->
        <div class="button-search-container">
        <div class="right-controls">
            <button id="exportCSV" class="btn export-btn">Export</button>
            <button id="importCSVBtn" class="btn import-btn">Import</button>
            <input type="text" id="searchInput" placeholder="Search by name..." class="form-control search-bar">
            <button id="addLecturerBtn" class="btn add-btn">Add Lecturer</button>
            <button id="sortLecturerBtn" class="btn sort-btn">Sort by Name</button>
        </div>
        </div>

        <!-- Modal for Importing Lecturer CSV -->
        <div id="importModal" class="modal">
          <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Upload CSV File</h3>
            <form id="importCSVForm" method="post" enctype="multipart/form-data">
              <input type="file" name="csv_file" id="csvFile" accept=".csv" required style="margin-bottom:15px;">
              <button type="submit" name="import_csv_lecturer" class="btn" style="background-color: #20b2aa; color: #fff;">Upload</button>
            </form>
          </div>
        </div>

         <!-- Modal for Adding Lecturer -->
         <div id="addLecturerModal" class="modal">
          <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Add New Lecturer</h3>
            <form id="addLecturerForm">
              <input type="text" id="lecturerName" name="lecturerName" placeholder="Enter Lecturer Name" required>
              <input type="email" id="lecturerEmail" name="lecturerEmail" placeholder="Enter Lecturer Email" required>
              <button type="submit">Add Lecturer</button>
            </form>
          </div>
        </div>

        <!-- Lecturer Table -->
        <table id="lecturerTable" class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>Bil</th>
              <th>Name</th>
              <th>Email</th>
            </tr>
          </thead>
          <tbody>
            <?php
              // (Fetch and render lecturer records from your database as needed)
              // Example: 
              $sql = "SELECT id, name, email FROM users WHERE role = 'lecturer' ORDER BY id ASC";
              $result = $conn->query($sql);              
              if ($result->num_rows > 0) {
                  $counter = 1;
                  while ($row = $result->fetch_assoc()) {
                      echo "<tr>";
                      echo "<td>" . $counter++ . "</td>";
                      echo "<td><a href='admin_lecturer.php?lecturer=" . urlencode($row['name']) . "' class='lecturer-link'>" 
                           . htmlspecialchars($row['name']) . "</a></td>";
                      echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                      echo "</tr>";
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
  const importCSVBtn = document.getElementById("importCSVBtn");
  const importModal = document.getElementById("importModal");
  const importClose = importModal.querySelector(".close");

  // Open the modal when clicking the "Import" button
  importCSVBtn.addEventListener("click", () => {
    importModal.style.display = "flex";
  });

  // Close the modal when clicking the close icon
  importClose.addEventListener("click", () => {
    importModal.style.display = "none";
  });

  // Close the modal when clicking outside the modal content
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

document.getElementById("sortLecturerBtn").addEventListener("click", function() {
    const tableBody = document.querySelector("#lecturerTable tbody");
    const rows = Array.from(tableBody.querySelectorAll("tr"));

    rows.sort((a, b) => {
        const nameA = a.cells[1].textContent.trim().toLowerCase();
        const nameB = b.cells[1].textContent.trim().toLowerCase();
        return nameA.localeCompare(nameB);
    });

    // Re-append sorted rows and update serial numbers (first column)
    rows.forEach((row, index) => {
        row.cells[0].textContent = index + 1;
        tableBody.appendChild(row);
    });

    console.log("Lecturer table sorted by name.");
});


document.getElementById("searchInput").addEventListener("keyup", function() {
  const searchValue = this.value.toLowerCase();
  const rows = document.querySelectorAll("#lecturerTable tbody tr");
  
  rows.forEach(row => {
    const nameCell = row.cells[1].textContent.toLowerCase();
    row.style.display = nameCell.includes(searchValue) ? "" : "none";
  });
});


document.getElementById("exportCSV").addEventListener("click", () => {
  let csvContent = "data:text/csv;charset=utf-8,";
  
  // Get table headers
  const headers = [];
  document.querySelectorAll("#lecturerTable thead tr th").forEach(th => {
    headers.push(th.innerText);
  });
  csvContent += headers.join(",") + "\n";
  
  // Get table rows
  const rows = document.querySelectorAll("#lecturerTable tbody tr");
  rows.forEach(row => {
    let rowData = [];
    row.querySelectorAll("td").forEach(cell => {
      rowData.push(cell.innerText);
    });
    csvContent += rowData.join(",") + "\n";
  });
  
  // Create and click download link
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", "lecturers.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
});


   // Modal functionality for adding lecturer
   const addLecturerBtn = document.getElementById("addLecturerBtn");
    const addLecturerModal = document.getElementById("addLecturerModal");
    const closeModal = document.querySelector("#addLecturerModal .close");
    addLecturerBtn.addEventListener("click", () => {
      addLecturerModal.style.display = "flex";
    });
    closeModal.addEventListener("click", () => {
      addLecturerModal.style.display = "none";
    });
    window.addEventListener("click", (event) => {
      if (event.target === addLecturerModal) {
        addLecturerModal.style.display = "none";
      }
    });

     // Handle Add Lecturer form submission via Fetch API
    document.getElementById("addLecturerForm").addEventListener("submit", function(e) {
      e.preventDefault();
      const lecturerName = document.getElementById("lecturerName").value.trim();
      const lecturerEmail = document.getElementById("lecturerEmail").value.trim();
      if (!lecturerName || !lecturerEmail) {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'All fields are required.',
          confirmButtonColor: '#d33'
        });
        return;
      }
      const formData = new FormData(this);
      fetch("admin_lecturer_list.php", {
        method: "POST",
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === "success") {
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: data.message,
            confirmButtonColor: '#6c5ce7'
          }).then(() => {
            location.reload();
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
          title: 'Network Error!',
          text: 'Failed to connect to the server.',
          confirmButtonColor: '#d33'
        });
      });
    });

</script>

</body>
</html>