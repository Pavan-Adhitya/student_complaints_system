<?php
// --- 1. SECURITY AND SESSION ---
session_start();
// This is the security guard. If an admin is not logged in, it redirects to the login page.
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// --- 2. DATABASE CONNECTION AND DATA FETCHING ---
include 'db.php';

// --- Get Statistics for the cards ---
$pending_count = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status = 'Pending'")->fetch_assoc()['count'];
$in_progress_count = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status = 'In Progress'")->fetch_assoc()['count'];
$resolved_count = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status = 'Resolved'")->fetch_assoc()['count'];

// --- Handle Filtering Logic ---
// Check if a filter has been set in the URL (e.g., admin.php?filter=Pending)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'All';
$sql = "SELECT id, student_name, category, subject, description, status, created_at, is_anonymous, attachment, admin_note FROM complaints";

// Modify the SQL query based on the selected filter
if ($filter == 'Pending') {
    $sql .= " WHERE status = 'Pending'";
} elseif ($filter == 'In Progress') {
    $sql .= " WHERE status = 'In Progress'";
} elseif ($filter == 'Resolved') {
    $sql .= " WHERE status = 'Resolved'";
}

$sql .= " ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Complaints</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container admin-container">
        <h1>Admin Dashboard</h1>
        <p style="text-align: center; margin-top: -20px; margin-bottom: 30px;">
            Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>! 
            <a href="logout.php">Logout</a>
        </p>

        <!-- Statistics Section -->
        <h2>At a Glance</h2>
        <div class="stat-container">
            <div class="stat-card pending">
                <h3>Pending</h3>
                <p><?php echo $pending_count; ?></p>
            </div>
            <div class="stat-card in-progress">
                <h3>In Progress</h3>
                <p><?php echo $in_progress_count; ?></p>
            </div>
            <div class="stat-card resolved">
                <h3>Resolved</h3>
                <p><?php echo $resolved_count; ?></p>
            </div>
        </div>

        <!-- Filtering Navigation -->
        <h2><?php echo htmlspecialchars($filter); ?> Complaints</h2>
        <div class="filter-nav">
            <a href="admin.php" class="<?php if($filter == 'All') echo 'active'; ?>">All</a>
            <a href="admin.php?filter=Pending" class="<?php if($filter == 'Pending') echo 'active'; ?>">Pending</a>
            <a href="admin.php?filter=In Progress" class="<?php if($filter == 'In Progress') echo 'active'; ?>">In Progress</a>
            <a href="admin.php?filter=Resolved" class="<?php if($filter == 'Resolved') echo 'active'; ?>">Resolved</a>
        </div>

        <!-- Admin Actions & Search -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <a href="export_csv.php" style="background-color: var(--success-color); color: white; padding: 10px 20px; border-radius: 8px; font-weight: 600; display:flex; align-items:center; gap:8px; text-decoration: none; box-shadow: var(--shadow-sm);"><span class="material-icons">download</span> Export CSV Report</a>
            <input type="text" id="searchInput" placeholder="🔍 Search complaints..." style="width: 100%; max-width: 350px; margin-bottom: 0; border-radius: 30px; padding: 12px 20px;">
        </div>

        <!-- Complaints Table -->
        <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student Name</th>
                    <th>Category</th>
                    <th>Subject</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if there are any results to display
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        
                        $displayName = htmlspecialchars($row["student_name"]);
                        if ($row["is_anonymous"]) {
                            $displayName = "<span style='color: var(--subtle-text); font-style: italic; display:flex; align-items:center; gap:4px;'><span class='material-icons' style='font-size:16px;'>security</span> Anonymous</span>";
                        }

                        $attachmentLink = '';
                        if (!empty($row["attachment"])) {
                            $attachmentLink = "<br><a href='" . htmlspecialchars($row["attachment"]) . "' target='_blank' style='display:inline-flex; align-items:center; gap:4px; font-size:12px; margin-top:8px;'><span class='material-icons' style='font-size:16px;'>attach_file</span> View Proof</a>";
                        }

                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $displayName . "</td>";
                        echo "<td>" . htmlspecialchars($row["category"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["subject"]) . "</td>";
                        echo "<td class='description-cell' title='" . htmlspecialchars($row["description"]) . "' style='white-space: normal;'>" . htmlspecialchars($row["description"]) . $attachmentLink . "</td>";
                        echo "<td><span class='status-badge status-" . str_replace(' ', '-', $row["status"]) . "'>" . htmlspecialchars($row["status"]) . "</span></td>";
                        echo "<td>" . date('M j, Y g:i A', strtotime($row["created_at"])) . "</td>";
                        echo '<td>
                                <form action="update_status.php" method="POST" style="display: flex; flex-direction: column; gap: 8px; min-width: 220px;">
                                    <input type="hidden" name="complaint_id" value="' . $row["id"] . '">
                                    <select name="new_status" style="margin:0; padding:8px; width:100%;">
                                        <option value="Pending"' . ($row["status"] == "Pending" ? " selected" : "") . '>Pending</option>
                                        <option value="In Progress"' . ($row["status"] == "In Progress" ? " selected" : "") . '>In Progress</option>
                                        <option value="Resolved"' . ($row["status"] == "Resolved" ? " selected" : "") . '>Resolved</option>
                                    </select>
                                    <textarea name="admin_note" placeholder="Add an admin note/comment..." rows="2" style="margin:0; padding:8px; font-size:13px; width:100%; resize:vertical;">' . htmlspecialchars($row["admin_note"] ?? '') . '</textarea>
                                    <button type="submit" style="padding:8px; font-size:13px; margin:0; width:100%;"><span class="material-icons" style="font-size:16px;">send</span> Update & Notify</button>
                                </form>
                              </td>';
                        echo "</tr>";
                    }
                } else {
                    // Display a message if no complaints are found for the current filter
                    echo "<tr><td colspan='8'>No complaints found for this filter.</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- Search Script -->
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
</body>
</html>