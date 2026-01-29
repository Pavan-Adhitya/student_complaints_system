<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Register as a Student</h1>
        <form action="register.php" method="POST">
            <label for="full_name">Full Name</label>
            <input type="text" name="full_name" required>

            <label for="email">Email</label>
            <input type="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" name="password" required>

            <button type="submit" name="register">Register</button>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>

        <?php
        if (isset($_POST['register'])) {
            include 'db.php';
            $full_name = htmlspecialchars($_POST['full_name']);
            $email = htmlspecialchars($_POST['email']);
            // NEVER store plain text passwords. Always hash them.
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

            $stmt = $conn->prepare("INSERT INTO students (full_name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $full_name, $email, $password);
            
            if ($stmt->execute()) {
                echo "<p style='color:green;'>Registration successful! You can now login.</p>";
            } else {
                echo "<p style='color:red;'>Error: This email may already be registered.</p>";
            }
            $stmt->close();
            $conn->close();
        }
        ?>
    </div>
</body>
</html>