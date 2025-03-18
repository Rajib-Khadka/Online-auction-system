<?php
require('../Config/dbconnect.php'); // Adjust the path as per your file structure

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['company_name'])) {
    header("Location: ../Logins/companylogin.php");
    exit();
}

$company_name = $_SESSION['company_name'];

// Prepare and execute the delete query
$sql = "DELETE FROM company WHERE name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $company_name);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // Successfully deleted
    session_destroy(); // Destroy the session
    header("Location: ../Logins/companysignup.php"); // Redirect to login page
    exit();
} else {
    // Handle errors
    $_SESSION['company_pass_notchanged'] = "Error deleting account.";
    header("Location: setting.php");
    exit();
}

?>