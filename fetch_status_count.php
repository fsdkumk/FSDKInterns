<?php
header("Content-Type: application/json");

// Database connection
include("db_connect.php");

// Count students for each status
$query = "SELECT status, COUNT(*) as count FROM students GROUP BY status";
$result = $conn->query($query);

$status_counts = [
    "Haven't got any LI yet" => 0,
    "At least get 1 LI" => 0,
    "Already confirmed / decided" => 0
];

while ($row = $result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}

$conn->close();

// Return data as JSON
echo json_encode($status_counts);
?>
