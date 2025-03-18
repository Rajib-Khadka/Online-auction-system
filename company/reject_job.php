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

// Check if the user ID and job ID are set in the URL
if (!isset($_GET['userid'])) {
    die("User ID is not provided.");
}
if (!isset($_GET['job_id'])) {
    die("Job ID is not provided.");
}

// Get the user ID and job ID from the URL
$userId = $_GET['userid'];
$jobId = $_GET['job_id'];

// Prepare and execute the update statement
$sql = "UPDATE appliedjob SET status = 'Rejected' WHERE user_id = ? AND job_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $userId, $jobId); // Assuming job_id is an integer
if ($stmt->execute()) {
    $_SESSION['Application_accept'] = "Application rejected.";
} else {
    $_SESSION['Application_reject'] = "Error in rejecting application.";
}

$sql1 = "SELECT * FROM appliedjob WHERE user_id = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param('s', $userId); // Assuming application ID is an integer
$stmt1->execute();
$result = $stmt1->get_result();
$applyjob_info = $result->fetch_assoc();

$job_id = $applyjob_info['job_id'];



// Close the statement and connection
$stmt->close();
$conn->close();

// Optionally, redirect back to the job listing or another page
header("Location: viewjob.php?id=" . urlencode(string: $job_id));
exit();
?>