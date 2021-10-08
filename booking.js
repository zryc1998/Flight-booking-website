$(document).ready(function() {

    //====submit bookings
    $("#bookingform").on('submit', function(e) {
        var email = $("#email").val(),
            confirm = $("#email-confirm").val();

        if (email!=confirm) {
            alert('Email Not Matching');
            e.preventDefault();
        }

        if (email==confirm) {
            $.ajax({
                type: "POST",
                url: "booking.php",
                data: $(e.target).serialize(),
                dataType: 'json',
                success: function (json) {
                }
            });
            alert('Booking has been made');
        }
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

    // ====cancel bookings
    // $("#cancellation").on('submit', function(e) {
    //     $.ajax({
    //         type: "POST",
    //         url: "bookingpage.php",
    //         data: $(e.target).serialize(),
    //         dataType: 'json',
    //         success: function (json) {
    //         }
    //
    //     });
        // e.preventDefault();
        // window.location.reload();
        // return false;
    // });
});
