<?php
require_once "db.php";

$user_sql ="CREATE TABLE my_db.`user` (
    `userid` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(255) NOT NULL,
    `passwordhash` char(60) NOT NULL,
    `passwordsalt` char(60) NOT NULL,
    `creationdatetime` datetime NOT NULL,
    `isadmin` tinyint(1) NOT NULL,
    PRIMARY KEY (`userid`),
    UNIQUE KEY `username` (`username`)
   )";
$eventlog_sql = "CREATE TABLE my_db.`eventlog` (
    `logid` int(11) NOT NULL AUTO_INCREMENT,
    `type` varchar(10) NOT NULL,
    `description` text NOT NULL,
    `datetimeoccured` datetime NOT NULL,
    PRIMARY KEY (`logid`)
   )";

if($mysqli->query($user_sql)){
    echo "<BR/>User table created!";
}
if($mysqli->query($eventlog_sql)){
    echo "<BR/>Eventlog table created!";
}


$admin_salt = random_bytes(32);
$admin_hashed_salt = md5($admin_salt);
$admin_creation_datetime = date("Y-m-d H:i:s");
$admin_password_hash = md5($admin_hashed_salt."SAD_2021!");

$admin_sql = "INSERT INTO my_db.user (username, passwordhash, passwordsalt, creationdatetime, isadmin) VALUES (?, ?, ?, ?, ?)";
if($admin_stmt = $mysqli->prepare($admin_sql)){
    $admin_stmt->bind_param("ssssi", $paramUsername, $paramPasswordHash, $paramPasswordSalt, $paramCreationDateTime, $paramIsAdmin);
    $paramUsername = "ADMIN";
    $paramPasswordHash = $admin_password_hash;
    $paramPasswordSalt = $admin_hashed_salt;
    $paramCreationDateTime = date("Y-m-d H:i:s");
    $paramIsAdmin = 1;

    if($admin_stmt->execute()){
        echo "<BR/> Admin user created";
    }
    $admin_stmt->close();
}

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
    }
    
    $containsuppercase = preg_match('/[A-Z]/', $_POST["password"]);
    $containslowercase = preg_match('/[a-z]/', $_POST["password"]);
    $containsnumber    = preg_match('/\d/', $_POST["password"]);
    $containsspecialchars = preg_match('/[^\w]/', $_POST["password"]);
    if(!$containsuppercase || !$containslowercase || !$containsnumber || !$containsspecialchars || strlen($_POST["password"]) < 8){
        $passwordError = "Passwords should be: <ul>
                                                   <li>8 characters long</li>
                                                   <li>Contains 1 lowercase letter</li>
                                                   <li>Contains 1 uppercase letter</li>
                                                   <li>Contains 1 number</li>
                                                   <li>Contains 1 special character</li>
                                                </ul>";
    }
    else{
        $password = $_POST["password"];
        $password = PreventXSS($password);   
    }
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
    
    //create unique salt
    $isUnique = false;
    $sql = "SELECT * FROM my_db.user WHERE passwordsalt = ?";
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
    //hash password and salt together
    $passwordHash = md5($passwordSalt.$password);
    $creationDateTime = date("Y-m-d H:i:s");
    if(empty($usernameError) && empty($passwordError) && empty($confirmPasswordError)){
        $sql = "INSERT INTO my_db.user (username, passwordhash, passwordsalt, creationdatetime) VALUES (?, ?, ?, ?)";
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