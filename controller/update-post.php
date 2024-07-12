<?php
include '../controller/user_restrict.php';
include '../controller/connection.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize variables from form data
    $post_id = $_POST['post_id'];
    $company_id = $_POST['company_id'];
    $post_title = $_POST['post_title'];
    $post_content = $_POST['post_content'];

    // Validate input (if necessary)
    // You can add validation here if needed

    // Prepare update query
    $update_query = "UPDATE posts SET title = ?, content = ? WHERE id = ? AND company_id = ?";

    // Prepare the statement
    $stmt = $mysqli->prepare($update_query);
    if (!$stmt) {
        die("Prepare statement failed: " . $mysqli->error);
    }

    // Bind parameters
    $stmt->bind_param("ssii", $post_title, $post_content, $post_id, $company_id);

    // Execute the statement
    if ($stmt->execute()) {
        // Success: Redirect to a success page or back to the post view page
        header("Location: ../view/post.php?company_id=$company_id&post_id=$post_id&success=1");
        exit();
    } else {
        // Error: Redirect back with error message or handle as needed
        header("Location: ../view/post.php?company_id=$company_id&post_id=$post_id&error=1");
        exit();
    }

    // Close statement
    $stmt->close();
}

// Close connection
$mysqli->close();
?>
