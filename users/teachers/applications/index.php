<?php
require "../../../init.php";
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$teacher_id = userInfo("user_id");
$invite_status = "pending";
$sql = "SELECT * FROM club_invites LEFT JOIN clubs ON clubs.club_id = club_invites.club_id LEFT JOIN users ON users.user_id = club_invites.student_id WHERE teacher_id = :teacher_id AND club_invites.status = :invite_status";
$statement = $pdo->prepare($sql);
$statement->bindParam(":teacher_id", $teacher_id);
$statement->bindParam(":invite_status", $invite_status);
$statement->execute();
$applications = $statement->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM club_invites LEFT JOIN clubs ON clubs.club_id = club_invites.club_id LEFT JOIN users ON users.user_id = club_invites.student_id WHERE teacher_id = :teacher_id AND club_invites.status <> :invite_status";
$statement = $pdo->prepare($sql);
$statement->bindParam(":teacher_id", $teacher_id);
$statement->bindParam(":invite_status", $invite_status);
$statement->execute();
$remainingApplications = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($remainingApplications as $key => $app) {
    if ($app["status"] == "pending") {
        $remainingApplications[$key]["status"] = '<span class="badge text-bg-primary">' . ucwords($app["status"]) . '</span>';
    }

    if ($app["status"] == "accepted") {
        $remainingApplications[$key]["status"] = '<span class="badge text-bg-success">' . ucwords($app["status"]) . '</span>';
    }

    if ($app["status"] == "rejected") {
        $remainingApplications[$key]["status"] = '<span class="badge text-bg-danger">' . ucwords($app["status"]) . '</span>';
    }
}

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
                <h2>My Applications <span class="badge text-bg-primary">New</span></h2>
                <div class="card mt-4">
                    <div class="card-body">
                        <?php if ($applications): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Club Name</th>
                                    <th scope="col">Student Name</th>
                                    <th scope="col">Application Status</th>
                                    <th scope="col">Applied Date</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $application): ?>
                                <tr>
                                    <th scope="row">1</th>
                                    <td><?php echo $application["club_name"]; ?></td>
                                    <td><?php echo $application["name"]; ?></td>
                                    <td>
                                        <?php echo $application["status"]; ?>
                                    </td>
                                    <td><?php echo $application["created_at"]; ?></td>
                                    <td>

                                        <div class="btn-group btn-group-sm" role="group" aria-label="Actions">
                                            <button type="button" class="btn btn-primary" data-bs-toggle="tooltip"
                                                data-bs-placement="left" data-bs-title="View Application"
                                                onclick="showApplication(<?php echo $application['invite_id']; ?>)"><i
                                                    class="fa-solid fa-eye"></i></button>

                                            <button type="button"
                                                onclick="updateApp('accepted', <?php echo $application['invite_id']; ?>)"
                                                class="btn btn-success" data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-title="Accept Application"><i
                                                    class="fa-solid fa-check"></i></button>
                                            <button type="button"
                                                onclick="updateApp('rejected', <?php echo $application['invite_id']; ?>)"
                                                class="btn btn-danger" data-bs-toggle="tooltip"
                                                data-bs-placement="right" data-bs-title="Reject Application"><i
                                                    class="fa-solid fa-xmark"></i></button>
                                        </div>

                                    </td>
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


            <div class="col-12 mb-3">

                <div class="card mt-4">
                    <div class="card-body">
                        <?php if ($remainingApplications): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Club Name</th>
                                    <th scope="col">Student Name</th>
                                    <th scope="col">Application Status</th>
                                    <th scope="col">Applied Date</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($remainingApplications as $app): ?>
                                <tr>
                                    <th scope="row">1</th>
                                    <td><?php echo $app["club_name"]; ?></td>
                                    <td><?php echo $app["name"]; ?></td>
                                    <td>
                                        <?php echo $app["status"]; ?>
                                    </td>
                                    <td><?php echo $app["created_at"]; ?></td>
                                    <td>

                                        <div class="btn-group btn-group-sm" role="group" aria-label="Actions">
                                            <button type="button" class="btn btn-primary" data-bs-toggle="tooltip"
                                                data-bs-placement="left" data-bs-title="View Application"
                                                onclick="showApplication(<?php echo $app['invite_id']; ?>)"><i
                                                    class="fa-solid fa-eye"></i></button>
                                        </div>

                                    </td>
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
    <script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    const updateApp = (status, invite_id) => {
        let apiEndpoint = "";
        if (status == "rejected") {
            const confirmReject = confirm("Are you sure you want to reject this application?");
            if (confirmReject) {
                apiEndpoint = "/users/teachers/api/applications/action.php?invite_id=" + invite_id +
                    "&status=rejected";

                let fetchPromise = fetch(apiEndpoint);
                fetchPromise.then(response => {
                    return response.json();
                }).then(response => {
                    if (response["status"] == "success") {
                        alert("Applicant application rejected!");
                        location.reload();
                    }
                });
            }
        } else if (status == "accepted") {
            apiEndpoint = "/users/teachers/api/applications/action.php?invite_id=" + invite_id + "&status=accepted";
            let fetchPromise = fetch(apiEndpoint);
            fetchPromise.then(response => {
                return response.json();
            }).then(response => {
                alert("Applicant application accepted!");
                location.reload();
            });
        }
    };

    // Function to Show Modal
    function showApplication(invite_id) {
        let apiEndpoint = 'http://localhost:8888/users/teachers/api/applications/application.php?id=' + invite_id;
        const fetchPromise = fetch(apiEndpoint);
        fetchPromise.then(response => {
            return response.json();
        }).then(application => {
            let applicationElement = document.getElementById("applicationContent");
            applicationElement.innerHTML = application;
            bootstrap.Modal.getOrCreateInstance('#applicationModal').show();

        });



    }
    </script>

    <div class="modal modal-lg fade" tabindex="-1" id="applicationModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="applicationContent">Application Data</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>