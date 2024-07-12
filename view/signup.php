<?php
session_start();

if(isset($_SESSION["login"])){

    if($_SESSION["role"] == "admin"){
        header("location: view/admin.php");
    }

    else if ($_SESSION["role"] == "user"){
        header('location: view/dashboard.php');
    }


    
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link rel="stylesheet" href="../styles/font.css">
    <style>

*{
    padding:0;
    margin:0;
    box-sizing: border-box;
}

body,html{
    background:#87DB8A;
}

.login{
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
   
}

.login-container{
display: flex;
flex-direction: column;
justify-content: center;
align-items: center;
height: 100vh;
}

.login-flexbox{
   
    display: flex;
    height: 60vh;
  margin:0 auto;

}

.banner{

}

.banner img{
   height: 100%;
   box-shadow: 0 3px 5px rgb(0,0,0,0.1);
   border-top-left-radius: 10px; /* Adjust the value as needed */
  border-bottom-left-radius: 10px; /* Adjust the value as needed */
}

.login-form{
    background:white;
   padding:2em 8em;
   display: flex;
   align-items: center;
   box-shadow: 0 3px 5px rgb(0,0,0,0.1);
   border-top-right-radius: 10px; /* Adjust the value as needed */
   border-bottom-right-radius: 10px; /* Adjust the value as needed */
}

.login-form h1{
    color:#87DB8A;
    text-transform: uppercase;
    text-align: center;
    margin-bottom: 1em;
 
 
}

.form-row{
display: flex;
flex-direction: column;
text-align: center;
align-items: center;
margin-bottom: 1em;
}

.form-row input{
    background:none;
    border:none;
  
    width: 100%;
    text-align: center;
}
.inputs input{
    border-bottom:1px solid #87DB8A;
    width: 100%;
    text-align: center;
}
.form-row p{
    text-transform: uppercase;
    color: #87DB8A;
    margin-top: 3px;
    font-size: 14px;
}

.form-row-btn .login-btn{
    background-color: #87DB8A;
    margin-top:2em;
    padding:8px;
    color:white;
    text-transform: uppercase;
    border-radius: 5px;
    box-shadow: 0 3px 5px rgb(0,0,0,0.1);
    cursor: pointer;
 }
 
 .form-row-btn  a{
    color: #87DB8A;
    text-decoration: none;
    font-size: 10px;
    margin-top: 5px;
 }

 .err{
    font-size: 10px;
    color:red;
 }
 </style>
</head>


<body>
    


<div class="login">

    <div class="login-container">

            <div class="login-flexbox">

                    <div class="banner">
                        <img src="../assets/banner.png">
                    </div>


                    <div class="login-form">
                        <form method = "POST" action = "../controller/add-user.php">

                        <h1>Signup</h1>

                        <div class="form-row inputs">
                                <input required type="text" name="name">
                                <p>name</p>
                            </div>

                            <div class="form-row inputs">
                                <input required type="text" name="username">
                                <p>username</p>
                            </div>

                           

                            <div class="form-row inputs">
                                <input required type="password" name="password">
                                <p>password</p>
                            </div>

                            <div class="form-row inputs">
                                <input required type="password" name="confirm_password">
                                <p>confirm password</p>
                            </div>


                            

<?php
if (isset($_GET['err']) && $_GET['err']) {
    echo "<p class='err'>Invalid Username or Wrong Password</p>";
}
?>
                            <div class="form-row form-row-btn">
                                <input class="login-btn" type="submit" name="login" value="Create account">
                                <a href="../index.php">Already have an account</a>
                            </div>

                        </form>

                   
                    </div>
            </div>
    </div>
</div>


</body>
</html>