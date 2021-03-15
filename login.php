<?php
session_start();
//check if user is logged in already
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: home.php");
    exit;
}

require_once "db.php";

$username = "";
$password = "";
$usernameError = "";
$passwordError = "";


if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_SESSION["loginCounter"])){
        $_SESSION["loginCounter"]++;
        if ($_SESSION["loginCounter"] > 5){
            echo "<p class='text-danger'>You have failed to login too many times</p>";
            exit;
        }
    }
    else{
        $_SESSION["loginCounter"] = 1;
    }
    //validate username
    if(empty ($_POST["username"])){
        $usernameError = "Please enter a valid username.";
    } else{
        $username = $_POST["username"];
        $username = PreventXSS($username);
    }
    //validate password
    if(empty($_POST["password"])){
        $passwordError = "please enter a password";
    } else{
        $password = $_POST["password"];
        $password = PreventXSS($password);
    }

    if(empty($usernameError) && empty($passwordError)){
        $sql = "SELECT userid, username, passwordhash, passwordsalt, isadmin FROM user WHERE username = ?";        
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $paramUsername);
            $paramUsername = $username;

            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows == 1){
                    $userid = $username = $passwordhash = $passwordsalt = $isadmin = "";
                    $stmt->bind_result($userid, $username, $passwordhash, $passwordsalt, $isadmin);
                    $stmt->fetch();

                    $hashedpassword = md5($passwordsalt.$password);
                    if($passwordhash == $hashedpassword){
                        session_start();

                        $_SESSION["loggedin"] = true;
                        $_SESSION["userid"] = $userid;
                        $_SESSION["username"] = $username;
                        $_SESSION["changepasswordtoken"] = md5(uniqid());
                        $_SESSION["isadmin"] = $isadmin;
                        header("location:home.php");
                        $loginsuccessdescription = "Successful login for username: '" . $username . "' and userId: '" . $userid . "'";
                    }
                }
                echo "<p class='text-danger'>The username '" . $username ."' and password could not be authenticated at the moment</p>";
                $loginfaileddescription = "Unsuccessful login for username: " . $username;
            }
            else{
                echo "Could not connect to the database <BR/>";
            }
            $stmt->close();
        }
    }
    $sql = "";
    if (isset($loginsuccessdescription)){
        $type = "SUCCESS";
        $description = $loginsuccessdescription;
    }
    else{
        $type = "FAILURE";
        $description = $loginfaileddescription;
    }
    $datetime = date('Y-m-d H:i:s');
    $sql = "INSERT INTO eventlog (type, description, datetimeoccured) VALUES (?, ?, ?)";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("sss", $paramType, $paramDescription, $paramDateTimeOccured);
        $paramType = $type;
        $paramDescription = $description;
        $paramDateTimeOccured = $datetime;
        $stmt->execute();
        $stmt->close();
    }
    $mysqli->close();
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
    <div class="wrapper">
        <h2>Login</h2>
        <p>Enter in your login details</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <div class="form-group <?php echo (!empty($usernameError)) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block">
                    <?php echo $usernameError; ?>
                </span>
            </div>
            <div class="form-group <?php echo (!empty($passwordError)) ? 'has-error' : '';?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
                <span class="help-block">
                    <?php echo $passwordError; ?>
                </span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-success" value="Login">
            </div>
            <p><a href="register.php"> Or register here if you dont have an account.</a></p>
        </form>
    </div>
</body>
</html>