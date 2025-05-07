<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "fsdk");

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$lecturerName = $_SESSION['name'];

function getStatusCount($conn, $lecturerName, $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM students WHERE lecturer = ? AND status = ?");
    $stmt->bind_param("ss", $lecturerName, $status);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['total'];
}

echo json_encode([
    'havent' => getStatusCount($conn, $lecturerName, "Haven't got any LI yet"),
    'atleast' => getStatusCount($conn, $lecturerName, "At least get 1 LI"),
    'confirmed' => getStatusCount($conn, $lecturerName, "Already confirmed / decided")
]);

$conn->close();
?>

<script>
 // Toggle Sidebar and Main Content
const sidebarToggle = document.getElementById("sidebarToggle");
const sidebar = document.querySelector(".sidebar");
const mainContent = document.querySelector(".main-content");
const toggleIcon = sidebarToggle.querySelector("i");

sidebarToggle.addEventListener("click", () => {
    sidebar.classList.toggle("active");
    toggleIcon.classList.toggle("fa-bars");
    toggleIcon.classList.toggle("fa-times");
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

</script>
