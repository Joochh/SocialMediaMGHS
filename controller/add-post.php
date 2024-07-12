<?php
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_post'])) {
    $company_id = $_POST['company_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $platforms = isset($_POST['platforms']) ? $_POST['platforms'] : [];
    $facebook_date = $_POST['facebook_date'] ?? null;
    $instagram_date = $_POST['instagram_date'] ?? null;
    $twitter_date = $_POST['twitter_date'] ?? null;

    // Start transaction
    $mysqli->begin_transaction();

    try {
        // Insert the post into the posts table
        $query_post = "INSERT INTO posts (company_id, title, content) VALUES (?, ?, ?)";
        $stmt_post = $mysqli->prepare($query_post);
        if (!$stmt_post) {
            throw new Exception("Prepare statement failed: " . $mysqli->error);
        }
        $stmt_post->bind_param("iss", $company_id, $title, $content);
        $stmt_post->execute();
        
        // Get the id of the newly inserted post
        $post_id = $stmt_post->insert_id;
        
        // Close the post statement
        $stmt_post->close();
        
        // Insert platform entries for the new post
        if (!empty($platforms)) {
            $query_platform = "INSERT INTO platform (post_id, platform, isPosted, target_date) VALUES (?, ?, 0, ?)";
            $stmt_platform = $mysqli->prepare($query_platform);
            if (!$stmt_platform) {
                throw new Exception("Prepare statement failed: " . $mysqli->error);
            }
            
            foreach ($platforms as $platform) {
                $target_date = null;
                if ($platform == 'facebook') {
                    $target_date = $facebook_date;
                } elseif ($platform == 'instagram') {
                    $target_date = $instagram_date;
                } elseif ($platform == 'twitter') {
                    $target_date = $twitter_date;
                }
                $stmt_platform->bind_param("iss", $post_id, $platform, $target_date);
                $stmt_platform->execute();
            }
            
            // Close the platform statement
            $stmt_platform->close();
        }
        
        // Commit the transaction
        $mysqli->commit();
        header("location: ../view/company.php?id=$company_id");
    } catch (Exception $e) {
        // Rollback the transaction if something failed
        $mysqli->rollback();
      
    }
    
    // Close the connection
    $mysqli->close();
} else {
    echo "Invalid request.";
}
?>
