<?php
$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_checkout'])) {
    $reason = $_POST['reason'];
    $date = $_POST['out_date'];
    $time = $_POST['out_time'];
    $uid = $_SESSION['user_id'];
    
    // Insert checkout request
    $sql = "INSERT INTO requests (user_id, type, reason, out_date, out_time, status) 
            VALUES ('$uid', 'check_out', '$reason', '$date', '$time', 'pending')";
    if ($conn->query($sql)) {
        $msg = "Check-out request submitted successfully!";
    } else {
        $msg = "Error: " . $conn->error;
    }
}

// Fetch history
$uid = $_SESSION['user_id'];
$hist_sql = "SELECT * FROM requests WHERE user_id='$uid' ORDER BY created_at DESC";
$history = $conn->query($hist_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="theme-student">
    <div class="dashboard-container">
        <div class="sidebar">
            <h3>Student Panel</h3>
            <a href="#" class="nav-link active">Dashboard</a>
            <a href="inc/logout.php" class="logout-btn">Logout</a>
        </div>
        <div class="main-content">
            <div class="header">
                <h2>Welcome, <?php echo $_SESSION['name']; ?></h2>
                <span class="status-badge status-completed">Student</span>
            </div>

            <?php if($msg): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <!-- Gate Pass Logic: Show if HOD Approved but NOT YET COMPLETED (Gate checked out) -->
            <?php 
            $pass_sql = "SELECT r.*, u.name, u.department, u.student_reg_no, u.class_batch 
                         FROM requests r 
                         JOIN users u ON r.user_id = u.id 
                         WHERE r.user_id = '$uid' 
                         AND r.type = 'check_out' 
                         AND r.hod_status = 'approved' 
                         AND r.status != 'completed' 
                         LIMIT 1";
            $pass_res = $conn->query($pass_sql);
            if ($pass_res && $pass_res->num_rows > 0): 
                $active_pass = $pass_res->fetch_assoc();
            ?>
                <div class="card">
                    <div class="gate-pass">
                        <h1>Gate Pass</h1>
                        <div class="approved-stamp">APPROVED</div>
                        <div class="pass-details">
                            <p><strong>Name:</strong> <?php echo $active_pass['name']; ?></p>
                            <p><strong>Reg No:</strong> <?php echo $active_pass['student_reg_no']; ?></p>
                            <p><strong>Dept:</strong> <?php echo $active_pass['department']; ?></p>
                            <p><strong>Class:</strong> <?php echo $active_pass['class_batch']; ?></p>
                            <p><strong>Valid Date:</strong> <?php echo $active_pass['out_date']; ?></p>
                            <p><strong>Time:</strong> <?php echo $active_pass['out_time']; ?></p>
                            <p><strong>Reason:</strong> <?php echo $active_pass['reason']; ?></p>
                        </div>
                        <p style="color: #888;">Show this to the Gate Keeper to scan/verify.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card">
                <h3>Request Check-Out</h3>
                <form method="POST" style="margin-top: 20px;">
                    <div class="form-group">
                        <label>Reason for Leaving</label>
                        <textarea name="reason" required placeholder="Enter detailed reason..."></textarea>
                    </div>
                    <div style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex:1;">
                            <label>Date</label>
                            <input type="date" name="out_date" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Time</label>
                            <input type="time" name="out_time" required>
                        </div>
                    </div>
                    <button type="submit" name="request_checkout" class="btn" style="width: auto; padding: 10px 30px;">Submit Request</button>
                </form>
            </div>

            <div class="card">
                <h3>My Request History</h3>
                <table>
                    <thead>
                        <tr>
                            <th>DateTime</th>
                            <th>Type</th>
                            <th>Reason</th>
                            <th>Status (Overall)</th>
                            <th>Approvals (Coord / HOD / Gate)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $history->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['out_date'] . ' ' . $row['out_time']; ?></td>
                            <td><?php echo strtoupper(str_replace('_', ' ', $row['type'])); ?></td>
                            <td><?php echo $row['reason']; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $row['status']; ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td>
                                <small>
                                    C: <?php echo $row['coordinator_status']; ?> | 
                                    H: <?php echo $row['hod_status']; ?> | 
                                    G: <?php echo $row['gate_status']; ?>
                                </small>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
