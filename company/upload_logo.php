<?php
session_start();
require('../Config/dbconnect.php'); // Adjust the path as per your file structure

if (!isset($_SESSION['company_name'])) {
    header("Location: ../Logins/companylogin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['logo'])) {
    $uploadDir = '../uploads/logos/';
    $uploadFile = $uploadDir . basename($_FILES['logo']['name']);
    $uploadSuccess = true;
    $fileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
    
    // Check if the file is a valid image
    $check = getimagesize($_FILES['logo']['tmp_name']);
    if ($check === false) {
        $uploadSuccess = false;
        $_SESSION['logo_update_error'] = 'File is not an image.';
    }

    // Check file size (e.g., 5MB limit)
    if ($_FILES['logo']['size'] > 5000000) {
        $uploadSuccess = false;
        $_SESSION['logo_update_error'] = 'File is too large.';
    }

    // Allow certain file formats
    $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
    if (!in_array($fileType, $allowedTypes)) {
        $uploadSuccess = false;
        $_SESSION['logo_update_error'] = 'Only JPG, JPEG, PNG & GIF files are allowed.';
    }

    if ($uploadSuccess) {
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadFile)) {
            // Update logo path in the database
            $companyName = $_SESSION['company_name'];
            $sql = "UPDATE company SET logo = ? WHERE name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $uploadFile, $companyName);

            if ($stmt->execute()) {
                $_SESSION['logo_update_success'] = 'Logo updated successfully.';
            } else {
                $_SESSION['logo_update_error'] = 'Database update failed.';
            }
            $stmt->close();
        } else {
            $_SESSION['logo_update_error'] = 'Failed to upload file.';
        }
    }

    header('Location: setting.php');
    exit();
}
?>