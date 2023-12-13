<?php 
session_start(); 
include "conn.php";

if (isset($_POST['uname']) && isset($_POST['password'])) {
    $uname = $_POST['uname'];
    $pass = $_POST['password'];

    if (empty($uname)) {
        header("Location: login.php?error=User Name is required");
        exit();
    } else if (empty($pass)) {
        header("Location: login.php?error=Password is required");
        exit();
    } else {
        if ($uname === "admin" && $pass === "admin") {
            $_SESSION['USERNAME'] = $uname;
            $_SESSION['NAMA_CUSTOMER'] = "Administrator";
            $_SESSION['ID_CUSTOMER'] = 0; // ID untuk administrator
            header("Location: index.php");
            exit();
        } else {
            header("Location: login.php?error=Incorrect User name or password");
            exit();
        }
    }
} else {
    header("Location: login.php");
    exit();
}
