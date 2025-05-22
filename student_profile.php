<?php
include("db_connect.php"); // Ensure database connection

// Get the student ID from the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $student_id = $_GET['id'];

    // Fetch student details from the database
    $sql = "SELECT * FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
    } else {
        echo "<h3 style='text-align:center; color:red;'>Student not found!</h3>";
        exit();
    }
} else {
    echo "<h3 style='text-align:center; color:red;'>Invalid student ID!</h3>";
    exit();
}

// Fetch the attachment from the form table for this student (if any)
$student_email = $student['email']; // from the students table
$sqlAttachment = "SELECT attachment FROM form WHERE email = ? LIMIT 1";
$stmt3 = $conn->prepare($sqlAttachment);
$stmt3->bind_param("s", $student_email);
$stmt3->execute();
$resultAttachment = $stmt3->get_result();
$rowAttachment = $resultAttachment->fetch_assoc();
$attachmentLink = ($rowAttachment && !empty($rowAttachment['attachment'])) ? $rowAttachment['attachment'] : "";
$stmt3->close();

$sqlUploads = "SELECT form_name, file_path, upload_date
               FROM student_uploads
               WHERE student_email = ?
               ORDER BY upload_date DESC";
$stmt2 = $conn->prepare($sqlUploads);
$stmt2->bind_param("s", $student_email);
$stmt2->execute();
$resUploads = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQ5BtjmQVKynWj8fPqKflEUVb+6ugvcgO/nr36M9pqE8s6rHuX4r3xIM+" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Add Bootstrap JavaScript -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>

<style>
/* Background to Match Main Theme */
body {
    margin: 0;
    padding: 0;
    background: #e0f7f5;
    font-family: Arial, sans-serif;
    color: #333;
}

.profile-container {
    background: white;
    width: 90%; /* Instead of fixed 900px, make it 90% */
    max-width: 900px; /* Limit maximum size */
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    border: 2px solid #20b2aa;
    margin: 120px auto 40px auto;
    box-sizing: border-box; /* Very important to prevent overflow */
}

/* Header Styling */
h2 {
    color: #157270;
    font-weight: bold;
    font-size: 24px;
    margin-bottom: 15px;
}

/* Table Styling */
.profile-table {
    width: 100%;
    border-radius: 10px;
    overflow: hidden;
}

.profile-table th {
    background: #20b2aa;
    color: white;
    padding: 12px;
    font-size: 16px;
    text-align: left;
}

.profile-table td {
    padding: 12px;
    font-size: 16px;
    color: #333;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

/* Back Button */
.btn-back {
    background: #20b2aa;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s;
    margin-top: 20px;
    display: inline-block;
}

.btn-back:hover {
    background: #157270;
    transform: scale(1.05);
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

/* Custom style for Uploaded Forms Table */
.uploads-table {
  font-size: 16px;
}

.uploads-table thead {
  background: #20b2aa;
}

.uploads-table thead th {
  border: none;
  font-weight: 600;
  color: #20b2aa;
  padding: 12px;
}

.uploads-table tbody td {
  padding: 12px;
  background: #f9f9f9;
  border: none;
}

.uploads-table tbody tr {
  transition: background 0.3s ease;
}

.uploads-table tbody tr:hover {
  background: #f1fdfc;
}

/* Optional: Style the file link */
.text-teal {
  color: #20b2aa !important;
  font-weight: 500;
  text-decoration: none;
}

.text-teal:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
    .profile-container {
        padding: 15px;
    }
}

</style>

<body>
<div class="container">
    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">
           <img src="image/iams.png" alt="UMK Logo" style="width:70px;height:75px;flex-shrink:0;">
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

    <div class="profile-container" >
        <h2>Student Profile</h2>
        
        <table class="profile-table table table-bordered">
    <tr>
        <th>Matric</th>
        <td><?php echo htmlspecialchars($student['matrix']); ?></td>
    </tr>
    <tr>
        <th>Name</th>
        <td><?php echo htmlspecialchars($student['name']); ?></td>
    </tr>
    <tr>
        <th>Email</th>
        <td><?php echo htmlspecialchars($student['email']); ?></td>
    </tr>
    <tr>
        <th>Lecturer</th>
        <td><?php echo htmlspecialchars($student['lecturer']); ?></td>
    </tr>
    <tr>
        <th>Status</th>  <!-- New Row for Status -->
        <td><?php echo htmlspecialchars($student['status']); ?></td>
    </tr>
     <!-- New row for displaying the Attachment -->
    <tr>
        <th>Attachment</th>
        <td>
            <?php if (!empty($attachmentLink)) : ?>
                <a href="<?php echo htmlspecialchars($attachmentLink); ?>" target="_blank">Download Attachment</a>
            <?php else: ?>
                No attachment uploaded.
            <?php endif; ?>
        </td>
    </tr>
</table>

<!-- Uploaded Forms Table -->
<h4 class="mb-3" style="color: #157270;font-weight: bold;">Uploaded Forms</h4>
<?php if ($resUploads->num_rows > 0): ?>
  <div class="table-responsive">
    <table class="table table-hover table-borderless uploads-table">
      <thead class="bg-teal text-white">
        <tr>
          <th>Form Name</th>
          <th>File</th>
          <th>Upload Date</th>
        </tr>
      </thead>
      <tbody>
        <?php while($upload = $resUploads->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($upload['form_name']); ?></td>
            <td>
            <a href="download.php?file=<?php echo urlencode($upload['file_path']); ?>&name=<?php echo urlencode($upload['form_name']); ?>" class="text-teal">
                <i class="fas fa-file-alt"></i> View File
              </a>
            </td>
            <td><?php echo htmlspecialchars($upload['upload_date']); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    </div>
        <?php else: ?>
        <p class="text-muted">No uploaded forms found for this student.</p>
        <?php endif; ?>

            </div>
            </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <img src="image/logoumk.png" alt="UMK Logo" class="footer-logo">
                <p>&copy; 2021 Universiti Malaysia Kelantan | Entrepreneur University. All Rights Reserved.</p>
            </div>
        </footer>
        
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
            // navbar
            document.addEventListener("DOMContentLoaded", () => {
            console.log("Script initialized, DOM fully loaded");

            // Navbar scroll-hide functionality
            const navbar = document.querySelector(".navbar");
            let lastScrollTop = 0;

            window.addEventListener("scroll", () => {
                const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;

                if (currentScrollTop > lastScrollTop) {
                    navbar.style.transform = "translateY(0)"; // Hide when scrolling down
                } else {
                    navbar.style.transform = "translateY(0)"; // Show when scrolling up
                }

                lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop; // Prevent negative values
            });

        });

        
</script>
</body>
</html>
