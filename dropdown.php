<?php
//file name: dropdown.php
include_once "dbconnect.php";

//====send drop-down information using json
$pdo = get_pdo_instance();

$smt2 = $pdo->prepare('select * From Destinations');
$smt2->execute();
$airports= $smt2->fetchAll(PDO::FETCH_ASSOC);

//json_encode($airports);
header('Content-Type: application/json');
echo json_encode($airports, JSON_PRETTY_PRINT);
