<?php
// The password you want to use.
$password = 'admin123';

// Hash the password using the best available algorithm.
$hash = password_hash($password, PASSWORD_BCRYPT);

// Display the hash.
echo "<h1>New Password Hash</h1>";
echo "<p>Copy this entire line of text and use it to update the password in the database:</p>";
echo "<hr>";
echo "<code>" . $hash . "</code>";
?>