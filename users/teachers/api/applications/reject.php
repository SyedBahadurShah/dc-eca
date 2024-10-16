<?php
require "../../../../init.php";
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}
