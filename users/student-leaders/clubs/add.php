<?php
require "../../../init.php";
if (!isLoggedIn()) {
    redirect("../login.php");
}

if (!isset($_GET["club_id"]) || empty($_GET["club_id"])) {
    redirect("index.php");
}

$created_by = userInfo("user_id");
$club_id = $_GET["club_id"];
$sql = "SELECT * FROM clubs WHERE club_id = :club_id";
$statement = $pdo->prepare($sql);
$statement->bindParam(":club_id", $club_id);
$statement->execute();
$c = $statement->fetch(PDO::FETCH_ASSOC);
if ( $c["created_by"] != $created_by ) {
    redirect("index.php");
}

$sql = "
    SELECT users.* 
    FROM users
    LEFT JOIN club_memberships 
        ON users.user_id = club_memberships.student_id
        AND club_memberships.club_id = :club_id
    WHERE (club_memberships.status IS NULL 
    OR club_memberships.status <> 'active')
    AND users.account_type IN ('student', 'student_leader');
";
$statement = $pdo->prepare($sql);
$statement->bindParam(":club_id", $club_id);
$statement->execute();
$students = $statement->fetchAll(PDO::FETCH_ASSOC);

if ( isset($_POST["student_id"]) && !empty($_POST["student_id"])) {
    
    $student_id = $_POST["student_id"];
    $sql = "SELECT * FROM clubs WHERE club_id = :club_id";
    $statement = $pdo->prepare($sql);
    $statement->bindParam(":club_id", $club_id);
    $statement->execute();
    $newClub = $statement->fetch(PDO::FETCH_ASSOC);
    $membership_status = "active";

    $sql = "
        SELECT * FROM users
        INNER JOIN clubs ON clubs.created_by = users.user_id
        WHERE users.user_id = :student_id 
        AND clubs.club_meeting_time = :meeting_time
        AND clubs.club_meeting_day = :meeting_day
        LIMIT 1
    ";

    $statement = $pdo->prepare($sql);
    $statement->bindParam(":student_id", $student_id);
    $statement->bindParam(":meeting_time", $newClub["club_meeting_time"]);
    $statement->bindParam(":meeting_day", $newClub["club_meeting_day"]);
    $statement->execute();
    $conflict = $statement->fetch(PDO::FETCH_ASSOC);

    if ( $conflict ) {
        $conflictingClubName = urlencode($conflict["club_name"]);
        Flash::set("<strong>Conflicted:</strong> You are adding a student, who own a club at time.");
        redirect("index.php");
    }

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
    
    $status = "active";
    $student_id = $_POST["student_id"];

    $sql = "SELECT * FROM club_memberships WHERE club_id = :club_id AND student_id = :student_id";
    $statement = $pdo->prepare($sql);
    $statement->bindParam(":club_id", $club_id);
    $statement->bindParam(":student_id", $student_id);
    $statement->execute();
    $membership = $statement->fetch(PDO::FETCH_ASSOC);

    if ( $membership ) {
        $sql = "UPDATE club_memberships SET status = :status WHERE membership_id = :membership_id";
        $statement = $pdo->prepare($sql);
        $statement->bindParam(":status", $status);
        $statement->bindParam(":membership_id", $membership["membership_id"]);
        $updated = $statement->execute();
    } else {
        $sql = "INSERT INTO club_memberships (club_id, student_id, status) VALUES (:club_id, :student_id, :status)";
        $statement = $pdo->prepare($sql);
        $statement->bindParam(":club_id", $club_id);
        $statement->bindParam(":student_id", $student_id );
        $statement->bindParam(":status", $status);
        $inserted = $statement->execute();
    }

    if ( $updated || $inserted ) {
        $user_id = $student_id;
        $action = "added";
        $action_by = "student_leader";
        $sql = "INSERT INTO club_logs (club_id, user_id, action, action_by) VALUES (:club_id, :user_id, :action, :action_by)";
        $statement = $pdo->prepare($sql);
        $statement->bindParam(":club_id", $club_id);
        $statement->bindParam(":user_id", $user_id);
        $statement->bindParam(":action", $action);
        $statement->bindParam(":action_by", $action_by);
        $logged = $statement->execute();
        if ( $logged ) {
            redirect("view.php?id=" . $club_id . "&student-added");
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <title>Add Student in Club</title>
</head>

<body>
    <?php include "../layouts/partials/navbar.php";?>
    <div class="container">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6 mt-4">
                <div class="card">
                    <div class="card-body p-4">
                    <form action="add.php?club_id=<?php echo $club_id?>" method="post">
                        <h4 class="text-center">Add Student</h4>
                        <div class="mb-3">
                            <label for="student" class="form-label"><strong>Search Student</strong></label>
                            <select class="form-control student" name="student_id" id="student_id">
                                <?php foreach( $students as $student ): ?>
                                    <option value="<?php echo $student["user_id"]; ?>"><?php echo $student["name"]; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Add</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-3"></div>
        </div>
    </div>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>

    $(document).ready(function () {
        $('#student_id').select2();
    });

    function kickStudent(club_id, student_id) {
        let kick = confirm('Are you sure you want to kick this student?');
        if (kick) {
            window.location.href = "kick.php?club_id=" + club_id + "&student_id=" + student_id;
            return;
        }
    }
    </script>
</body>

</html>