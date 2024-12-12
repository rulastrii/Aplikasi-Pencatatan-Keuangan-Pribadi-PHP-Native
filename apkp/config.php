<?php
$conn = new mysqli("localhost", "root", "", "apkp");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
