<?php
include("db_connect.php");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize all variables to avoid undefined warnings
$student_name = "";
$programme = "";
$duration = "";
$email = "";
$phone = "";
$internship_end_date = "";
$reporting_date = "";
$reporting_time = "";
$industry_name = "";
$industry_address = "";
$state = "";
$supervisor_name = "";
$supervisor_phone = "";
$supervisor_email = "";
$remarks = "";
$allowance_amount = "";
$which_form = "";
$attachment = "";

// Check if email is passed via GET parameter (for editing existing data)
if (isset($_GET['email']) && !empty($_GET['email'])) {
    $email = $_GET['email'];
    $sql = "SELECT * FROM form WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Populate variables from database
        $student_name = $row['student_name'];
        $programme = $row['programme'];
        $duration = $row['duration'];
        $email = $row['email'];
        $phone = $row['phone'];
        $internship_end_date = $row['internship_end_date'];
        $reporting_date = $row['reporting_date'];
        $reporting_time = $row['reporting_time'];
        $industry_name = $row['industry_name'];
        $industry_address = $row['industry_address'];
        $state = $row['state'];
        $supervisor_name = $row['supervisor_name'];
        $supervisor_phone = $row['supervisor_phone'];
        $supervisor_email = $row['supervisor_email'];
        $remarks = $row['remarks'];
        $allowance_amount = $row['allowance_amount'];
        $which_form = $row['which_form'];
        $attachment = $row['attachment'];
    }
    $stmt->close();
}

