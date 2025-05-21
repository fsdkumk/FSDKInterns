<?php
session_start();
if ($_SESSION['role'] != 'lecturer') {
    header("Location: login.php");
    exit();
}

// Database connection
include("db_connect.php");

$lecturerName = $_SESSION['name'];

// Status counters
$stmt1 = $conn->prepare("SELECT COUNT(*) as total FROM students WHERE lecturer = ? AND status = 'Haven''t got any LI yet'");
$stmt1->bind_param("s", $lecturerName);
$stmt1->execute();
$result1 = $stmt1->get_result()->fetch_assoc()['total'];

$stmt2 = $conn->prepare("SELECT COUNT(*) as total FROM students WHERE lecturer = ? AND status = 'At least get 1 LI'");
$stmt2->bind_param("s", $lecturerName);
$stmt2->execute();
$result2 = $stmt2->get_result()->fetch_assoc()['total'];

$stmt3 = $conn->prepare("SELECT COUNT(*) as total FROM students WHERE lecturer = ? AND status = 'Already confirmed / decided'");
$stmt3->bind_param("s", $lecturerName);
$stmt3->execute();
$result3 = $stmt3->get_result()->fetch_assoc()['total'];

// Fetch forms list
$formQuery = "SELECT form_name, due_date FROM before_li ORDER BY id DESC";
$formResult = $conn->query($formQuery);

// Total students under lecturer
$stmtTotalStudents = $conn->prepare("SELECT COUNT(*) as total FROM students WHERE lecturer = ?");
$stmtTotalStudents->bind_param("s", $lecturerName);
$stmtTotalStudents->execute();
$totalStudents = $stmtTotalStudents->get_result()->fetch_assoc()['total'];
$stmtTotalStudents->close();
?>

<!DOCTYPE html>
<html lang="en">   
<head> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lecturer | Dashboard</title>
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

