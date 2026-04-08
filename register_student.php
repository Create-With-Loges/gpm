<?php
require_once 'inc/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $reg_no = $_POST['reg_no'];
    $dept = $_POST['dept'];
    $batch = $_POST['batch'];

    // Basic Validation
    if (empty($name) || empty($email) || empty($password) || empty($reg_no)) {
        $error = "All fields are required";
    } else {
        // Insert
        $sql = "INSERT INTO users (name, email, password, role, student_reg_no, department, class_batch) 
                VALUES ('$name', '$email', '$password', 'student', '$reg_no', '$dept', '$batch')";
        
        if ($conn->query($sql) === TRUE) {
            $success = "Registration successful! You can now login.";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Registration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="theme-auth">
    <div class="auth-container">
        <h2>Student Registration</h2>
        <?php if($error): ?>
            <div style="color: red; margin-bottom: 15px; background: #ffe6e6; padding: 10px; border-radius: 5px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <?php if($success): ?>
            <div style="color: green; margin-bottom: 15px; background: #e6fffa; padding: 10px; border-radius: 5px;">
                <?php echo $success; ?> <a href="index.php">Login here</a>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Register Number (ID)</label>
                <input type="text" name="reg_no" required>
            </div>
            <div class="form-group">
                <label>Department</label>
                <input type="text" name="dept" required placeholder="e.g. CSE">
            </div>
            <div class="form-group">
                <label>Class/Batch</label>
                <input type="text" name="batch" required placeholder="e.g. 2024-2028">
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
        <div class="links">
            <a href="index.php">Back to Login</a>
        </div>
    </div>
</body>
</html>
