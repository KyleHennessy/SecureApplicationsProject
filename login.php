<?php
set_time_limit(500);
session_start();
//check if user is logged in already
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: home.php");
    exit;
}

require_once "db.php";

//if database does not exist
$database_sql = "CREATE DATABASE IF NOT EXISTS my_db";
$db_selected = $mysqli->select_db("my_db");
if(!$db_selected){
    $database_sql = "CREATE DATABASE IF NOT EXISTS my_db";
    if($mysqli->query($database_sql)){
        echo "<BR/>Database created successfully! Tables will be created upon visiting the register page";
    }
}

$username = "";
$password = "";
$usernameError = "";
$passwordError = "";


if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_SESSION["loginCounter"])){
        $_SESSION["loginCounter"]++;
        if ($_SESSION["loginCounter"] > 4){
            echo "<head><link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css'/></head><h2 class='text-danger'>You have failed to login too many times. Try again in 3 minutes</p></h2>";
            ob_end_flush();
            flush();
            sleep(180);
            $_SESSION["loginCounter"] = 0;
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
                        $_SESSION["sessiontimer"] = time();
                        $_SESSION["changepasswordtoken"] = md5(uniqid());
                        $_SESSION["isadmin"] = $isadmin;
                        header("location:home.php");
                        $loginsuccessdescription = "Successful login for username: '" . $username . "' and userId: '" . $userid . "'";
                    }
                }
                echo "<p class='text-danger'>The username '" . preventXSS($username) ."' and password could not be authenticated at the moment</p>";
                if($passwordError != "please enter a password"){
                    $loginfaileddescription = "Unsuccessful login for username: " . $username;
                }
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
        if ($passwordError === "please enter a password"){
            $type = "FAILURE";
            $description = "Fields were left empty";
        }
        else{
            $type = "FAILURE";
            $description = $loginfaileddescription;
        }
    }
    $datetime = date('Y-m-d H:i:s');
    $sql = "INSERT INTO my_db.eventlog (type, description, datetimeoccured) VALUES (?, ?, ?)";
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
        .wrapper{ width: 500px; padding: 20px; }
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