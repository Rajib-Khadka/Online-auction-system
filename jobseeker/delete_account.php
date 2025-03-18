<?php
require('../Config/dbconnect.php'); // Adjust the path as per your file structure

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Logins/jobseekerlogin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Prepare and execute the delete query
$sql = "DELETE FROM user WHERE userid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // Successfully deleted
    session_destroy(); // Destroy the session
    header("Location: ../Logins/jobseekersignup.php"); // Redirect to login page
    exit();
} else {
    // Handle errors
    $_SESSION['user_pass_notchanged'] = "Error deleting account.";
    header("Location: setting.php");
    exit();
}

?>