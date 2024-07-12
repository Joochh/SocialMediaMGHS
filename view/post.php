<?php
include '../controller/user_restrict.php';
include '../controller/connection.php';

// Ensure correct retrieval of query parameters
$company_id = $_GET['company_id'];
$post_id = $_GET['post_id'];

// Check connection
if (!$mysqli) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query to get the post details
$query_post = "SELECT title, content FROM posts WHERE id = ? AND company_id = ?";
$stmt_post = $mysqli->prepare($query_post);
if (!$stmt_post) {
    die("Prepare statement failed: " . $mysqli->error);
}

// Bind the parameters
$stmt_post->bind_param("ii", $post_id, $company_id);

// Execute the statement
$stmt_post->execute();

// Get the result
$result_post = $stmt_post->get_result();

if ($result_post->num_rows > 0) {
    // Fetch post data
    $post = $result_post->fetch_assoc();

    $post_title = $post['title'];
    $post_content = $post['content'];

    // Query to get the platforms related to the post
    $query_platforms = "SELECT id,platform, isPosted, target_date, reacts, comments, shares FROM platform WHERE post_id = ?";

    $stmt_platforms = $mysqli->prepare($query_platforms);
    if (!$stmt_platforms) {
        die("Prepare statement failed: " . $mysqli->error);
    }

    // Bind the parameter
    $stmt_platforms->bind_param("i", $post_id);

    // Execute the statement
    $stmt_platforms->execute();

    // Get the result
    $result_platforms = $stmt_platforms->get_result();

    $platforms = [];
    while ($platform = $result_platforms->fetch_assoc()) {
        $platforms[$platform['platform']] = [
            'id' => $platform['id'],
            'isPosted' => $platform['isPosted'],
            'target_date' => $platform['target_date'],
            'reacts' => $platform['reacts'],
            'comments' => $platform['comments'],
            'shares' => $platform['shares'],
            'total_engagements' => $platform['reacts'] + $platform['comments'] + $platform['shares']
        ];
    }


    // Close the platforms statement
    $stmt_platforms->close();
} else {
    echo "No post found with the given ID and company ID.";
}

// Close the post statement and connection
$stmt_post->close();
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    
    <link rel="stylesheet" href="../styles/font.css">
</head>

<style>

*{
    padding:0; 
    margin:0;
    box-sizing: border-box;
}
body,html{
background-color: #f5f5f5;

}
.username-holder{

    background:#A7D6CC;
    color:white;
    display: flex;
 justify-content: flex-end;
 padding:15px;
 
}

.username-holder .icon{
margin-left: 10px;
margin-top: 3px;
}

.content{
    display: flex;
}

nav{
background:#87DB8A;
height: auto;
min-height: 100vh;
box-shadow: 0 3px 5px rgb(0,0,0,0.2);
}

ul{

}

ul li{
list-style: none;
}

ul li a{
display: flex;
padding:20px;
color:white;
text-decoration: none;
}

ul li a p{
    width: 50%;
    
}

ul li a svg{
 
    margin-right: 10px;
}

.active{
    background:white;
    color:#87DB8A;
}

main{
    width: 100%;
    padding:1.5em;
}

.post-header{
    width: 100%;
    display: flex;
    justify-content: space-between;
}

.post-header .row .title{
    font-size: 1.3em;
    color: #87DB8A;
}

.post-header .row a{
    text-decoration: none;
    color: #191919;
}
.post-container{
    margin-top: 1em;
    padding:1em;
    background-color: white;
    box-shadow: 0 3px 2px rgb(0,0,0,0.2);
    border-radius: 3px;
}

.post-title-input{
    font-size: 2em;
    border:none;
    padding-bottom: 5px;
    border-bottom: 1px solid #87DB8A;
}

.post-title p,.post-content p,.soc-med-title{
    color:#87DB8A;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 3px;
}

.post-title{
    margin-bottom: 1em;
}

