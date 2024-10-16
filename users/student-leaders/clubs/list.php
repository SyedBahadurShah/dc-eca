<?php
require "../../../init.php";
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Fetch categories for the filter options
$sql = "SELECT * FROM club_categories";
$statement = $pdo->prepare($sql);
$statement->execute();
$categories = $statement->fetchAll(PDO::FETCH_ASSOC);

// My Own Clubs
$created_by = userInfo("user_id");
$sql = "SELECT * FROM clubs WHERE created_by = :created_by";
$statement =  $pdo->prepare($sql);
$statement->bindParam(":created_by", $created_by);
$statement->execute();
$myClubs = $statement->fetchAll(PDO::FETCH_ASSOC);

// Joined Clubs
$status = "active";
$student_id = userInfo("user_id");
$sql = "
SELECT * FROM club_memberships 
LEFT JOIN clubs ON club_memberships.club_id = clubs.club_id
WHERE club_memberships.student_id = :student_id AND club_memberships.status = :status
";
$statement = $pdo->prepare($sql);
$statement->bindParam(":student_id", $student_id);
$statement->bindParam(":status", $status);
$statement->execute();
$joinedClubs = $statement->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/plugins/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/app.min.css">
    <title>club - View Details</title>
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
            <div class="col-6">
                <div class="card">
                <div class="card-header">
                    <h2 class="text-center">My Clubs</h2>
                </div>
                <div class="card-body">

                    <div class="row">
                    <?php if (count($myClubs) > 0): ?>
                    <?php foreach ($myClubs as $club): ?>
                        <div class="col-6">
                        <div class="card club-card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($club["club_name"]); ?></h5>
                            <p class="card-text"><strong>Meeting Day:</strong>
                                <?php echo ucfirst(htmlspecialchars($club["club_meeting_day"])); ?></p>
                            <p class="card-text"><strong>Meeting Time:</strong>
                                <?php echo ucfirst(htmlspecialchars($club["club_meeting_time"])); ?></p>
                        </div>
                        <div class="card-footer">
                            <a href="/users/student-leaders/clubs/view.php?id=<?php echo $club["club_id"]; ?>"
                                class="btn btn-success">More
                                Actions</a>
                        </div>
                    </div>
                        </div>
                    <?php endforeach;?>
                    <?php else: ?>
                    <p>No clubs found based on the search criteria.</p>
                    <?php endif;?>
                    </div>
                </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                <div class="card-header">
                    <h2 class="text-center">Joined Clubs</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                    <?php if (count($joinedClubs) > 0): ?>
                    <?php foreach ($joinedClubs as $club): ?>
                        <div class="col-6">
                        <div class="card club-card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($club["club_name"]); ?></h5>
                            <p class="card-text"><strong>Meeting Day:</strong>
                                <?php echo ucfirst(htmlspecialchars($club["club_meeting_day"])); ?></p>
                            <p class="card-text"><strong>Meeting Time:</strong>
                                <?php echo ucfirst(htmlspecialchars($club["club_meeting_time"])); ?></p>
                        </div>
                        <div class="card-footer">
                            <a href="/users/student-leaders/clubs/show.php?id=<?php echo $club["club_id"]; ?>"
                                class="btn btn-success">More
                                Actions</a>
                        </div>
                    </div>
                        </div>
                    <?php endforeach;?>
                    <?php else: ?>
                    <p>No clubs found based on the search criteria.</p>
                    <?php endif;?>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>