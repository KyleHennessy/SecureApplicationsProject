<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');

$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

if($mysqli == false){
    die("ERROR: Could not connect. " .$mysqli->connect_error);
}

function PreventXSS($string)
{
    $sanitizedString ='';
    $string = str_split($string);
    for ($position=0;$position<count($string);$position++){
        $char = $string[$position];
        switch($char){
            case "<":
                $char = "&lt;";
            break;
            case ">":
                $char = "&gt;";
            break;
            case "'":
                $char = "&lsquo;";
            break;
            case '"':
                $char = "&quot;";
            break;
            case "&":
                $char = "&amp;";
            break;
            case "\\":
                $char = "&bsol;";
            break;
            case "/":
                $char = "&sol;";
            break;
            case "(":
                $char = "&lpar;";
            break;
            case ")":
                $char = "&rpar;";
            break;
            case "{":
                $char = "&lcub;";
            break;
            case "}":
                $char = "&rcub;";
            break;
            case "[":
                $char = "&lsqb;";
            break;
            case "]":
                $char = "&rsqb;";
            break;
            case ";":
                $char = "&semi;";
            break;
        }
        $sanitizedString .= $char;
    }
    return $sanitizedString;
}
?>