<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    die("Access denied.");
}

include 'db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="complaints_report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Output column headings
fputcsv($output, array('ID', 'Student Name', 'Email', 'Category', 'Subject', 'Description', 'Status', 'Is Anonymous', 'Admin Note', 'Submitted At'));

// Fetch the data
$sql = "SELECT id, student_name, student_email, category, subject, description, status, is_anonymous, admin_note, created_at FROM complaints ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['is_anonymous']) {
            $row['student_name'] = 'Anonymous';
            $row['student_email'] = 'Hidden';
        }
        fputcsv($output, $row);
    }
}

fclose($output);
$conn->close();
?>
