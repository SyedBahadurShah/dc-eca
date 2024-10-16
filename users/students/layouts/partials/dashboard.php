<?php
// All & Student Announcements
// FUTURE (I need annoucements for my joined clubs or recipient type: all and student)

$status = "active";
$student_id = userInfo("user_id");
$sql = "SELECT * FROM announcements WHERE recipient_type = 'all' OR recipient_type = 'student' OR club_id IN( SELECT club_id FROM club_memberships WHERE student_id = :student_id AND status = :status ) ORDER BY announcement_id DESC";
$statement = $pdo->prepare($sql);
$statement->bindParam(":student_id", $student_id);
$statement->bindParam(":status", $status);
$statement->execute();
$announcements = $statement->fetchAll(PDO::FETCH_ASSOC);
// dd($announcements);


foreach ( $announcements as $index => $announcement ) {
    $announcements[$index]["message"] = "<span class='text-". $announcement['label'] ."'><strong>" . $announcement["message"] . "</strong></span>";
}


// Logs
$user_id = userInfo("user_id");
$sql = "
    SELECT *, club_logs.created_at as 'date' FROM club_logs 
    LEFT JOIN clubs ON clubs.club_id = club_logs.club_id 
    LEFT JOIN users ON users.user_id = clubs.created_by 
    WHERE club_logs.user_id = :user_id 
    ORDER BY club_logs.log_id DESC
";
$statement = $pdo->prepare($sql);
$statement->bindParam(":user_id", $user_id);
$statement->execute();
$logs = $statement->fetchAll(PDO::FETCH_ASSOC);

