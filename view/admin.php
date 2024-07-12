<?php
include '../controller/admin_restrict.php';
include '../controller/connection.php';

// Fetch total users
$query_total_users = "SELECT COUNT(*) AS total_users FROM users";
$result_total_users = $mysqli->query($query_total_users);
$total_users = $result_total_users->fetch_assoc()['total_users'];

// Fetch total companies
$query_total_companies = "SELECT COUNT(*) AS total_companies FROM companies";
$result_total_companies = $mysqli->query($query_total_companies);
$total_companies = $result_total_companies->fetch_assoc()['total_companies'];

// Fetch company with highest engagement
$query_highest_engagement_company = "
    SELECT c.id, c.name, c.logo, SUM(pf.reacts + pf.shares + pf.comments) AS total_engagements
    FROM companies c
    JOIN posts p ON c.id = p.company_id
    JOIN platform pf ON p.id = pf.post_id
    GROUP BY c.id
    ORDER BY total_engagements DESC
    LIMIT 1";
$result_highest_engagement_company = $mysqli->query($query_highest_engagement_company);
$highest_engagement_company = $result_highest_engagement_company->fetch_assoc();
$highest_engagement_company_logo = $highest_engagement_company['logo'] ?? null;
$highest_engagement_company_name = $highest_engagement_company['name'] ?? 'Not Available';
$highest_engagement_company_total = $highest_engagement_company['total_engagements'] ?? 0;

// Fetch top 5 companies with engagement
$query_top_5_companies = "
    SELECT c.id, c.name, c.logo, SUM(pf.reacts + pf.shares + pf.comments) AS total_engagements
    FROM companies c
    JOIN posts p ON c.id = p.company_id
    JOIN platform pf ON p.id = pf.post_id
    GROUP BY c.id
    ORDER BY total_engagements DESC
    LIMIT 5";
$result_top_5_companies = $mysqli->query($query_top_5_companies);

$top_5_companies = [];
while ($row = $result_top_5_companies->fetch_assoc()) {
    $top_5_companies[] = $row;
}

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
    <li> <a class="active" href="admin.php"> 

                 <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-postcard" viewBox="0 0 16 16">
                     <path fill-rule="evenodd" d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zM1 4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm7.5.5a.5.5 0 0 0-1 0v7a.5.5 0 0 0 1 0zM2 5.5a.5.5 0 0 1 .5-.5H6a.5.5 0 0 1 0 1H2.5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5H6a.5.5 0 0 1 0 1H2.5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5H6a.5.5 0 0 1 0 1H2.5a.5.5 0 0 1-.5-.5M10.5 5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zM13 8h-2V6h2z"/>
                </svg>

               <p>Dashboard</p>

    </a> </li>


    <li> <a href="manage-users.php"> 

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
    <div class="dashboard">
        <div class="dashboard-container">

            <div class="total-users">
                <div class="total"><p><?php echo htmlspecialchars($total_users); ?></p></div>
                <p class="title">Total Users</p>
            </div>

            <div class="total-companies">
                <div class="total"><p><?php echo htmlspecialchars($total_companies); ?></p></div>
                <p class="title">Total Companies</p>
            </div>

            <div class="highest-engaged-company">
                <div class="logo">
                    <?php 
                    if ($highest_engagement_company_logo) {
                        echo '<img src="data:image/jpeg;base64,' . base64_encode($highest_engagement_company_logo) . '" alt="Highest Engagement Company Logo" width="50">';
                    } else {
                        echo "<p> Not yet Available</p><br>";
                    }
                    ?>
                </div>
                <div class="company-name"><p><?php echo htmlspecialchars($highest_engagement_company_name); ?></p></div>
                <br>
                <p class="title">Company with the Highest Engagement</p>
              
            </div>


            <div class="top-companies">
                <table>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Company</th>
                            <th>Total Engagements</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($top_5_companies)): ?>
                            <?php $rank = 1; ?>
                            <?php foreach ($top_5_companies as $company): ?>
                                <tr>
                                    <td class="center"><?php echo $rank; ?></td>
                                    <td class="center logo-company">
                                        <?php 
                                        $logoData = base64_encode($company["logo"]);
                                        ?>
                                        <img src="data:image/jpeg;base64,<?php echo $logoData; ?>" alt="Company Logo" width="50">
                                        <p><?php echo htmlspecialchars($company['name']); ?></p>
                                    </td>
                                    <td class="center"><?php echo htmlspecialchars($company['total_engagements']); ?></td>
                                </tr>
                                <?php $rank++; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td class="center" colspan="3">No companies available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <p class="title center">Top 5 Highly Engaged Companies</p>
            </div>
        </div>
    </div>
</main>

</div>




</body>
</html>