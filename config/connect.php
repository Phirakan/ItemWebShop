<?php

$servername = 'localhost';
$username = 'root';
$password = '';
$database = 'game_item_shop';

// Create connection
$conn = new mysqli($servername, $username, $password, $database );
// Check connection
if($conn->connect_error){
	die("Connection failed: ".$conn->connect_error);
}
// echo"Connected successfully.<br>";
$sql = "SET NAMES UTF8";
$conn->query($sql);


?>