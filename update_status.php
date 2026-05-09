<?php
// All 'use' statements must be at the very top of the file.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP; // <-- The debug 'use' statement goes here.

// Require the autoloader from Composer
require 'vendor/autoload.php';

// Include your database connection
include 'db.php';

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get the data from the form
    $complaint_id = intval($_POST['complaint_id']);
    $new_status = htmlspecialchars($_POST['new_status']);
    $admin_note = htmlspecialchars($_POST['admin_note'] ?? '');
    
    // --- Step 1: Find the student's email and ID associated with this complaint ---
    // We do this BEFORE updating, in case the update fails.
    $stmt = $conn->prepare("SELECT s.id as student_id, s.email FROM students s JOIN complaints c ON s.id = c.student_id WHERE c.id = ?");
    $stmt->bind_param("i", $complaint_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $student_email = $student['email']; // The recipient's email address
    $student_id = $student['student_id'];

    // --- Step 2: Update the status and admin note in the database ---
    $stmt = $conn->prepare("UPDATE complaints SET status = ?, admin_note = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_status, $admin_note, $complaint_id);
    
    // Check if the database update was successful AND we found an email address
    if ($stmt->execute() && $student_email) {
        
        // --- Step 2.5: Create In-App Notification ---
        if ($student_id) {
            $msg = "Your complaint (#$complaint_id) status changed to $new_status.";
            $notif_stmt = $conn->prepare("INSERT INTO notifications (student_id, complaint_id, message) VALUES (?, ?, ?)");
            $notif_stmt->bind_param("iis", $student_id, $complaint_id, $msg);
            $notif_stmt->execute();
        }
        
        // --- Step 3: Try to send the email notification ---
        $mail = new PHPMailer(true);
        
        // Add this one line to enable the powerful debug output:
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER; 

        try {
            // --- SERVER SETTINGS --- (REPLACE WITH YOUR DETAILS)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'pavanyasa5@gmail.com';     // Your full Gmail address
            $mail->Password   = 'ncowtmsymgubgtuk';  // Your 16-character App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // --- RECIPIENTS ---
            $mail->setFrom('no-reply@yourcollege.com', 'College Complaint System');
            $mail->addAddress($student_email); // Add the student's email as the recipient

            // --- EMAIL CONTENT ---
            $mail->isHTML(true);
            $mail->Subject = 'Update on your complaint #' . $complaint_id;
            
            $email_body = "Hello,<br><br>The status of your complaint (ID: <b>$complaint_id</b>) has been updated to: <b>$new_status</b>.<br><br>";
            if (!empty($admin_note)) {
                $email_body .= "<b>Admin Note:</b> $admin_note<br><br>";
            }
            $email_body .= "Thank you,<br>College Administration";
            
            $mail->Body    = $email_body;
            $mail->AltBody = "Hello, The status of your complaint (ID: $complaint_id) has been updated to: $new_status. Admin Note: $admin_note. Thank you, College Administration";
            
            $mail->send();
            
            // If the email sends successfully, redirect back to the admin page
            header("Location: admin.php?status=emailsent");
            exit();

        } catch (Exception $e) {
            // If the email FAILS, the debug output will show on the screen.
            // We can also add a custom error message.
            echo "<h1>Email Could Not Be Sent</h1>";
            echo "The complaint status was updated in the database, but the notification email failed to send.<br>";
            echo "Mailer Error: {$mail->ErrorInfo}";
            // We do NOT redirect here, so we can see the debug output.
        }

    } else {
        echo "Error: Could not update the complaint status or the student email was not found.";
    }

    $stmt->close();
    $conn->close();
}
?>