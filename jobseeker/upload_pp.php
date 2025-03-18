<?php
session_start();
require('../Config/dbconnect.php'); // Adjust the path as per your file structure

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pp'])) {
    $uploadSuccess = true;

    // Check if the file is a valid image
    $check = getimagesize($_FILES['pp']['tmp_name']);
    if ($check === false) {
        $uploadSuccess = false;
        $_SESSION['pp_update_error'] = 'File is not an image.';
    }

    // Check file size (e.g., 5MB limit)
    if ($_FILES['pp']['size'] > 5000000) {
        $uploadSuccess = false;
        $_SESSION['pp_update_error'] = 'File is too large.';
    }

    // Allow certain file formats
    $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
    $fileType = strtolower(pathinfo($_FILES['pp']['name'], PATHINFO_EXTENSION));
    if (!in_array($fileType, $allowedTypes)) {
        $uploadSuccess = false;
        $_SESSION['pp_update_error'] = 'Only JPG, JPEG, PNG & GIF files are allowed.';
    }

    if ($uploadSuccess) {
        // Read the file content
        $fileContent = file_get_contents($_FILES['pp']['tmp_name']);
        
        // Debug: Check if `user_id` is set and correct
        if (!isset($_SESSION['user_id'])) {
            die("Session error: user_id is not set.");
        }

        $userid = $_SESSION['user_id'];

        // Check if user exists in the database
        $userCheckSql = "SELECT * FROM user WHERE userid = ?";
        $userCheckStmt = $conn->prepare($userCheckSql);
        $userCheckStmt->bind_param("s", $userid);
        $userCheckStmt->execute();
        $userCheckResult = $userCheckStmt->get_result();

        // Ensure user exists
        if ($userCheckResult->num_rows === 0) {
            $_SESSION['pp_update_error'] = 'User does not exist in the database.';
            header('Location: setting.php');
            exit();
        } else {
            // Update profile picture (pp) in the database as a BLOB
            $sql = "UPDATE user SET pp = ? WHERE userid = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                die("SQL Error: " . $conn->error); // Check for preparation errors
            }

            // Bind `BLOB` as a string and user_id as a string
            $stmt->bind_param("bs", $null, $userid); // Temporarily bind null for `pp`
            $stmt->send_long_data(0, $fileContent); // Send the actual BLOB data as a stream
            
            if ($stmt->execute()) {
                $_SESSION['pp_update_success'] = 'Profile picture updated successfully.';
            } else {
                $_SESSION['pp_update_error'] = 'Database update failed: ' . $stmt->error;
            }

            $stmt->close();
        }

        $userCheckStmt->close();
    }

    header('Location: setting.php');
    exit();
}