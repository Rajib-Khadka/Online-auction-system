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

// Check if job ID is provided
if (!isset($_GET['id'])) {
    header("Location: managejob.php");
    exit();
}

$job_id = $_GET['id'];

// Prepare and execute the delete query
$delete_query = "DELETE FROM jobs WHERE id = ?";
$stmt = $conn->prepare($delete_query);

if ($stmt) {
    $stmt->bind_param("i", $job_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Success message
        $_SESSION['job_post_success'] = "Job listing deleted successfully.";
    } else {
        // Error message if no rows affected
        $_SESSION['job_post_error'] = "Failed to delete job listing. Please try again.";
    }

    $stmt->close();
} else {
    // Error message if statement preparation fails
    $_SESSION['job_post_error'] = "Failed to prepare the delete statement. Please try again.";
}

$conn->close();

// Redirect to manage jobs page
header("Location: managejob.php");
exit();
?>