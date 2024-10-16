<?php
require "init.php";
if (!isset($_SESSION["email"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["account_type"] != "Head") {
    header("Location: home.php");
    exit();
}

// Fetch categories for the filter options
$sql = "SELECT * FROM eca_categories";
$statement = $pdo->prepare($sql);
$statement->execute();
$categories = $statement->fetchAll(PDO::FETCH_ASSOC);

// Fetch meeting days and times
$year_group = $_SESSION["year_group"];
$sql = "SELECT * FROM ecas WHERE 1=1";

$params = [];

// Handle reset functionality
if (isset($_POST["reset"])) {
    $_POST["search_term"] = "";
    $_POST["filter"] = [];
    $_POST["meeting_days"] = [];
    $_POST["meeting_times"] = [];
}

$search_term = "";
if (isset($_POST["search_term"]) && !empty(trim($_POST["search_term"]))) {
    $search_term = "%" . trim($_POST["search_term"]) . "%";
    $sql .= " AND (eca_name LIKE :search_term OR eca_description LIKE :search_term)";
    $params[':search_term'] = $search_term;
}

$filter_ids = [];
if (isset($_POST["filter"])) {
    $filter_ids = array_map('intval', $_POST["filter"]);
    if (!empty($filter_ids)) {
        $ids = implode(",", $filter_ids);
        $sql .= " AND eca_category IN ($ids)";
    }
}

$meeting_day_filters = [];
if (isset($_POST['meeting_days']) && !empty($_POST['meeting_days'])) {
    $meeting_day_filters = array_map('intval', $_POST['meeting_days']);
    if (!empty($meeting_day_filters)) {
        $day_ids = implode(",", $meeting_day_filters);
        $sql .= " AND eca_meeting_day IN ($day_ids)";
    }
}

$meeting_time_filters = [];
if (isset($_POST['meeting_times']) && !empty($_POST['meeting_times'])) {
    $meeting_time_filters = array_map('intval', $_POST['meeting_times']);
    if (!empty($meeting_time_filters)) {
        $time_ids = implode(",", $meeting_time_filters);
        $sql .= " AND eca_meeting_time IN ($time_ids)";
    }
}

// For Student Leader, filter out their own led ECAs' meeting time and day
$account_type = $_SESSION["account_type"];
if ($account_type == "Student Leader") {
    $user_id = $_SESSION["user_id"];
    $sqlECA = "SELECT * FROM ecas WHERE eca_led_by = :eca_led_by";
    $statement = $pdo->prepare($sqlECA);
    $statement->bindParam(":eca_led_by", $user_id);
    $statement->execute();
    $eca = $statement->fetch(PDO::FETCH_ASSOC);
    if ($eca) {
        $eca_meeting_time = $eca["eca_meeting_time"];
        $eca_meeting_day = $eca["eca_meeting_day"];
        $sql .= " AND (eca_meeting_time <> :eca_meeting_time OR eca_meeting_day <> :eca_meeting_day)";
        $params[':eca_meeting_time'] = $eca_meeting_time;
        $params[':eca_meeting_day'] = $eca_meeting_day;
    }
}

$statement = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $statement->bindValue($key, $value);
}
$statement->execute();
$ecas = $statement->fetchAll(PDO::FETCH_ASSOC);

$eca_list = [];
foreach ($ecas as $eca) {
        $eca_list[] = $eca;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="plugins/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/app.min.css">
    <style>
        .eca-card-container {
            display: flex;
            flex-wrap: wrap;
        }
        .eca-card {
            flex: 1 1 calc(33.333% - 1rem);
            margin: 0.5rem;
        }
        .scrollable-card-body {
            max-height: 500px; /* Adjust based on your needs */
            overflow-y: auto;
        }
    </style>
    <title>ECA - View Details</title>
</head>
<body>
    <?php include "layouts/partials/navbar.php"; ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12 mb-3">
                <form method="post" class="d-flex">
                    <input class="form-control me-2" type="search" name="search_term" placeholder="Search ECAs" aria-label="Search" value="<?php echo htmlspecialchars(isset($_POST['search_term']) ? $_POST['search_term'] : ''); ?>">

                    <?php foreach ($filter_ids as $filter_id): ?>
                        <input type="hidden" name="filter[]" value="<?php echo $filter_id; ?>">
                    <?php endforeach; ?>
                    <?php foreach ($meeting_day_filters as $day_id): ?>
                        <input type="hidden" name="meeting_days[]" value="<?php echo $day_id; ?>">
                    <?php endforeach; ?>
                    <?php foreach ($meeting_time_filters as $time_id): ?>
                        <input type="hidden" name="meeting_times[]" value="<?php echo $time_id; ?>">
                    <?php endforeach; ?>

                    <button class="btn btn-outline-success" type="submit">Search</button>
                    <button class="btn btn-outline-danger ms-2" type="submit" name="reset">Reset</button>
                </form>
            </div>

            <!-- Filters -->
            <div class="col-md-4">
                <form method="post">
                    <!-- Add the search term and already selected filters as hidden inputs -->
                    <input type="hidden" name="search_term" value="<?php echo htmlspecialchars(isset($_POST['search_term']) ? $_POST['search_term'] : ''); ?>">

                    <?php foreach ($filter_ids as $filter_id): ?>
                        <input type="hidden" name="filter[]" value="<?php echo $filter_id; ?>">
                    <?php endforeach; ?>

                    <?php foreach ($meeting_day_filters as $day_id): ?>
                        <input type="hidden" name="meeting_days[]" value="<?php echo $day_id; ?>">
                    <?php endforeach; ?>

                    <?php foreach ($meeting_time_filters as $time_id): ?>
                        <input type="hidden" name="meeting_times[]" value="<?php echo $time_id; ?>">
                    <?php endforeach; ?>

                    <div class="card">
                        <div class="card-header">Filters</div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="form-group">
                                    <label for="categoryFilter">Category</label>
                                    <?php foreach ($categories as $category): ?>
                                        <div class="form-check">
                                            <?php
                                            $checked = "";
                                            if (isset($_POST["filter"]) && in_array($category["id"], $_POST["filter"])) {
                                                $checked = "checked";
                                            }
                                            ?>
                                            <input <?php echo $checked; ?> class="form-check-input" type="checkbox" name="filter[]" value="<?php echo $category["id"]; ?>" id="category_<?php echo $category["id"]; ?>">
                                            <label class="form-check-label" for="category_<?php echo $category["id"]; ?>">
                                                <?php echo $category["category_name"]; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </li>

                            <li class="list-group-item">
                                <div class="form-group">
                                    <label for="meetingDayFilter">Meeting Day</label>
                                    <?php foreach ($meeting_days as $key => $day): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="meeting_days[]" value="<?php echo $key; ?>" id="day_<?php echo $key; ?>" 
                                            <?php echo in_array($key, $meeting_day_filters) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="day_<?php echo $key; ?>"><?php echo $day; ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </li>

                            <li class="list-group-item">
                                <div class="form-group">
                                    <label for="meetingTimeFilter">Meeting Time</label>
                                    <?php foreach ($meeting_times as $key => $time): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="meeting_times[]" value="<?php echo $key; ?>" id="time_<?php echo $key; ?>" 
                                            <?php echo in_array($key, $meeting_time_filters) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="time_<?php echo $key; ?>"><?php echo $time; ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                        </ul>
                        <div class="card-footer">
                            <button class="btn btn-outline-success" type="submit">Apply Filters</button>
                            <button class="btn btn-outline-danger ms-2" type="submit" name="reset">Reset</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-md-8">
                <div class="card scrollable-card-body">
                    <div class="card-body">
                        <div class="eca-card-container">
                            <?php if (count($eca_list) > 0): ?>
                                <?php foreach ($eca_list as $eca): ?>
                                    <div class="card eca-card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($eca["eca_name"]); ?></h5>
                                            <p class="card-text"><strong>Meeting Day:</strong> <?php echo htmlspecialchars($meeting_days[$eca["eca_meeting_day"]]); ?></p>
                                            <p class="card-text"><strong>Meeting Time:</strong> <?php echo htmlspecialchars($meeting_times[$eca["eca_meeting_time"]]); ?></p>
                                        </div>
                                        <div class="card-footer">
                                            <a href="modify.php?id=<?php echo $eca["id"]; ?>" class="btn btn-success">More Actions</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No ECAs found based on the search criteria.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
