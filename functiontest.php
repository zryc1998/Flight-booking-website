<?php
//include_once "dbconnect.php";

//====this file for testing functions
//$pdo = get_pdo_instance();
//
//function get_time_zone($pdo, $destCode){
//    $sql = "SELECT * FROM Destinations WHERE code=?";
//    $stmt = $pdo->prepare($sql);
//    $stmt->execute([$destCode]);
//    return $stmt->fetch(PDO::FETCH_ASSOC)['tz'];
//}
//
//
//
//
//echo get_time_zone($pdo, 'NZNE');
//
//$departureTime = '12:00:00';
//$departureTimezone = 'Pacific/Auckland';
//$flightTime = 7.5;
//$arrivalTimezone = 'Australia/Sydney';
//$departureDate = '2021-10-15';
//get_arrival_time($departureTime, $departureTimezone, $flightTime, $arrivalTimezone, $departureDate);
//
//function get_arrival_time($departureTime, $departureTimezone, $flightTime, $arrivalTimezone, $departureDate) {
//    $depDate = $departureDate;
//    echo $depDate;
//    echo '<br>';
//    $depTime = $departureTime;
//    echo $depTime;
//    echo '<br>';
//
//    $minute = floor($flightTime * 60);
//    echo $minute;
//    echo '<br>';
//
//    $datetime = date('Y-m-d H:i:s', strtotime("$depDate  $depTime"));
//    echo $datetime;
//    echo '<br>';
//
//    $dt = new DateTime($datetime, new DateTimeZone($departureTimezone));
//    echo $dt->format('Y-m-d H:i:s');
//    echo '<br>';
//
//    $arrTime = $dt->add(new DateInterval('PT'.$minute.'M'));
//    echo $arrTime->format('Y-m-d H:i:s');
//    echo '<br>';
//
//    $arrTime->setTimezone(new DateTimeZone($arrivalTimezone));
//
//    echo $arrTime->format('Y-m-d H:i:s');
//}
//
//
//while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
//    $date = new DateTime($row['depDate']);
//    $depDate = $row['depDate'];
//    $dstr = $date -> format ("D Y M d");
//    $flightNo = $airlineCode . $row['flightNo'];
//    $depTime = $row['depTime'];
//    $duration = $flightTime."hr";
//    $seats = $row['maxPax'] - $row['numPax'];
//    if ($seats < 0) $seats = 0;
//    $arrivalTime =get_arrival_time($depTime,$dTimeZone,$flightTime,$aTimeZone,$depDate);
//    $price = "$ ".$routePrice;
//    $str = "<tr><td>$flightNo<td>$dstr<td>$depTime<td>$duration<td>$arrivalTime</td><td>$seats<td>$price<td><BUTTON>SELECT</td>";
//    echo $str;
//}