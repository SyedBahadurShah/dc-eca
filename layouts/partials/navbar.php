<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/home.php">DC ECA</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="/"><i class="fa-solid fa-house"></i>
                        Home</a>
                </li>
            </ul>
        </div>

        <div class="d-flex">
            <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-outline-success mx-2">Register</a>
            <a href="login.php" class="btn btn-outline-success mx-2">Login</a>
            <?php else: ?>
            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                <div class="btn-group" role="group">

                    <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-user"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <p class="dropdown-item p-2 m-0 text-center"><i class="fa-solid fa-user"></i>
                                <?php echo userInfo("name") . ' (' . ucfirst(userInfo("account_type")) . ')'; ?></p>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
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