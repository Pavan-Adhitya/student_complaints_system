<?php
// We must start the session to access the logged-in student's ID
session_start();
include 'db.php'; // Include the database connection

// Security Check: Is a user logged in? (Or are they submitting the public form?)
if (!isset($_SESSION['student_id']) && !isset($_POST['student_name'])) {
    // If not logged in and not submitting public form, send them to login.
    die("Access denied. Please <a href='login.php'>login</a>.");
}

// Check if the form was submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Check if user is logged in or submitting as guest
    if (isset($_SESSION['student_id'])) {
        $student_id = $_SESSION['student_id'];
        
        // We get the student's name and email from the database for consistency
        $stmt = $conn->prepare("SELECT full_name, email FROM students WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $student_name = $student['full_name'];
        $student_email = $student['email'];
        $stmt->close();
    } else {
        // Guest submission from index.html
        $student_id = NULL; 
        $student_name = htmlspecialchars($_POST['student_name']);
        $student_email = htmlspecialchars($_POST['student_email']);
    }

    // 2. Get the rest of the data from the form
    $category = htmlspecialchars($_POST['category']);
    if ($category === 'Other' && !empty($_POST['other_category'])) {
        $category = htmlspecialchars($_POST['other_category']);
    }
    $subject = htmlspecialchars($_POST['subject']);
    $description = htmlspecialchars($_POST['description']);
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;

    // Handle File Upload
    $attachment = NULL;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $fileName = time() . '_' . basename($_FILES['attachment']['name']);
        $targetFilePath = $uploadDir . $fileName;
        
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        $allowTypes = array('jpg','png','jpeg','gif','pdf','doc','docx');
        
        if(in_array(strtolower($fileType), $allowTypes)){
            if(move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFilePath)){
                $attachment = $targetFilePath;
            }
        }
    }

    // 3. Prepare the new SQL INSERT statement, now including student_id, is_anonymous, and attachment
    $stmt = $conn->prepare("INSERT INTO complaints (student_id, student_name, student_email, category, subject, description, is_anonymous, attachment) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    // 4. Bind the parameters.
    $stmt->bind_param("isssssis", $student_id, $student_name, $student_email, $category, $subject, $description, $is_anonymous, $attachment);

    // Execute the statement and show a success or error message
    if ($stmt->execute()) {
        if ($student_id) {
            // Logged in user: Redirect to the dashboard
            header("Location: dashboard.php?status=success");
        } else {
            // Guest user (from index.html): Show a simple success message
            echo "<h1>Success!</h1>";
            echo "<p>Your complaint has been submitted successfully.</p>";
            echo "<p><a href='index.html'>Go back to the form</a></p>";
        }
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