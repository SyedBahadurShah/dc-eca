<?php
require "init.php";

// For Guests
if (!isLoggedIn()) {
    include "users/home.php";
    exit();
}

// For Students
if (accountType("student")) {
    include "users/students/layouts/partials/dashboard.php";
    exit();
}

// For Student Leaders
if (accountType("student_leader")) {
    include "users/student_leaders/layouts/partials/dashboard.php";
    exit();
}

// Teachers
if (accountType("teacher")) {
    include "users/teachers/layouts/partials/dashboard.php";
    exit();
}

// Head
if (accountType("head")) {
    include "users/head/layouts/partials/dashboard.php";
    exit();
}
