<?php
require "../../../init.php";
if (!isLoggedIn()) {
    redirect("../login.php");
}

// if ( !accountCheck() ) {
//     redirect("/logout.php");
// }

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    redirect("index.php");
}

$club_id = $_GET["id"];
$is_invite_only = 0;
$student_id = userInfo("user_id");

$sql = "
SELECT * FROM clubs 
LEFT JOIN club_memberships ON club_memberships.club_id = clubs.club_id AND club_memberships.student_id = :student_id
LEFT JOIN club_categories ON clubs.category_id = club_categories.category_id 
WHERE clubs.club_id = :club_id AND clubs.is_invite_only = :is_invite_only";

$statement = $pdo->prepare($sql);
$statement->bindParam(":club_id", $club_id);
$statement->bindParam(":student_id", $student_id);
$statement->bindParam(":is_invite_only", $is_invite_only);
$statement->execute();
$club = $statement->fetch(PDO::FETCH_ASSOC);

if (!$club) {
    redirect("index.php");
}

$year_group = userInfo("year_group");
$club_requirements = json_decode($club["club_requirments"], true);
$club_min_requirement = $club_requirements[0];
$club_max_requirement = $club_requirements[1];

if ($year_group < $club_min_requirement || $year_group > $club_max_requirement) {
    redirect("index.php");
}

// Check for conflicting enrollments
if (isset($_POST["enroll"])) {

    // Fetch ECA details
    $sql = "SELECT * FROM clubs WHERE club_id = :club_id";
    $statement = $pdo->prepare($sql);
    $statement->bindParam(":club_id", $club_id);
    $statement->execute();
    $newClub = $statement->fetch(PDO::FETCH_ASSOC);

    $membership_status = "active";

    // Check for existing enrollments with the same meeting time and day
    $sql = "
        SELECT clubs.club_name, club_memberships.*
        FROM club_memberships
        INNER JOIN clubs ON club_memberships.club_id = clubs.club_id
        WHERE club_memberships.student_id = :student_id
          AND clubs.club_id <> :current_club_id
          AND clubs.club_meeting_time = :new_meeting_time
          AND clubs.club_meeting_day = :new_meeting_day
          AND club_memberships.status = :membership_status
          ORDER BY club_memberships.membership_id DESC";
    $statement = $pdo->prepare($sql);
    $statement->bindParam(":student_id", $student_id);
    $statement->bindParam(":current_club_id", $club_id);
    $statement->bindParam(":new_meeting_time", $newClub["club_meeting_time"]);
    $statement->bindParam(":new_meeting_day", $newClub["club_meeting_day"]);
    $statement->bindParam(":membership_status", $membership_status);
    $statement->execute();
    $conflict = $statement->fetch(PDO::FETCH_ASSOC);

    if ($conflict) {
        // Redirect with the conflicting ECA name as a query parameter
        $conflictingClubName = urlencode($conflict["club_name"]);
        Flash::set("Conflicted with $conflictingClubName");
        redirect("index.php");
    }

    if ($club["student_id"] != $student_id) {
        $status = "active";
        $sql = "INSERT INTO club_memberships (club_id, student_id, status) VALUES (:club_id, :student_id, :status)";
        $statement = $pdo->prepare($sql);
        $statement->bindParam(":club_id", $club_id);
        $statement->bindParam(":student_id", $student_id);
        $statement->bindParam(":status", $status);
        $inserted = $statement->execute();
        if ($inserted) {

            // Log - Start
            $user_id = $student_id;
            $action = "joined";
            $action_by = "student";
            $sql = "INSERT INTO club_logs (club_id, user_id, action, action_by) VALUES (:club_id, :user_id, :action, :action_by)";
            $statement = $pdo->prepare($sql);
            $statement->bindParam(":club_id", $club_id);
            $statement->bindParam(":user_id", $user_id);
            $statement->bindParam(":action", $action);
            $statement->bindParam(":action_by", $action_by);
            $statement->execute();
            // End

            redirect("view.php?id=$club_id");
        }
    }

    if ($club["student_id"] == $student_id) {
        $status = "active";
        $left_at = date("Y-m-d h:m:s");
        $sql = "UPDATE club_memberships SET left_at = :left_at, status = :status WHERE membership_id = :membership_id";
        $statement = $pdo->prepare($sql);
        $statement->bindParam(":left_at", $left_at);
        $statement->bindParam(":status", $status);
        $statement->bindParam(":membership_id", $club["membership_id"]);
        $updated = $statement->execute();

        // Log - Start
        $user_id = $student_id;
        $action = "joined";
        $action_by = "student";

        $sql = "INSERT INTO club_logs (club_id, user_id, action, action_by) VALUES (:club_id, :user_id, :action, :action_by)";
        $statement = $pdo->prepare($sql);

        $statement->bindParam(":club_id", $club_id);
        $statement->bindParam(":user_id", $user_id);
        $statement->bindParam(":action", $action);
        $statement->bindParam(":action_by", $action_by);
        $logged = $statement->execute();
        // End

        if ($updated) {
            redirect("view.php?id=$club_id");
        }
    }

}

