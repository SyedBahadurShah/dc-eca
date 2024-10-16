<?php

// Check if the user is not a student (i.e., it's a teacher or other account types)
if ($_SESSION["account_type"] == "Student" || $_SESSION["account_type"] == "Student Leader") {
    // For students and student leader, show their specific enrollment logs
    $user_id = $_SESSION["user_id"];
    $account_type = $_SESSION["account_type"];

    $sql = "
        SELECT eca_enrolled.*, users.email, ecas.*, eca_enrolled.created_at as 'log_date'
        FROM eca_enrolled
        LEFT JOIN ecas ON ecas.id = eca_enrolled.eca_id
        LEFT JOIN users ON eca_enrolled.user_id = users.id
        WHERE users.account_type = :account_type AND users.id = :user_id LIMIT 10";
    $statement = $pdo->prepare($sql);
    $statement->bindParam(":account_type", $account_type);
    $statement->bindParam(":user_id", $user_id);
    $statement->execute();
    $logs = $statement->fetchAll(PDO::FETCH_ASSOC);

} else {
    $user_id = $_SESSION["user_id"]; // Get the logged-in teacher's user ID
    $sql = "
    SELECT eca_enrolled.*, users.email, ecas.*, eca_enrolled.created_at as 'log_date'
    FROM eca_enrolled
    LEFT JOIN ecas ON ecas.id = eca_enrolled.eca_id
    LEFT JOIN users ON eca_enrolled.user_id = users.id
    WHERE ecas.eca_led_by = :user_id"; // Ensure the teacher only sees ECAs they lead
    $statement = $pdo->prepare($sql);
    $statement->bindParam(":user_id", $user_id);
    $statement->execute();
    $logs = $statement->fetchAll(PDO::FETCH_ASSOC);
}

if (isLoggedIn()) {
    $sql = "
        SELECT * FROM announcements
        WHERE (
            -- Student-specific announcements are seen by both students and student leaders
            (account_type = 'Student' AND (user_id = :student_id OR user_id IN (
                    SELECT eca_id FROM club_memberships WHERE student_id = :student_id AND status = 'active'
            )))
            OR
            -- Club-specific announcements for members of the club
            (account_type = 'club' AND user_id IN (
                    SELECT club_id FROM club_memberships WHERE student_id = :student_id AND status = 'active'
            ))
            OR
            -- General announcements for everyone
            (account_type = 'all')
            OR
            -- Student leaders can see student announcements
            (account_type = 'Student' AND EXISTS (
                SELECT 1 FROM users WHERE user_id = :user_id AND account_type = 'student_leader'
            ))
        )
        ORDER BY created_at DESC;
    ";

    $statement = $pdo->prepare($sql);
    $statement->execute();
    $announcementList = $statement->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>";
    print_r($announcementList);
    exit();

    // $user_id = $_SESSION["user_id"];
    // $account_type = $_SESSION["account_type"];
    // $is_enrolled = 1;
    // $sql = "SELECT GROUP_CONCAT(eca_id) as eca_ids FROM eca_enrolled WHERE user_id = :user_id AND is_enrolled = :is_enrolled ORDER BY id DESC";
    // $statement = $pdo->prepare($sql);
    // $statement->bindParam(":user_id", $user_id);
    // $statement->bindParam(":is_enrolled", $is_enrolled);
    // $statement->execute();
    // $eca_ids = $statement->fetch(PDO::FETCH_ASSOC);
    // $eca_ids = $eca_ids["eca_ids"];

    // $sql = "SELECT eca_announcements.*, ecas.eca_name FROM eca_announcements LEFT JOIN ecas ON eca_announcements.eca_id = ecas.id WHERE eca_announcements.eca_id IN(:eca_ids) OR eca_announcements.account_type IN (:account_type) ORDER BY eca_announcements.id DESC";
    // $statement = $pdo->prepare($sql);
    // $statement->bindParam(":eca_ids", $eca_ids);

    // if ($_SESSION["account_type"] == "Student Leader") {
    //     $account_type = "Student, Student Leader";
    //     $statement->bindParam(":account_type", $account_type);
    // } else {
    //     $statement->bindParam(":account_type", $account_type);
    // }

    // $statement->execute();
    // $announcementList = $statement->fetchAll(PDO::FETCH_ASSOC);

    // echo "<pre>";
    // print_r($announcementList);
    // exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../plugins/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/app.min.css">
    <title>Dashboard</title>
</head>

<body>
    <?php include "navbar.php";?>
    <div class="container">
        <div class="row">
            <?php if ($_SESSION["account_type"] == "Student" || $_SESSION["account_type"] == "Student Leader" || $_SESSION["account_type"] == "Teacher"): ?>
            <div class="col-6 mt-4">
                <div class="card">
                    <div class="card-body">
                        <h2>Announcements</h2>
                        <table class="table">

                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Announcement</th>
                                    <th scope="col">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $x = count($announcementList);foreach ($announcementList as $a): ?>
                                <?php

?>
                                <tr>
                                    <th scope="row"><?php echo $x--; ?></th>
                                    <td>
                                        <?php echo $a["announcement"]; ?>
                                    </td>
                                    <td><?php echo $a["created_at"]; ?></td>
                                </tr>
                                <?php endforeach;?>
                            </tbody>

                        </table>

                    </div>
                </div>

            </div>
            <div class="col-6 mt-4">
                <div class="card">
                    <div class="card-body">
                        <h2>Logs</h2>
                        <table class="table">

                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Log Name</th>
                                    <th scope="col">Log Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $x = count($logs);foreach ($logs as $log): ?>

                                <?php $color = $log["is_enrolled"] ? "green" : "red";?>
                                <?php $status = $log["is_enrolled"] ? "enrolled" : "left";?>
                                <tr>
                                    <th scope="row"><?php echo $x--; ?></th>
                                    <td><?php echo $_SESSION["account_type"] == "Student" || $_SESSION["account_type"] == "Student Leader" ? "You" : $log["email"]; ?>
                                        <strong style="color: <?php echo $color; ?>;"><?php echo $status; ?></strong>
                                        <?php echo $status == "enrolled" ? 'in' : 'from' ?>
                                        <?php echo $log["eca_name"]; ?> Club
                                    </td>
                                    <td><?php echo $log["log_date"]; ?></td>
                                </tr>
                                <?php endforeach;?>
                            </tbody>

                        </table>

                    </div>
                </div>

            </div>
            <?php endif;?>
        </div>
    </div>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>