<?php
require_once "db.php";

$username = "";
$password = "";
$confirmPassword = "";
$usernameError = "";
$passwordError = "";
$confirmPasswordError="";
$passwordHash = "";
$passwordSalt = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(empty ($_POST["username"])){
        $usernameError = "Please enter a valid username.";
    } else{
        $username = $_POST["username"];
        $username = PreventXSS($username);
        echo $username . "<BR/>";
    }
    // //validate password
    // if(empty($_POST["password"])){
    //     $passwordError = "please enter a password";
    // } 
    // elseif(strlen($_POST["password"]) < 8){
    //     $passwordError = "must be greater than 8 characters";
    // }
    // elseif(!preg_match("#[0-9]+#", $password)){
    //     $passwordError = "Password must include 1 number";
    // }
    // elseif(!preg_match("#[a-z]+#", $password)){
    //     $passwordError = "Password must include a lower case letter";
    // }
    // elseif(!preg_match("#[A-Z]+#", $password)){
    //     $passwordError = "Password must include an upper case letter";
    // }
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
    echo $password . "<BR/>";

    //validate confirm password
    if(empty($_POST["confirmPassword"])){
        $confirmPasswordError = "Please confirm password";
    } else{
        $confirmPassword = $_POST["confirmPassword"];
        $confirmPassword = PreventXSS($confirmPassword);
        if(empty($passwordError) && ($password != $confirmPassword)){
            $confirmPasswordError = "Passwords do not match";
        }
    }
    echo $confirmPassword . "<BR/>";

    //create unique salt
    $isUnique = false;
    $sql = "SELECT * FROM user WHERE passwordsalt = ?";
    while (!$isUnique == true){
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
    echo $salt . "<BR/>";

    //hash password and salt together
    $passwordHash = md5($passwordSalt.$password);
    echo $passwordHash . "<BR/>";

    $creationDateTime = date("Y-m-d H:i:s");
    echo $creationDateTime . "<BR/>";
    // $usernameError = $passwordError = $confirmPasswordError = "";
    if(empty($usernameError) && empty($passwordError) && empty($confirmPasswordError)){
        $sql = "INSERT INTO user (username, passwordhash, passwordsalt, creationdatetime) VALUES (?, ?, ?, ?)";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("ssss", $paramUsername, $paramPasswordHash, $paramPasswordSalt, $paramCreationDateTime);
            $paramUsername = $username;
            $paramPasswordHash = $passwordHash;
            $paramPasswordSalt = $passwordSalt;
            $paramCreationDateTime = $creationDateTime;
            if($stmt->execute()){
                header("location: login.php");
            } else{
                echo "Unable to register, username may not be unique.";
            }
            $stmt->close();
        }
    }
    $mysqli->close();
    echo "SQL did not execute";
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
        <h2>Sign up</h2>
        <p>Enter your details below to register.</p>
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