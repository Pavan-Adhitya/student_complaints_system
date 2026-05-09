<?php
// This is the bouncer/security guard. It checks if the user is logged in.
session_start();
if (!isset($_SESSION['student_id'])) {
    // If they aren't logged in, send them to the login page.
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit a New Complaint</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- We can greet the student by name, since we know who they are! -->
        <h1>New Complaint Form for <?php echo htmlspecialchars($_SESSION['student_name']); ?></h1>
        <p>Please fill out the form below to submit your complaint.</p>
        
        <!-- This form submits to our modified PHP file -->
        <form action="submit_complaint.php" method="POST">
            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="">--Select a Category--</option>
                <option value="Academic">Academic</option>
                <option value="Infrastructure">Infrastructure</option>
                <option value="Faculty">Faculty</option>
                <option value="Hostel & Accommodation">Hostel & Accommodation</option>
                <option value="Food & Mess">Food & Mess</option>
                <option value="Financial & Fees">Financial & Fees</option>
                <option value="Administration">Administration</option>
                <option value="Library & Resources">Library & Resources</option>
                <option value="Campus Security">Campus Security</option>
                <option value="Health & Wellness">Health & Wellness</option>
                <option value="Other">Other</option>
            </select>

            <div id="other_category_group" style="display: none; margin-top: -10px; margin-bottom: 20px;">
                <label for="other_category">Please specify your category</label>
                <input type="text" id="other_category" name="other_category" placeholder="Enter specific category" style="margin-bottom: 0;">
            </div>
            
            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" required>

            <label for="description">Detailed Description</label>
            <textarea id="description" name="description" rows="6" required></textarea>
            
            <button type="submit">Submit Complaint</button>
        </form>
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>

    <script>
        const categorySelect = document.getElementById('category');
        const otherCategoryGroup = document.getElementById('other_category_group');
        const otherCategoryInput = document.getElementById('other_category');

        categorySelect.addEventListener('change', function() {
            if (this.value === 'Other') {
                otherCategoryGroup.style.display = 'block';
                otherCategoryInput.required = true;
            } else {
                otherCategoryGroup.style.display = 'none';
                otherCategoryInput.required = false;
                otherCategoryInput.value = '';
            }
        });
    </script>
</body>
</html>