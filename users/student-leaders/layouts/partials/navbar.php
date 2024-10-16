<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">DC ECA</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="/"><i class="fa-solid fa-house"></i>
                        Dashboard</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-users-rectangle"></i> Clubs
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="/users/student-leaders/clubs/list.php">My Clubs</a></li>
                        <li><a class="dropdown-item" href="/users/student-leaders/clubs">Search Clubs</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/users/student-leaders/annoucements.php">
                    <i class="fa-solid fa-bullhorn"></i> Annoucements
                    </a>
                </li>

            </ul>
        </div>

        <div class="d-flex">

            <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-outline-success mx-2">Register</a>
            <a href="login.php" class="btn btn-outline-success mx-2">Login</a>
            <?php else: ?>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="javascript:void()"
                        class="nav-link mx-2"><?php echo userInfo("name") . ' (' . ucwords(str_replace("_", " ", userInfo("account_type"))) . ')'; ?></a>
                </li>
            </ul>
            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                <div class="btn-group" role="group">

                    <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-user"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/users/password.php">Change Password</a></li>
                        <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
            <?php endif;?>
        </div>
    </div>
</nav>

<?php if (Flash::exists()): ?>
<div class="alert alert-danger" role="alert">
    <class="text-center">
        <?php echo Flash::display(); ?>
        </class>
</div>
<?php endif;?>

<?php if (isset($_GET["student-added"])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo "Student added successfully!"; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif;?>