$logList = [];
foreach ($logs as $log) {
    $l = [];
    if ($log["action_by"] == "student") {
        if ($log["action"] == "joined") {
            $log["action"] = "<strong><span class='text-primary'>joined</span></strong>";
            $l['log'] = 'You ' . $log["action"] . ' ' . $log["club_name"] . " club";
            $l['date'] = $log["date"];
            $logList[] = $l;    
        }

        if ($log["action"] == "left") {
            $log["action"] = "<strong><span class='text-warning'>left</span></strong>";
            $l['log'] = 'You ' . $log["action"] . ' ' . $log["club_name"] . " club";
            $l['date'] = $log["date"];
            $logList[] = $l;
        }

        if ($log["action"] == "invite_requested") {
            $log["action"] = "<strong><span class='text-primary'>applied</span></strong>";
            $l['log'] = 'You ' . $log["action"] . ' for ' . $log["club_name"] . " club";
            $l['date'] = $log["date"];
            $logList[] = $l;
        }

        if ($log["action"] == "invite_accepted") {
            $log["action"] = "<strong><span class='text-success'>accepted</span></strong>";
            $l['log'] = 'You were ' . $log["action"] . ' by ' . $log["club_name"] . " club";
            $l['date'] = $log["date"];
            $logList[] = $l;
        }

        if ($log["action"] == "invite_rejected") {
            $log["action"] = "<strong><span class='text-danger'>rejected</span></strong>";
            $l['log'] = 'You were ' . $log["action"] . ' by ' . $log["club_name"] . " club";
            $l['date'] = $log["date"];
            $logList[] = $l;
        }

    }

    if ($log["action_by"] == "teacher") {

        if ($log["action"] == "joined") {
            $log["action"] = "<strong><span class='text-primary'>joined</span></strong>";
            $l['log'] = 'You ' . '<strong><span class="text-danger">' . $log["action"] . '</span></strong> ' . $log["club_name"] . ' club by ' . '<strong>' . $log["name"] . '</strong>';
            $l['date'] = $log["date"];
            $logList[] = $l;
        }

        if ($log["action"] == "added") {
            $log["action"] = "<strong><span class='text-success'>added</span></strong>";
            $l['log'] = 'You were ' . $log["action"] . ' to ' . $log["club_name"] . " club";
            $l['date'] = $log["date"];
            $logList[] = $l;
        }

        if ($log["action"] == "invite_accepted") {
            $log["action"] = "<strong><span class='text-success'>accepted</span></strong>";
        }

        if ($log["action"] == "kicked") {
            $log["action"] = "<strong><span class='text-danger'>kicked</span></strong>";
            $l['log'] = 'You were ' . '<strong><span class="text-danger">' . $log["action"] . '</span></strong> from <strong>' . $log["club_name"] . '</strong> club';
            $l['date'] = $log["date"];
            $logList[] = $l;
        }

        if ($log["action"] == "invite_rejected") {
            // You were Rejected by Teacher from Chess club
            $log["action"] = "<strong><span class='text-danger'>rejected</span></strong>";
            $l['log'] = 'You were ' . '<strong><span class="text-danger">' . $log["action"] . '</span></strong> by ' . $log["name"] . ' from ' . '<strong>' . $log["club_name"] . '</strong> club';
            $l['date'] = $log["date"];
            $logList[] = $l;

        }
    }

    if ($log["action_by"] == "student_leader") {

        if ($log["action"] == "joined") {
            $log["action"] = "<strong><span class='text-primary'>joined</span></strong>";
            $l['log'] = 'You ' . '<strong><span class="text-danger">' . $log["action"] . '</span></strong> ' . $log["club_name"] . ' club by ' . '<strong>' . $log["name"] . '</strong>';
            $l['date'] = $log["date"];
            $logList[] = $l;
        }

        if ($log["action"] == "added") {
            $log["action"] = "<strong><span class='text-success'>added</span></strong>";
            $l['log'] = 'You were ' . $log["action"] . ' to ' . $log["club_name"] . " club";
            $l['date'] = $log["date"];
            $logList[] = $l;
        }

        if ($log["action"] == "invite_accepted") {
            $log["action"] = "<strong><span class='text-success'>accepted</span></strong>";
        }

        if ($log["action"] == "kicked") {
            $log["action"] = "<strong><span class='text-danger'>kicked</span></strong>";
            $l['log'] = 'You were ' . '<strong><span class="text-danger">' . $log["action"] . '</span></strong> from <strong>' . $log["club_name"] . '</strong> club';
            $l['date'] = $log["date"];
            $logList[] = $l;
        }

        if ($log["action"] == "invite_rejected") {
            // You were Rejected by Teacher from Chess club
            $log["action"] = "<strong><span class='text-danger'>rejected</span></strong>";
            $l['log'] = 'You were ' . '<strong><span class="text-danger">' . $log["action"] . '</span></strong> by ' . $log["name"] . ' from ' . '<strong>' . $log["club_name"] . '</strong> club';
            $l['date'] = $log["date"];
            $logList[] = $l;

        }
    }
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
            <div class="col-6 mt-4">
                <div class="card">
                    <div class="card-body">
                        <h2>Announcements</h2>
                        <div class="overflow-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Announcement</th>
                                        <th scope="col">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $x = 1;foreach ($announcements as $a): ?>
                                    <tr>
                                        <th scope="row"><?php echo $x++; ?></th>
                                        <td>
                                            <?php echo $a["message"]; ?>
                                        </td>
                                        <td><?php echo $a["created_at"]; ?></td>
                                    </tr>
                                    <?php endforeach;?>
                                </tbody>

                            </table>
                        </div>


                    </div>
                </div>

            </div>
            <div class="col-6 mt-4">
                <div class="card">
                    <div class="card-body">
                        <h2>Logs</h2>
                        <div class="overflow-container">
                            <table class="table">

                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Log</th>
                                        <th scope="col">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $x = 1;foreach ($logList as $log): ?>
                                    <tr>
                                        <th><?php echo $x++; ?></th>
                                        <td><?php echo $log["log"]; ?></td>
                                        <td><?php echo $log["date"]; ?></td>
                                    </tr>
                                    <?php endforeach;?>
                                </tbody>

                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>