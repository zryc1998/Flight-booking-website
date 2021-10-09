<?php
include_once 'dbconnect.php';
$pdo = get_pdo_instance();
function get_airport_name($pdo, $destCode)
{
    $sql = "SELECT * FROM Destinations WHERE code=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$destCode]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['airport'];
}

$flightCode ="DF";

$bookID = $_REQUEST ['bookID'];
$flightNo = $_REQUEST ['flightNo'];
$depDate = $_REQUEST ['depDate'];
$depTime =  $_REQUEST ['depTime'];
$duration = $_REQUEST ['duration'];
$arrTime = $_REQUEST ['arrTime'];
$price =  $_REQUEST ['price'];
$ticket = $_REQUEST ['ticket'];
$name = $_REQUEST ['customer'];
$email = $_REQUEST ['email'];
$from = $_REQUEST ['from'];
$to = $_REQUEST ['to'];
$dAirport = get_airport_name($pdo, $from);
$aAirport = get_airport_name($pdo, $to);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Receipt</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/templatemo_misc.css">
    <link rel="stylesheet" href="css/query.dataTables.css">
    <link rel="icon" href="./images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="css/receipt.css">
</head>

<body>
<h1 style="font-weight: bold">THANK YOU FOR YOUR BUSINESS</h1>
<h3>DAIRY FLAT BOOKING CENTRE</h3>
<?php
echo'
<form id = "go-back" action="bookingpage.php" method="post">
    <input type="hidden" name="search-name" value="'.$name.'">
    <input type="hidden" name="search-email" value="'.$email.'">
    <BUTTON href="javascript:;" onclick="window.print()"  style="background: #ac2925" class="btn form-group">Print</BUTTON>
    <BUTTON id = "back"  type="submit" class="btn form-group">Go Back</BUTTON>
    <input type="hidden" name="type" value="all">
</form>';
?>

<div class="receipt-box" style="margin-top: 20px">
    <table>
        <tr class="top">
            <td colspan="7">
                <table>
                    <tr>
                        <td class="title">
                            <img src="./images/logo.png" alt="Company logo" style="width: 40%; max-width: 300px" />
                        </td>
                        <td>
                            <?php
                            echo 'Created: '.date('D d/m/yy').'<br />';

                            ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="information">
            <td colspan="7">
                <table>
                    <tr>
                        <td>
                            DAIRY FLAT BOOKING LTD<br>
                            12345 Dairy Flat Road<br>
                            Dairy Flat, 12345
                        </td>
                        <td>
                            <?php
                            echo '
                            '.$name.'<br>
                                    <br>
                            '.$email.'<br>
                                    <br>
                        <p>Flight Booked</p>
                        <p>From: '.$dAirport.' to: '.$aAirport.'</p>';
                        ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>


        <tr class="heading" style="text-align: center">
            <?php
            echo '
            <td>'.$from.' ->'.$to.'</td>
            <td> </td>
            <td> </td>
            <td> </td>
            <td>Ref # '.$bookID.'</td>
            '
            ?>
        </tr>

        <tr class="details" style="text-align: center; font-weight: bold ">
            <td>Flight No
            <td>Departure Date
            <td>Departure Time
            <td>Duration
            <td>Arrival Time</td>
        </tr>
        <tr class="details" style="text-align: center">
            <?php
            echo '
            <td>'.$flightCode.''.$flightNo.'</td>
            <td>'.$depDate.'</td>
            <td>'.$depTime.'</td>
            <td>'.$duration.'</td>
            <td>'.$arrTime.'</td>
            '
            ?>
        </tr>

        <tr class="heading" style="text-align: center">
            <td>Ticket</td>
            <td></td>
            <td></td>
            <td></td>
            <td>Price</td>
        </tr>

        <tr class="item" style="text-align: center">
            <?php
            echo '
            <td>'.$ticket.'</td>
            <td></td>
            <td></td>
            <td></td>
            <td>$'.$price.'</td>
            '
            ?>
        </tr>

        <tr class="item" style="text-align: center">
            <td>GST (Icluded)</td>
            <td></td>
            <td></td>
            <td></td>
            <td>%15</td>
        </tr>
        <tr class="total" style="text-align: center">
            <?php
            $total = $ticket * $price;
            echo '
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>Total: $'.$total.'</td>
            '
            ?>

        </tr>
    </table>
</div>
</body>
</html>