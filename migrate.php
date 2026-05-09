<?php
include 'db.php';

echo "<h1>Database Migration</h1>";

// 1. Add attachment column
$sql = "ALTER TABLE complaints ADD COLUMN attachment VARCHAR(255) DEFAULT NULL";
try {
    if ($conn->query($sql) === TRUE) {
        echo "<p>Added 'attachment' column successfully.</p>";
    }
} catch (Exception $e) {
    echo "<p>Note on 'attachment' (Already exists?): " . $e->getMessage() . "</p>";
}

// 2. Add is_anonymous column
$sql = "ALTER TABLE complaints ADD COLUMN is_anonymous TINYINT(1) DEFAULT 0";
try {
    if ($conn->query($sql) === TRUE) {
        echo "<p>Added 'is_anonymous' column successfully.</p>";
    }
} catch (Exception $e) {
    echo "<p>Note on 'is_anonymous' (Already exists?): " . $e->getMessage() . "</p>";
}

// 3. Add admin_note column
$sql = "ALTER TABLE complaints ADD COLUMN admin_note TEXT DEFAULT NULL";
try {
    if ($conn->query($sql) === TRUE) {
        echo "<p>Added 'admin_note' column successfully.</p>";
    }
} catch (Exception $e) {
    echo "<p>Note on 'admin_note' (Already exists?): " . $e->getMessage() . "</p>";
}

// 4. Create notifications table
$sql = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    complaint_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
try {
    if ($conn->query($sql) === TRUE) {
        echo "<p>Created 'notifications' table successfully.</p>";
    }
} catch (Exception $e) {
    echo "<p>Error creating 'notifications': " . $e->getMessage() . "</p>";
}

echo "<h3>Migration Complete! You can now test the new features.</h3>";
$conn->close();
?>
