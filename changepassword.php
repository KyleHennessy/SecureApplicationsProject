<?php
require_once "db.php";
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

if (time()-$_SESSION["sessiontimer"] > 3600){
    session_unset();
    session_destroy();
    header("location: login.php");
    exit;
}

$password = $confirmPassword = "";
$passwordError = $confirmPasswordError = "";
$token = isset($_SESSION["changepasswordtoken"]) ? $_SESSION["changepasswordtoken"] : "";
if(!isset($_GET["password"]) || !isset($_GET["confirmPassword"])){
    $_GET["password"] = $_GET["confirmPassword"] = "";
    $_GET["token"] = $token;
}
if($_SERVER["REQUEST_METHOD"] == "GET"){
    $containsuppercase = preg_match('/[A-Z]/', $_GET["password"]);
    $containslowercase = preg_match('/[a-z]/', $_GET["password"]);
    $containsnumber    = preg_match('/\d/', $_GET["password"]);
    $containsspecialchars = preg_match('/[^\w]/', $_GET["password"]);
    if(!$containsuppercase || !$containslowercase || !$containsnumber || !$containsspecialchars || strlen($_GET["password"]) < 8){
        $passwordError = "Passwords should be: <ul>
                                                    <li>8 characters long</li>
                                                    <li>Contains 1 lowercase letter</li>
                                                    <li>Contains 1 uppercase letter</li>
                                                    <li>Contains 1 number</li>
                                                    <li>Contains 1 special character</li>
                                                </ul>";
    }
    else{
        $password = $_GET["password"];
        $password = PreventXSS($password);   
    }

    if(empty($_GET["confirmPassword"])){
        $confirmPasswordError = "Please enter a password that matches your new one.";
    }
    else{
        $confirmPassword = $_GET["confirmPassword"];
        $confirmPassword = PreventXSS($confirmPassword);
        if(empty($passwordError) && ($password != $confirmPassword)){
            $confirmPasswordError = "Passwords do not match";
        }
    }
    
    if ($_GET["token"] === $token){
        if(empty($passwordError) && empty($confirmPasswordError)){
            $isUnique = false;
            $sql = "SELECT * FROM my_db.user WHERE passwordsalt = ?";
            while (!$isUnique){
                if($stmt = $mysqli->prepare($sql)){
                    $salt = random_bytes(32);
                    $hashedSalt = md5($salt);
                    $stmt->bind_param("s", $paramSalt);
                    $paramSalt = $hashedSalt;
                    if($stmt->execute()){
                        $stmt->store_result();
                        if($stmt->num_rows == 0){
                            $isUnique = true;
                            $passwordSalt = $hashedSalt;
                        }
                    }
                }
                $stmt->close();
            }
            $sql = "";
            $newHashedPassword = md5($passwordSalt.$password);
            $sql = "UPDATE my_db.user SET passwordhash = ?, passwordsalt = ? WHERE userid = ?";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("ssi",$paramPassword,$paramSalt,$paramId);

                $paramPassword = $newHashedPassword;
                $paramSalt = $passwordSalt;
                $paramId = $_SESSION["userid"];

                if($stmt->execute()){
                    session_destroy();
                    unset($_SESSION["changepasswordtoken"]);
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
        
    }
    else{
        header("location: logout.php");
        exit;
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
    <meta http-equiv="refresh" content="900;url=logout.php"/>
    <style type="text/css">
        body{ font: 14px sans-serif; }
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
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="GET">
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
       
        </form>
    </div>
</body>
</html>