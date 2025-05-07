<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is an admin or student
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$isStudent = isset($_SESSION['role']) && $_SESSION['role'] === 'student';

// Database connection
include("db_connect.php");

// Fetch forms
$query = "SELECT id, form_name, file_path, due_date, button_name, require_upload, redirect_url FROM before_li ORDER BY id DESC";
$result = $conn->query($query);
if (!$result) {
    die("Query error: " . $conn->error);
}


if ($result->num_rows > 0) {
    echo "<div class='form-list'>";
    while ($row = $result->fetch_assoc()) {
        $fileName = htmlspecialchars($row['form_name'] ?? ''); 
        $filePath = htmlspecialchars($row['file_path'] ?? ''); 
        $dueDate = htmlspecialchars($row['due_date'] ?? '');
        $buttonName = !empty($row['button_name']) ? htmlspecialchars($row['button_name']) : '';
        $requireUpload = isset($row['require_upload']) ? $row['require_upload'] : 'No';
        $redirectUrl = !empty($row['redirect_url']) ? htmlspecialchars($row['redirect_url']) : '';

        echo "<div class='form-card' id='form_{$row['id']}'>
                <div class='form-details'>
                   <a href='download.php?file=" . urlencode($filePath) . "&name=" . urlencode($fileName) . "' class='form-link'>$fileName</a>
                    <span class='due-date'><i class='fas fa-calendar-alt'></i> Due: " . $dueDate . "</span>
                </div>";

        // ✅ Ensure students see the correct button
        if ($isStudent) {
            if (!empty($buttonName)) {
                if ($requireUpload === 'Yes' && !empty($redirectUrl)) {
                    echo "<a href='$redirectUrl' class='dynamic-btn redirect-btn' target='_blank'>$buttonName</a>";
                } elseif ($requireUpload === 'No') {
                    echo "<button class='dynamic-btn upload-btn' data-bs-toggle='modal' data-bs-target='#uploadModal' data-id='{$row['id']}' data-form-name='" . htmlspecialchars($fileName, ENT_QUOTES) . "'>Upload</button>";

                }
            } else {
                if ($requireUpload === 'No') {
                    echo "<button class='dynamic-btn upload-btn' data-bs-toggle='modal' data-bs-target='#uploadModal' data-id='{$row['id']}' data-form-name='" . htmlspecialchars($fileName, ENT_QUOTES) . "'>Upload</button>";

                } else {
                    echo "<p style='color: red; font-weight: bold;'>⚠ Button Name Missing</p>";
                }
            }
        } elseif ($isAdmin) {
            echo "<button class='delete-btn' data-id='{$row['id']}'><i class='fas fa-trash'></i> Delete</button>";
        }

        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<p class='no-forms'>No forms available.</p>";
}

$conn->close();
?>

<script>
function handleButtonClick(formId) {
    alert("Button clicked for Form ID: " + formId);
    // Implement further functionality like navigation, modal popup, or submission
}
</script>

<?php if ($isAdmin): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Hide button name for admin dynamically
    document.querySelectorAll(".button-name").forEach(button => button.style.display = "none");

    // Delete Form Logic
    document.querySelectorAll(".delete-btn").forEach(button => {
        button.addEventListener("click", function () {
            const formId = this.getAttribute("data-id");
            if (confirm("Are you sure you want to delete this form?")) {
                fetch("delete_form.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: new URLSearchParams({ id: formId }),
                    credentials: "include"
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        alert("Form deleted successfully!");
                        location.reload();
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => console.error("Error deleting form:", error));
            }
        });
    });
});
</script>
<?php endif; ?>