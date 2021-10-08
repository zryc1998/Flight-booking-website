<?php
include_once 'dbconnect.php';

//====Auto generate schedules, comment out when don't need it

//$pdo = get_pdo_instance();
//$dt1 = new DateTime('2021-09-30T00:00:00');
//$dt2 = new DateTime('2021-11-30T23:59:00');
////$daysreq = Array('Mon', 'Wed', 'Sun');
//$intv = new DateInterval("P1D"); // One time i n t e r v a l object
//$dt = clone $dt1; // Want to keep the o r i g i n a l value for $dt1
//while ($dt < $dt2) {
//    $dow = $dt->format('D');
//    echo $dow.'<br>'
//    $sql = "SELECT * FROM Timetable WHERE depDay='$dow'";
//    $stmt = $pdo->prepare($sql);
//    $stmt->execute([$dow]);
//    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
//        $tID = $row['tID'];
//        $rID = $row['rID'];
//        $capacity = $row['capacity'];
//        echo $tID.' | ';
//        echo $rID.' | ';
//        echo $row['capacity'];
//        echo '<br>';
//        $newdate = $dt->format('Y-m-d');
//        $statement = $pdo->prepare("INSERT INTO Schedules(schedID, tID, rID, depDate, numPax, maxPax)
//        VALUES(DEFAULT, '$tID', '$rID', CAST('". $newdate ."' AS DATE), 0, $capacity)");
//        $statement->execute();
//    }
//    $dt->add($intv);
//}