.navbar .menu li {
    display: inline-flex;
    align-items: center;
    gap: 10px;
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
    background: transparent;
    color: #20b2aa;
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

.main-content {
    margin-left: 0;
    transition: margin-left 0.3s ease;
    padding: 20px;
    width: 100%;
    background-color: #e0f7f5;
    min-height: 100%;
    overflow-y: auto;
    margin-top: 95px;
    display: flex;
    flex-direction: column;
    align-items: center;
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
    width: 100%;
    background-color: #e0f7f5;
    min-height: 100%;
    overflow-y: auto;
    margin-top: 95px;
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
    align-items: center;
    flex-wrap: wrap; /* in case small screen */
    gap: 50px;
    margin-top: 40px;
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

/* Red square */
#status1 {
    background: linear-gradient(145deg, #ff4c4c, #d63030);
}

/* Orange square */
#status2 {
    background: linear-gradient(145deg, #ffb347, #ff8c00);
}

/* Green square */
#status3 {
    background: linear-gradient(145deg, #4caf50, #2e7d32);
}

.square:hover {
    transform: translateY(-10px) rotateX(15deg) rotateY(15deg);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
}

.status-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;
}

/* Footer Styling */
html, body {
    margin: 0;
    padding: 0;
    height: auto;
    overflow-x: hidden;
    font-family: Arial, sans-serif;
}

.footer {
    width: 100%;
    clear: both;
    background: linear-gradient(145deg, #33d1c9, #1da2a0);
    color: #ffffff;
    text-align: center;
    padding: 10px 0;
    box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.2);
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

/* =========================== */
/* ✅ Final Responsive Fix ✅ */
/* =========================== */

/* For tablet and below */
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

    .status-squares {
        gap: 30px;
    }

    .footer-logo {
        width: 40px;
    }
}

/* For mobile devices */
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

    .status-squares {
        flex-direction: column;
        gap: 20px;
    }

    .footer-content {
        flex-direction: column;
        text-align: center;
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
      to   { opacity: 1; transform: translateY(0)    scale(1); }
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
      border:1px solid #d1d1d1;
      border-right:none;
      border-radius: 0.5rem 0 0 0.5rem;
      box-shadow:none;
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
      background: linear-gradient(135deg,#20b2aa,#1da2a0);
      border:none;
      border-radius:0.5rem;
      padding:0.25rem 1rem;
    }

    @media (max-width: 768px) {
    body {
        overflow-x: hidden;
    }

  .sidebar {
    position: fixed;
    top: 16px; /* exactly under navbar */
    left: 0;
    width: 250px;
    height: calc(110vh - 90px); /* full screen minus navbar */
    background: linear-gradient(180deg, #20b2aa, #1da2a0);
    overflow-y: auto;
    z-index: 999;
    transition: transform 0.3s ease;
    transform: translateX(-100%); /* HIDE sidebar by default */
  }

  .sidebar.active {
    transform: translateX(0); /* SHOW sidebar when active */
  }

  .main-content {
    margin: 90px 0 0 0; /* only top margin for navbar */
    padding: 20px;
    width: 100%;
    overflow-x: hidden;
    overflow-y: auto;
  }
}

.main-inner {
    width: 100%;
    max-width: 1000px;
    margin: 0 auto; /* 🔥 Center horizontally */
    padding: 0 20px;
    box-sizing: border-box;
}

@media (max-width: 768px) {
  .main-content {
    margin-top: 100px !important; /* 🔥 Move up when small screen */
  }
}

</style>

<body>
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
        <!-- Lecturer‑name + Change‑Password Popup -->
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
    <a href="usermanual/User_Manual_Lecturer.pdf" download><i class="fas fa-book"></i> User Manual</a>
</div>

<div class="main-content">
<div class="main-inner">
    <div class="status-wrapper"> <!-- NEW WRAPPER START -->
        <div class="application-status">
            <h2>Application Status</h2>
        </div>

        <div class="status-squares">
            <div style="text-align:center;">
            <div class="square" id="status1"><?php echo $result1; ?></div>
                <p style="margin-top:8px; color:#d63030; font-weight:bold;">Haven't got any LI yet</p>
            </div>
            <div style="text-align:center;">
            <div class="square" id="status2"><?php echo $result2; ?></div>
                <p style="margin-top:8px; color:#ff8c00; font-weight:bold;">At least get 1 LI</p>
            </div>
            <div style="text-align:center;">
            <div class="square" id="status3"><?php echo $result3; ?></div>
                <p style="margin-top:8px; color:#2e7d32; font-weight:bold;">Already confirmed / decided</p>
            </div>
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
  $counter = 1;

  if ($formResult && $formResult->num_rows > 0) {
      while ($row = $formResult->fetch_assoc()) {
          $formName = $row['form_name'];
          $dueDate  = $row['due_date'];

          // Decide which table holds submissions for this form
          if (stripos($formName, 'LI_BORANG MAKLUM BALAS INDUSTRI NEW') !== false) {
              $subTable    = 'form';
              $whichColumn = 'which_form';
          } else {
              $subTable    = 'student_uploads';
              $whichColumn = 'form_name';
          }

          // Fetch all students under this lecturer
            $sqlAll = "
            SELECT name
            FROM students
            WHERE lecturer = ?
            ";
            $stmtAll = $conn->prepare($sqlAll);
            $stmtAll->bind_param("s", $lecturerName);
            $stmtAll->execute();
            $resAll = $stmtAll->get_result();

            $allStudents = [];
            while ($r = $resAll->fetch_assoc()) {
            $allStudents[] = $r['name'];
            }
            $stmtAll->close();

            // Fetch all students under this lecturer
            $sqlAll = "
            SELECT name, email
                FROM students
            WHERE lecturer = ?
            ";
            $stmtAll = $conn->prepare($sqlAll);
            $stmtAll->bind_param("s", $lecturerName);
            $stmtAll->execute();
            $resAll = $stmtAll->get_result();

            $students = [];
            while ($r = $resAll->fetch_assoc()) {
            $students[$r['email']] = $r['name']; // use email as key
            }
            $stmtAll->close();

            // Fetch students who have submitted this form
            if ($subTable == 'form') {
                $sqlSub = "
                SELECT email
                    FROM form
                WHERE {$whichColumn} = ?
                ";
            } else {
                $sqlSub = "
                SELECT student_email
                    FROM student_uploads
                WHERE {$whichColumn} = ?
                ";
            }
            $stmtSub = $conn->prepare($sqlSub);
            $stmtSub->bind_param("s", $formName);
            $stmtSub->execute();
            $resSub = $stmtSub->get_result();

            $submitted = [];
            while ($r = $resSub->fetch_assoc()) {
            if ($subTable == 'form') {
                $submitted[] = $r['email'];
            } else {
                $submitted[] = $r['student_email'];
            }
            }
            $stmtSub->close();

            // Calculate students who did NOT submit
            $notSubmittedNames = [];
            foreach ($students as $email => $name) {
                if (!in_array($email, $submitted)) {
                    $notSubmittedNames[] = $name;
                }
            }
            $notCount = count($notSubmittedNames);

        ?>
      <div class="accordion-item mb-3 border-0 shadow-sm rounded">
        <h2 class="accordion-header" id="heading-<?php echo $counter; ?>">
          <button class="accordion-button collapsed bg-white text-dark fw-bold"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#collapse-<?php echo $counter; ?>"
                  aria-expanded="false"
                  aria-controls="collapse-<?php echo $counter; ?>">
            <i class="fas fa-file-alt me-2 text-primary"></i>
            <?php echo htmlspecialchars($formName); ?>
            <?php if ($dueDate): ?>
              <span class="badge bg-danger ms-3">Due: <?php echo htmlspecialchars($dueDate); ?></span>
            <?php endif; ?>
            <span class="badge bg-success ms-3"><?php echo $notCount . ' / ' . $totalStudents; ?></span>
          </button>
        </h2>
        <div id="collapse-<?php echo $counter; ?>"
             class="accordion-collapse collapse"
             aria-labelledby="heading-<?php echo $counter; ?>"
             data-bs-parent="#formsAccordion">
          <div class="accordion-body">
            <?php if ($notCount > 0): ?>
              <ul>
                <?php foreach ($notSubmittedNames as $name): ?>
                  <li><?php echo htmlspecialchars($name); ?></li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p class="text-success">All students have submitted this form ✅</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
  <?php
          $counter++;
      }  // ← end while
  } else {
      echo "<p class='text-muted'>No forms found.</p>";
  }    // ← end if
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

// ✅ Auto-refresh status counts every 5 seconds
function loadStatusCounts() {
    fetch("fetch_status1.php")
        .then(response => response.json())
        .then(data => {
            document.getElementById("status1").textContent = data.havent;
            document.getElementById("status2").textContent = data.atleast;
            document.getElementById("status3").textContent = data.confirmed;
        })
        .catch(error => console.error("Error fetching status counts:", error));
}

document.addEventListener("DOMContentLoaded", function() {
    loadStatusCounts(); // First time load
    setInterval(loadStatusCounts, 5000); // Refresh every 5 seconds
});

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

</body>
</html>