// Process form data when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Section A: Student's Details
    $student_name = $_POST['student_name'] ?? "";
    $programme    = $_POST['programme'] ?? "";
    $duration     = $_POST['duration'] ?? "";
    $email        = $_POST['email'] ?? "";
    $phone        = $_POST['phone'] ?? "";

    // Section B: Industry's Details
    $internship_end_date = !empty($_POST['internship_end_date']) ? $_POST['internship_end_date'] : null;
    $reporting_date   = $_POST['reporting_date'] ?? "";
    $reporting_time   = $_POST['reporting_time'] ?? "";
    $industry_name    = $_POST['industry_name'] ?? "";
    $industry_address = $_POST['industry_address'] ?? "";
    $state            = $_POST['state'] ?? "";
    $supervisor_name  = $_POST['supervisor_name'] ?? "";
    $supervisor_phone = $_POST['supervisor_phone'] ?? "";
    $supervisor_email = $_POST['supervisor_email'] ?? "";
    $remarks          = isset($_POST['remarks']) ? implode(", ", $_POST['remarks']) : "";
    $which_form = '1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)';

    // Handle file upload (Attachment)
    $attachment = "";
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $targetDir = "uploads/"; // make sure this folder exists
        // Create unique filename to prevent overwrite
        $fileName = time() . "_" . basename($_FILES['attachment']['name']);
        $targetFilePath = $targetDir . $fileName;

        // Optionally, you can restrict file types (e.g., PDF, DOCX, images only)
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        $allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

        if (in_array(strtolower($fileType), $allowedTypes)) {
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFilePath)) {
                $attachment = $targetFilePath;
            } else {
                die("<script>alert('Sorry, there was an error uploading your file.'); window.location.href='form.php';</script>");
            }
        } else {
            die("<script>alert('Only PDF, DOC, DOCX, JPG, JPEG, and PNG files are allowed.'); window.location.href='form.php';</script>");
        }
    }
        
    // Capture the allowance amount (if provided)
    $allowance_amount = $_POST['allowance_amount'] ?? "";

    // Email is required
    if (!$email) {
        die("<script>alert('Email is required.'); window.location.href='form.php';</script>");
    }

    // Check if the student already exists (by email)
    $check_existing = "SELECT * FROM form WHERE email=?";
    $stmt_check = $conn->prepare($check_existing);
    if (!$stmt_check) {
        die("Error preparing SELECT statement: " . $conn->error);
    }
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $stmt_check->close();

    // If the record exists, UPDATE; otherwise, INSERT.
    if ($result->num_rows > 0) {
        // UPDATE logic
        if (isset($_POST['save'])) {
            // Save (allow incomplete data) using UPDATE with NULLIF for optional fields.
            $sql = "UPDATE form SET 
                        student_name = NULLIF(?, ''),
                        programme = NULLIF(?, ''),
                        duration = NULLIF(?, ''),
                        phone = NULLIF(?, ''),
                        internship_end_date = NULLIF(?, ''),
                        reporting_date = NULLIF(?, ''),
                        reporting_time = NULLIF(?, ''),
                        industry_name = NULLIF(?, ''),
                        industry_address = NULLIF(?, ''),
                        state = NULLIF(?, ''),
                        supervisor_name = NULLIF(?, ''),
                        supervisor_phone = NULLIF(?, ''),
                        supervisor_email = NULLIF(?, ''),
                        remarks = NULLIF(?, ''),
                        allowance_amount = NULLIF(?, ''), 
                        which_form = NULLIF(?, ''),
                        attachment = NULLIF(?, '')
                    WHERE email = ?";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Error preparing UPDATE statement: " . $conn->error);
            }
            // Bind parameters (18 parameters)
            $stmt->bind_param("ssssssssssssssssss",
                $student_name,
                $programme,
                $duration,
                $phone,
                $internship_end_date,
                $reporting_date,
                $reporting_time,
                $industry_name,
                $industry_address,
                $state,
                $supervisor_name,
                $supervisor_phone,
                $supervisor_email,
                $remarks,
                $allowance_amount,
                $which_form,
                $attachment,
                $email
            );
            if ($stmt->execute()) {
                echo "<script>alert('Form saved successfully!'); window.location.href='form.php';</script>";
            } else {
                die("Error updating form: " . $stmt->error);
            }
            $stmt->close();
        }

        if (isset($_POST['submit'])) {
            // Require all Section A fields for submission
            if (!$student_name || !$programme || !$duration || !$phone) {
                die("<script>alert('Please fill in all fields in Section A before submitting.'); window.location.href='form.php';</script>");
            }
            $sql = "UPDATE form SET
                        student_name = NULLIF(?, ''),
                        programme = NULLIF(?, ''),
                        duration = NULLIF(?, ''),
                        phone = NULLIF(?, ''),
                        internship_end_date = NULLIF(?, ''),
                        reporting_date = NULLIF(?, ''),
                        reporting_time = NULLIF(?, ''),
                        industry_name = NULLIF(?, ''),
                        industry_address = NULLIF(?, ''),
                        state = NULLIF(?, ''),
                        supervisor_name = NULLIF(?, ''),
                        supervisor_phone = NULLIF(?, ''),
                        supervisor_email = NULLIF(?, ''),
                        remarks = NULLIF(?, ''),
                        allowance_amount = NULLIF(?, ''),   
                        which_form = NULLIF(?, ''),
                        attachment = NULLIF(?, '')
                    WHERE email = ?";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Error preparing UPDATE statement: " . $conn->error);
            }
            $stmt->bind_param("ssssssssssssssssss",
                $student_name,
                $programme,
                $duration,
                $phone,
                $internship_end_date,
                $reporting_date,
                $reporting_time,
                $industry_name,
                $industry_address,
                $state,
                $supervisor_name,
                $supervisor_phone,
                $supervisor_email,
                $remarks,
                $allowance_amount,
                $which_form, 
                $attachment,
                $email
            );
            if ($stmt->execute()) {
                echo "<script>alert('Form submitted successfully!'); window.location.href='form.php';</script>";
            } else {
                die("Error updating form: " . $stmt->error);
            }
            $stmt->close();
        }
    } else {
        // INSERT logic
        if (isset($_POST['save'])) {
            $sql = "INSERT INTO form (
                        student_name,
                        programme,
                        duration,
                        email,
                        phone,
                        internship_end_date,
                        reporting_date,
                        reporting_time,
                        industry_name,
                        industry_address,
                        state,
                        supervisor_name,
                        supervisor_phone,
                        supervisor_email,
                        remarks,
                        allowance_amount,
                        which_form,
                        attachment
                    ) VALUES (
                        NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), ?,
                        NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''),
                        NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''),
                        NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''),
                        NULLIF(?, ''), NULLIF(?, '')
                    )";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Error preparing INSERT statement: " . $conn->error);
            }
            $stmt->bind_param("ssssssssssssssssss",
                $student_name,
                $programme,
                $duration,
                $email,
                $phone,
                $internship_end_date,
                $reporting_date,
                $reporting_time,
                $industry_name,
                $industry_address,
                $state,
                $supervisor_name,
                $supervisor_phone,
                $supervisor_email,
                $remarks,
                $allowance_amount,
                $which_form,
                $attachment
            );
            if ($stmt->execute()) {
                echo "<script>alert('Form saved successfully!'); window.location.href='form.php';</script>";
            } else {
                die("Error inserting record: " . $stmt->error);
            }
            $stmt->close();
        }

        if (isset($_POST['submit'])) {
            if (!$student_name || !$programme || !$duration || !$phone) {
                die("<script>alert('Please fill in all fields in Section A before submitting.'); window.location.href='form.php';</script>");
            }
            $sql = "INSERT INTO form (
                student_name,
                programme,
                duration,
                email,
                phone,
                internship_end_date,
                reporting_date,
                reporting_time,
                industry_name,
                industry_address,
                state,
                supervisor_name,
                supervisor_phone,
                supervisor_email,
                remarks,
                allowance_amount,
                which_form,
                attachment
            ) VALUES (
                ?, ?, ?, ?, ?, NULLIF(?, ''), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";            
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Error preparing INSERT statement: " . $conn->error);
            }
            $stmt->bind_param("ssssssssssssssssss",
                $student_name,
                $programme,
                $duration,
                $email,
                $phone,
                $internship_end_date,
                $reporting_date,
                $reporting_time,
                $industry_name,
                $industry_address,
                $state,
                $supervisor_name,
                $supervisor_phone,
                $supervisor_email,
                $remarks,
                $allowance_amount,
                $which_form,
                $attachment
            );
            if ($stmt->execute()) {
                echo "<script>alert('Form submitted successfully!'); window.location.href='form.php';</script>";
            } else {
                die("Error inserting record: " . $stmt->error);
            }
            $stmt->close();
        }
    }
}

