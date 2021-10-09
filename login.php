<?php
include_once 'dbconnect.php';
include 'bookingpage.php';
$pdo = get_pdo_instance();

$loginName = htmlspecialchars(trim($_POST['login-name']));
$loginEmail = htmlspecialchars(trim($_POST['login-email']));

$customerID = find_customer_id($pdo, $loginName, $loginEmail);

$isLoggedIn = 1;

session_start();
$_SESSION['name'] = $loginName;
$_SESSION['email'] = $loginEmail;
$_SESSION['isLoggedIn'] = $isLoggedIn;





