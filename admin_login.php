<?php
session_start(); // Start the session

if (isset($_POST['login'])) {
    include 'db.php';
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, full_name, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        // Verify the hashed password
        if (password_verify($password, $admin['password'])) {
            // Password is correct, store admin info in the session
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            
            // Redirect to the main admin dashboard
            header("Location: admin.php");
            exit();
        }
    }
    $error = "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Administrator Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 500px; margin-top: 50px;">
        <h1>Administrator Login</h1>
        <form action="admin_login.php" method="POST">
            <label for="username">Username</label>
            <input type="text" name="username" required>

            <label for="password">Password</label>
            <input type="password" name="password" required>

            <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>

            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>