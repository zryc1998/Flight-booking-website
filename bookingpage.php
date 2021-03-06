<?php
include_once "dbconnect.php";
session_start();

$startDate = $_SESSION['start'];
$endDate = $_SESSION['end'];
$dep = $_SESSION['from'];
$arr = $_SESSION['to'];
$numOfPassenger=$_SESSION['passenger'];

$name = $_SESSION['name'];
$email = $_SESSION['email'];
$isLoggedIn = $_SESSION['isLoggedIn'];

$pdo = get_pdo_instance();
$submitted = 0;

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
    return $arrTime->setTimezone(new DateTimeZone($aTimeZone));

}

function get_route_result(PDO $pdo, $rID, $startDate, $endDate, $dTimeZone, $aTimeZone)
{

    $sql = "SELECT * FROM Schedules 
    JOIN Timetable ON Schedules.tID = Timetable.tID  
    JOIN Routes R on R.routeID = Timetable.rID
    WHERE Schedules.rID=? AND depDate>=? AND depDate<=?";
    $stmt = $pdo->prepare($sql);
    $stmt -> execute([$rID, $startDate, $endDate]);
    $i = 0;
    $result = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $result['depDate'][$i]          = $row['depDate'];
//        $dstr = $date->format("D d M Y");
        $result['flightNo'][$i]         = $row['flightNo'];
        $result['depTime'][$i]          = $row['depTime'];
        $result['duration'][$i]         = $row['flightTime'];

        $seats = $row['maxPax'] - $row['numPax'];
        if ($seats < 0) $seats = 0;
        $result['seats'][$i]            = $seats;

        try {
            $result['arrTime'][$i] = get_arrival_time($row['depTime'], $dTimeZone, $row['flightTime'], $aTimeZone, $row['depDate'])
                ->format('D d M H:i:s');
        } catch (Exception $e) {
        }

        $result['price'][$i] = $row['price'];
        $result['routID'][$i] = $row['rID'];
        $i++;
    }
    return $result;
}

$airlineCode = "DF";

$dAirport = get_airport_name($pdo, $dep);
$aAirport = get_airport_name($pdo, $arr);

$dTimeZone = get_time_zone($pdo, $dep);
$aTimeZone = get_time_zone($pdo, $arr);

$rID = get_routeID($pdo, $dep, $arr);

$flightTime = get_flight_time($pdo, $rID);
$routePrice = get_price($pdo, $rID);


$routeResult = get_route_result($pdo, $rID, $startDate, $endDate, $dTimeZone, $aTimeZone);


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
    $sql1 = "SELECT schedID FROM Bookings WHERE bookID=?";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute([$bookingID]);
    $schedule = $stmt1->fetch(PDO::FETCH_ASSOC)['schedID'];

    $sql = "DELETE FROM `Bookings` WHERE `bookID` = :bookID";
    $statement = $pdo->prepare($sql);
    $idToDelete = $bookingID;
    $statement->bindValue(':bookID', $idToDelete);
    $statement->execute();

    return $schedule;
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

function readjust_seats_number($pdo, $schedID, $ticket)
{
    $sql = "SELECT numPax FROM Schedules WHERE schedID=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$schedID]);
    $numPax =  $stmt->fetch(PDO::FETCH_ASSOC)['numPax']-$ticket;

    $sql2= "UPDATE Schedules SET numPax=? WHERE schedID=?";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([$numPax,$schedID]);
}


$bookingID = $_REQUEST['bookingID'];
$submitted = $_REQUEST['submitted'];
$deletedTicket = $_REQUEST['ticket'];

$deletedSchedID = delete_booking($pdo, $bookingID);

$customerID = find_customer_id($pdo, $name, $email);

readjust_seats_number($pdo, $deletedSchedID, $deletedTicket);

if (is_numeric($customerID)) $isExisted = true;
else $isExisted = 0;

