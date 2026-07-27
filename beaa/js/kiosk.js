$(document).ready(function () {

    updateTime();
    
//    $(".btn-kiosk").mouseover(function () {
//        
////        $(this).
//    });

    refreshKioskBtns();

});
//==============================================================================|| vars

var url_refreshBtns = "../api/kiosk/get.php";
var url_createTicket = "../api/kiosk/set.php";
var url_printTicket = "../api/printticket.php";

var arrowAnimation;

//==============================================================================|| language

function changeLang(lang) {
    if (currentLang != lang) {
        location.replace(langPathReplace + lang);
    }
}

//==============================================================================|| Create and print Tickets

function createTicket(id) {
    
    $("#"+id).attr("disabled", "disabled");
    var cat = id.replace("b-", "");
    
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: url_createTicket,
        data: {lang: currentLang, kiosk: kioskID, category: cat},
        success: function (response, textStatus, jqXHR) {

            if (response) {
                //alert(response);
                //alert($("#"+id).text());
                removeDisabled();
                if (isPrintTicket == 1) {
                    // show printing dialog
                    
                    var serviceName = $("#"+id).text();
                    startAnimationArrow(serviceName);
                    $("#print-note").fadeIn(400);
                    
                    printTicket(response);
                }

            } else {
                // alert("error! please check connection.");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            //alert('Error - ' + errorThrown);
        }
    });
}

function printTicket(eventID) {
    $("#print-note").delay(2500).fadeOut();
//    $("#digital-clock").focus();
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: url_printTicket,
        data: {kiosk: kioskID, event: eventID},
        success: function (response, textStatus, jqXHR) {

            if (response) {
                //alert(response);
                
                
                stopAnimationArrow();
            } else {
                // alert("error! please check connection.");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            //alert('Error - ' + errorThrown);
        }
    });
}

//==============================================================================|| refresh btns

setInterval(function () {
    //refreshKioskBtns();
}, 8000);
function refreshKioskBtns() {
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: url_refreshBtns,
        data: {kiosk: kioskID, language: currentLang},
        success: function (response, textStatus, jqXHR) {

            if (response && response.length > 0) {
                var txt = "";
                for (var i = 0; i < response.length; i++) {
                    var service = response[i];
                    var btn = "<button id='b-" + service.ID + "' onclick='createTicket(this.id);' class='btn  btn-kiosk no-cursor btn-corner btn-corner-leaf' >" + service.Name + "</button>";
//                    var btn = "<button class='btn btn-primary btn-kiosk' data-service='"+service.ID+"' >"+service.Name+"</button>";
//                    var btn = "<button onclick='createEvent("+service.ID+");' class='btn btn-primary btn-kiosk'>"+service.Name+"</button>";
                    txt += btn;
                }
                document.getElementById("btns").innerHTML = txt;

            } else {
                // alert("error! please check connection.");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            //alert('Error - ' + errorThrown);
        }
    });
}

//==============================================================================|| animation

function startAnimationArrow(name) {
    
    $("#service-name").text(name);
//    animateArrow();
//    arrowAnimation = setInterval(function () {
//        animateArrow();
//    }, 1000);
}
function removeDisabled(){
    setTimeout(function (){
        $(".btn").delay(100).removeAttr("disabled");
    }, 3000);
    
}

function stopAnimationArrow() {
//    window.clearInterval(arrowAnimation);
}


function animateArrow() {
    $("#down").delay(100).animate({paddingBottom: "0px"}).delay(100).animate({paddingBottom: "10px"});
}

//==============================================================================|| time
setInterval(function () {
    updateTime();
}, 1000);

//==============================================================================||