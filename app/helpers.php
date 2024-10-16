<?php
function isLoggedIn()
{
    return isset($_SESSION["email"]) ? true : false;
}

function redirect($location)
{
    header("Location: $location");
    exit();
}

function accountType($type)
{
    if (isset($_SESSION["account_type"]) && $_SESSION["account_type"] == $type) {
        return true;
    }
    return false;
}

function userInfo($info)
{
    if (isset($_SESSION[$info]) && !empty($_SESSION[$info])) {
        return $_SESSION[$info];
    }
}

function dd($data)
{
    echo "<pre>";
    print_r($data);
    exit();
}

function clean($string)
{
    return preg_replace("/[^A-Za-z0-9,.?!-_\ ]/", '', $string); // Removes special chars.
}


function accountCheck( ) {
    $path = $_SERVER["SCRIPT_NAME"];
    $path = explode("/", $path);
    if ( $path[2] == userInfo("account_type") ) return true;
    return false;
}