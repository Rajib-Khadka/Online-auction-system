<?php

$server = "localhost";
$user = "root";
$password = "";
$db = "jobportal_db";

$conn = mysqli_connect($server,$user,$password,$db);

if(!$conn) {
    die("Connection Failed:".mysqli_connect_error());
}

?>