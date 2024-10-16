<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require "init.php";
require "vendor/autoload.php";

$errors = [];
$name = '';
$email = '';
$password = '';
$accountType = '';
$yearGroup = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accountType = $_POST["account_type"];
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $yearGroup = $_POST["year_group"];

    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
        $errors[] = "Password does not meet standard requirements";
    }

    $sql = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $sql->execute(['email' => $email]);
    $user = $sql->fetch();

    if ($user) {
        $errors[] = "An account with this email already exists.";
    }

    if ($accountType == "Student") {
        if ($yearGroup < 7 || $yearGroup > 14) {
            $errors[] = "Invalid year group selected for student account!";
        }
        /*if (!preg_match('/^[a-zA-Z]{3,}[0-9]{4,5}@gmail.com$/i', $email)) {
    $errors[] = "Invalid email format for student account!";
    }*/
    }

    if ($accountType == "Teacher") {
        if (!empty($yearGroup)) {
            $errors[] = "Teacher can't select year group!";
        }
        // if (!preg_match('/^[a-zA-Z]{3,}.[a-zA-Z]{3,}@gmail.com$/i', $email)) {
        //     $errors[] = "Invalid email format for teacher account!";
        // }
    }

    if ($accountType == "Student Leader") {
        if ($yearGroup != 12) {
            $errors[] = "Invalid year group selected for student leader account!";
        }

        if (!preg_match('/^[a-zA-Z]{3,}[0-9]{4,5}@gmail.com$/i', $email)) {
            $errors[] = "Invalid email format for student leader account!";
        }
    }

    if (empty($errors)) {
        $sql = "INSERT INTO users (`email`, `name`, `password`, `account_type`, `year_group`) VALUES (:email, :name, :password, :account_type, :year_group)";
        $statement = $pdo->prepare($sql);
        $statement->bindParam(":email", $email);
        $statement->bindParam(":name", $name);
        $statement->bindParam(":password", $password);
        $statement->bindParam(":account_type", $accountType);
        $statement->bindParam(":year_group", $yearGroup);
        $user = $statement->execute();

        $user_id = $pdo->lastInsertId();
        $token = md5(date("d/m/Y H:m:s"));

        if ($user) {
            $sql = "INSERT INTO email_verify (user_id, token) VALUES (:user_id, :token)";
            $statement = $pdo->prepare($sql);
            $statement->bindParam(":user_id", $user_id);
            $statement->bindParam(":token", $token);
            $statement->execute();

            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'radicat777@gmail.com';
                $mail->Password = 'yptn gxnd xvuh yzlc';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                $mail->setFrom('iskander9086@dubaicollege.org', 'Iskander');
                $mail->addAddress($email, $email);

                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Account';
                $mail->Body = 'Please click on following click to verify your account:<br><br><a href="http://localhost:8888/verify.php?token=' . $token . '">Verify Account</a>';
                $mail->send();
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }

            Flash::set("Please verify your account by checking email address.");
            header("Location: login.php");
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
    <title>REGISTER</title>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col"></div>
            <div class="col-4 mt-5">
                <div class="card">
                    <div class="card-body">
                        <h2 class="text-center">SIGN UP</h2>
                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <strong>Error(s): </strong><br>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                                <?php endforeach;?>
                            </ul>
                        </div>
                        <?php endif;?>

                        <form method="post">
                            <div class="form-group">
                                <label for="accountType">ACCOUNT TYPE</label>
                                <select class="form-control" id="accountType" name="account_type">
                                    <option <?php if ($accountType == 'Student') {
    echo 'selected';
}
?>>Student</option>
                                    <option <?php if ($accountType == 'Teacher') {
    echo 'selected';
}
?>>Teacher</option>
                                    <option <?php if ($accountType == 'Student Leader') {
    echo 'selected';
}
?>>Student
                                        Leader</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="name">Fullname:</label>
                                <input type="text" class="form-control" id="name" aria-describedby="emailHelp"
                                    name="name" placeholder="Fullname" value="<?php echo htmlspecialchars($email); ?>">
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Email address:</label>
                                <input type="email" class="form-control" id="exampleInputEmail1"
                                    aria-describedby="emailHelp" name="email" placeholder="Email"
                                    value="<?php echo htmlspecialchars($email); ?>">
                                <small id="emailHelp" class="form-text text-muted">Please enter a valid DC email</small>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputPassword1">Password:</label>
                                <input type="password" class="form-control" id="exampleInputPassword1" name="password"
                                    placeholder="Password" value="<?php echo htmlspecialchars($password); ?>">
                            </div>

                            <div class="form-group">
                                <label for="yearGroup">Year Group</label>
                                <select class="form-control" id="yearGroup" name="year_group">
                                    <option value="">Select Year Group</option>
                                    <?php for ($x = 7; $x <= 13; $x++): ?>
                                    <option <?php if ($yearGroup == $x) {
    echo 'selected';
}
?>><?php echo $x; ?></option>
                                    <?php endfor;?>
                                </select>
                            </div>
                            <div class="text-center mt-3">
                                <small id="emailHelp" class="form-text text-muted">Dont have an account?
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
        <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>