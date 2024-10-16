<?php
try {
    $pdo = new PDO("mysql:dbname=$database;host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $error) {
    die($error->getMessage());
}