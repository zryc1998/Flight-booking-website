<?php
include_once "dbconnect.php";

$pdo = get_pdo_instance();

function get_routeID($pdo, $dep, $arr){
    $sql = "SELECT * FROM Routes WHERE point1=? AND point2=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dep, $arr]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['routeID'];
}

function get_airport_name($pdo, $destCode)
{
    $sql = "SELECT * FROM Destinations WHERE code=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$destCode]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['airport'];
}

function get_time_zone($pdo, $destCode){
    $sql = "SELECT * FROM Destinations WHERE code=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$destCode]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['tz'];
}


function get_price(PDO $pdo, $route){
    $sql = "SELECT * FROM Routes WHERE routeID=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$route]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['price'];
}

function get_flight_time(PDO $pdo, $route)
{
    $sql = "SELECT * FROM Routes WHERE routeID=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$route]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['flightTime'];
}

/**
 * @throws Exception
 */
function get_arrival_time($depTime, $dTimeZone, $flightTime, $aTimeZone, $depDate) {
    $minute = floor($flightTime * 60);
    $dateTime = date('Y-m-d H:i:s', strtotime("$depDate  $depTime"));
    $dt = new DateTime($dateTime, new DateTimeZone($dTimeZone));
    $dt->format('Y-m-d H:i:s');
    $arrTime = $dt->add(new DateInterval('PT'.$minute.'M'));
    $arrTime->setTimezone(new DateTimeZone($aTimeZone));
//    return $arrTime->format('D d M H:i:s');
    return $arrTime->setTimezone(new DateTimeZone($aTimeZone));

}

$airlineCode = "DF";

$startDate = htmlspecialchars(trim($_POST['start']));
$endDate = htmlspecialchars(trim($_POST['end']));
$dep = htmlspecialchars(trim($_POST['from']));
$arr = htmlspecialchars(trim($_POST['to']));
$numOfPassenger = htmlspecialchars(trim($_POST['passenger']));


$dAirport = get_airport_name($pdo, $dep);
$aAirport = get_airport_name($pdo, $arr);

$dTimeZone = get_time_zone($pdo, $dep);
$aTimeZone = get_time_zone($pdo, $arr);

$rID = get_routeID($pdo, $dep, $arr);

$flightTime = get_flight_time($pdo, $rID);
$routePrice = get_price($pdo, $rID);


$sql = "SELECT * FROM Schedules INNER JOIN Timetable ON Schedules.tID = Timetable.tID WHERE Schedules.rID=? AND depDate>=? AND depDate<=?";
$stmt = $pdo->prepare($sql);
$stmt -> execute([$rID, $startDate, $endDate]);

$i = 0;


//====search order
function find_customer_id(PDO $pdo, $name, $email)
{
    $sql = "SELECT * FROM Customers WHERE name=? AND email=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['custID'];
}

function delete_booking(PDO $pdo, $bookingID)
{
    $sql = "DELETE FROM `Bookings` WHERE `bookID` = :bookID";

    $statement = $pdo->prepare($sql);
    $makeToDelete = $bookingID;
    $statement->bindValue(':bookID', $makeToDelete);
    $delete = $statement->execute();
}

function find_booking(PDO $pdo, $customerID)
{
    $sql = "SELECT * FROM Bookings B 
    JOIN  Schedules S ON B.schedID = S.schedID 
    JOIN  Timetable T on T.tID = S.tID 
    JOIN Routes R on T.rID = R.routeID 
    JOIN Customers C on B.custID = C.custID 
    JOIN Aircraft A on A.craftID = R.craftID
    WHERE B.custID=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customerID]);
    $result = array();
    $i = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $result['model'][$i]        = $row['model'];
        $result['bookID'][$i]       = $row['bookID'];
        $result['arrTime'][$i]      = $row['arrTime'];
        $result['ticket'][$i]       = $row['ticket'];
        $result['name'][$i]         = $row['name'];
        $result['email'][$i]        = $row['email'];
        $result['from'][$i]         = $row['point1'];
        $result['to'][$i]           = $row['point2'];
        $result['duration'][$i]     = $row['flightTime'];
        $result['depDate'][$i]      = $row['depDate'];
        $result['price'][$i]        = $row['price'];
        $result['flightNo'][$i]    = $row['flightNo'];
        $result['depDay'][$i]       = $row['depDay'];
        $result['depTime'][$i]      = $row['depTime'];
        $result['maxPax'][$i]       = $row['capacity'];
        $i++;

    }
    return $result;
}

