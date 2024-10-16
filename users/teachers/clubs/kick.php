<?php
require "../../../init.php";
if (!isLoggedIn()) {
    redirect("../login.php");
}

if ( !isset($_GET["membership_id"]) || empty($_GET["membership_id"])) {
    redirect("index.php");
}

$created_by = userInfo("user_id");
$membership_id = $_GET["membership_id"];

$sql = "
    SELECT * FROM club_memberships
    LEFT JOIN clubs ON clubs.club_id = club_memberships.club_id
    WHERE membership_id = :membership_id AND clubs.created_by = :created_by 
";

$statement = $pdo->prepare($sql);
$statement->bindParam(":membership_id", $membership_id);
$statement->bindParam(":created_by", $created_by);
$statement->execute();
$membership = $statement->fetch(PDO::FETCH_ASSOC);

if (!$membership) {
    redirect("index.php");
}

$status = "kicked";
$sql = "UPDATE club_memberships SET status = :status WHERE membership_id = :membership_id";
$statement = $pdo->prepare($sql);
$statement->bindParam(":status", $status);
$statement->bindParam(":membership_id", $membership_id);
$kicked = $statement->execute();

if ($kicked) {
    $action = "kicked";
    $action_by = "teacher";
    $club_id = $membership["club_id"];
    $user_id = $membership["student_id"];

    $sql = "INSERT INTO club_logs (club_id, user_id, action, action_by) VALUES (:club_id, :user_id, :action, :action_by)";
    $statement = $pdo->prepare($sql);
    $statement->bindParam(":club_id", $club_id);
    $statement->bindParam(":user_id", $user_id);
    $statement->bindParam(":action", $action);
    $statement->bindParam(":action_by", $action_by);
    $logged = $statement->execute();

    if ($logged) {
        redirect("view.php?id=" . $club_id);
    }

}
