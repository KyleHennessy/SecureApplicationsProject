<?php
require_once "db.php";
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css"/>
    <link rel="stylesheet" href="site.css"/>
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
<nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
            <a class="navbar-brand" href="home.php">Kyle Hennessy Auth App</a>
            </div>
            <ul class="nav navbar-nav">
            <li><a href="home.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li class="active"><a href="contact.php">Contact Us</a></li>
            <li><a href="changepassword.php">Change Password</a></li>
            <li><a href="eventlog.php">Event Log</a></li>
            <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <h2>Contact Us</h2>
        <p>Contact me at C00227463@itcarlow.ie</p>
        <p>If you are seeing this, it means that you are an authenticated user. Congratulations</p>
        
    </div>
</body>
</html>