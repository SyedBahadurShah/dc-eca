<?php
require "../../../init.php";
if (!isLoggedIn()) {
    redirect("../login.php");
}

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    redirect("index.php");
}

$club_id = $_GET["id"];
$is_invite_only = 1;
$student_id = userInfo("user_id");

$sql = "SELECT *, club_invites.status as invite_status, club_memberships.status as membership_status FROM clubs LEFT JOIN club_invites ON clubs.club_id = club_invites.club_id LEFT JOIN club_memberships ON club_memberships.club_id = club_invites.club_id LEFT JOIN club_categories ON clubs.category_id = club_categories.category_id WHERE clubs.club_id = :club_id";

$statement = $pdo->prepare($sql);
$statement->bindParam(":club_id", $club_id);
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

$display = "";

if (empty($club["invite_status"]) && empty($club["membership_status"])) {
    $display = '
    <div class="mb-3 text-center mt-4">
    <h4>How to Apply?</h4>
    <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Facere a sapiente blanditiis
        pariatur, iste praesentium vitae voluptates temporibus? Accusamus delectus reprehenderit
        est tempore molestiae dolor totam repellendus sapiente ab atque?</p>
    <label for="exampleFormControlTextarea1" class="form-label"><strong>Write your
            application bellow: </strong></label>
    <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
                <button type="submit" name="apply" class="btn btn-success mt-4">Apply</button>
    </div>
    ';
}

if (($club["invite_status"] == "pending" && empty($club["membership_status"])) ||
    ($club["invite_status"] == "accepted" && empty($club["membership_status"]))
) {
    $display = '
        <p class="text-center text-primary mt-4">You have already applied for this club and your application is under review.
    ';
}

if ($club["invite_status"] == "accepted" && $club["membership_status"] == "active") {
    $display = '
    <div class="text-center">
        <p class="text-center mt-4">You are already enrolled in this club!</p>
    <button type="submit" name="leave" class="btn btn-danger">Leave</button></div>
    ';
}

if ($club["invite_status"] == "accepted" && $club["membership_status"] == "left") {
    $display = '<p class="text-center text-danger mt-4">You left this club!</p></div>';
}

if ($club["invite_status"] == "accepted" && $club["membership_status"] == "kicked") {
    $display = '<p class="text-center text-danger mt-4">You have been kicked from this club and you are not allowed join this club!</p>';
}

if ($club["invite_status"] == "rejected") {
    $display = '<p class="text-center text-danger mt-4">You application has been rejected by club teacher!</p>';
}

// Check for conflicting enrollments
if (isset($_POST["apply"])) {

    $sql = "SELECT * FROM club_invites WHERE student_id = :student_id";
    $statement = $pdo->prepare($sql);
    $statement->bindParam(":student_id", $student_id);
    $statement->execute();
    $applications = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (count($applications) > 1) {
        Flash::set("You can't join more than 2 invite only clubs!");
        redirect("index.php");
    }

    $sql = "SELECT * FROM clubs WHERE club_id = :club_id";
    $statement = $pdo->prepare($sql);
    $statement->bindParam(":club_id", $club_id);
    $statement->execute();
    $newClub = $statement->fetch(PDO::FETCH_ASSOC);

    $sql = "
        SELECT *
        FROM clubs
        LEFT JOIN club_memberships ON club_memberships.club_id = clubs.club_id
        LEFT JOIN club_invites ON club_invites.club_id = clubs.club_id
        WHERE (club_memberships.membership_id IS NOT NULL OR club_invites.invite_id IS NOT NULL)
        AND (club_memberships.student_id = :student_id OR club_invites.student_id = :student_id)
        AND clubs.club_meeting_day = :meeting_day
        AND clubs.club_meeting_time = :meeting_time
        AND clubs.club_id <> :current_club_id
    ";

    $statement = $pdo->prepare($sql);
    $statement->bindParam(":student_id", $student_id);
    $statement->bindParam(":meeting_day", $newClub["club_meeting_day"]);
    $statement->bindParam(":meeting_time", $newClub["club_meeting_time"]);
    $statement->bindParam(":current_club_id", $club_id);

    $statement->execute();
    $conflict = $statement->fetch(PDO::FETCH_ASSOC);

    if ($conflict) {
        $conflictingClubName = urlencode($conflict["club_name"]);
        Flash::set("Conflict with $conflictingClubName. You are already enrolled or invited to a club that meets at the same time and day.");
        redirect("index.php");
    }

    if (empty($club["invite_status"]) && empty($club["membership_status"])) {
        $status = "pending";
        $sql = "INSERT INTO club_invites (club_id, student_id, status) VALUES (:club_id, :student_id, :status)";
        $statement = $pdo->prepare($sql);
        $statement->bindParam(":club_id", $club_id);
        $statement->bindParam(":student_id", $student_id);
        $statement->bindParam(":status", $status);
        $inserted = $statement->execute();

        if ($inserted) {
            // Log - Start
            $user_id = $student_id;
            $action = "invite_requested";
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
}

if (isset($_POST["leave"])) {
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
                        <p class="mb-2"><strong>ECA Description: </strong>
                            <?php echo htmlspecialchars($club["club_description"]); ?>
                        </p>
                        <p class="mb-2"><strong>ECA Category: </strong>
                            <?php echo htmlspecialchars($club["category_name"]); ?></p>
                        <p class="mb-2"><strong>ECA Location: </strong>
                            <?php echo htmlspecialchars($club["club_location"]); ?></p>
                        <p class="mb-2"><strong>ECA Meeting Day: </strong>
                            <?php echo ucfirst(htmlspecialchars($club["club_meeting_day"])); ?></p>
                        <p class="mb-2"><strong>ECA Meeting Time: </strong>
                            <?php echo ucfirst(htmlspecialchars($club["club_meeting_time"])); ?></p>



                        <form method="post" onsubmit="return confirmLeaveECA();">
                            <?php echo $display; ?>
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