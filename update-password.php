<?php
require "init.php";

if (!isset($_SESSION["email"])) {
    header("Location: login.php");
    exit();
}

$errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST as $inputName => $inputValue) {
        if (empty($inputValue)) {
            $errors[] = ucfirst(str_replace("_", " ", $inputName)) . " is required!";
        }
    }

    if (empty($errors)) {
        $old_password = $_POST["old_password"];
        $new_password = $_POST["new_password"];
        $confirm_password = $_POST["confirm_password"];

        $email = $_SESSION["email"];
        $sql = "SELECT * FROM users WHERE email = :email AND password = :password";
        $statement = $pdo->prepare($sql);
        $statement->bindParam(":email", $email);
        $statement->bindParam(":password", $old_password);
        $statement->execute();

        $user = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $errors[] = "Incorrect old password!";
        }

        if ($new_password != $confirm_password) {
            $errors[] = "New password and confirm password must match!";
        }

        $sql = "UPDATE users SET password = :new_password WHERE email = :email";
        $statement = $pdo->prepare($sql);
        $statement->bindParam(":new_password", $new_password);
        $statement->bindParam(":email", $email);
        $user = $statement->execute();

        if ($user) {
            session_destroy();
            header("Location: login.php?password_updated");
            exit();
        }

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <title>Update Password</title>
</head>

<body>
    <?php include "layouts/partials/navbar.php";?>
    <div class="container">
        <div class="row">
            <div class="col-4"></div>
            <div class="col-4 mt-5">
                <div class="card">
                    <div class="card-body">
                        <form method="post">
                            <h2 class="text-center mb-4">Update Password</h2>
                            <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <strong>Error(s): </strong><br>
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                    <li><?php echo ucfirst($error); ?></li>
                                    <?php endforeach;?>
                                </ul>
                            </div>
                            <?php endif;?>

                            <div class="mb-3">
                                <label for="old_password" class="form-label">Old Password <span
                                        style="color: red;">*</span></label>
                                <input type="text" class="form-control" name="old_password" id="old_password"
                                    autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password <span
                                        style="color: red;">*</span></label>
                                <input type="text" class="form-control" name="new_password" id="new_password"
                                    autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password <span
                                        style="color: red;">*</span></label>
                                <input type="text" class="form-control" name="confirm_password" id="confirm_password">
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-success">Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-4"></div>
        </div>
    </div>
    <h1><?php echo Flash::display(); ?></h1>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>