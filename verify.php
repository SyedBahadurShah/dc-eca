<?php
require "init.php";
if (isset($_GET["token"]) && !empty($_GET["token"])) {
    $token = $_GET["token"];

    $sql = "SELECT * FROM email_verify WHERE token = :token";
    $statement = $pdo->prepare($sql);
    $statement->bindParam(":token", $token);
    $statement->execute();
    $data = $statement->fetch(PDO::FETCH_ASSOC);
    if ($data) {
        $user_id = $data["user_id"];
        $is_active = 1;

        $sql = "UPDATE users SET is_active = :is_active WHERE id = :id";
        $statement = $pdo->prepare($sql);
        $statement->bindParam("is_active", $is_active);
        $statement->bindParam(":id", $user_id);
        $statement->execute();
        echo 'Your account is successfully verified and activated, click here to <a href="login.php">login</a>';
    }
}