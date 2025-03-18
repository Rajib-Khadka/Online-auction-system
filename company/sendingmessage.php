<?php
require('../Config/dbconnect.php'); // Adjust the path as needed

session_start();

if (!isset($_SESSION['company_name'])) {
    header("Location: ../Logins/companylogin.php");
    exit();
}

$company_name = mysqli_real_escape_string($conn, $_SESSION['company_name']);
$company_query = "SELECT companyid FROM company WHERE name = '$company_name'";
$company_result = mysqli_query($conn, $company_query);

if (!$company_result) {
    die("Error fetching company ID: " . mysqli_error($conn));
}

$company_data = mysqli_fetch_assoc($company_result);
$company_id = $company_data['companyid'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $receiver_id = mysqli_real_escape_string($conn, $_POST['receiver_id']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    if ($company_id && $receiver_id && $message) {
        $query = "INSERT INTO messages (sender_id, receiver_id, message, timestamp) VALUES ('$company_id', '$receiver_id', '$message', NOW())";
        if (mysqli_query($conn, $query)) {
            header("Location: message.php?receiver_id=$receiver_id");
        } else {
            echo "Error sending message: " . mysqli_error($conn);
        }
    } else {
        echo "Invalid input.";
    }
}
?>