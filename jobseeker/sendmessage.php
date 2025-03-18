<?php
require('../Config/dbconnect.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Logins/jobseekerlogin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);
    $company_id = 1; // Hardcoded for demo purposes; replace with dynamic receiver ID
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    if (!empty($message)) {
        $query = "INSERT INTO messages (sender_id, receiver_id, message, is_read) 
                  VALUES ('$user_id', '$company_id', '$message', 0)";
        mysqli_query($conn, $query);
    }
}

header("Location: message.php");
exit();