$("#short-beep").click(function () {
    var curValue = $("#short-beep").val();
    var newValue = curValue == 0 ? 'active' : 'inactive';
    console.log("new value: " + newValue);
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/update.php',
        data: {type: 'shortaudio', value: newValue, id:11},
        success: function (response, textStatus, jqXHR) {

            console.log(response);
            if (response) {
                
                if (response == 'active') {
                    $("#short-beep").text(lang_active);
                    $("#short-beep").val(1);
                    $("#short-beep").addClass("btn-success");
                    $("#short-beep").removeClass("btn-danger");

                } else {
                    $("#short-beep").text(lang_inactive);
                    $("#short-beep").val(0);
                    $("#short-beep").removeClass("btn-success");
                    $("#short-beep").addClass("btn-danger");
                }
            }

        },
        error: function (jqXHR, textStatus, errorThrown) {
            //alert('Error - ' + errorThrown);
        }
    });
});