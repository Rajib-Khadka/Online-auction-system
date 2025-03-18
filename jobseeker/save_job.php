<?php
session_start();

include '../Config/dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Logins/jobseekerlogin.php");
    exit();
}

if (isset($_POST['save_job'])) {
    $job_id = $_POST['job_id'];
    $user_id = $_SESSION['user_id'];

    // Check if job is already saved
    $check_sql = "SELECT * FROM saved_jobs WHERE job_id = '$job_id' AND user_id = '$user_id'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $_SESSION['applied_error'] = "You have already saved this job.";
    } else {
        // Save the job
        $save_sql = "INSERT INTO saved_jobs (job_id, user_id, saved_at) VALUES ('$job_id', '$user_id', NOW())";
        if ($conn->query($save_sql) === TRUE) {
            $_SESSION['applied_success'] = "Job saved successfully!";
        } else {
            $_SESSION['applied_error'] = "Failed to save the job.";
        }
    }

    // Redirect back to job details page
    header("Location: job_details.php?id=$job_id");
    exit();
}
?>