<?php
require "../../../../init.php";
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["invite_id"]) || empty($_GET["invite_id"])) {
    redirect("index.php");
}

if (!isset($_GET["status"]) || empty($_GET["status"])) {
    redirect("index.php");
}

$output = [];
$errorMessage = "Oops! something went wrong!";

$invite_id = $_GET["invite_id"];
$invite_status = "pending";

$sql = "SELECT * FROM club_invites WHERE invite_id = :invite_id AND status = :invite_status";
$statement = $pdo->prepare($sql);
$statement->bindParam(":invite_id", $invite_id);
$statement->bindParam(":invite_status", $invite_status);
$statement->execute();
$application = $statement->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    redirect("index.php");
}

try {

    $status = "active";
    $student_id = $application["student_id"];

    // Batch/Transaction - Start (  1 / 0 )
    $pdo->beginTransaction();

    // Step #4
    $status = $_GET["status"];
    $invite_id = $application["invite_id"];

    $sql = "UPDATE club_invites SET status = :status WHERE invite_id = :invite_id";
    $statement = $pdo->prepare($sql);
    $statement->bindParam(":status", $status);
    $statement->bindParam(":invite_id", $invite_id);
    $updated = $statement->execute();

    if ($updated && $status != "rejected") {

        // Step #1
        $membership_status = "active";
        $sql = "INSERT INTO club_memberships (club_id, student_id, status) VALUES (:club_id, :student_id, :status)";
        $statement = $pdo->prepare($sql);
        $statement->bindParam(":club_id", $application["club_id"]);
        $statement->bindParam(":student_id", $application["student_id"]);
        $statement->bindParam(":status", $membership_status);
        $inserted = $statement->execute();

        if ($inserted) {

            // Step #2
            $action = "invite_accepted";
            $action_by = "student";

            $sql = "INSERT INTO club_logs (club_id, user_id, action, action_by) VALUES (:club_id, :user_id, :action, :action_by)";
            $statement = $pdo->prepare($sql);
            $statement->bindParam(":club_id", $application["club_id"]);
            $statement->bindParam(":user_id", $student_id);
            $statement->bindParam(":action", $action);
            $statement->bindParam(":action_by", $action_by);
            $inserted = $statement->execute();

            if ($inserted) {
                // Step #3
                $action = "joined";
                $action_by = "teacher";

                $sql = "INSERT INTO club_logs (club_id, user_id, action, action_by) VALUES (:club_id, :user_id, :action, :action_by)";
                $statement = $pdo->prepare($sql);
                $statement->bindParam(":club_id", $application["club_id"]);
                $statement->bindParam(":user_id", $student_id);
                $statement->bindParam(":action", $action);
                $statement->bindParam(":action_by", $action_by);
                $inserted = $statement->execute();

                if ($inserted) {
                    echo json_encode(["status" => "success"]);
                }

            }
        }

    } else {
        $action = "invite_rejected";
        $action_by = "teacher";

        $sql = "INSERT INTO club_logs (club_id, user_id, action, action_by) VALUES (:club_id, :user_id, :action, :action_by)";
        $statement = $pdo->prepare($sql);
        $statement->bindParam(":club_id", $application["club_id"]);
        $statement->bindParam(":user_id", $student_id);
        $statement->bindParam(":action", $action);
        $statement->bindParam(":action_by", $action_by);
        $inserted = $statement->execute();
        if ($inserted) {
            echo json_encode(["status" => "success"]);
        }
    }

    $pdo->commit();
    // End

} catch (PDOException $error) {
    $pdo->rollBack();
    $output["error"] = $errorMessage;
    echo json_encode($output);
    exit();
}

/**
 * Membership - Done
 * Log
 * Update Status Invitation
 */
