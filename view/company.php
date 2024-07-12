<?php
include '../controller/user_restrict.php';
include '../controller/connection.php';

$company_id = $_GET['id'];
$user_id = $_SESSION['id'];
$nopost = 0;
// Check connection
if (!$mysqli) {
    die("Connection failed: " . mysqli_connect_error());
}

// First query to get the company details
$query_company = "SELECT id, name, user_id, logo
                  FROM companies
                  WHERE id = ? AND user_id = ?";

// Prepare the statement
$stmt_company = $mysqli->prepare($query_company);
if (!$stmt_company) {
    die("Prepare statement failed: " . $mysqli->error);
}

// Bind the parameters
$stmt_company->bind_param("ii", $company_id, $user_id);

// Execute the statement
$stmt_company->execute();

// Get the result
$result_company = $stmt_company->get_result();

if ($result_company->num_rows > 0) {
    // Fetch company data
    $company = $result_company->fetch_assoc();
    // Store company data in variables if needed
    $company_name = $company['name'];
    $company_logo = $company['logo'];

    // Second query to get the posts and platform details associated with the company
    $query_posts = "SELECT posts.id, posts.title, posts.content, platform.platform, platform.isPosted, platform.target_date, platform.reacts, platform.comments, platform.shares
                    FROM posts
                    LEFT JOIN platform ON posts.id = platform.post_id
                    WHERE posts.company_id = ?";

    // Prepare the statement
    $stmt_posts = $mysqli->prepare($query_posts);
    if (!$stmt_posts) {
        die("Prepare statement failed: " . $mysqli->error);
    }

    // Bind the parameter
    $stmt_posts->bind_param("i", $company_id);

    // Execute the statement
    $stmt_posts->execute();

    // Get the result
    $result_posts = $stmt_posts->get_result();

    $posts_data = [];
    if ($result_posts->num_rows > 0) {
        // Fetch posts and platform data
        while ($post = $result_posts->fetch_assoc()) {
            $post_id = $post['id'];
            if (!isset($posts_data[$post_id])) {
                $posts_data[$post_id] = [
                    'id' => $post['id'],
                    'title' => $post['title'],
                    'content' => $post['content'],
                    'facebook' => 'Not Included',
                    'instagram' => 'Not Included',
                    'twitter' => 'Not Included'
                ];
            }
            $posts_data[$post_id][$post['platform']] = $post['isPosted'] ? 'Posted' : 'Not Yet Posted';
        }
    } else {
       
        $nopost = 1;
    }

    // Close the posts statement
    $stmt_posts->close();
} else {
    echo "No company found with the given ID and user ID.";
}

// Close the company statement and connection
$stmt_company->close();



// Query to get the total posts
$query_total_posts = "SELECT COUNT(*) AS total_posts FROM posts WHERE company_id = ?";
$stmt_total_posts = $mysqli->prepare($query_total_posts);
if (!$stmt_total_posts) {
    die("Prepare statement failed: " . $mysqli->error);
}
$stmt_total_posts->bind_param("i", $company_id);
$stmt_total_posts->execute();
$result_total_posts = $stmt_total_posts->get_result();
$total_posts = $result_total_posts->fetch_assoc()['total_posts'] ?? 0;
$stmt_total_posts->close();

// Query to get the total engagements by joining posts and platform tables
$query_total_engagements = "
    SELECT SUM(pf.reacts + pf.shares + pf.comments) AS total_engagements 
    FROM platform pf
    JOIN posts p ON pf.post_id = p.id
    WHERE p.company_id = ?";
$stmt_total_engagements = $mysqli->prepare($query_total_engagements);
if (!$stmt_total_engagements) {
    die("Prepare statement failed: " . $mysqli->error);
}
$stmt_total_engagements->bind_param("i", $company_id);
$stmt_total_engagements->execute();
$result_total_engagements = $stmt_total_engagements->get_result();
$total_engagements = $result_total_engagements->fetch_assoc()['total_engagements'] ?? 0;
$stmt_total_engagements->close();

// Query to get the top platform by engagements
$query_top_platform = "
    SELECT pf.platform, SUM(pf.reacts + pf.shares + pf.comments) AS engagements
    FROM platform pf
    JOIN posts p ON pf.post_id = p.id
    WHERE p.company_id = ?
    GROUP BY pf.platform
    ORDER BY engagements DESC
    LIMIT 1";
