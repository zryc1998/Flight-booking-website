<?php
include_once 'dbconnect.php';
include 'bookingpage.php';

$pdo = get_pdo_instance();

//====functions
function create_new_customer(PDO $pdo, $customer, $email)
{
    $sql = "INSERT INTO Customers (name, email) VALUES (?,?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customer, $email]);
    return $pdo->lastInsertId();

}

function get_customer_id($pdo, $customer, $email){
    $sql = "SELECT * FROM Customers WHERE name=? AND email=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customer, $email]);
    $customerID = $stmt->fetch(PDO::FETCH_ASSOC)['custID'];
    if (!is_numeric($customerID))
        return create_new_customer($pdo, $customer, $email);

    return $customerID;
}


function get_timetable_id(PDO $pdo, $depTime, $routID, $depDay)
{
    $sql = "SELECT * FROM Timetable WHERE depTime=? AND rID=? AND depDay=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$depTime, $routID, $depDay]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['tID'];
//    echo 'timeTableID in function: '. $stmt->fetch(PDO::FETCH_ASSOC)['tID'].'<br>';
}

function get_schedule_id(PDO $pdo, $depDate, $timeTableID)
{
    $sql = "SELECT schedID FROM Schedules WHERE depDate=? AND tID=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$depDate, $timeTableID]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['schedID'];
}

function insert_new_booking_and_return_id(PDO $pdo, $customerID, $scheduleID, $ticket, $arrTime)
{
    $sql ="INSERT INTO Bookings(custID, schedID, ticket, arrTime) VALUES (?,?,?,?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customerID, $scheduleID, $ticket, $arrTime]);
    return $pdo->lastInsertId();
}

//=====variables
//$flightNo = $_REQUEST['flightNo'];
//$depDate = $_REQUEST['depDate'];
//$depDay = date("D", strtotime($depDate));
//$depTime =  $_REQUEST['depTime'];
//$duration = $_REQUEST['duration'];
//$arrTime =  $_REQUEST['arrTime'];
//$bookedPrice =  $_REQUEST['price'];
//$bookedTicket = $_REQUEST['ticket'];
//$bookedRoutID = $_REQUEST['routeID'];

//$customer = $_REQUEST['customer'];
//$email = $_REQUEST['email'];
//$from = $_REQUEST['from'];
//$to = $_REQUEST['to'];

$flightNo = htmlspecialchars(trim($_POST['flightNo']));
$depDate = htmlspecialchars(trim($_POST['depDate']));
$depDay = date("D", strtotime($depDate));
$depTime =  htmlspecialchars(trim($_POST['depTime']));
$duration = htmlspecialchars(trim($_POST['duration']));
$arrTime =  htmlspecialchars(trim($_POST['arrTime']));
$bookedPrice =  htmlspecialchars(trim($_POST['price']));
$bookedTicket = htmlspecialchars(trim($_POST['ticket']));
$bookedRoutID = htmlspecialchars(trim($_POST['routeID']));


$customer = htmlspecialchars(trim($_POST['customer']));
$email = htmlspecialchars(trim($_POST['email']));
$from = htmlspecialchars(trim($_POST['from']));
$to = htmlspecialchars(trim($_POST['to']));




//====get variables
$timeTableID = get_timetable_id($pdo, $depTime, $bookedRoutID, $depDay);
$customerID = get_customer_id($pdo, $customer,$email);
$scheduleID = get_schedule_id($pdo, $depDate, $timeTableID);

session_start();
$_SESSION['ticket'] = $bookedTicket;
$_SESSION['timeTableID'] = $timeTableID;
$_SESSION['depDate'] = $depDate;

//adjust_seats_number($pdo,$bookedTicket,$depDate,$timeTableID);
if(!empty($customer) || !empty($email)){
    $bookingNumber = insert_new_booking_and_return_id($pdo, $customerID, $scheduleID, $bookedTicket, $arrTime);
    echo '<script>alert("Booking has been made")</script>';
    header("Refresh:0");
}


//prompts
//echo 'flightNo: '. $flightNo.'<br>';
//echo 'depDate: '. $depDate.'<br>';
//echo 'depDay: '. $depDay.'<br>';
//echo 'depTime: '. $depTime.'<br>';
//echo 'duration: '. $duration.'<br>';
//echo 'arrTime: '. $arrTime.'<br>';
//echo 'price: '. $bookedPrice.'<br>';
//echo 'ticket: '. $bookedTicket.'<br>';
//echo 'customer: '. $customer.'<br>';
//echo 'email: '. $email.'<br>';
//echo 'customerID: '. $customerID.'<br>';
//echo 'timeTableID: '. $timeTableID.'<br>';
//echo 'routeID: '. $bookedRoutID.'<br>';
//echo 'scheduleID: '. $scheduleID.'<br>';
//echo 'bookingNumber: '. $bookingNumber.'<br>';




