<?php
require "../../init.php";
if (!isLoggedIn()) {
    redirect("../login.php");
}

$labels = [
    "success",
    "warning",
    "danger",
    "info"
];

$created_by = userInfo("user_id");
$sql = "SELECT * FROM clubs WHERE created_by = :created_by";
$statement = $pdo->prepare($sql);
$statement->bindParam(":created_by", $created_by);
$statement->execute();
$clubs = $statement->fetchAll(PDO::FETCH_ASSOC);


if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
    if ( 
        (!isset($_POST["club_id"]) || empty($_POST["club_id"])) && 
        (!isset($_POST["annoucement"]) || empty($_POST["annoucement"]) && 
        (!isset($_POST["label"]) || empty($_POST["label"]))) 
    ) {
        redirect("index.php");
    }   


    $club_id = $_POST["club_id"];
    $label = $_POST["label"];
    $message = $_POST["annoucement"];

    if ( !in_array($label, $labels) ) {
        redirect("index.php");
    }

    $created_by = userInfo("user_id");
    $sql = "SELECT * FROM clubs WHERE created_by = :created_by AND club_id = :club_id";
    $statement = $pdo->prepare($sql);
    $statement->bindParam(":created_by", $created_by);
    $statement->bindParam(":club_id", $club_id);
    $club = $statement->execute();
    if ( !$club ) {
        redirect("index.php");
    }

    $sql = "INSERT INTO announcements (club_id, message, created_by, label) VALUES (:club_id, :message, :created_by, :label)";
    $statement = $pdo->prepare($sql);
    $statement->bindParam(":club_id", $club_id);
    $statement->bindParam(":message", $message);
    $statement->bindParam(":created_by", $created_by);
    $statement->bindParam(":label", $label);
    $announced = $statement->execute();
    if ($announced) Flash::set("Message sent successfully.");
    redirect("/");
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
    <title>Annoucements</title>
</head>

<body>
    <?php include "layouts/partials/navbar.php";?>
    <div class="container">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6 mt-4">
                <div class="card">
                    <div class="card-body p-4">
                    <form method="post">
                        <h4 class="text-center">Annoucements</h4>
                        <div class="mb-3">
                            <div class="row">
                                <div class="col">
                                    <label for="club" class="form-label"><strong>Search Clubs</strong></label>
                                    <select class="form-control club" name="club_id" id="club_id">
                                        <?php foreach( $clubs as $club ): ?>
                                            <option value="<?php echo $club["club_id"]; ?>"><?php echo $club["club_name"]; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="label" class="form-label"><strong>Labels</strong></label>
                                    <select class="form-control club" name="label" id="label">
                                        <?php foreach( $labels as $label ): ?>
                                            <option value="<?php echo $label; ?>"><?php echo ucfirst( $label ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3 mt-4">
                                <label for="annoucement" class="form-label"><strong>Annoucement</strong></label>
                                <textarea class="form-control" name="annoucement" id="annoucement" rows="3"></textarea>
                            </div>

                        </div>
                        <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-bullhorn"></i> Annouce</button>
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
</body>

</html>