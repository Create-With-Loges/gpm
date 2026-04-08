<?php
$msg = '';
$uid = $_SESSION['user_id'];

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reason = $_POST['reason'];
    $date = $_POST['out_date'];
    $time = $_POST['out_time'];
    
    // Default statuses
    $c_status = 'pending';
    $h_status = 'pending';
    
    if (isset($_POST['req_checkin'])) {
        $type = 'check_in';
        // Check-in: Sent to Gate. Coord/HOD auto-approved/bypassed.
        $c_status = 'approved'; 
        $h_status = 'approved'; 
        // Gate status remains pending.
        $req_sql = "INSERT INTO requests (user_id, type, reason, out_date, out_time, status, coordinator_status, hod_status) 
                    VALUES ('$uid', '$type', '$reason', '$date', '$time', 'pending', '$c_status', '$h_status')";
    } elseif (isset($_POST['req_checkout'])) {
        $type = 'check_out';
        // Check-out (Parent): Streamlined to go straight to Gate (Visitor Logic)
        // Auto-approve Coord and HOD so it appears in Gate's dashboard pending list
        $c_status = 'approved'; 
        $h_status = 'approved'; 
        $req_sql = "INSERT INTO requests (user_id, type, reason, out_date, out_time, status, coordinator_status, hod_status) 
                    VALUES ('$uid', '$type', '$reason', '$date', '$time', 'pending', '$c_status', '$h_status')";
    }

    if ($conn->query($req_sql)) {
        $msg = "Request submitted successfully!";
    } else {
        $msg = "Error: " . $conn->error;
    }
}

// Fetch history
$hist_sql = "SELECT * FROM requests WHERE user_id='$uid' ORDER BY created_at DESC";
$history = $conn->query($hist_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parent Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="theme-parent">
    <div class="dashboard-container">
        <div class="sidebar">
            <h3>Parent Panel</h3>
            <a href="#" class="nav-link active">Dashboard</a>
            <a href="inc/logout.php" class="logout-btn">Logout</a>
        </div>
        <div class="main-content">
            <div class="header">
                <h2>Welcome, <?php echo $_SESSION['name']; ?></h2>
                <span class="status-badge status-completed">Parent</span>
            </div>

            <?php if($msg): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <!-- Gate Pass Logic for Active Request -->
            <?php 
            $pass_sql = "SELECT r.*, u.name, u.student_reg_no 
                         FROM requests r 
                         JOIN users u ON r.user_id = u.id 
                         WHERE r.user_id = '$uid' 
                         AND r.gate_status = 'pending' 
                         ORDER BY r.created_at DESC LIMIT 1";
            // Note: For parents, we show newest pending gate request
            $pass_res = $conn->query($pass_sql);
            if ($pass_res && $pass_res->num_rows > 0): 
                $active_pass = $pass_res->fetch_assoc();
                // Parents are auto-approved for C/H status on checkout, or Check-In triggers entry.
                // We show pass if it's ready for Gate.
            ?>
                <div class="card">
                    <div class="gate-pass">
                        <h1>Gate Pass (<?php echo strtoupper(str_replace('_', ' ', $active_pass['type'])); ?>)</h1>
                        <div class="approved-stamp">AUTHORIZED</div>
                        <div class="pass-details">
                            <p><strong>Parent Name:</strong> <?php echo $active_pass['name']; ?></p>
                            <p><strong>Student Ref:</strong> <?php echo $active_pass['student_reg_no']; ?></p>
                            <p><strong>Date:</strong> <?php echo $active_pass['out_date']; ?></p>
                            <p><strong>Time:</strong> <?php echo $active_pass['out_time']; ?></p>
                            <p><strong>Reason:</strong> <?php echo $active_pass['reason']; ?></p>
                        </div>
                        <p style="color: #888;">Proceed to Gate.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div style="display: flex; gap: 20px;">
                <div class="card" style="flex: 1;">
                    <h3>Request Check-IN (Entry)</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Reason for Visit</label>
                            <input type="text" name="reason" required placeholder="Meeting / Pick up...">
                        </div>
                        <div class="form-group">
                            <label>Date/Time of Arrival</label>
                            <div style="display:flex; gap:10px;">
                                <input type="date" name="out_date" required>
                                <input type="time" name="out_time" required>
                            </div>
                        </div>
                        <button type="submit" name="req_checkin" class="btn">Submit Entry Request</button>
                    </form>
                </div>
                <div class="card" style="flex: 1;">
                    <h3>Request Check-OUT (Exit)</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Reason for Leaving</label>
                            <input type="text" name="reason" required placeholder="Leaving campus because...">
                        </div>
                         <div class="form-group">
                            <label>Date/Time of Departure</label>
                            <div style="display:flex; gap:10px;">
                                <input type="date" name="out_date" required>
                                <input type="time" name="out_time" required>
                            </div>
                        </div>
                        <button type="submit" name="req_checkout" class="btn" style="background: linear-gradient(90deg, #ff9966, #ff5e62);">Submit Exit Request</button>
                    </form>
                </div>
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
                            <th>Approvals (C / H / G)</th>
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
                                C: <?php echo $row['coordinator_status']; ?> | 
                                H: <?php echo $row['hod_status']; ?> | 
                                G: <?php echo $row['gate_status']; ?>
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
