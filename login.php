<?php
session_start(); // Start the session at the very top

if (isset($_POST['login'])) {
    include 'db.php';
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, full_name, password FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();
        // Verify the hashed password
        if (password_verify($password, $student['password'])) {
            // Password is correct, store user info in session
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['full_name'];
            
            // Redirect to the new dashboard page
            header("Location: dashboard.php");
            exit();
        }
    }
    $error = "Invalid email or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Student Login</h1>
        <form action="login.php" method="POST">
            <label for="email">Email</label>
            <input type="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" name="password" required>

            <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>

            <button type="submit" name="login">Login</button>
            <p>Don't have an account? <a href="register.php">Register here</a>.</p>
        </form>
    </div>
</body>
</html>