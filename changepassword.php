<?php
require_once "db.php";
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$password = $confirmPassword = "";
$passwordError = $confirmPasswordError = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $containsuppercase = preg_match('/[A-Z]/', $_POST["password"]);
    $containslowercase = preg_match('/[a-z]/', $_POST["password"]);
    $containsnumber    = preg_match('/\d/', $_POST["password"]);
    $containsspecialchars = preg_match('/[^\w]/', $_POST["password"]);
    if(!$containsuppercase || !$containslowercase || !$containsnumber || !$containsspecialchars || strlen($_POST["password"]) < 8){
        $passwordError = "Passwords should be: <ul><li>8 characters long</li><li>Contains 1 lowercase letter</li><li>Contains 1 uppercase letter</li><li>Contains 1 number</li><li>Contains 1 special character</li></ul>";
        echo "This is uppercase:$containsuppercase<br/>this is lowercase:$containslowercase<br/>this is number:$containsnumber<br/>this is special:$containsspecialchars<br/>";
    }
    else{
        $password = $_POST["password"];
        $password = PreventXSS($password);   
    }

    if(empty($_POST["confirmPassword"])){
        $confirmPasswordError = "Please enter a password that matches your new one.";
    }
    else{
        $confirmPassword = $_POST["confirmPassword"];
        $confirmPassword = PreventXSS($confirmPassword);
        if(empty($passwordError) && ($password != $confirmPassword)){
            $confirmPasswordError = "Passwords do not match";
        }
    }
    $token = isset($_SESSION["changepasswordtoken"]) ? $_SESSION["changepasswordtoken"] : "";
    if ($_POST["token"] === $token){
        if(empty($passwordError) && empty($confirmPasswordError)){
            $sql = "SELECT passwordsalt FROM my_db.user WHERE userid = ?";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("i", $paramUserId);
                $paramUserId = $_SESSION["userid"];
                if($stmt->execute()){
                    $stmt->store_result();
                    if($stmt->num_rows==1){
                        $passwordSalt = "";
                        $stmt->bind_result($passwordSalt);
                        $stmt->fetch();
                        echo $passwordSalt . "<BR/>";
                        $newHashedPassword = md5($passwordSalt.$password);
                        echo $newHashedPassword . "<BR/>";
                    }
                }
                else{
                    echo "Cant find user <BR/>";
                }
                $stmt->close();
            }
            $sql = "";
            $sql = "UPDATE my_db.user SET passwordhash = ? WHERE userid = ?";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("si",$paramPassword,$paramId);

                $paramPassword = $newHashedPassword;
                $paramId = $_SESSION["userid"];

                if($stmt->execute()){
                    session_destroy();
                    echo "<BR/>password changed. New password hash = " . $newHashedPassword;
                    echo "<BR/>password Salt = " . $passwordSalt;
                    header("location: login.php");
                    exit;
                }
                else{
                    echo "Unable to change password. Please try again<br/>";
                }
                $stmt->close();
            }
        }
        $mysqli->close();
        unset($_SESSION["changepasswordtoken"]);
    }
    else{
        //log csrf attack
    }
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
            <li><a href="contact.php">Contact Us</a></li>
            <li class="active"><a href="changepassword.php">Change Password</a></li>
            <li><a href="eventlog.php">Event Log</a></li>
            <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <h2>Reset Password</h2>
        <p>To change your password please enter it below</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <input type="hidden" name="token" value="<?php echo $token; ?>" />
            <div class="form-group <?php echo (!empty($passwordError)) ? 'has-error' : '';?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
                <span class="help-block">
                    <?php echo $passwordError; ?>
                </span>
            </div>
            <div class="form-group <?php echo(!empty($confirmPasswordError)) ? 'has-error' : '';?>">
                <label>Confirm Password</label>
                <input type="password" name="confirmPassword" class="form-control" value="<?php echo $confirmPassword; ?>">
                <span class="help-block">
                    <?php echo $confirmPasswordError; ?>
                </span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-danger" value="Reset">
            </div>
            <p><a href="login.php"> Or login here.</a></p>
        </form>
    </div>
</body>
</html>