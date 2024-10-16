<?php
// All & Student Announcements
$sql = "SELECT * FROM announcements WHERE recipient_type = 'all' OR recipient_type = 'teacher'";
$statement = $pdo->prepare($sql);
$statement->execute();
$announcements = $statement->fetchAll(PDO::FETCH_ASSOC);

// Logs
$created_by = userInfo("user_id");
$sql = "SELECT *, u.name as 'student_name', t.name as 'teacher_name', cl.created_at as 'date' FROM club_logs as cl
LEFT JOIN clubs as c ON c.club_id = cl.club_id
LEFT JOIN users as u ON u.user_id = cl.user_id
LEFT JOIN users as t ON t.user_id = c.created_by
WHERE c.created_by = :created_by
ORDER BY cl.log_id DESC";
$statement = $pdo->prepare($sql);
$statement->bindParam(":created_by", $created_by);
$statement->execute();
$logs = $statement->fetchAll(PDO::FETCH_ASSOC);

$logList = [];
foreach ($logs as $log) {
    $l = [];
    if ($log["action_by"] == "student") {
        if ($log["action"] == "joined") {
            $log["action"] = "<strong><span class='text-success'>joined</span></strong>";
            $l['log'] = ucfirst($log['student_name']) . ' ' . $log["action"] . ' ' . $log["club_name"] . " club";
            $l['date'] = $log["date"];
            $logList[] = $l;
        }

        if ($log["action"] == "left") {
            $log["action"] = "<strong><span class='text-warning'>left</span></strong>";
            $l['log'] = ucfirst($log['student_name']) . ' ' . $log["action"] . ' ' . $log["club_name"] . " club";
            $l['date'] = $log["date"];
            $logList[] = $l;
        }

        if ($log["action"] == "invite_requested") {
            $log["action"] = "<strong><span class='text-primary'>applied</span></strong>";
            $l['log'] = ucfirst($log['student_name']) . ' ' . $log["action"] . ' ' . $log["club_name"] . " club";
            $l['date'] = $log["date"];
            $logList[] = $l;
        }

        if ($log["action"] == "invite_accepted") {
            $log["action"] = "<strong><span class='text-success'>accepted</span></strong>";
            $l['log'] = 'You ' . '<strong><span class="text-danger">' . $log["action"] . '</span></strong> ' . ucfirst($log["student_name"]) . ' from <strong>' . $log["club_name"] . '</strong> club';
            $l['date'] = $log["date"];
            $logList[] = $l;
        }

    }

    if ($log["action_by"] == "teacher") {
        if ($log["action"] == "added") {
            $log["action"] = "<strong><span class='text-success'>added</span></strong>";
            $l['log'] = 'You ' . '<strong><span class="text-danger">' . $log["action"] . '</span></strong> ' . ucfirst($log["student_name"]) . ' to <strong>' . $log["club_name"] . '</strong> club';
            $l['date'] = $log["date"];
            $logList[] = $l;
        }

        if ($log["action"] == "kicked") {
            $log["action"] = "<strong><span class='text-danger'>kicked</span></strong>";
            $l['log'] = 'You ' . '<strong><span class="text-danger">' . $log["action"] . '</span></strong> ' . ucfirst($log["student_name"]) . ' from <strong>' . $log["club_name"] . '</strong> club';
            $l['date'] = $log["date"];
            $logList[] = $l;
        }

        if ($log["action"] == "invite_rejected") {
            $log["action"] = "<strong><span class='text-danger'>rejected</span></strong>";
            $l['log'] = 'You ' . '<strong><span class="text-danger">' . $log["action"] . '</span></strong> ' . ucfirst($log["student_name"]) . ' from <strong>' . $log["club_name"] . '</strong> club';
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