// Close the primary connection after processing form data
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Industry Feedback Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: linear-gradient(to right, #74ebd5, #acb6e5);
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.15);
            max-width: 800px;
            width: 100%;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .header {
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 2px solid #00796b;
            margin-bottom: 25px;
        }
        .header img {
            height: 100px;
            width: 80px;
            margin-bottom: 10px;
        }
        .header h5 {
            font-weight: 700;
            font-size: 25px;
            color: #00796b;
        }
        .header h6 {
            color: #004d40;
            font-weight: 500;
        }
        fieldset {
            border: none;
            margin-bottom: 20px;
            padding: 20px;
            background: #e0f7f5;
            border-radius: 10px;
        }
        legend {
            font-size: 18px;
            font-weight: bold;
            color: #00796b;
            padding: 5px 10px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
        input, textarea, select {
            border-radius: 8px;
        }
        .btn-primary, .btn-secondary {
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            transition: 0.3s;
        }
        .btn-primary {
            background: #00796b;
            border: none;
        }
        .btn-primary:hover {
            background: #004d40;
        }
        .btn-secondary {
            background: #6c757d;
            border: none;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }

    @media (max-width: 768px) {
    .container {
        padding: 20px;
        margin-top: 20px;
        margin-bottom: 20px;
        border-radius: 10px;
        max-width: 95%; /* Make container smaller on mobile */
    }

    .header h5 {
        font-size: 20px;
    }

    .header h6 {
        font-size: 14px;
    }

    input, textarea, select {
        font-size: 14px;
    }

    legend {
        font-size: 16px;
    }

    .btn-primary, .btn-secondary {
        font-size: 14px;
        padding: 10px;
    }
}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="image/logoumk.png" alt="UMK Logo">
            <h5>INDUSTRY FEEDBACK FORM</h5>
            <h6>Faculty of Data Science and Computing (FSDK)</h6>
        </div>
        
        <form action="" method="POST" enctype="multipart/form-data">
          <!-- Section A: Student's Details -->
          <fieldset>
              <legend>Section A: To be completed by Student</legend>
              <div class="mb-3">
                  <label class="form-label">Student’s Name</label>
                  <input type="text" class="form-control" name="student_name" value="<?php echo htmlspecialchars($student_name); ?>" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Programme of Study</label>
                  <input type="text" class="form-control" name="programme" value="<?php echo htmlspecialchars($programme); ?>" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Duration</label>
                  <input type="text" class="form-control" name="duration" value="<?php echo htmlspecialchars($duration); ?>" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Email Address</label>
                  <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Telephone No</label>
                  <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
              </div>
          </fieldset>

          <!-- Section B: Industry's Details -->
          <fieldset>
              <legend>Section B: Industry’s Details</legend>
              <div class="mb-3">
                  <label class="form-label">Internship End Date</label>
                  <input type="date" class="form-control" name="internship_end_date" value="<?php echo htmlspecialchars($internship_end_date); ?>">
              </div>
              <div class="mb-3">
                  <label class="form-label">Reporting Date</label>
                  <input type="date" class="form-control" name="reporting_date" value="<?php echo htmlspecialchars($reporting_date); ?>">
              </div>
              <div class="mb-3">
                  <label class="form-label">Reporting Time</label>
                  <input type="time" class="form-control" name="reporting_time" value="<?php echo htmlspecialchars($reporting_time); ?>">
              </div>
              <div class="mb-3">
                  <label class="form-label">Industry Name</label>
                  <input type="text" class="form-control" name="industry_name" value="<?php echo htmlspecialchars($industry_name); ?>">
              </div>
              <div class="mb-3">
                  <label class="form-label">Industry Address</label>
                  <textarea class="form-control" name="industry_address"><?php echo htmlspecialchars($industry_address); ?></textarea>
              </div>
              <div class="mb-3">
                  <label class="form-label">State</label>
                  <select class="form-select" name="state">
                      <option value="">-- Select State --</option>
                      <?php
                      $states = ["Johor", "Kedah", "Kelantan", "Melaka", "Negeri Sembilan", "Pahang", "Penang", "Perak", "Perlis", "Sabah", "Sarawak", "Selangor", "Terengganu", "W.P. Kuala Lumpur", "W.P. Labuan", "W.P. Putrajaya", "Others"];
                      foreach ($states as $st) {
                          $selected = ($state == $st) ? "selected" : "";
                          echo "<option value=\"$st\" $selected>$st</option>";
                      }
                      ?>
                  </select>
              </div>
              <div class="mb-3">
                  <label class="form-label">Supervisor’s Name</label>
                  <input type="text" class="form-control" name="supervisor_name" value="<?php echo htmlspecialchars($supervisor_name); ?>">
              </div>
              <div class="mb-3">
                  <label class="form-label">Telephone No</label>
                  <input type="tel" class="form-control" name="supervisor_phone" value="<?php echo htmlspecialchars($supervisor_phone); ?>">
              </div>
              <div class="mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" class="form-control" name="supervisor_email" value="<?php echo htmlspecialchars($supervisor_email); ?>">
              </div>

              <?php $remarksArray = explode(", ", $remarks); ?>
              <div class="mb-3">
                  <label class="form-label">Remarks (if any)</label><br>
                  <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="remarks[]" value="Allowance" id="checkAllowance"
                          <?php if (in_array("Allowance", $remarksArray)) echo 'checked'; ?> onclick="toggleAllowanceField()">
                      <label class="form-check-label" for="checkAllowance">Allowance</label>
                  </div>
                  <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="remarks[]" value="Accommodation"
                          <?php if (in_array("Accommodation", $remarksArray)) echo 'checked'; ?>>
                      <label class="form-check-label">Accommodation</label>
                  </div>
                  <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="remarks[]" value="Transportation"
                          <?php if (in_array("Transportation", $remarksArray)) echo 'checked'; ?>>
                      <label class="form-check-label">Transportation</label>
                  </div>
                  <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="remarks[]" value="Food and beverages"
                          <?php if (in_array("Food and beverages", $remarksArray)) echo 'checked'; ?>>
                      <label class="form-check-label">Food and beverages</label>
                  </div>
              </div>

              <div class="mb-3" id="allowanceAmountDiv" style="<?php echo (in_array("Allowance", $remarksArray)) ? '' : 'display: none;'; ?>">
                  <label class="form-label">Allowance Amount</label>
                  <input type="text" class="form-control" name="allowance_amount" placeholder="Enter the allowance amount"
                      value="<?php echo htmlspecialchars($allowance_amount); ?>">
              </div>

              <div class="mb-3">
                  <label class="form-label">Attachment (Upload File)</label>
                  <input type="file" class="form-control" name="attachment">
                  <?php if (!empty($attachment)) : ?>
                      <p>Current Attachment: <a href="<?php echo $attachment; ?>" target="_blank">Download</a></p>
                  <?php endif; ?>
              </div>
          </fieldset>

          <input type="hidden" name="which_form" value="1. LI_BORANG MAKLUM BALAS INDUSTRI NEW (Sebelum Latihan Industri)">

          <div class="d-flex gap-2">
              <button type="submit" name="save" class="btn btn-secondary w-50">Save</button>
              <button type="submit" name="submit" class="btn btn-primary w-50">Submit</button>
          </div>
      </form>

    </div>

<script>
    function toggleAllowanceField() {
    var allowanceCheckbox = document.getElementById("checkAllowance");
    var allowanceDiv = document.getElementById("allowanceAmountDiv");

    // Show the "Allowance Amount" field only if "Allowance" is checked
    if (allowanceCheckbox.checked) {
        allowanceDiv.style.display = "block";
    } else {
        allowanceDiv.style.display = "none";
    }
}
</script>
</body>
</html>
