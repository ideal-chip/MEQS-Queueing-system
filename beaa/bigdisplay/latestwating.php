<?php
if (!isset($displayID)) {
    exit(1);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $big_displayNum ?> - waiting idealQ&reg; <?php echo getTextValue('state', $lang); ?></title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" type="text/css" href="../css/bigdisplay.css">
        <link rel="shortcut icon" href="icon.png"/>

    </head>
    <body style="direction:<?php echo getTextValue('dir', $lang); ?>;">
        <div class="row hd">
            <div class="logoContainer">
                <img src="<?php echo $filesPath ?>/logos/systemlogo.png" class="logo" alt="">
            </div>
        </div>
        <div class="row bdy">
            <?php
            if (isset($_GET['ad']) && $_GET['ad'] > 0) {
                $audioID = filter_input(INPUT_GET, 'ad');
                $bdID = $displayID;
                //require_once("../api/db.php");
                require_once("../api/audio/player-mf.php");
            }
            ?>
            <div class="col-lg-2">
                <div class="cool2">
                    <?php
                    if ($arrowDir > 0) {
                        $arrowStyle = strtolower(getArrowDirection($arrowDir));
                        ?> 
                        <div class="b-arrow">
                            <i class="fa fa-arrow-<?php echo $arrowStyle ?>"></i><i class="fa fa-arrow-<?php echo $arrowStyle ?> text-info"></i><i class="fa fa-arrow-<?php echo $arrowStyle ?> text-danger"></i>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="clock-parent2">
                        <div id="myclock2" class="clock2">
                            <div id="curDate2">
                                <div id="day"></div>
                                <div id="month"></div>
                                <div id="year"></div>
                            </div>
                        </div> 
                    </div>

                    <div class="cm-logo">
                        <img src="../files/logos/unhcr-logo.jpg" alt=""/>
                        <!--<img src="../files/systemlogo.png" alt=""/>-->
                        <!--<img src="../files/logo-a7wal-md.png" alt=""/>-->
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="e-tick-con marg-bottom-10 card-sh no-pad alert-info no-radius text-larg" >
                    <?php echo getTextValue('lastCalled', $lang); ?>
                </div>
                <div class="" id="ticks"> </div>
            </div>
            <div class="col-lg-7 ">
                <div class="e-tick-con marg-bottom-10 card-sh no-pad alert-info no-radius text-larg" >
                    <?php echo getTextValue('eventsWaiting', $lang); ?>
                </div>
                <div class="tick-con">
                    <div class="" id="w-ticks"> </div>
                </div>
            </div>
        </div>
        <div class="row ftr">
            <div class="logo-sml <?php echo ($lang == 'ar' ? 'p-left' : 'p-right') ?>">
                <!--<img src="../files/systemlogo.png" alt=""/>-->
                <!--<img src="../files/logo-a7wal-md.png" alt=""/>-->
                <img src="../files/logos/unhcr-logo.jpg" alt=""/>
            </div>
            <div class="marquee oldMarquee">
                <p>
                    <img class="imgMar" src='<?php echo $uploadsPath . "star.png" ?>'   alt='|'>
                    <?php
                    for ($i = 0; $i < getSetting("bigdisplayMessageCount"); $i++) {
                        echo getTextValue("message" . $i, $lang)
                        ?>
                        <img class="imgMar" src='<?php echo $uploadsPath . "star.png" ?>' alt='|'>
                        <?php
                    }
                    ?> 
                </p>

            </div>
        </div>

        <div  id="sc">
            <div class="row">
                <div class="col-sm-7 text-left">
                    <label class="e-tick tt" >eventnoTXT</label>
                </div>
                <div class="col-sm-5 ">
                    <div class="e-c-num">
                        <label class="counter-tx" ><?php echo getTextValue('counter', $lang); ?></label>
                        <label class="counter" >counterTXT</label>
                    </div>
                </div>
            </div>
        </div>
        <div  id="xx" class="hidden">
            <div class="row ticket">
                <div class="col-sm-12">
                    <label class="event" >{{TICKET}}</label>
                </div>
            </div>
        </div>
        <script src="../js/jquery-3.1.1.min.js" type="text/javascript"></script>
        <script src="../js/clock-1.1.0-mon-mod.min.js" type="text/javascript"></script>
        <script type="text/javascript">

var displayID = <?php echo $displayID; ?>;
var maxTransactions =<?php echo getSetting('maxTransactions'); ?>;

var refreshPageXML = new XMLHttpRequest();

var refreshRate = 2000;

$(document).ready(function () {

    lastWaiting(displayID);
    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| date
    var d = new Date();
    setDate();
    function setDate() {
        d.setHours(<?php echo date("H"); ?>);
        d.setMinutes(<?php echo date("i"); ?>);
        d.setSeconds(<?php echo date("s"); ?>);

        var day = <?php echo date("d") ?>;
        var month = <?php echo date("m") ?>;
        var year = <?php echo date("Y") ?>;
        document.getElementById('day').innerHTML = day > 9 ? day : "0" + day;
        document.getElementById('month').innerHTML = month > 9 ? month : "0" + month;
        document.getElementById('year').innerHTML = year;
    }
    //alert(d);

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| new clock [clock 1.1.0]
    var clock1 = $("#myclock2").clock({
        width: 190,
        height: 250,
        theme: 't2',
        date: d
    });
});
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| refresh page


//var co = 0;

setInterval(function () {
    refreshPage();
}, 1000);
function refreshPage() {
    refreshPageXML.open('GET', '../api/checkupdate.php?type=bigdisplay&id=' + displayID);
    refreshPageXML.send();
}

refreshPageXML.onreadystatechange = function () {
    if (refreshPageXML.readyState == 4 && refreshPageXML.status == 200) {
        var status = parseInt(refreshPageXML.responseText);
        if (status == 1) {
            location.reload();
        }
    }
};

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| Latst waiting
setInterval(function () {
    lastWaiting(displayID);
}, refreshRate);

function lastWaiting(id) {

    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/bigdisplay/latestwating.php',
        data: {id: id, max: maxTransactions},
        success: function (response, textStatus, jqXHR) {

            if (response) {
//                            alert(response[0].length);
//                            alert(response[0].length);
                var tickets = document.getElementById('ticks');
                var w_tickets = document.getElementById('w-ticks');
                var lastCalled = response[0];
                var lastWaiting = response[1];
                if (lastCalled.length > 0)
                {
//                    var tickets = document.getElementById('ticks');
                    tickets.innerHTML = '';
                    for (var i = 0; i < lastCalled.length; i++) {
                        var ticket = document.getElementById('sc').innerHTML;
                        if (lastCalled[i]) {
                            var ev = lastCalled[i].ticket;
                            var c = lastCalled[i].counter;
                            var pr = lastCalled[i].priority;

                            ticket = ticket.replace("eventnoTXT", ev);
                            ticket = ticket.replace("counterTXT", c);
                            if (pr > 0) {
                                ticket = ticket.replace("tt", "tt-red");
                            }
                            tickets.innerHTML = tickets.innerHTML + ticket;

                        }
                    }

                } else {
//                    var tickets = document.getElementById('ticks');
                    tickets.innerHTML = '';
                }
                if (lastWaiting.length)
                {

                    w_tickets.innerHTML = '';
                    for (var i = 0; i < lastWaiting.length; i++) {
                        var ticket = document.getElementById('xx').innerHTML;
                        if (lastWaiting[i]) {
                            var tik = lastWaiting[i].ticket;
                            var pr = lastWaiting[i].priority;

                            ticket = ticket.replace("{{TICKET}}", tik);
                            if (pr > 0) {
                                ticket = ticket.replace("ticket", "ticket tick-red");
                            }

                            w_tickets.innerHTML = w_tickets.innerHTML + ticket;
                        }
                    }

                } else {
//                    var w_tickets = document.getElementById('w-ticks');
                    w_tickets.innerHTML = '';
                }



            } else {
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}


        </script>

    </body>

</html>