$stmt_top_platform = $mysqli->prepare($query_top_platform);
if (!$stmt_top_platform) {
    die("Prepare statement failed: " . $mysqli->error);
}
$stmt_top_platform->bind_param("i", $company_id);
$stmt_top_platform->execute();
$result_top_platform = $stmt_top_platform->get_result();
$top_platform_data = $result_top_platform->fetch_assoc();
$top_platform = $top_platform_data['platform'] ?? 'Not Available';
$stmt_top_platform->close();

// Query to get the top 10 posts by total engagements across all platforms
$query_top_posts = "
    SELECT p.title, SUM(pf.reacts + pf.shares + pf.comments) AS total_engagements
    FROM posts p
    JOIN platform pf ON p.id = pf.post_id
    WHERE p.company_id = ?
    GROUP BY p.id, p.title
    ORDER BY total_engagements DESC
    LIMIT 10";
$stmt_top_posts = $mysqli->prepare($query_top_posts);
if (!$stmt_top_posts) {
    die("Prepare statement failed: " . $mysqli->error);
}
$stmt_top_posts->bind_param("i", $company_id);
$stmt_top_posts->execute();
$result_top_posts = $stmt_top_posts->get_result();

$top_posts = [];
while ($row = $result_top_posts->fetch_assoc()) {
    $top_posts[] = $row;
}

$stmt_top_posts->close();
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
    position: relative;
    padding:1.5em;
    width: 100%;
}


.company-header{
    display: flex;
}

.company-image img{
    width: 70px;
    height: 70px;
    object-fit: cover;
    border-radius: 50%;
}
.company-name{
    margin-top: 13px;
    font-size: 1.7em;
    margin-left: 10px;
    color:#87DB8A;
    font-weight: 600;
}


.modal-container{
    width: 100%;
   
    margin-top: 1em;
  
}
.modal-window {
    width:100% ;
    display: none;
    padding:1em;
    height: 74vh;
    box-shadow: 0 3px 5px rgb(0,0,0,0.1);
}
        .modal-window.active {
            display: block;
        }
        .modal-navigator button.active {
        
        color:#87DB8A;
        background:white;
      
        }

.modal-navigator button{
    border:none;
    background:none;
    background: #87DB8A;
    color:white;
    padding:10px 100px;
    margin-right: -4px;
    cursor:pointer;
    font-size: 1em;
   
}

.posts-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1em;
}
.posts-table th, .posts-table td {
    border-bottom: 1px solid #ddd;
    padding: 10px;
}
.posts-table th {
    background-color: #87DB8A;
    color: white;
    text-align: left;
}

.posts-table tr:nth-child(even) {
    background-color: #f2f2f2;
}

.posts-table th {
    padding-top: 12px;
    padding-bottom: 12px;
}


.not-yet{
    background-color:#D7524A;
    color:white;
}

.not-included{
    background:#8BC5FA;
    color:white;
}

.posted{
    color:white;
    background:#87DB8A;
}

.not-yet,.not-included,.posted{
    text-align: center;
    padding:7px;
    width: 70%;
    font-size: 12px;
    margin: 0 auto;
    border-radius: 20px;
}

tr .pf{
    text-align: center;
  
}

.manage-btn{
    text-decoration: none;
    color:#87DB8A;
}

.post-title{
    color:#191919;
}

.add-post-row{
    width: 100%;
  
  display: flex;
  justify-content: flex-end;
}

.add-post-row button{
background:#87DB8A;
color:white;
border:none;
padding:10px 20px;
box-shadow: 0 2px 4px rgb(0,0,0,0.1);
cursor: pointer;
border-radius: 4px;
}

.no-posts-message {
    text-align: center;
    font-size: 16px;
    padding: 40px 0;
    color: #555;
    margin:10em;
}

#overlay{
position: absolute;
background:rgb(0,0,0,0.3);
width: 100%;
height: auto;
min-height:100vh;
top: 50%;
left:50%;
transform: translate(-50%,-50%);
z-index: 100;
}

.add-post-form{
    position: absolute;
    z-index: 101;
    top: 50%;
left:50%;
transform: translate(-50%,-50%);
}

.add-post-form-container{
    background-color: white;
    display: flex;
    flex-direction: column;
    padding:1.5em;
    width: 400px;
    border-radius: 4px;
    box-shadow: 0 3px 5px rgb(0,0,0,0.1);
}

.add-post-form-container form{
    display: flex;
    flex-direction: column;
}

