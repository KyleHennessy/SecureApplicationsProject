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
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
        <a class="navbar-brand" href="home.php">Kyle Hennessy Auth App</a>
        </div>
        <ul class="nav navbar-nav">
        <li class="active"><a href="home.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact Us</a></li>
        <li><a href="changepassword.php">Change Password</a></li>
        <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>
<div class="container">
<?php
require_once "db.php";
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
if($_SESSION["isadmin"] == 0){
    echo "<h1 class='text-danger'> ACCESS DENIED </h1>
          <p> You must have administrator privilages to see this page </p>";
}
else{
    $sql = "SELECT * FROM eventlog";
    $result = $mysqli->query($sql);
    echo "<table class='table table-striped'>
          <thead>
                <tr>
                    <th>Event ID</th>
                    <th>Event Type</th>
                    <th>Description</th>
                    <th>Date & Time Occured</th>
                </tr>
            </thead>
            <tbody>";
    while($resultRow = $result->fetch_row()){
        echo "<tr>";
        for ($i = 0; $i < $result->field_count; $i++){
            echo "<td>$resultRow[$i]</td>";
        }
        echo "<tr/>";
    }
}
?>
</div>