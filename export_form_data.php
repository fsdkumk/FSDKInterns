<?php
// Database connection
include 'db_connect.php';

// Set headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=form_data.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Column headers (you can customize based on table structure)
fputcsv($output, [
    'ID', 'Student Name', 'Programme', 'Duration', 'Email', 'Phone', 'Feedback',
    'Reporting Date', 'Reporting Time', 'Industry Name', 'Industry Address',
    'State', 'Supervisor Name', 'Supervisor Phone', 'Supervisor Email',
    'Remarks', 'Allowance Amount', 'Created At', 'Which Form'
]);

// Fetch and output data
$sql = "SELECT * FROM form";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
$conn->close();
exit;
?>
