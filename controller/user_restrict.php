<?php
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== 'user') {
    // If the user is not logged in or not an admin, redirect to login page or a "not authorized" page
    header("Location: ../index.php");
    exit;
}
?>
