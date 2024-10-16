<?php
require "../../../init.php";
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$student_id = userInfo("user_id");
$sql = "SELECT * FROM club_invites LEFT JOIN clubs ON clubs.club_id = club_invites.club_id WHERE student_id = :student_id";
$statement = $pdo->prepare($sql);
$statement->bindParam(":student_id", $student_id);
$statement->execute();
$applications = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($applications as $key => $application) {
    if ($application["status"] == "pending") {
        $applications[$key]["status"] = '<span class="badge text-bg-primary">' . ucwords($application["status"]) . '</span>';
    }

    if ($application["status"] == "accepted") {
        $applications[$key]["status"] = '<span class="badge text-bg-success">' . ucwords($application["status"]) . '</span>';
    }

    if ($application["status"] == "rejected") {
        $applications[$key]["status"] = '<span class="badge text-bg-danger">' . ucwords($application["status"]) . '</span>';
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
    <title>My Applications</title>
</head>

<body>
    <?php include "../layouts/partials/navbar.php";?>
    <?php if (Flash::exists()): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo Flash::display(); ?>
    </div>
    <?php endif;?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12 mb-3">
                <h2>My Applications</h2>
                <div class="card mt-4">
                    <div class="card-body">
                        <?php if ($applications): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Club Name</th>
                                    <th scope="col">Application Status</th>
                                    <th scope="col">Applied Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $application): ?>
                                <tr>
                                    <th scope="row">1</th>
                                    <td><?php echo $application["club_name"]; ?></td>
                                    <td>
                                        <?php echo $application["status"]; ?>
                                    </td>
                                    <td><?php echo $application["created_at"]; ?></td>
                                </tr>
                                <?php endforeach;?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p class="text-center">No record found!</p>
                        <?php endif;?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>