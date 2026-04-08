<?php
require_once 'inc/auth.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (login($email, $password)) {
        redirect('dashboard.php');
    } else {
        $error = "Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - OutPass Monitor</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="theme-auth">
    <div class="auth-container">
        <h2>Welcome Back</h2>
        <?php if($error): ?>
            <div style="color: red; margin-bottom: 15px; background: #ffe6e6; padding: 10px; border-radius: 5px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="Enter your email">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter password">
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <div class="links">
            <p>Don't have an account?</p>
            <a href="register_student.php">Register as Student</a> | 
            <a href="register_parent.php">Register as Parent</a>
        </div>
    </div>
</body>
</html>
