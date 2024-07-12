<?php
// Include database connection
include 'connection.php';

// Initialize variables to store form data
$name = $username = $password = $confirm_password = '';
$errors = array();

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $errors['name'] = "Please enter your name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $errors['username'] = "Please enter a username.";
    } else {
        // Check if username already exists
        $query = "SELECT id FROM users WHERE username = ?";
        if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param("s", $param_username);
            $param_username = trim($_POST["username"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $errors['username'] = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $errors['password'] = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $errors['confirm_password'] = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if ($password != $confirm_password) {
            $errors['confirm_password'] = "Password did not match.";
        }
    }

    // If no validation errors, proceed with inserting into database
    if (empty($errors)) {
        // Prepare an insert statement

        $role = "user";

        $query = "INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)";
        if ($stmt = $mysqli->prepare($query)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssss", $param_name, $param_username, $param_password, $role);

            // Set parameters
            $param_name = $name;
            $param_username = $username;
            $param_password = $password; // Note: Password is inserted as plain text

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to login page after successful signup
                header("location: ../index.php?acc=1");
                exit();
            } else {
                header("location: ../view/signup.php?err=1");
            }

            // Close statement
            $stmt->close();
        }
    }

    else{
        header("location: ../view/signup.php?err=1");
    }

    // Close database connection
    $mysqli->close();
}
?>