.input{
    padding:10px;
    margin-bottom: 1em;
}

.add-post-form-container .title{
    text-align: center;
    color: #87DB8A;
}
.hidden {
            display: none;
        }
        .platform-container {
            margin-bottom: 10px;
        }
        

.platform-container{
    display: flex;
    width: 100%;
    justify-content: space-between;
}

.add_post{
padding:10px;
background:#87DB8A;
color:white;
border:none;
}

.modal-window {
    /* Your modal styling here */
}

.analytics {
    /* Your analytics section styling here */
}

.top-posts {
    margin-top: 20px;
}

.top-posts table {
    width: 100%;
    border-collapse: collapse;
}

.top-posts tr td{
    color: #191919;
}
.top-posts th, .top-posts td {
    border: 1px solid #ddd;
    padding: 8px;
}

.top-posts th {
    background-color: #f2f2f2;
    text-align: left;
}

.analytics{
    width: 100%;
    display: flex;
    justify-content: space-between;
}

.analytics-window{
background: #87DB8A;
color: white;
width: 33%;
padding:1em;
border-radius: 4px;
box-shadow:0 3px 2px rgb(0,0,0,0.2);
}

.analytics-window .title{
    font-size: 1.2em;
    margin-bottom: 1em;
}

.analytics-window .stats{
    width: 100%;
    text-align: right;
    font-size: 2em;
    font-weight: 600;
}