//$name = htmlspecialchars(trim($_POST['search-name']));
//$email = htmlspecialchars(trim($_POST['search-email']));
//$bookingID = htmlspecialchars(trim($_POST['bookingID']));

$name = $_REQUEST['search-name'];
$email = $_REQUEST['search-email'];
$bookingID = $_REQUEST['bookingID'];

// echo 'bookingID:'. $bookingID;

delete_booking($pdo, $bookingID);

$customerID = find_customer_id($pdo, $name, $email);

if (is_numeric($customerID)) $isExisted = true;
else $isExisted = 0;


$result = find_booking($pdo, $customerID);



//echo 'isExisted: '. $isExisted.'<br>';
//echo 'customerID: '. $customerID.'<br>';
//header('Content-type:application/json;charset=utf-8');
// echo json_encode($result, JSON_PRETTY_PRINT);


?>


<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <title>159339 Assignment 2 By Chao Yue 20008378</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/templatemo_misc.css">
    <link rel="stylesheet" href="css/templatemo_style_booking.css">
    <link rel="stylesheet" href="css/query.dataTables.css">


    <script src="js/jquery.min.js"></script>
    <script src="js/vendor/modernizr-2.6.1-respond-1.1.0.min.js"></script>
    <script src="js/jquery.dataTable.js"></script>
    <script type="text/javascript" src="booking.js"></script>


</head>
<body>

