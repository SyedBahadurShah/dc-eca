<?php

require "init.php";

$errors = [];
$email = '';
$password = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Store the submitted values
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';

    // Validate each input
    foreach ($_POST as $inputName => $inputValue) {
        if (empty($inputValue)) {
            $errors[] = "$inputName is required!";
        }
    }

    if (empty($errors)) {
        $sql = "SELECT * FROM users WHERE email = :email AND password = :password";
        $statement = $pdo->prepare($sql);
        $statement->bindParam(":email", $email);
        $statement->bindParam(":password", $password);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $errors[] = "Username or password is incorrect!";
        } else if (!$user["is_active"]) {
            $errors[] = "Please verify your email address before login!";
        } else {
            $_SESSION["email"] = $email;
            $_SESSION["name"] = $user["name"];
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["account_type"] = $user["account_type"];
            $_SESSION["year_group"] = $user["year_group"];
            header("Location: /");
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
    <title>LOGIN</title>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col"></div>
            <div class="col-4 mt-5">
                <div class="card">
                    <div class="card-body">
                        <h2 class="text-center">LOGIN</h2>

                        <?php if (Flash::exists()): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo Flash::display(); ?>
                        </div>
                        <?php endif;?>

                        <?php if (isset($_GET["password_updated"])): ?>
                        <div class="alert alert-success" role="alert">
                            Password Updated Successfully!
                        </div>
                        <?php endif;?>

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

                        <form method="post">
                            <div class="form-group">
                                <label for="email">Email address:</label>
                                <input type="email" class="form-control" id="email" aria-describedby="emailHelp"
                                    name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>">
                                <small id="emailHelp" class="form-text text-muted">Please enter a valid DC email</small>
                            </div>
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Password" value="<?php echo htmlspecialchars($password); ?>">
                            </div>
                            <div class="text-center mt-3">
                                <small id="emailHelp" class="form-text text-muted">Don't have an account?
                                    <a href="register.php">REGISTER HERE</a>
                                </small>
                                <br><br>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col"></div>
        </div>
    </div>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>