<?php
$databaseHost = getenv('DB_HOST');
$databaseName = getenv('DB_NAME');
$databaseUsername = getenv('DB_USER');
$databasePassword = getenv('DB_PASS');

$mysqli = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName);

if (!$mysqli) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
