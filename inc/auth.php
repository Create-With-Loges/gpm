<?php
session_start();
require_once 'db.php';

function login($email, $password) {
    global $conn;
    $email = $conn->real_escape_string($email);
    // Plain text password comparison as requested
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['department'] = $user['department']; // Store department
        return true;
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function logout() {
    session_destroy();
    redirect('../index.php');
}
?>
