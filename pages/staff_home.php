<?php
$role = $_SESSION['role'];
$my_dept = isset($_SESSION['department']) ? $_SESSION['department'] : ''; // Current Staff's Department
$msg = '';

// Handle Approval/Rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $req_id = $_POST['req_id'];
    $action = $_POST['action']; // approve or reject
    $status_val = ($action == 'approve') ? 'approved' : 'rejected';
    
    // Update logic based on role
    if ($role == 'coordinator') {
        $sql = "UPDATE requests SET coordinator_status='$status_val' WHERE id='$req_id'";
    } elseif ($role == 'hod') {
        $sql = "UPDATE requests SET hod_status='$status_val' WHERE id='$req_id'";
    } elseif ($role == 'gate') {
        $sql = "UPDATE requests SET gate_status='$status_val', status='$status_val' WHERE id='$req_id'";
    }

    if ($conn->query($sql)) {
        if ($status_val == 'rejected') {
            $conn->query("UPDATE requests SET status='rejected' WHERE id='$req_id'");
        }
        $msg = "Request updated successfully";
    } else {
        $msg = "Error updating request";
    }
}

// Helper to get Department and filter
// Correct Dept derivation query for user
$dept_column = "CASE WHEN u.role = 'student' THEN u.department 
                     WHEN u.role = 'parent' THEN (SELECT s.department FROM users s WHERE s.student_reg_no = u.student_reg_no AND s.role = 'student' LIMIT 1) 
                     ELSE 'N/A' END as user_dept"; // Changed alias to user_dept

$pending_sql = "";

if ($role == 'coordinator') {
    // Coord sees Check-outs for THEIR Department
    $pending_sql = "SELECT r.*, u.name as uname, u.role as urole, $dept_column 
                    FROM requests r 
                    JOIN users u ON r.user_id = u.id 
                    HAVING user_dept = '$my_dept' AND coordinator_status = 'pending' AND type = 'check_out'";
    // Note: 'HAVING' is used because user_dept is an alias. 
    // Alternatively, I can wrap it, but for simple SQL this works or I need to repeat the subquery in WHERE.
    // Repeating subquery in WHERE is safer for older MySQL versions if HAVING is strict.
    // Let's use WHERE with repeated Logic for robustness:
    /*
      WHERE (
        (u.role='student' AND u.department='$my_dept') OR 
        (u.role='parent' AND (SELECT s.department FROM users s WHERE s.student_reg_no=u.student_reg_no LIMIT 1)='$my_dept')
      )
    */
    $dept_filter = "AND (
        (u.role='student' AND u.department='$my_dept') OR 
        (u.role='parent' AND (SELECT s.department FROM users s WHERE s.student_reg_no=u.student_reg_no LIMIT 1)='$my_dept')
    )";

    $pending_sql = "SELECT r.*, u.name as uname, u.role as urole, $dept_column 
                    FROM requests r 
                    JOIN users u ON r.user_id = u.id 
                    WHERE r.coordinator_status = 'pending' 
                    AND r.type = 'check_out' $dept_filter";

} elseif ($role == 'hod') {
    // HOD sees Check-outs (Coord Approved) for THEIR Department
    $dept_filter = "AND (
        (u.role='student' AND u.department='$my_dept') OR 
        (u.role='parent' AND (SELECT s.department FROM users s WHERE s.student_reg_no=u.student_reg_no LIMIT 1)='$my_dept')
    )";

    $pending_sql = "SELECT r.*, u.name as uname, u.role as urole, $dept_column 
                    FROM requests r 
                    JOIN users u ON r.user_id = u.id 
                    WHERE r.coordinator_status = 'approved' 
                    AND r.hod_status = 'pending' 
                    AND r.type = 'check_out' $dept_filter";

} elseif ($role == 'gate') {
    // Gate sees ALL (No Dept Filter Needed)
    // Check-ins (Pending) OR Check-outs (HOD Approved)
    $pending_sql = "SELECT r.*, u.name as uname, u.role as urole, $dept_column
                    FROM requests r 
                    JOIN users u ON r.user_id = u.id 
                    WHERE r.gate_status = 'pending' 
                    AND (
                        (r.type = 'check_in') OR 
                        (r.type = 'check_out' AND r.hod_status = 'approved')
                    )";
}

$pending = $conn->query($pending_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<!-- Dynamic Theme: Gate gets 'theme-gate', others get 'theme-staff' -->
<body class="<?php echo ($role == 'gate') ? 'theme-gate' : 'theme-staff'; ?>">
    <div class="dashboard-container">
        <div class="sidebar">
            <h3><?php echo ucfirst($role); ?> Panel</h3>
            <?php if($role != 'gate'): ?>
            <small style="display:block; text-align:center; color:#888; margin-bottom:10px;">(<?php echo $my_dept; ?>)</small>
            <?php endif; ?>
            <a href="#" class="nav-link active">Pending Requests</a>
            <a href="inc/logout.php" class="logout-btn">Logout</a>
        </div>
        <div class="main-content">
            <div class="header">
                <h2>Pending Approvals</h2>
                <span class="status-badge status-completed"><?php echo ucfirst($role); ?></span>
            </div>

            <?php if($msg): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <?php if($pending && $pending->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Dept</th>
                                <th>Type</th>
                                <th>Reason</th>
                                <th>DateTime</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $pending->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['uname']; ?></td>
                                <td><?php echo strtoupper($row['urole']); ?></td>
                                <td><?php echo $row['user_dept']; ?></td>
                                <td><?php echo strtoupper(str_replace('_', ' ', $row['type'])); ?></td>
                                <td><?php echo $row['reason']; ?></td>
                                <td><?php echo $row['out_date'] . ' ' . $row['out_time']; ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="req_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="action" value="approve" style="background:#28a745; color:white; border:none; padding:8px 15px; border-radius:5px; cursor:pointer;">Approve</button>
                                        <button type="submit" name="action" value="reject" style="background:#dc3545; color:white; border:none; padding:8px 15px; border-radius:5px; cursor:pointer;">Reject</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align:center; color:#888;">No pending requests found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
