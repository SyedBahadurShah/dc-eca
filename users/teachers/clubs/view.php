<?php
require "../../../init.php";
if (!isLoggedIn()) {
    redirect("../login.php");
}

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    redirect("index.php");
}

$club_id = $_GET["id"];
$created_by = userInfo("user_id");
$sql = "SELECT * FROM clubs  LEFT JOIN club_categories ON club_categories.category_id = clubs.category_id WHERE club_id = :club_id AND created_by = :created_by";

$statement = $pdo->prepare($sql);
$statement->bindParam(":club_id", $club_id);
$statement->bindParam(":created_by", $created_by);
$statement->execute();
$club = $statement->fetch(PDO::FETCH_ASSOC);

$status = "active";
$sql = "SELECT * FROM club_memberships LEFT JOIN users ON users.user_id = club_memberships.student_id WHERE club_id = :club_id AND status = :status";
$statement = $pdo->prepare($sql);
$statement->bindParam(":club_id", $club["club_id"]);
$statement->bindParam(":status", $status);
$statement->execute();
$students = $statement->fetchAll(PDO::FETCH_ASSOC);

if (!$club) {
    redirect("index.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/plugins/font-awesome/css/all.min.css">
    <title><?php echo $club["club_name"]; ?> Club</title>
</head>

<body>
    <?php include "../layouts/partials/navbar.php";?>
    <div class="container">
        <div class="row">
            <div class="col-12 mt-4">
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
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h3 class="mb-4 mt-4">Enrolled Students <a data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Add Student" href="add.php?club_id=<?php echo $club["club_id"]; ?>" class="btn btn-primary btn-sm mb-2"><i class="fa-solid fa-plus"></i></a></h3>
                        <?php if (count($students) > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Student Name</th>
                                    <th scope="col">Student Email</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $id = count($students);foreach ($students as $student): ?>
                                <tr>
                                    <td scope="col"><?php echo $id--; ?></td>
                                    <td scope="col"><?php echo $student["name"]; ?></td>
                                    <td scope="col"><?php echo $student["email"]; ?></td>
                                    <td scope="col"><button type="button" data-membership-id="<?php echo $student["membership_id"]; ?>" class="btn btn-danger btn-sm kick-btn"><i class="fa-solid fa-right-from-bracket"></i> Kick</button></td>
                                </tr>
                                <?php endforeach;?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p class="text-color">No record found!</p>
                        <?php endif;?>
                    </div>
                </div>
                <div class="col-3"></div>
            </div>
        </div>
    </div>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    $(document).ready(function () {
        $(".kick-btn").click(function () {
            const membership_id = $(this).data("membership-id");
            const confirmed = confirm("Are you sure you want to kick this student?");
            if ( confirmed ) {
                window.location.href = "kick.php?membership_id=" + membership_id;
                return;
            }
        });
    });

    </script>
</body>

</html>