.top-posts h3{
    color:white;
    background-color: #87DB8A;
    width: 100%;
    padding:.7em;
    text-align: center;
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


    <li> <a class="active"  href="companies.php"> 

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
<div id="overlay"></div>


<div class="add-post-form" id="add_form">

    <div class="add-post-form-container">

    <form action="../controller/add-post.php" method="POST">
        <p class="title">Post Plan Details</p>
        <input type="hidden" name="company_id" value="<?php echo $company_id; ?>">
        <br>
        <input class="input" type="text" name="title" placeholder="Enter title here" required>
        <textarea class="input" name="content" placeholder="Enter post here" required></textarea>
        
        <p>Check where it will be posted:</p>
        <br>
        <div class="platform-container">

        
            <label>
                <input type="checkbox" name="platforms[]" value="facebook" onchange="toggleDate('facebook_container')"> Facebook
            </label>
            <div id="facebook_container" class="hidden">
                <p>Target Post Date</p>
                <input type="date" name="facebook_date">
            </div>
        </div>
        
        <div class="platform-container">
            <label>
                <input type="checkbox" name="platforms[]" value="instagram" onchange="toggleDate('instagram_container')"> Instagram
            </label>
            <div id="instagram_container" class="hidden">
                <p>Target Post Date</p>
                <input type="date" name="instagram_date">
            </div>
        </div>
        
        <div class="platform-container">
            <label>
                <input type="checkbox" name="platforms[]" value="twitter" onchange="toggleDate('twitter_container')"> Twitter
            </label>
            <div id="twitter_container" class="hidden">
                <p>Target Post Date</p>
                <input type="date" name="twitter_date">
            </div>
        </div>
        <br>
        <input class="add_post" type="submit" value="Add Post Plan" name="add_post">
    </form>

    </div>
</div>


    <div class="company-header">

    <div class="company-image">

<?php   $logoData = base64_encode($company["logo"]);
        echo '<img src="data:image/jpeg;base64,' . $logoData . '" alt="Company Logo" />';

?>

</div>

            <div class="company-name">
                <p><?php echo $company['name'] ?></p>
            </div>

           

    </div>
   

    <div class="modal-container">
        <div class="modal-navigator">
            <button id="posts-btn" class="active">Posts</button>
            <button id="analytics-btn">Analytics</button>
        </div>
        <div class="modal-window posts-modal active">
    <div class="add-post-row">
        <button id="add_btn">+ Add a post</button>
    </div>
    <table class="posts-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Content</th>
                <th class="pf">Facebook</th>
                <th class="pf">Instagram</th>
                <th class="pf">Twitter</th>
                <th class="pf">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($nopost) : ?>
                <tr>
                    <td colspan="6" class="no-posts-message">
                        <p>No Post Plans yet!</p>
                    </td>
                </tr>
            <?php endif; ?>
            <?php foreach ($posts_data as $post) : ?>
                <tr class="table-data">
                    <td class="post-title"><?php 
        $title = htmlspecialchars($post['title']);
        echo (mb_strlen($title) > 15) ? mb_substr($title, 0, 20) . '...' : $title; 
        ?></td>
                    <td class="post-title"> <?php 
        $content = htmlspecialchars($post['content']);
        echo (mb_strlen($content) > 15) ? mb_substr($content, 0, 20) . '...' : $content; 
        ?></td>
                    <td>
                        <?php if ($post['facebook'] == "Not Yet Posted") {
                            echo '<p class="not-yet">Not yet Posted</p>';
                        } else if ($post['facebook'] == "Posted") {
                            echo '<p class="posted">Posted</p>';
                        } else if ($post['facebook'] == "Not Included") {
                            echo '<p class="not-included">Not Included</p>';
                        } ?>
                    </td>
                    <td>
                        <?php if ($post['instagram'] == "Not Yet Posted") {
                            echo '<p class="not-yet">Not yet Posted</p>';
                        } else if ($post['instagram'] == "Posted") {
                            echo '<p class="posted">Posted</p>';
                        } else if ($post['instagram'] == "Not Included") {
                            echo '<p class="not-included">Not Included</p>';
                        } ?>
                    </td>
                    <td>
                        <?php if ($post['twitter'] == "Not Yet Posted") {
                            echo '<p class="not-yet">Not yet Posted</p>';
                        } else if ($post['twitter'] == "Posted") {
                            echo '<p class="posted">Posted</p>';
                        } else if ($post['twitter'] == "Not Included") {
                            echo '<p class="not-included">Not Included</p>';
                        } ?>
                    </td>
                    <td>
                        <center><a class="manage-btn" href="post.php?company_id=<?php echo $company_id?>&post_id=<?php echo $post['id']?>">Manage</a></center>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="modal-window analytics-modal">
    <div class="analytics">
        <div class="analytics-window total-posts">
            <div class="title">Total Posts</div>

                        
            <div class="stats">

            <p><?php echo htmlspecialchars($total_posts); ?></p>

            </div>
        </div>
        <div class="analytics-window total-engagements">

        <div class="title">Total Engagements</div>

        <div class="stats">
        <p><?php echo htmlspecialchars($total_engagements); ?></p>
        </div>
           
        </div>
        <div class="analytics-window top-platform">
            <div class="title">Top Platform</div>

            <div class="stats">
            <p> <?php echo htmlspecialchars($top_platform); ?></p>
            </div>
            
        </div>
        </div>
        <!-- Top 10 Posts Table -->
        <div class="top-posts">
            <h3>Top 10 Posts</h3>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Total Engagements</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($top_posts)): ?>
                        <?php foreach ($top_posts as $post): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td><?php echo htmlspecialchars($post['total_engagements']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">No posts available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
   
</div>
    </div>

</main>

</div>


 <script>
        document.addEventListener("DOMContentLoaded", function () {
            const postsBtn = document.getElementById('posts-btn');
            const analyticsBtn = document.getElementById('analytics-btn');

            const postsModal = document.querySelector('.posts-modal');
            const analyticsModal = document.querySelector('.analytics-modal');

            // Function to toggle modals
            function toggleModal(btn, modal) {
                btn.addEventListener('click', function () {
                    // Remove 'active' class from all modals and buttons
                    document.querySelectorAll('.modal-window').forEach(modal => {
                        modal.classList.remove('active');
                    });
                    document.querySelectorAll('.modal-navigator button').forEach(button => {
                        button.classList.remove('active');
                    });

                    // Add 'active' class to the clicked modal and button
                    modal.classList.add('active');
                    btn.classList.add('active');
                });
            }

            // Toggle modals when buttons are clicked
            toggleModal(postsBtn, postsModal);
            toggleModal(analyticsBtn, analyticsModal);
        });


    var overlay = document.getElementById('overlay');
    var form = document.getElementById('add_form');
    var add_btn = document.getElementById('add_btn');
    overlay.style.display = "none";
    form.style.display = 'none';


    function activeOverlay(){
        overlay.style.display = "block";
        form.style.display = 'block';
    }

    function inactiveOverlay(){
        overlay.style.display = "none";
        form.style.display = 'none';
    }
    add_btn.addEventListener('click', activeOverlay);
    overlay.addEventListener('click', inactiveOverlay);

    function toggleDate(containerId) {
            var container = document.getElementById(containerId);
            if (container.classList.contains('hidden')) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }
    </script>


</body>
</html>