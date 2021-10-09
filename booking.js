$(document).ready(function() {

    //====submit bookings
    $("#bookingform").on('submit', function(e) {

            $.ajax({
                type: "POST",
                url: "booking.php",
                data: $(e.target).serialize(),
                dataType: 'json',
                success: function (json) {
                }
            });

        // window.location.reload();
        // return false;
    });


    // ====search bookings
    $("#search-booking").on('submit', function(e) {

        $.ajax({
                type: "POST",
                url: "bookingpage.php",
                data: $(e.target).serialize(),
                dataType: 'json',
                success: function (json) {
                }
            });
    });

    $("#login").on('submit', function(e) {
        var name = $('#name').val(),
            email1 = $('#email1').val(),
            email2 = $('#email2').val();
        if (email1 != email2){
            alert('Emails not matching');
            return false;
        }
        if (email1 == email2){
            $.ajax({
                type: "POST",
                url: "login.php",
                data: $(e.target).serialize(),
                dataType: 'json',
                success: function (json) {
                }
            });
            alert(" WELCOME "+name);
            alert(" PLEASE REMEMBER YOUR: \n LOGIN NAME: " + name +"\n E-MAIL: "+ email1);
        }
    });
    //
    $("#logout").on('submit', function(e) {
        $.ajax({
            type: "POST",
            url: "logout.php",
            data: $(e.target).serialize(),
            dataType: 'json',
            success: function (json) {
            }
        });
        alert("You have logged out")
    });

});

// if(!empty($customer) || !empty($email)){
//     $bookingNumber = insert_new_booking_and_return_id($pdo, $customerID, $scheduleID, $bookedTicket, $arrTime);
//     echo '<script>alert("Booking has been made")</script>';
//     header("Refresh:0");
// }