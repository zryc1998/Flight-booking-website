<?php
include_once 'dbconnect.php';
session_abort();
session_start();
$pdo = get_pdo_instance();

$startDate = htmlspecialchars(trim($_POST['start']));
$endDate = htmlspecialchars(trim($_POST['end']));
$dep = htmlspecialchars(trim($_POST['from']));
$arr = htmlspecialchars(trim($_POST['to']));
$numOfPassenger = htmlspecialchars(trim($_POST['passenger']));

$_SESSION['start'] = $startDate;
$_SESSION['end'] = $endDate;
$_SESSION['from'] = $dep;
$_SESSION['to'] = $arr;
$_SESSION['passenger'] = $numOfPassenger;
$_SESSION['isLoggedIn'] = 0;





