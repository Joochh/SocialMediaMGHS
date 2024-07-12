<?php
include '../controller/user_restrict.php';

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Companies</title>
    
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
    padding:1em;
}

.company-container{
   
    display: flex;
    flex-wrap: wrap ;
}

.company{
    background:white;
    padding:1em;
    box-shadow: 0 3px 5px rgb(0,0,0,0.3);
    border-radius: 4px;
   margin-right: 1em;
   margin-bottom: 1em;
}

.company .company-logo img{
    width: 250px;
    height: 250px;
    object-fit: cover; /* Cover the container, maintaining aspect ratio */
    margin-bottom: 1em;
}

.company-header{
    display: flex;
    justify-content: space-between;
    margin-bottom: 1em;
}

.company_name{
    color:#87DB8A;
    font-weight: 600;
}
.company_posts{
    color:rgb(0,0,0,0.5);
}

.manage-btn{
    text-decoration: none;
    color:#87DB8A;
    font-weight: 400;
    text-align: center;
}

.add{
    width: 290px;
    background-color: #87DB8A;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color:white;
    cursor:pointer;
}

.add h1{
    font-size: 8em;
}

main{
    position: relative;
   
    width: 100%;
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

.add-form-container{
    z-index: 101;
    position: absolute;  
    
    top: 50%;
    left: 50%;
    transform: translate(-50%,-50%);
    box-shadow: 0 3px 5px rgb(0,0,0,0.3);
  
}

.form-container-flex{
    width: 400px;
    background-color: white;
  
   margin: 0 auto;
    padding:1em;
    border-radius: 5px;
}
.file-picker{
    margin-left: 6em;
}

.form-container-flex .form-row{
    width: 100%;
  
    display: flex;
    flex-direction: column;
  justify-content: center;
  align-items: center;
  margin-bottom: 1.5em;
}

.form-row p{
    color:#87DB8A;
}
.add-title{
    font-size:1.3em;
    font-weight: 400;
}

.add-btn{
    background: #87DB8A;
    color:white;
    padding:8px 30px;
    border:none;
    box-shadow: 0 3px 5px rgb(0,0,0,0.3);
    border-radius: 3px;
}

#overlay, #add-form{
    display: none;
}

#overlay{
cursor: pointer;
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


<div id="overlay"></div>
<div id="add-form" class="add-form-container">

<div class="form-container-flex">

<form method="POST" action="../controller/add-company.php" enctype="multipart/form-data">

<div class="form-row">
    <p class="add-title">Company Details</p>
</div>
<div class="form-row">
<input type="hidden" name = "user_id" value="<?php echo $_SESSION['id']?>">
</div>
   
<div class="form-row">
<input type="input" name="company_name">
<p>Company Name</p>
</div>
    <div class="form-row">
    <input class='file-picker'type="file" name="company_logo">
    <p>Company Logo</p>
    </div>  

<div class="form-row">
    <input class="add-btn" type="submit" name="add_company" value="Add company">
</div>
   
</form>

</div>

  

</div>


<div class="companies-container">

 <?php include '../controller/get-companies.php'; ?>
</div>
    

</main>

</div>

<script>

    var overlay = document.getElementById('overlay');
    var form = document.getElementById('add-form');
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
</script>


</body>
</html>