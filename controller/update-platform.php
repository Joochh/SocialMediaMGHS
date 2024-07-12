<?php
include 'connection.php';
include 'user_restrict.php';

// Check if the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_platform'])) {
    // Get the submitted form data
    $platform_id = isset($_POST['platform_id']) ? intval($_POST['platform_id']) : null;
    $company_id = isset($_POST['company_id']) ? intval($_POST['company_id']) : null;
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : null;
    $is_posted = isset($_POST['is_posted']) ? intval($_POST['is_posted']) : null;
    $reacts = isset($_POST['reacts']) ? intval($_POST['reacts']) : 0;
    $shares = isset($_POST['shares']) ? intval($_POST['shares']) : 0;
    $comments = isset($_POST['comments']) ? intval($_POST['comments']) : 0;

    // Ensure all required data is present
    if ($platform_id !== null && $company_id !== null && $post_id !== null && $is_posted !== null) {
        // Prepare the update query
        $query = "UPDATE platform SET isPosted = ?, reacts = ?, shares = ?, comments = ? WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        
        if (!$stmt) {
            die("Prepare statement failed: " . $mysqli->error);
        }

        // Bind the parameters
        $stmt->bind_param("iiiii", $is_posted, $reacts, $shares, $comments, $platform_id);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to the post page with a success message
            header("Location: ../view/post.php?company_id=$company_id&post_id=$post_id&success=1");
            exit;
        } else {
            // Handle execution error
            echo "Error updating record: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Missing required fields.";
    }
}

// Close the connection
$mysqli->close();
?>
