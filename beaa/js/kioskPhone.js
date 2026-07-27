//document.addEventListener('contextmenu', event => event.preventDefault());

$(document).ready(function () {
    clear_phone();
    
    $("#conform-btn").click(function () {
        open_Services();
    });

    $(".key").click(function () {
        keypad_pressed(this);
    });

    $(".key-x").click(function () {
        keypad_erase(this);
    });
    $(".key-y").click(function () {
        clear_phone();
    });
});

var check1 = false;
var check2 = false;

status = 0;

function keypad_pressed(id) {

    var txt = $("#phone-num").text();

    if (txt.length < 10) {
        var val = $(id).text();
        $("#phone-num").text(txt + val);
    }

    validate();

}
function keypad_erase() {

    var txt = $("#phone-num").text();
    if (txt.length > 2) {
        var val = txt.substr(0, txt.length - 1);
        $("#phone-num").text(val);
    }

    validate();
}

function clear_phone() {
    $("#phone-num").text("07");
    clear_errors();
}

function clear_errors() {
    $(".num-validate").toggleClass("hidden", true);
    $("#err-ok").toggleClass("hidden", true);
    $("#err-no").toggleClass("hidden", true);
    $("#err-txt").toggleClass("hidden", true);
    check1 = check2 = false;
}
function validate() {
    var txt = $("#phone-num").text();
    var num_type = txt.charAt(2);
    if (num_type == 7 || num_type == 8 || num_type == 9) {
        check1 = true;
    } else {
        check1 = false;
    }

    if (txt.length === 10) {
        check2 = true;
        phone_feedback();
    } else {
        check2 = false;
        clear_errors();
    }


}
function phone_feedback() {
    if (check1 && check2) {
        $(".num-validate").toggleClass("hidden", false);
        $("#err-ok").toggleClass("hidden", false);
        $("#err-no").toggleClass("hidden", true);

    } else {
        $(".num-validate").toggleClass("hidden", false);
        $("#err-ok").toggleClass("hidden", true);
        $("#err-no").toggleClass("hidden", false);
        $("#err-txt").toggleClass("hidden", false);
    }

}
function open_Services() {
    if (check1 && check2) {
//        $("#window-up").slideUp();
//        $("#window-down").slideUp(200);
        $(".glass-container").slideUp(200);
    }else {
        $("#err-txt").toggleClass("hidden", false);
    }

}