if (isset($_POST["leave"])) {
    if ($club["student_id"] == $student_id) {
        $status = "left";
        $left_at = date("Y-m-d h:m:s");
        $sql = "UPDATE club_memberships SET left_at = :left_at, status = :status WHERE membership_id = :membership_id";
        $statement = $pdo->prepare($sql);
        $statement->bindParam(":left_at", $left_at);
        $statement->bindParam(":status", $status);
        $statement->bindParam(":membership_id", $club["membership_id"]);
        $updated = $statement->execute();

        // Log - Start
        $user_id = $student_id;
        $action = "left";
        $action_by = "student";
        $sql = "INSERT INTO club_logs (club_id, user_id, action, action_by) VALUES (:club_id, :user_id, :action, :action_by)";
        $statement = $pdo->prepare($sql);
        $statement->bindParam(":club_id", $club_id);
        $statement->bindParam(":user_id", $user_id);
        $statement->bindParam(":action", $action);
        $statement->bindParam(":action_by", $action_by);
        $statement->execute();
        // End

        if ($updated) {
            redirect("view.php?id=$club_id");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/plugins/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/app.min.css">
    <title><?php echo htmlspecialchars($eca["eca_name"]); ?> - ECAS</title>
</head>

<body>
    <?php include "../layouts/partials/navbar.php";?>
    <div class="container">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6 mt-4">
                <div class="card">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4"><?php echo htmlspecialchars($club["club_name"]); ?> - ECA Details
                        </h2>
                        <p><strong>ECA Description: </strong> <?php echo htmlspecialchars($club["club_description"]); ?>
                        </p>
                        <p><strong>ECA Category: </strong> <?php echo htmlspecialchars($club["category_name"]); ?></p>
                        <p><strong>ECA Location: </strong> <?php echo htmlspecialchars($club["club_location"]); ?></p>
                        <p><strong>ECA Meeting Day: </strong>
                            <?php echo ucfirst(htmlspecialchars($club["club_meeting_day"])); ?></p>
                        <p><strong>ECA Meeting Time: </strong>
                            <?php echo ucfirst(htmlspecialchars($club["club_meeting_time"])); ?></p>
                        <form method="post" onsubmit="return confirmLeaveECA();">
                            <div class="text-center mt-4">
                                <?php if ($club["student_id"] == $student_id && $club["status"] == "kicked"): ?>
                                <p class="text-danger">You have been kicked from this club!</p>
                                <?php else: ?>

                                <?php if (empty($club["student_id"]) || $club["student_id"] == $student_id && $club["status"] == "left"): ?>
                                <button type="submit" name="enroll" class="btn btn-success">Enroll</button>
                                <?php endif;?>


                                <?php if ($club["student_id"] == $student_id && $club["status"] == "active"): ?>
                                <button type="submit" name="leave" class="btn btn-danger">Leave</button>
                                <?php endif;?>
                                <?php endif;?>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-3"></div>
            </div>
        </div>
    </div>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmLeaveECA() {
        if (document.querySelector('button[name="leave"]')) {
            return confirm('Are you sure you want to leave this ECA?');
        }
        return true;
    }
    </script>
</body>

</html>