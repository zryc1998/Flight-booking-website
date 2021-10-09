$(document).ready(function() {
    //====drop down effect
    load_json_data('departure');
    load_json_data('arrival');
    function load_json_data(id) {
        var html_code = '';
        $.getJSON('dropdown.php', function (data) {

            if (id === 'departure') html_code += '<option value="">From: --Please select--</option>';
            if (id === 'arrival') html_code += '<option value="">To: --Please select--</option>';
            $.each(data, function (key, value) {

                html_code += '<option value="' + value.code + '">' + value.airport + '</option>';

            });
            $('#' + id).html(html_code);
        });
    }

    //=====submit form
    $("#condition").on('submit', function(e) {
        $.ajax({
            type: "POST",
            url: "session.php",
            data: $(e.target).serialize(),
            dataType: 'json',
            success: function (json) {
            }
        });
    });
});
