<?php
// We must start the session to access the logged-in student's ID
session_start();
include 'db.php'; // Include the database connection

// Security Check: Is a user logged in?
if (!isset($_SESSION['student_id'])) {
    // If not, they shouldn't be here. Send them to the login page.
    die("Access denied. Please <a href='login.php'>login</a>.");
}

// Check if the form was submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Get the student's ID from the session (the "VIP pass")
    $student_id = $_SESSION['student_id'];

    // 2. Get the rest of the data from the form
    $category = htmlspecialchars($_POST['category']);
    $subject = htmlspecialchars($_POST['subject']);
    $description = htmlspecialchars($_POST['description']);
    
    // We get the student's name and email from the database for consistency, not the form
    // (This part is an improvement and not strictly required, but it's good practice)
    $stmt = $conn->prepare("SELECT full_name, email FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $student_name = $student['full_name'];
    $student_email = $student['email'];

    // 3. Prepare the new SQL INSERT statement, now including student_id
    $stmt = $conn->prepare("INSERT INTO complaints (student_id, student_name, student_email, category, subject, description) VALUES (?, ?, ?, ?, ?, ?)");
    
    // 4. Bind the parameters. The "isssss" tells the database the data types:
    // i = integer (for student_id)
    // s = string (for everything else)
    $stmt->bind_param("isssss", $student_id, $student_name, $student_email, $category, $subject, $description);

    // Execute the statement and show a success or error message
    if ($stmt->execute()) {
        // Redirect to the dashboard so they can see their newly submitted complaint
        header("Location: dashboard.php?status=success");
        exit();
    } else {
        echo "<h1>Error</h1>";
        echo "<p>Something went wrong. Please try again later.</p>";
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // If someone tries to access this page directly, send them away.
    header("Location: dashboard.php");
    exit();
}
?>