<div class="site-main" id="sTop">
    <div class="site-header">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <ul class="social-icons">
                        <li><a href="https://www.facebook.com/profile.php?id=1073830227" class="fa fa-facebook"></a></li>
                        <li><a href="https://instagram.com/chao.yue1020?utm_medium=copy_link" class="fa fa-instagram"></a></li>
                        <li><a href="https://www.linkedin.com/in/chao-yue-48375b21a" class="fa fa-linkedin"></a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="main-header">
            <div class="container">
                <div id="menu-wrapper">
                    <div class="row">
                        <div class="logo-wrapper col-md-4 col-sm-2 col-xs-8">
                            <h1>
                                <a href="index.html">BOOKINGS</a>
                            </h1>
                        </div>
                        <div class="col-md-8 col-sm-10 col-xs-4 main-menu text-right">
                            <ul class="menu-first hidden-sm hidden-xs">
                                <li class="active"><a href="#">available flights</a></li>
                                <li><a href="#manage">manage your booking</a></li>
                                <li><a href="index.html">Find a flight</a></li>
                            </ul>
                            <a href="#" class="toggle-menu visible-sm visible-xs"><i class="fa fa-bars"></i></a>
                        </div>
                    </div>
                </div>
                <div class="menu-responsive hidden-md hidden-lg">
                    <ul>
                        <li class="active"><a href="#">available flights</a></li>
                        <li><a href="#manage">manage your booking</a></li>
                        <li><a href="index.html">Find a flight</a></li>

                    </ul>
                </div>
            </div>
        </div>
    </div>


    <div class="container" id = "booking">
        <div class="row">
            <div class="service-item " id="service-1">
                <div class="inner-service heading-section col-md-12 text-center">
                    <?php
                    if ($rID == null && $startDate != null){
                        echo "<h2>No Available Flight</h3>";
                        echo "<p>$startDate to $endDate</p>";
                        echo "<p></p>";
                        echo "<img href='#mangage' class='inner-service heading-section col-md-12 text-center' src='./images/slide5-1.jpg' alt='Please Re-search'>";
                    }
                    else if ($startDate == null || $endDate == null){
                        echo "<h2>Looking for a flight? Please go to home page</h3>";
                        echo "<p>or</p>";
                        echo "<p>Click 'manage your booking' for booking record</p>";
                        echo "<p></p>";
                        echo "<img href='#mangage' class='inner-service heading-section col-md-12 text-center' src='./images/slide8-1.jpg' alt='Re-start searching of manage your booking'>";
                    }else {
                        echo "<h2>$dAirport --to-- $aAirport</h3>";
                        echo "<p></p>";
                        echo "<p>$startDate to $endDate</p>";
                        echo "<p>*Please leave your name and email before booking</p>";
                        echo '
                                    </div>
                                    <table id="table" class="col-md-12 col-sm-6 table table-bordered table-striped">
                                        <tr>
                                            <th>Flight No.</th>
                                            <th>Departure Date</th>
                                            <th>Departure Time</th>
                                            <th>Duration</th>
                                            <th>Arrival Time</th>
                                            <th>Available Seats</th>
                                            <th>Price</th>
                                            <th>Book</th>
                                            <th>Your Detail</th>';
                    }

                    $index = 0;
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                        try {
                            $date = new DateTime($row['depDate']);
                        } catch (Exception $e) {
                        }
                        $depDate = $row['depDate'];
                        $dstr = $date -> format ("D d M Y");
                        $flightNo = $airlineCode . $row['flightNo'];
                        $depTime = $row['depTime'];
                        $duration = $flightTime."hr";
                        $seats = $row['maxPax'] - $row['numPax'];
                        if ($seats < 0) $seats = 0;
                        try {
                            $arrivalTime = get_arrival_time($depTime, $dTimeZone, $flightTime, $aTimeZone, $depDate)
                                ->format('D d M H:i:s');
                        } catch (Exception $e) {
                        }
                        $price = "$ ".$routePrice;
                        $str = "<tr><td>$flightNo<td>$dstr<td>$depTime<td>$duration<td>$arrivalTime<td>$seats<td>$price<td>";
                        echo $str;

                        $schedule['index'][$i] = $i;
                        $schedule['flightNo'][$i] = $flightNo;
                        $schedule['depDate'][$i] = $dstr;
                        $schedule['depTime'][$i] = $depTime;
                        $schedule['duration'][$i] = $duration;
                        $schedule['arrTime'][$i] = $arrivalTime;
                        $schedule['seats'][$i] = $seats;
                        $schedule['price'][$i] = $routePrice;

                        $availableSeats = array();
                        for ($j = 1; $j <= $seats; $j++) array_push($availableSeats, $j);
                        $sqlDepDate = $date -> format('Y-m-d');

                        if ($seats>0){
                            echo '
                                <form id = "bookingform" action="bookingpage.php" method="post">
                                    <input type="hidden" name="flightNo" value="'.$flightNo.'">
                                    <input type="hidden" name="depDate" value="'.$sqlDepDate.'">
                                    <input type="hidden" name="depTime" value="'.$depTime.'">
                                    <input type="hidden" name="duration" value="'.$duration.'">
                                    <input type="hidden" name="arrTime" value="'.$arrivalTime.'">
                                    <input type="hidden" name="price" value="'.$routePrice.'">
                                    <input type="hidden" name="routeID" value="'.$rID.'">
                                    <input type="hidden" name="from" value="'.$dep.'">
                                    <input type="hidden" name="to" value="'.$arr.'">
                                    <input type="hidden" name="type" value="all">
                                        <select id="ticket" name= "ticket" class="frm-field" required >';
                            for($k = 0; $k < sizeof($availableSeats); $k++) {
                                echo '
                                <option value ="' . $availableSeats[$k] . '">' . $availableSeats[$k] . '</option>';
                            }
                            echo '</select>
                            <button id = "submitbooking" type="submit" href="#booking" class="btn">Book</button><td>
                            <input type="text" class="form-control-info" id="customer" name="customer" placeholder="Name" required>
                            <input type="email" class="form-control-info" id="email" name="email" placeholder="Email" required>
                            <input type="email" class="form-control-info" id="email-confirm" name="email-confirm" placeholder="Confirm Your Email" required">
                            </form>
                            </td>';
                        }
                        else echo '<p id = "sold out">SOLD OUT</p><td></td>';
                        $i++;
                    }
                    ?>
                    </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <!--==============================search booking==================================================-->
    <div class="container" id = "manage">
        <div class="row">
            <div class="service-item " id="service-2">
                <div class="inner-service heading-section col-md-12 text-center">
                    <h2>manage your booking</h2>
                    <p>search your booking by entering your name and email</p>
                </div> <!-- /#service-1 -->
                <form id = "search-booking" method="post">
                    <input type="text" class="form-group" id="" name="search-name" placeholder="Name" required>
                    <input type="email" class="form-group" id="" name="search-email" placeholder="Email" required>
                    <input type="submit" class="form-group" id="" name="search-submit" value="Search" style="color:whitesmoke;background: #66512c" >
                </form>

                <?php
                $j=0;
                if(sizeof($result)>0){
                    echo '
                    <table id="table" class="col-md-12 col-sm-6 table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th id = "to-here">Booking Ref#</th>
                        <th>Flight No.</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Departure Date</th>
                        <th>Departure Time</th>
                        <th>Duration</th>
                        <th>Arrival Time</th>
                        <th>Customer</th>
                        <th>Ticket</th>
                        <th>Price</th>
                        <th>Receipt</th>
                        <th>Cancel Booking</th>
                    </tr>
                    </thead>
                    ';
                    foreach ($result['bookID'] as $item) {
                        echo'
                            <tr>
                            <td>'.$result['bookID'][$j].'</td>
                            <td>DF'.$result['flightNo'][$j].'</td>
                            <td>'.$result['from'][$j].'</td>
                            <td>'.$result['to'][$j].'</td>
                            <td>'.$result['depDate'][$j].'</td>
                            <td>'.$result['depTime'][$j].'</td>
                            <td>'.$result['duration'][$j].' hr</td>
                            <td>'.$result['arrTime'][$j].'</td>
                            <td>'.$result['name'][$j].'</td>
                            <td>'.$result['ticket'][$j].'</td>
                            <td>$'.$result['price'][$j]. '</td>
                            <form id = "view-receipt" action= "receipt.php" method="post">
                                    <input type="hidden" name="bookID" value="' .$result['bookID'][$j].'">
                                    <input type="hidden" name="customer" value="'.$result['name'][$j].'">
                                    <input type="hidden" name="email" value="'.$result['email'][$j].'">
                                    <input type="hidden" name="flightNo" value="' .$result['flightNo'][$j].'">
                                    <input type="hidden" name="depDate" value="'.$result['depDate'][$j].'">
                                    <input type="hidden" name="depTime" value="'.$result['depTime'][$j].'">
                                    <input type="hidden" name="duration" value="' .$result['duration'][$j].'">
                                    <input type="hidden" name="arrTime" value="'.$result['arrTime'][$j].'">
                                    <input type="hidden" name="price" value="'.$result['price'][$j].'">
                                    <input type="hidden" name="ticket" value="' .$result['ticket'][$j].'">
                                    <input type="hidden" name="from" value="'.$result['from'][$j].'">
                                    <input type="hidden" name="to" value="'.$result['to'][$j].'">
                                    <input type="hidden" name="type" value="all">
                                    <td><button class="btn" type="submit" href="receipt.php" id="reciept">View</button>
                            </form>
                            <form id = "cancellation" action="" method="post">
                                    <input type="hidden" name="bookingID" value="' .$result['bookID'][$j].'">
                                    <input type="hidden" name="search-name" value="'.$result['name'][$j].'">
                                    <input type="hidden" name="search-email" value="'.$result['email'][$j].'">
                                    <input type="hidden" name="type" value="all"><td>
                            <button id = "btn-cancel" type="submit" href="#manage" style="background: #ac2925" class="cancellation-btn btn">Cancel</button>
                            </form>
                            </td>';
                        $j++;
                    }
                }
                echo '</table>';
                if(sizeof($result)===0 && is_numeric($customerID)){
                    echo '<p style="color: rgb(139,0,0)">No Booking Record</p>';
                }
                if(sizeof($result)===0 && !is_numeric($customerID)){
                    echo '<p style="color: darkred">Could Not Find Customer</p>';
                }
                ?>
            </div>
        </div>
    </div>



    <!-- =========================footer =================================    -->
    <footer id="footer" >
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-xs-12 text-left">
                    <a>Chao yue 20008378 Sep/2021</a>
                </div>
                <div class="col-md-4 hidden-xs text-right">
                    <a href="#top" id="go-top">Back to top</a>
                </div>
            </div>
        </div>
    </footer>


    <script src="js/vendor/jquery-1.11.0.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script src="js/plugins.js"></script>
    <script src="js/main.js"></script>


</body>
</html>