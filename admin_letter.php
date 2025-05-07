<?php
session_start();
include("db_connect.php");

if (isset($_POST["delete_id"])) {
  $id = intval($_POST["delete_id"]);

  // Optional: delete file too
  $res = $conn->query("SELECT file_path FROM letters WHERE id = $id");
  if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    if (file_exists($row["file_path"])) {
      unlink($row["file_path"]);
    }
  }

  $conn->query("DELETE FROM letters WHERE id = $id");
  header("Location: admin_letter.php?deleted=1");
  exit;

if (isset($_SESSION["delete_status"])) {
  unset($_SESSION["delete_status"]);
  echo "
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Deleted!',
      text: 'The letter has been successfully removed.',
      showConfirmButton: false,
      timer: 2000
    });
  </script>";
}
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["letterFile"])) {
  $file = $_FILES["letterFile"];
  $name = basename($file["name"]);
  $desc = isset($_POST["description"]) ? $conn->real_escape_string($_POST["description"]) : "Uploaded Letter";
  $target = "uploads/" . $name;

  if (!is_dir("uploads")) {
    mkdir("uploads", 0777, true);
  }

  if (move_uploaded_file($file["tmp_name"], $target)) {
    $stmt = $conn->prepare("INSERT INTO letters (letter_name, description, file_path) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $desc, $target);
    $stmt->execute();
    header("Location: admin_letter.php?upload=success");
    exit;
  } else {
    header("Location: admin_letter.php?upload=fail");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>admin | Letter</title>
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<style>
  /* General Body Styles */
  body {
    background-color: #e0f7f5;
    margin: 0;
    font-family: Arial, sans-serif;
    display: flex;
    overflow-x: hidden; /* prevent horizontal scroll */
    overflow-y: auto;  
    padding-top: 10px; /* matches navbar height */
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
    height: 90px; /* Implied from margin-top elsewhere */
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
    overflow: unset;
    height: auto;
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

  .text-teal {
  color: #20b2aa !important;
}

.btn-light:hover {
  background-color: #f0fdfc !important;
  color: #20b2aa !important;
}

.bg-teal {
  background-color: #20b2aa !important;
}

.btn-teal {
  background-color: #20b2aa;
}

.btn-teal:hover {
  background-color: #1a8d8a;
}


/* Media Queries */
@media (max-width: 992px) {
  .sidebar {
    width: 220px;
  }

  .sidebar.active ~ .main-content {
    margin-left: 0;
  }

  .main-content {
    padding: 15px;
    width: 100%;
  }

  header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  header h2 {
    font-size: 20px;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 576px) {
  .logout-btn {
    font-size: 14px;
    padding: 6px 16px;
  }

  .sidebar {
    width: 100%;
    height: auto;
    position: absolute;
    top: 70px;
  }

  .sidebar.active ~ .main-content {
    margin-left: 0;
  }
}

.text-teal {
  color: #20b2aa !important;
}

.letter-list {
  display: flex;
  flex-direction: column;
  align-items: center;
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
        <img src="image/logoumk.png" alt="UMK Logo" class="logo-img" />
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
    <header class="d-flex justify-content-between align-items-center px-4">
      <h2 class="m-0">LETTER</h2>
      <button class="btn btn-light text-teal fw-bold shadow-sm rounded-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#addLetterModal">
        <i class="fas fa-plus me-2"></i> Add Letter
      </button>
    </header>

    <div class="letter-list mt-4 px-3">
<?php
$result = $conn->query("SELECT * FROM letters ORDER BY id DESC");
$index = 1;

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    echo '
    <div class="d-flex justify-content-between align-items-center flex-wrap p-3 my-2 rounded shadow-sm"
        style="background-color: #ffffff; border-left: 6px solid #20b2aa; max-width: 850px; width: 100%; margin: auto;">
          
      <div style="flex: 1; min-width: 0; padding-right: 10px;">
        <div class="fw-bold text-teal" style="font-size: 16px; word-break: break-word;">
          ' . $index++ . '. ' . htmlspecialchars($row["letter_name"]) . '
        </div>
        <div class="text-muted fst-italic small">' . htmlspecialchars($row["description"]) . '</div>
      </div>


     <form method="POST" class="delete-form">
        <input type="hidden" name="delete_id" value="' . $row["id"] . '">
        <button type="submit" class="btn btn-danger fw-bold rounded-pill px-3 delete-btn">
          <i class="fas fa-trash me-1"></i> Delete
        </button>
      </form>

        <input type="hidden" name="delete_id" value="' . $row["id"] . '">
      </form>
    </div>';
  }
} else {
  echo '<div class="text-center text-muted mt-4">No letters uploaded yet.</div>';
}
?>
</div>

<!-- Modal -->
<div class="modal fade" id="addLetterModal" tabindex="-1" aria-labelledby="addLetterLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-teal text-white">
        <h5 class="modal-title" id="addLetterLabel">Upload New Letter</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="mb-3">
            <label for="letterFile" class="form-label">Upload File</label>
            <input class="form-control" type="file" name="letterFile" id="letterFile" required />
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" name="description" id="description" rows="2" placeholder="e.g. Sebelum Latihan Industri" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-teal text-white fw-bold">Upload</button>
        </div>
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

    // Show SweetAlert messages passed via URL
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get("upload") === "success") {
  window.history.replaceState(null, "", window.location.pathname); // Clean URL first
  Swal.fire({
    icon: "success",
    title: "Upload Successful!",
    text: "Your letter has been uploaded.",
    showConfirmButton: false,
    timer: 2000
  });
} else if (urlParams.get("upload") === "fail") {
  window.history.replaceState(null, "", window.location.pathname); // Clean URL first
  Swal.fire({
    icon: "error",
    title: "Upload Failed!",
    text: "There was a problem uploading your file.",
    showConfirmButton: false,
    timer: 2500
  });
}


  if (urlParams.get("deleted") === "1") {
  // Immediately clean the URL before anything else
  window.history.replaceState(null, "", window.location.pathname);

  // Then show the alert
  Swal.fire({
    icon: 'success',
    title: 'Deleted!',
    text: 'The letter has been successfully removed.',
    showConfirmButton: false,
    timer: 2000
  });
}

  document.querySelectorAll(".delete-form").forEach(form => {
  form.addEventListener("submit", function(e) {
    e.preventDefault(); // stop form from submitting

    Swal.fire({
      title: 'Are you sure?',
      text: "This action cannot be undone!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#20b2aa',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit(); // proceed with form submission
      }
    });
  });
});
  </script>
</body>
</html>
