<?php
require "init.php";


// Function to set a flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
}

// Function to get and clear a flash message
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

if (!isset($_SESSION["email"])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION["account_type"] != "Head") {
    header("Location: /home.php");
    exit();
}

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: /ecas");
    exit();
}

$id = $_GET["id"];
$sql = "
    SELECT ecas.*, eca_categories.category_name 
    FROM ecas 
    INNER JOIN eca_categories ON ecas.eca_category = eca_categories.id 
    WHERE ecas.id = :id";
$statement = $pdo->prepare($sql);
$statement->bindParam(":id", $id);
$statement->execute();
$eca = $statement->fetch(PDO::FETCH_ASSOC);

if (!$eca) {
    header("Location: /ecas");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect POST data and sanitize
    $eca_name = $_POST['eca_name'];
    $eca_description = $_POST['eca_description'];
    $eca_location = $_POST['eca_location'];
    $eca_meeting_day = $_POST['eca_meeting_day'];
    $eca_meeting_time = $_POST['eca_meeting_time'];
    $eca_min_req = $_POST['eca_min_req'];
    $eca_max_req = $_POST['eca_max_req'];
    $eca_category = $_POST['eca_category'];
    $invite = isset($_POST['invite']) ? 1 : 0;

    // Validation: Ensure eca_max_req >= eca_min_req and within valid range
    if ($eca_max_req < $eca_min_req) {
        $error = "Maximum requirement must be greater than or equal to minimum requirement.";
    } elseif ($eca_min_req < 7 || $eca_max_req > 13) {
        $error = "Year group requirements must be between 7 and 13.";
    } else {
        // Update ECA details
        $sql = "UPDATE ecas SET 
            eca_name = :eca_name,
            eca_description = :eca_description,
            eca_location = :eca_location,
            eca_meeting_day = :eca_meeting_day,
            eca_meeting_time = :eca_meeting_time,
            eca_min_req = :eca_min_req,
            eca_max_req = :eca_max_req,
            eca_category = :eca_category,
            invite = :invite
            WHERE id = :id";
        $statement = $pdo->prepare($sql);
        $statement->bindParam(":eca_name", $eca_name);
        $statement->bindParam(":eca_description", $eca_description);
        $statement->bindParam(":eca_location", $eca_location);
        $statement->bindParam(":eca_meeting_day", $eca_meeting_day);
        $statement->bindParam(":eca_meeting_time", $eca_meeting_time);
        $statement->bindParam(":eca_min_req", $eca_min_req);
        $statement->bindParam(":eca_max_req", $eca_max_req);
        $statement->bindParam(":eca_category", $eca_category);
        $statement->bindParam(":invite", $invite);
        $statement->bindParam(":id", $id);
        $updated = $statement->execute();
        
        if ($updated) {
            setFlashMessage('success', 'Changes applied successfully.');
            header("Location: modify.php?id=$id");
            exit();
        }
    }
}

$categories = [
    1 => 'Academic',
    2 => 'Sports',
    3 => 'Music',
    4 => 'Misc'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../plugins/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/app.min.css">
    <title><?php echo htmlspecialchars($eca["eca_name"]); ?> - ECAS</title>
    <style>
        .flash-message {
            position: relative;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .flash-message .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 18px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <?php include "layouts/partials/navbar.php"; ?>
    <div class="container">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6 mt-4">
                <div class="card">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Edit ECA Details</h2>
                        <?php
                        $flash = getFlashMessage();
                        if ($flash):
                            $alertType = $flash['type'] === 'success' ? 'success' : 'danger';
                        ?>
                            <div class="flash-message alert alert-<?php echo $alertType; ?>">
                                <span class="close" onclick="this.parentElement.style.display='none';">&times;</span>
                                <?php echo htmlspecialchars($flash['message']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($error)): ?>
                            <div class="flash-message alert alert-danger">
                                <span class="close" onclick="this.parentElement.style.display='none';">&times;</span>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="form-group">
                                <label for="eca_name">ECA Name:</label>
                                <input type="text" id="eca_name" name="eca_name" class="form-control" value="<?php echo htmlspecialchars($eca["eca_name"]); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="eca_description">ECA Description:</label>
                                <input type="text" id="eca_description" name="eca_description" class="form-control" value="<?php echo htmlspecialchars($eca["eca_description"]); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="eca_location">ECA Location:</label>
                                <input type="text" id="eca_location" name="eca_location" class="form-control" value="<?php echo htmlspecialchars($eca["eca_location"]); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="eca_meeting_day">Meeting Day:</label>
                                <select id="eca_meeting_day" name="eca_meeting_day" class="form-control" required>
                                    <?php foreach ($meeting_days as $key => $day): ?>
                                        <option value="<?php echo $key; ?>" <?php if ($eca["eca_meeting_day"] == $key) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($day); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="eca_meeting_time">Meeting Time:</label>
                                <select id="eca_meeting_time" name="eca_meeting_time" class="form-control" required>
                                    <?php foreach ($meeting_times as $key => $time): ?>
                                        <option value="<?php echo $key; ?>" <?php if ($eca["eca_meeting_time"] == $key) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($time); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="eca_min_req">Minimum Requirement:</label>
                                <input type="number" id="eca_min_req" name="eca_min_req" class="form-control" value="<?php echo htmlspecialchars($eca["eca_min_req"]); ?>" min="7" max="13" required>
                            </div>
                            <div class="form-group">
                                <label for="eca_max_req">Maximum Requirement:</label>
                                <input type="number" id="eca_max_req" name="eca_max_req" class="form-control" value="<?php echo htmlspecialchars($eca["eca_max_req"]); ?>" min="7" max="13" required>
                            </div>
                            <div class="form-group">
                                <label for="eca_category">Category:</label>
                                <select id="eca_category" name="eca_category" class="form-control" required>
                                    <?php foreach ($categories as $key => $category): ?>
                                        <option value="<?php echo $key; ?>" <?php if ($eca["eca_category"] == $key) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($category); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="invite" name="invite" class="form-check-input" <?php if ($eca["invite"]) echo 'checked'; ?>>
                                <label for="invite" class="form-check-label">Invite Only</label>
                            </div>
                            <div class="form-group mt-3">
                                
                                <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
                                
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-3"></div>
        </div>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
