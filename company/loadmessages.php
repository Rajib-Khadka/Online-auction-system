<?php
require('../Config/dbconnect.php');
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

$receiver_id = isset($_GET['receiver_id']) ? mysqli_real_escape_string($conn, $_GET['receiver_id']) : null;

if ($receiver_id) {
    $query = "SELECT * FROM messages 
              WHERE (sender_id = '$receiver_id' AND receiver_id = '$company_id') 
                 OR (sender_id = '$company_id' AND receiver_id = '$receiver_id') 
              ORDER BY timestamp ASC";
    $messages_result = mysqli_query($conn, $query);

    if (!$messages_result) {
        die("Error fetching messages: " . mysqli_error($conn));
    }

    $messages = [];
    while ($row = mysqli_fetch_assoc($messages_result)) {
        $messages[] = $row;
    }
    echo json_encode($messages);
}
?>