$result = find_booking($pdo, $customerID);


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
                        <?php
                        if ($isLoggedIn == 1) {
                            echo '
                            <li><form id = "logout" method="post" action="">
                                <a style="color: rgba(94,94,94,0.8)">Welcome ' . $name . ' </a>
                                    <input type="hidden" name="login-name" value="">
                                    <input type="hidden" name="login-email" value="">
                                <input type="submit" class="form-group" id="" name="search-submit" value="Logout" style="color:whitesmoke;background: rgba(51,73,95,0.86)" >
                         </form></li>
                        ';
                        }
                        else if($isLoggedIn == 0){
                            echo '
                                <li><form id = "login" method="post" action="login.php">
                                <input type="text" class="form-group" id="name" name="login-name" placeholder="Name" required>
                                <input type="email" class="form-group" id="email1" name="login-email" placeholder="E-mail" required>
                                <input type="email" class="form-group" id="email2" name="login-email-confirm" placeholder="Confirm Your E-mail" required>
                                <input type="submit" class="form-group" id="" name="search-submit" value="Login" style="color:whitesmoke;background: rgba(51,73,95,0.86)" >
                                <a style="font-size:small; color: rgba(94,94,94,0.8)">Not a member? No worries, once you have booked a flight, you will automatically become one. </a>
                         </form></li>
                        ';
                        }
                        ?>

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
                    if (sizeof($rID)  == 0 && $startDate != null){
                        echo "<h2>No Available Flight</h3>";
                        echo "<p>$startDate to $endDate</p>";
                        echo "<p></p>";
                        echo "<a href='index.html' ><img class='inner-service heading-section col-md-12 text-center' src='./images/slide5-1.jpg' alt='Please Re-search'></a>";
                    }
                    else if ($startDate == null || $endDate == null){
                        echo "<h2>Looking for a flight? Please go to home page</h3>";
                        echo "<p>or</p>";
                        echo "<p>Click 'manage your booking' for booking record</p>";
                        echo "<p></p>";
                        echo "<a href='index.html' ><img class='inner-service heading-section col-md-12 text-center' src='./images/slide8-1.jpg' alt='Re-start searching of manage your booking'></a>";
                        }
                    else if(sizeof($rID) > 0 && $startDate != null && $isLoggedIn == 0 ){
                        echo "<h2>$dAirport --to-- $aAirport</h3>";
                        echo "<p></p>";
                        echo "<p>$startDate to $endDate</p>";
                        echo '<p style="color: darkred; font-weight: bold">*Please log in before booking</p>';
                        echo '
                            </div>
                             <a style="color: rgba(94,94,94,0.8)">Available tickets are low? Refresh to see if you can get lucky </a>
                             <a href = "booking.php">
                                <input type="submit" class="form-group" name="search-submit" value="Refresh Result"
                               style="color:whitesmoke;background: #66512c"/>
                            <br></a>
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
                            </tr>
                        ';

                    }else if (sizeof($rID) > 0 && $startDate != null && $isLoggedIn == 1 ){
                        echo "<h2>$dAirport --to-- $aAirport</h3>";
                        echo "<p></p>";
                        echo "<p>$startDate to $endDate</p>";
                        echo "<p>*Please choose your flight</p>";
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
                            </tr>
                        ';
                    }
                    $i = 0;
                    foreach ($routeResult['depDate'] as $item){
                        echo '<tr>
                            <td>'.$airlineCode.''.$routeResult['flightNo'][$i].'</td>
                            <td>'.$routeResult['depDate'][$i].'</td>
                            <td>'.$routeResult['depTime'][$i].'</td>
                            <td>'.$routeResult['duration'][$i].' hr</td>
                            <td>'.$routeResult['arrTime'][$i].'</td>
                            <td>'.$routeResult['seats'][$i].'</td>
                            <td>$'.$routeResult['price'][$i].'</td>
                            ';
                        if ($routeResult['seats'][$i]>0){
                            echo '
                            <form id = "bookingform" action="booking.php" method="post">
                                <input type="hidden" name="flightNo" value="'.$routeResult['flightNo'][$i].'">
                                <input type="hidden" name="depDate" value="'.$routeResult['depDate'][$i].'">
                                <input type="hidden" name="depTime" value="'.$routeResult['depTime'][$i].'">
                                <input type="hidden" name="duration" value="'.$routeResult['duration'][$i].'">
                                <input type="hidden" name="arrTime" value="'.$routeResult['arrTime'][$i].'">
                                <input type="hidden" name="price" value="'.$routeResult['price'][$i].'">
                                <input type="hidden" name="routeID" value="'.$routeResult['routID'][$i].'">
                                <input type="hidden" name="from" value="'.$dep.'">
                                <input type="hidden" name="to" value="'.$arr.'">
                                <input type="hidden" name="type" value="all">
                                <td><select id="ticket" name= "ticket" class="frm-field" required >
                                ';
                            for($k = 1; $k <= $routeResult['seats'][$i]; $k++) {
                                echo '
                                <option value ="' . $k. '">' . $k. '</option>
                                ';
                            }
                                echo ' </select>
                                <input type="hidden" id="isLoggedIn" name="email" value ="'.$isLoggedIn.'">
                                <input type="hidden" id="customer" name="customer" value ="'.$name.'">
                                <input type="hidden" id="email" name="email" value ="'.$email.'">';
                            if($isLoggedIn == 1){
                                echo '<button id = "submitbooking" type="submit" href="#booking" class="btn">Book</button>';
                            }
                            else {
                                echo '<button id = "submitbooking" type="submit" href="#booking" class="btn" disabled>Book</button>';
                            }
                            echo '</form>
                        </td>';
                        }
                        else echo '<td><p id = "sold out">SOLD OUT</p></td>';
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
                    <p>print out or cancel your booking</p>
                </div>
                    <a style="color: rgba(94,94,94,0.8)">Having trouble finding your booking? Why don't push the button here? </a>
                    <a href = "booking.php">
                        <input type="submit" class="form-group" name="search-submit" value="Refresh Booking"
                               style="color:whitesmoke;background: #66512c"/>
                    <br></a>
                <?php
                if ($isLoggedIn == 1 && sizeof($result) == 0)
                echo '<a style="color: #66512c; font-weight: bold;">No Booking Record</a>';
                if ($isLoggedIn == 0 )
                echo '<a style="color: #66512c; font-weight: bold;">Please Login First</a>';
                ?>

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
                            <form id = "cancellation" action="bookingpage.php" method="post">
                             <input type="hidden" name="ticket" value="' .$result['ticket'][$j].'">
                                    <input type="hidden" name="bookingID" value="' .$result['bookID'][$j].'">
                                    <input type="hidden" name="search-name" value="'.$result['name'][$j].'">
                                    <input type="hidden" name="search-email" value="'.$result['email'][$j].'">
                                    <input type="hidden" name="submitted" value="1">
                                    <input type="hidden" name="type" value="all"><td>
                            <button id = "btn-cancel" type="submit" href="#manage" style="background: #ac2925" class="cancellation-btn btn">Cancel</button>
                            </form>
                            </td>';
                        $j++;
                    }
                }
               echo '</table>';
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