<?php

include '../controller/connection.php';
include '../controller/admin_restrict.php';


$user_id = $_GET['user_id'];

// Fetch username of the user
$query_username = "SELECT username FROM users WHERE id = ?";
$stmt_username = $mysqli->prepare($query_username);
if (!$stmt_username) {
    die("Prepare statement failed: " . $mysqli->error);
}
$stmt_username->bind_param("i", $user_id);
$stmt_username->execute();
$stmt_username->bind_result($username);
$stmt_username->fetch();
$stmt_username->close();

// Fetch companies handled by the user, total posts, and total engagement (sum of reacts, comments, shares)
$query_companies = "
    SELECT c.name AS company_name,
            c.logo AS company_logo, 
           COUNT(p.id) AS total_posts, 
           SUM(pl.reacts + pl.comments + pl.shares) AS total_engagement
    FROM companies c
    LEFT JOIN posts p ON c.id = p.company_id
    LEFT JOIN platform pl ON p.id = pl.post_id
    WHERE c.user_id = ?
    GROUP BY c.id
";
$stmt_companies = $mysqli->prepare($query_companies);
if (!$stmt_companies) {
    die("Prepare statement failed: " . $mysqli->error);
}
$stmt_companies->bind_param("i", $user_id);
$stmt_companies->execute();
$result_companies = $stmt_companies->get_result();
$stmt_companies->close();

// Close the database connection
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin</title>
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
.dashboard{
    width: 100%;
}
.dashboard-container{
    width: 100%;
    display: flex;
    justify-content: space-between;
}
.total-users, .total-companies,.highest-engaged-company,.total-engagement-highest{
    background:white;
    padding:1em;
    width: 19%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    box-shadow: 0 3px 2px rgb(0,0,0,0.2);
}
.total-users, .total-companies, .total-engagement-highest, .highest-engaged-company{
    color:#87DB8A;
}
.highest-engaged-company .logo img{
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
}
.total-users .total p, .total-companies .total p, .total-engagement-highest .total p{
    font-size: 70px;
    font-weight: 600;
}
.total-users p, .total-companies p,.highest-engaged-company p,.total-engagement-highest p{
    text-align: center;
}
.top-companies{
    width: 40%;
    background:white;
    border-radius: 4px;
    box-shadow: 0 3px 2px rgb(0,0,0,0.2);
}
.top-companies table{
    width: 100%;
}
.top-companies thead tr th{
    background-color: #87DB8A;
    color: white;
    padding: 10px;
}
tbody tr td{
    margin: 0 auto;
}
.center{
    text-align: center;
    margin:10px 0;
}
.top-companies .title{
    margin-top: 1em;
    color:#87DB8A;
}
.logo-company{
   display: flex;
   justify-content: flex-start;
   margin-left: 50px;
}
.logo-company img{
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 50%;
}
.logo-company p{
    margin-top: 12px;
    margin-left: 10px;
}
.user-admin{
    background:white;
    padding:1em;
    border-radius: 5px;
    height: auto;
    min-height: 100vh;
    box-shadow: 0 3px 5px rgb(0,0,0,0.2);
}

table{
    width: 100%;
}


table .header{
background:#87DB8A;
color:white;

}
table tr th{
padding:10px;
}

table tr td{
    text-align: center;
    padding:10px;
    border-bottom: 1px solid #87DB8A;
}

/* Apply different background color to even-numbered columns */
table tr:nth-child(even)
 {
 }

.search-container{
    padding-bottom: 1em;
    width: 100%;
}

.search-container form input{
    width: 25%;
    padding:10px;
    border-radius: 20px;
    border:2px solid #87DB8A;
    box-shadow: inset 0 3px 2px rgb(0,0,0,0.2);
}

.search-btn{
   
   padding: 10px;
   border-radius: 20px;
   border:none;
   background:#87DB8A;
   color:white;
   box-shadow: 0 3px 2px rgb(0,0,0,0.2);
   cursor: pointer;
 }


.overview{
    text-decoration: none;
    color:#87DB8A;
}

.user-company{
    background: white;
padding:1em;
height: auto;
min-height: 100vh;
box-shadow: 0 3px 2px rgb(0,0,0,0.2);
border-radius: 4px;
}

thead{
    color:white;
    background-color: #87DB8A;
}

.name{
    margin-left: 10px;
    font-weight: 600;
    color:#87DB8A;
}

.user-company-header{
    display: flex;
    margin-bottom: 1em;
}

.goback-container{
    width: 100%;
    margin-bottom: 1em;
    display: flex;
    justify-content: flex-end;
}

.goback-container a{
    text-decoration: none;
    color:#191919;
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
    <li> <a  href="admin.php"> 
                 <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-postcard" viewBox="0 0 16 16">
                     <path fill-rule="evenodd" d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zM1 4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm7.5.5a.5.5 0 0 0-1 0v7a.5.5 0 0 0 1 0zM2 5.5a.5.5 0 0 1 .5-.5H6a.5.5 0 0 1 0 1H2.5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5H6a.5.5 0 0 1 0 1H2.5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5H6a.5.5 0 0 1 0 1H2.5a.5.5 0 0 1-.5-.5M10.5 5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zM13 8h-2V6h2z"/>
                </svg>
               <p>Dashboard</p>
    </a> </li>
    <li> <a class="active" href="manage-users.php"> 
    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
  <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
         <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
</svg>
        <p>Users</p>
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

    <div class="goback-container">
        <a href="manage-users.php">Go back</a>
    </div>

    <div class="user-company">

    <div class="user-company-header">
    <p>Companies handled by </p>
    <p class="name"><?php echo htmlspecialchars($username); ?>: </p>
    </div>

<table>
    <thead>
        <tr>
           
            <th>Company Name</th>
            <th>Total Posts</th>
            <th>Total Engagement</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result_companies->fetch_assoc()): ?>
            <tr>
                
                <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                <td><?php echo htmlspecialchars($row['total_posts']); ?></td>
                <td><?php echo htmlspecialchars($row['total_engagement']); ?></td>
            </tr>
        <?php endwhile; ?>
        <?php if ($result_companies->num_rows === 0): ?>
            <tr>
                <td colspan="3">No data available</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>






    </div>
       
    </main>



</div>
</body>
</html>