<?php
require_once 'inc/auth.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$role = strtolower(trim($_SESSION['role'] ?? ''));

// Router logic
switch ($role) {
    case 'student':
        include 'pages/student_home.php';
        break;
    case 'parent':
        include 'pages/parent_home.php';
        break;
    case 'coordinator':
    case 'hod':
    case 'gate':
        include 'pages/staff_home.php';
        break;
    default:
        // Debugging output
        echo "Error: Unknown Role detected: [" . htmlspecialchars($role) . "]";
        echo "<br><a href='inc/logout.php'>Logout and try again</a>";
        exit();
}
?>
