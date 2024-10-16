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

// Meeting days and times
$meeting_days = ["monday", "tuesday", "wednesday", "thursday", "friday"];
$meeting_times = ["morning", "lunch", "after_school"];

// Fetch meeting days and times
$year_group = userInfo("year_group");

$params = [];

$searchSql = "SELECT * FROM clubs";
$student_id = userInfo("user_id");

// Handle reset functionality
if (isset($_POST["reset"])) {
    redirect("clubs");
}

// Search Term
$search_term = $_POST["search_term"] ?? "";
if (!empty($search_term)) {
    $searchSql .= " AND club_name LIKE :search_term";
    $params[":search_term"] = "%{$search_term}%";
}

// Category Filter
$category_filter = [];
if (isset($_POST["categories"])) {
    $category_filter = array_map('intval', $_POST["categories"]);
    if (!empty($category_filter)) {
        $category_ids = implode(",", $category_filter);
        $searchSql .= " AND category_id IN ($category_ids)";
    }
}

// Meeting Days Filter
$meeting_day_filters = [];
if (isset($_POST['meeting_days']) && !empty($_POST['meeting_days'])) {
    $meeting_day_filters = $_POST["meeting_days"];
    if (!empty($meeting_day_filters)) {
        $days = implode("', '", $meeting_day_filters);
        $searchSql .= " AND club_meeting_day IN ('$days')";
    }
}

// Meeting Times Filter
$meeting_time_filters = [];
if (isset($_POST['meeting_times']) && !empty($_POST['meeting_times'])) {
    $meeting_time_filters = $_POST["meeting_times"];
    if (!empty($meeting_time_filters)) {
        $times = implode("', '", $meeting_time_filters);
        $searchSql .= " AND club_meeting_time IN ('$times')";
    }
}

// dd($searchSql);

$statement = $pdo->prepare($searchSql);
foreach ($params as $key => $value) {
    $statement->bindValue($key, $value);
}

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
    <link rel="stylesheet" href="/assets/css/app.min.css">
    <title>List of Clubs</title>
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
                <h2>List of Clubs <a href="/users/head/clubs/view.php?id=<?php echo $club["club_id"]; ?>" class="btn btn-primary btn-sm mb-2" data-bs-toggle="tooltip" data-bs-title="Create Club"><i class="fa-solid fa-plus"></i></a></h2>
                <form method="post" class="d-flex">
                    <input class="form-control me-2" type="search" name="search_term" placeholder="Search clubs"
                        aria-label="Search" value="<?php echo htmlspecialchars($search_term); ?>">

                    <button class="btn btn-outline-success" type="submit">Search</button>
                    <button class="btn btn-outline-danger ms-2" type="submit" name="reset">Reset</button>
            </div>

            <!-- Filters -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Filters</div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="form-group">
                                <label for="categories">Category</label>
                                <?php foreach ($categories as $category): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]"
                                        value="<?=htmlspecialchars($category['category_id']);?>"
                                        id="category_<?=htmlspecialchars($category['category_id']);?>"
                                        <?=isset($_POST['categories']) && in_array($category['category_id'], $_POST['categories']) ? 'checked' : '';?>>
                                    <label class="form-check-label"
                                        for="category_<?php echo $category["category_id"]; ?>">
                                        <?php echo $category["category_name"]; ?>
                                    </label>
                                </div>
                                <?php endforeach;?>
                            </div>
                        </li>

                        <li class="list-group-item">
                            <div class="form-group">
                                <label for="meetingDayFilter">Meeting Day</label>
                                <?php foreach ($meeting_days as $day): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="meeting_days[]"
                                        value="<?php echo $day; ?>" id="day_<?php echo $day; ?>"
                                        <?php echo in_array($day, $meeting_day_filters) ? 'checked' : ''; ?>>
                                    <label class="form-check-label"
                                        for="day_<?php echo $day; ?>"><?php echo ucfirst($day); ?></label>
                                </div>
                                <?php endforeach;?>
                            </div>
                        </li>

                        <li class="list-group-item">
                            <div class="form-group">
                                <label for="meetingTimeFilter">Meeting Time</label>
                                <?php foreach ($meeting_times as $time): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="meeting_times[]"
                                        value="<?php echo $time; ?>" id="time_<?php echo $time; ?>"
                                        <?php echo in_array($time, $meeting_time_filters) ? 'checked' : ''; ?>>
                                    <label class="form-check-label"
                                        for="time_<?php echo $time; ?>"><?php echo ucwords(str_replace("_", " ", $time)); ?></label>
                                </div>
                                <?php endforeach;?>
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
                <div class="row">
                    
                    <?php if (count($clubs) > 0): ?>
                            <?php foreach ($clubs as $club): ?>
                                <div class="col-4 mb-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($club["club_name"]); ?></h5>
                                            <p class="card-text"><strong>Meeting Day:</strong>
                                                <?php echo ucfirst(htmlspecialchars($club["club_meeting_day"])); ?></p>
                                            <p class="card-text"><strong>Meeting Time:</strong>
                                                <?php echo ucfirst(htmlspecialchars($club["club_meeting_time"])); ?></p>
                                        </div>
                                        <div class="card-footer">
                                            <a data-bs-toggle="tooltip" data-bs-title="Edit Club" href="/users/head/clubs/view.php?id=<?php echo $club["club_id"]; ?>"
                                            class="btn btn-primary btn-sm"><i class="fa-solid fa-pen-to-square"></i></a>

                                            <button type="button" data-bs-toggle="tooltip" data-bs-title="Delete Club" data-club-id="<?php echo $club["club_id"]; ?>"
                                            class="btn btn-danger btn-sm btn-delete"><i class="fa-solid fa-trash"></i></button>
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
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    
        $(document).ready(() => {
            $(".btn-delete").click(() => {
                const club_id = $(this).data("club-id");
                console.log(club_id);
                const confirmed = confirm("Are you sure you want to delete this club?");
                if ( !confirmed ) return;
                window.location.href = "/users/head/clubs/delete.php?id=" + club_id
            });
        });
    </script>
</body>

</html>