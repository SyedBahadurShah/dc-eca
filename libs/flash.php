<?php
class Flash
{
    public static function set($message)
    {
        $_SESSION["flash"] = $message;
    }

    public static function display()
    {
        if (isset($_SESSION["flash"])) {
            $flash = $_SESSION["flash"];
            unset($_SESSION["flash"]);
            return $flash;
        }
        return "";
    }

    public static function exists()
    {
        if (isset($_SESSION["flash"])) {
            return true;
        }
        return false;
    }
}