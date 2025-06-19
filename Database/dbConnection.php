<?php
$servername ="sql308.infinityfree.com";
$username ="if0_38940717"; // your database username
$password ="oPEJzR6OEdAX"; // your database password
$dbname ="if0_38940717_saEcomm"; // replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>