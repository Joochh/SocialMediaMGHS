<?php

include 'connection.php';

$user_id = $_SESSION['id'];

// Check if the user ID is set
if (!isset($user_id)) {
    echo "User ID not set.";
    exit;
}

// Prepare the SQL statement to get companies and their post counts
$sql = "SELECT c.id, c.name, c.logo, COUNT(p.id) AS post_count
        FROM companies c
        LEFT JOIN posts p ON c.id = p.company_id
        WHERE c.user_id = ?
        GROUP BY c.id, c.name, c.logo";

if ($stmt = mysqli_prepare($mysqli, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);

    // Execute the statement
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    echo "<div class='company-container'>";
    if (mysqli_num_rows($result) > 0) {
        // Output data of each row
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<div class="company">';
            echo '<div class="company-header">';
                echo '<p class="company_name">'.htmlspecialchars($row["name"]).' </p>';
                echo '<p class="company_posts">'.htmlspecialchars($row["post_count"]).' Posts </p>';
            echo '</div>';

            echo '<div class="company-logo">';
          
            if (!empty($row["logo"])) {
                $logoData = base64_encode($row["logo"]);
                echo '<img src="data:image/jpeg;base64,' . $logoData . '" alt="Company Logo" />';
            } else {
                echo '<img src="../assets/placeholder.png">';
            }
            echo '</div>';
            echo '<center><a class="manage-btn" href="company.php?id='.$row['id'].'"> Manage Posts </a></center>';
          
            echo '</div>';
           
        }
    } else {
    
    }

    echo "<div id='add_btn' class='company add'>";
    echo "<h1>+</h1>";
    echo "<p> Add a company </p>";
    echo "</div>";
    echo "</div>";

    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($mysqli);
}

mysqli_close($mysqli);
?>
