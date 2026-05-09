<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
$student_id = $_SESSION['student_id'];

// Query for notifications
$notif_stmt = $conn->prepare("SELECT message, created_at FROM notifications WHERE student_id = ? ORDER BY created_at DESC LIMIT 5");
$notif_stmt->bind_param("i", $student_id);
$notif_stmt->execute();
$notifications = $notif_stmt->get_result();
$notif_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 1000px;">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['student_name']); ?>!</h1>
        <p><a href="complaint.php" style="background: var(--primary-color); color: white; padding: 10px 20px; border-radius: 8px; display:inline-flex; align-items:center; gap:5px;"><span class="material-icons">add_circle</span> Submit a New Complaint</a> | <a href="logout.php">Logout</a></p>
        
        <!-- Notifications -->
        <?php if ($notifications->num_rows > 0): ?>
        <div style="background: #EBF8FF; border-left: 4px solid var(--info-color); padding: 15px; margin-bottom: 25px; border-radius: 4px;">
            <h3 style="color: var(--primary-color); margin-bottom: 10px; font-size: 16px; display:flex; align-items:center; gap:5px;"><span class="material-icons" style="color: var(--warning-color);">notifications_active</span> Recent Updates</h3>
            <ul style="list-style-type: none; padding-left: 0;">
                <?php while($n = $notifications->fetch_assoc()): ?>
                    <li style="margin-bottom: 8px; font-size: 14px; color: var(--text-color);">
                        <span style="color: var(--subtle-text); font-size: 12px; margin-right: 5px;"><?php echo date('M j, g:i A', strtotime($n['created_at'])); ?></span> 
                        <?php echo htmlspecialchars($n['message']); ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
        <?php endif; ?>

        <h2>My Complaint History</h2>
        <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Subject</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("SELECT id, category, subject, description, status, created_at, attachment, admin_note FROM complaints WHERE student_id = ? ORDER BY created_at DESC");
                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        
                        $noteHTML = '';
                        if (!empty($row["admin_note"])) {
                            $noteHTML = "<div style='margin-top: 10px; padding: 10px; background: #F8FAFC; border-left: 3px solid var(--accent-color); font-size: 13px; border-radius: 4px;'><strong>Admin Note:</strong> " . htmlspecialchars($row["admin_note"]) . "</div>";
                        }
                        
                        $attachmentLink = '';
                        if (!empty($row["attachment"])) {
                            $attachmentLink = "<br><a href='" . htmlspecialchars($row["attachment"]) . "' target='_blank' style='display:inline-flex; align-items:center; gap:4px; font-size:12px; margin-top:8px;'><span class='material-icons' style='font-size:16px;'>attach_file</span> View Proof</a>";
                        }

                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . htmlspecialchars($row["category"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["subject"]) . "</td>";
                        echo "<td class='description-cell' title='" . htmlspecialchars($row["description"]) . "' style='white-space: normal;'>" . htmlspecialchars($row["description"]) . $attachmentLink . $noteHTML . "</td>";
                        echo "<td><span class='status-badge status-" . str_replace(' ', '-', $row["status"]) . "'>" . htmlspecialchars($row["status"]) . "</span></td>";
                        echo "<td>" . date('M j, Y g:i A', strtotime($row["created_at"])) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>You have not submitted any complaints yet.</td></tr>";
                }
                $stmt->close();
                $conn->close();
                ?>
            </tbody>
        </table>
        </div>
    </div>
</body>
</html>