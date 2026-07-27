$(document).ready(function () {

    $("#btn-sms").click(function () {
        sendSMS();
    });
});

var username = 'Moenv1';
var password = 'Moe@1234';

function make_base_auth(user, password) {
    var tok = user + ':' + password;
    var hash = Base64.encode(tok);
    return "Basic " + hash;
}

function sendSMS() {

    var values = $("#sms-form").serialize();
    $.ajax({
        type: 'POST',
        dataType: 'json',
        cache: false,
        url: 'https://bulk-sms.gov.jo/index.php/api/send_sms/send',//https://bulk-sms.gov.jo.  https://bulksms.arabiacell.net
        data: values,
//        beforeSend: function (xhr) {
//            xhr.setRequestHeader("Authorization", "Basic " + btoa(username + ":" + password));
//        }, 
        headers: {
            "Authorization": "Basic " + btoa(username + ":" + password)
        },
        success: function (response, textStatus, jqXHR) {

            if (response) {

                $("#sms-result").text(response);
            } else {

            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}