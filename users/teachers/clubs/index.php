<?php

require "../../../init.php";
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$created_by = userInfo("user_id");
$searchSql = "SELECT * FROM clubs WHERE created_by = :created_by";
$statement = $pdo->prepare($searchSql);
$statement->bindParam(":created_by", $created_by);
$statement->execute();
$clubs = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/plugins/font-awesome/css/all.min.css">
    <title>Clubs</title>
</head>

<body>
    <?php include "../layouts/partials/navbar.php";?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12 mb-3">
                <h2>My Clubs</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <?php if (count($clubs) > 0): ?>
                            <?php foreach ($clubs as $club): ?>
                            <div class="col-4">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($club["club_name"]); ?></h5>
                                        <p class="card-text"><strong>Meeting Day:</strong>
                                            <?php echo ucfirst(htmlspecialchars($club["club_meeting_day"])); ?></p>
                                        <p class="card-text"><strong>Meeting Time:</strong>
                                            <?php echo ucfirst(htmlspecialchars($club["club_meeting_time"])); ?></p>
                                    </div>
                                    <div class="card-footer">
                                        <a href="/users/teachers/clubs/view.php?id=<?php echo $club["club_id"]; ?>"
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