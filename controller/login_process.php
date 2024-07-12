<?php
session_start();
include 'connection.php'; // Include your database configuration file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare the SQL statement
    $stmt = $mysqli->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $db_password, $role);
        $stmt->fetch();

        // Verify password (plain text comparison)
        if ($password === $db_password) {
            $_SESSION["login"] = true;
            $_SESSION["id"] = $id;
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $role;

            // Redirect based on role
            if ($role == 'admin') {
                $_SESSION["is_admin"] = true; // Set an additional session variable for admin
                header("Location: ../view/admin.php");
            } else {
                $_SESSION["is_admin"] = false; // Ensure is_admin is false for regular users
                header("Location: ../view/dashboard.php");
            }
        } else {
            header("location: ../index.php?err=1");
        }
    } else {
        header("location: ../index.php?user=1");
    }

    $stmt->close();
    $mysqli->close();
}
?>
