<?php

include 'connection.php';

if(isset($_POST['add_company'])){ // Changed from $_REQUEST['add_company'] to $_POST['add_company']

    $user_id = $_POST['user_id']; // Changed from $_REQUEST to $_POST
    $company_name = $_POST['company_name']; // Changed from $_REQUEST to $_POST

    if(!empty($_FILES['company_logo']["tmp_name"])){

        $target_dir = "C:/xampp/htdocs/social_media_management_system-main/uploads/";
        $target_file = $target_dir . basename($_FILES["company_logo"]["name"]);
        $uploadOk = 1;

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["company_logo"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $uploadOk = 0;
        }

        if ($uploadOk && move_uploaded_file($_FILES["company_logo"]["tmp_name"], $target_file)) {
            $image_data = file_get_contents($target_file);

            // Insert statement
            $query = "INSERT INTO companies (name, user_id, logo) values (?, ?, ?)"; // Fixed missing closing quote
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sis", $company_name, $user_id, $image_data); // Fixed parameter binding and number of parameters

            if ($stmt->execute()) {
                // Company added successfully
                header("Location: ../view/companies.php");
                exit();
            } else {
                // Error handling if insertion fails
                echo "Error adding company: " . $stmt->error;
            }

            $stmt->close();
        } else {
            // Error handling if file upload fails
            header("Location: ../view/companies.php");
            exit();
        }
    } else {
        // Error handling if company logo is empty
        echo "Company logo is required.";
    }
}

?>