.post-content{
    width: 100%;
    margin-bottom: 1em;
}
.post-content textarea{
    width: 80%;
    resize: none;
    height: 200px; /* Set initial height */
    min-height: 200px; /* Ensure minimum height */
    padding:1em;
    border:1px solid #87DB8A;
}


.update-btn{
    color:white;
    background:#87DB8A;
    padding:10px 50px;
    font-size: 0.8em;
    border:none;
    border-radius: 3px;
    box-shadow: 0 3px 3px rgb(0,0,0,0.2);
    margin-bottom: 2em;
}

.platforms-container{

}
.platform{
    padding:1em;
    width: 100%;
   border:2px solid #87DB8A;
    margin-bottom: 1em;
   margin-top: 1em;
   border-radius: 4px;
}

.platform-header{
    width: 100%;
    display: flex;
    justify-content: space-between;
    margin-bottom: 1em;
}
.socmed{
    display: flex;
}

.socmed svg{
    color:#87DB8A;
    margin-right: 10px;
   
}

.socmed p{
    margin-top: 12px;
}

.update-platform{
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.target-date {
    margin-bottom: 10px; /* Adjust margin as needed */
}

.post-status {
    display: flex;
    align-items: center;
}

.post-status label {
    margin-right: 10px; /* Adjust margin between label and select box */
}

.post-engagements{
    width: 70%;
    display: flex;
    justify-content: space-between;
}

.total-engagements{
    background:#87DB8A;
    color:white;
    padding: 12px 20px;
    border-radius: 30px;
}

.update-platform-btn{
    margin-top: 2em;
    border:none;
    background:#87DB8A;
    color:white;
    padding: 8px 40px;
    border-radius: 4px;
    box-shadow:0 3px 2px rgb(0,0,0,0.1);
    cursor: pointer;
}

.post-engagement-row input{
    padding:5px;
}
</style>
<body>

<header>

<div class="username-holder">

    <p><?php  echo $_SESSION['username']?></p>
    
    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
  <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
         <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
</svg>


</div>

</header>

<div class="content">

<nav>

<ul>
    <li> <a  href="dashboard.php"> 

                 <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-postcard" viewBox="0 0 16 16">
                     <path fill-rule="evenodd" d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zM1 4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm7.5.5a.5.5 0 0 0-1 0v7a.5.5 0 0 0 1 0zM2 5.5a.5.5 0 0 1 .5-.5H6a.5.5 0 0 1 0 1H2.5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5H6a.5.5 0 0 1 0 1H2.5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5H6a.5.5 0 0 1 0 1H2.5a.5.5 0 0 1-.5-.5M10.5 5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zM13 8h-2V6h2z"/>
                </svg>

               <p>Dashboard</p>

    </a> </li>


    <li> <a class="active" href="companies.php"> 

    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-building" viewBox="0 0 16 16">
  <path d="M4 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zM4 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM7.5 5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM4.5 8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
  <path d="M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1zm11 0H3v14h3v-2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V15h3z"/>
</svg>

        <p>Companies</p>

        </a> </li>


        <li> <a href="../controller/logout.php"> 

        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-box-arrow-left" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M6 12.5a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v2a.5.5 0 0 1-1 0v-2A1.5 1.5 0 0 1 6.5 2h8A1.5 1.5 0 0 1 16 3.5v9a1.5 1.5 0 0 1-1.5 1.5h-8A1.5 1.5 0 0 1 5 12.5v-2a.5.5 0 0 1 1 0z"/>
        <path fill-rule="evenodd" d="M.146 8.354a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L1.707 7.5H10.5a.5.5 0 0 1 0 1H1.707l2.147 2.146a.5.5 0 0 1-.708.708z"/>
        </svg>

    <p>Logout</p>

    </a> </li>



</ul>

</nav>

<main>
    
<div class="post-header">

    <div class="row">
        <p class="title">Manage Post Plan</p>
    </div>

    <div class="row">
        <a href="company.php?id=<?php echo $company_id ?>">Go back</a>
    </div>

</div>

<div class="post-container">

    <form method="post" action="../controller/update-post.php">

        <div class="post-title">
        <input type='hidden' value="<?php echo $post_id; ?>" name="post_id">
        <input type='hidden' value="<?php echo $company_id; ?>" name="company_id">

        <div class="input-row">

            <input class='post-title-input'type="text" value="<?php echo htmlspecialchars($post_title); ?>" name="post_title">
        
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#87DB8A" class="bi bi-pencil" viewBox="0 0 16 16">
            <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
            </svg>

        </div>
            <p>title</p>
        </div>

        <div class="post-content">
    <textarea name="post_content"><?php echo htmlspecialchars($post_content); ?></textarea>
    <p>Content</p>
</div>

    <input class="update-btn" type="submit" value="Update post details" name="update_post">


    </form>
   
   

    <p class="soc-med-title">Social Media Involvements</p>


    <div class="platforms-container">
    <?php if (isset($platforms['facebook'])): ?>
        <div class="platform">

        <div class="platform-header">

        <div class="socmed">
                <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
                <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/>
                </svg>
                    <p>Facebook</p>
        </div>
                
        <div class="total-engagements">
                <p>Total Engagements: <?php echo $platforms['facebook']['total_engagements']; ?></p>
        </div>
        </div>

        <form class="update-platform" method="POST" action="../controller/update-platform.php">
    <input type="hidden" name="platform_id" value="<?php echo $platforms['facebook']['id']; ?>">
    <input type="hidden" name="company_id" value="<?php echo $company_id ?>">
    <input type="hidden" name="post_id" value="<?php echo $post_id ?>">

    <!-- Target Date -->
    <div class="target-date">
        <p>Target Date:
            <?php
            $facebook_target_date = isset($platforms['facebook']['target_date']) ? $platforms['facebook']['target_date'] : null;
            if ($facebook_target_date) {
                echo date('F j, Y', strtotime($facebook_target_date));
            } else {
                echo 'N/A';
            }
            ?>
        </p>
    </div>

    <!-- Dropdown for isPosted -->
    <div class="post-status">
        <label for="is_posted">Status:</label>
        <select name="is_posted" id="is_posted">
            <option value="1" <?php if ($platforms['facebook']['isPosted'] == 1) echo 'selected'; ?>>Posted</option>
            <option value="0" <?php if ($platforms['facebook']['isPosted'] == 0) echo 'selected'; ?>>Not Posted</option>
        </select>
    </div>

    <br>
    <p>Engagements</p>
    <div class="post-engagements">
        <div class="post-engagement-row">
            <label>Reacts: </label>
            <input type="text" name="reacts" value="<?php echo htmlspecialchars($platforms['facebook']['reacts']); ?>">
        </div>
        <div class="post-engagement-row">
            <label>Shares: </label>
            <input type="text" name="shares" value="<?php echo htmlspecialchars($platforms['facebook']['shares']); ?>">
        </div>
        <div class="post-engagement-row">
            <label>Comments: </label>
            <input type="text" name="comments" value="<?php echo htmlspecialchars($platforms['facebook']['comments']); ?>">
        </div>
    </div>

    <input class="update-platform-btn" type="submit" name="update_platform" value="Update">
</form>


        </div>
    <?php endif; ?>




    <?php if (isset($platforms['instagram'])): ?>
    <div class="platform">

        <div class="platform-header">

            <div class="socmed">
            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-instagram" viewBox="0 0 16 16">
  <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/>
</svg>
                <p>Instagram</p>
            </div>

            <div class="total-engagements">
                <p>Total Engagements: <?php echo $platforms['instagram']['total_engagements']; ?></p>
            </div>
        </div>

        <form class="update-platform" method="POST" action="../controller/update-platform.php">
            <input type="hidden" name="platform_id" value="<?php echo $platforms['instagram']['id']; ?>">
            <input type="hidden" name="company_id" value="<?php echo $company_id ?>">
            <input type="hidden" name="post_id" value="<?php echo $post_id ?>">

            <!-- Target Date -->
            <div class="target-date">
                <p>Target Date:
                    <?php
                    $instagram_target_date = isset($platforms['instagram']['target_date']) ? $platforms['instagram']['target_date'] : null;
                    if ($instagram_target_date) {
                        echo date('F j, Y', strtotime($instagram_target_date));
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </p>
            </div>

            <!-- Dropdown for isPosted -->
            <div class="post-status">
                <label for="is_posted">Status:</label>
                <select name="is_posted" id="is_posted">
                    <option value="1" <?php if ($platforms['instagram']['isPosted'] == 1) echo 'selected'; ?>>Posted</option>
                    <option value="0" <?php if ($platforms['instagram']['isPosted'] == 0) echo 'selected'; ?>>Not Posted</option>
                </select>
            </div>

            <br>
            <p>Engagements</p>
            <div class="post-engagements">
                <div class="post-engagement-row">
                    <label>Reacts: </label>
                    <input type="text" name="reacts" value="<?php echo htmlspecialchars($platforms['instagram']['reacts']); ?>">
                </div>
                <div class="post-engagement-row">
                    <label>Shares: </label>
                    <input type="text" name="shares" value="<?php echo htmlspecialchars($platforms['instagram']['shares']); ?>">
                </div>
                <div class="post-engagement-row">
                    <label>Comments: </label>
                    <input type="text" name="comments" value="<?php echo htmlspecialchars($platforms['instagram']['comments']); ?>">
                </div>
            </div>

            <input class="update-platform-btn" type="submit" name="update_platform" value="Update">
        </form>

    </div>
<?php endif; ?>








<?php if (isset($platforms['twitter'])): ?>
    <div class="platform">

        <div class="platform-header">

            <div class="socmed">
            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16">
  <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
</svg>
                <p>Twitter</p>
            </div>

            <div class="total-engagements">
                <p >Total Engagements: <?php echo $platforms['twitter']['total_engagements']; ?></p>
            </div>
        </div>

        <form class="update-platform" method="POST" action="../controller/update-platform.php">
            <input type="hidden" name="platform_id" value="<?php echo $platforms['twitter']['id']; ?>">
            <input type="hidden" name="company_id" value="<?php echo $company_id ?>">
            <input type="hidden" name="post_id" value="<?php echo $post_id ?>">

            <!-- Target Date -->
            <div class="target-date">
                <p>Target Date:
                    <?php
                    $twitter_target_date = isset($platforms['twitter']['target_date']) ? $platforms['twitter']['target_date'] : null;
                    if ($twitter_target_date) {
                        echo date('F j, Y', strtotime($twitter_target_date));
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </p>
            </div>

            <!-- Dropdown for isPosted -->
            <div class="post-status">
                <label for="is_posted">Status:</label>
                <select name="is_posted" id="is_posted">
                    <option value="1" <?php if ($platforms['twitter']['isPosted'] == 1) echo 'selected'; ?>>Posted</option>
                    <option value="0" <?php if ($platforms['twitter']['isPosted'] == 0) echo 'selected'; ?>>Not Posted</option>
                </select>
            </div>

            <br>
            <p>Engagements</p>
            <div class="post-engagements">
                <div class="post-engagement-row">
                    <label>Reacts: </label>
                    <input type="text" name="reacts" value="<?php echo htmlspecialchars($platforms['twitter']['reacts']); ?>">
                </div>
                <div class="post-engagement-row">
                    <label>Shares: </label>
                    <input type="text" name="shares" value="<?php echo htmlspecialchars($platforms['twitter']['shares']); ?>">
                </div>
                <div class="post-engagement-row">
                    <label>Comments: </label>
                    <input type="text" name="comments" value="<?php echo htmlspecialchars($platforms['twitter']['comments']); ?>">
                </div>
            </div>

            <input class="update-platform-btn" type="submit" name="update_platform" value="Update">
        </form>

    </div>
<?php endif; ?>



</div>

    </div>

</main>

</div>




</body>
</html>