<?php
require "../../../../init.php";
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$output = [];
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    $output = ["error" => "incorrect invite id"];
    echo json_encode($output);
}

$invite_id = $_GET["id"];
$sql = "SELECT application FROM club_invites WHERE invite_id = :invite_id";
$statement = $pdo->prepare($sql);
$statement->bindParam(":invite_id", $invite_id);
$statement->execute();
$data = $statement->fetch(PDO::FETCH_ASSOC);

if ($data) {
    $output = $data["application"];
    echo json_encode($output);
}