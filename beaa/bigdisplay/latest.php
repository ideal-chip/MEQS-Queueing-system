<?php
if (!isset($displayID)) {
    exit(1);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $big_displayNum ?> - idealQ&reg; <?php echo getTextValue('state', $lang); ?></title>
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
            <div class="col-lg-10">
                <div class="">
                    <div class="tickets" id="ticks"> </div>
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
            <div class="row ticket">
                <div class="col-sm-9">
                    <label class="event" >eventnoTXT</label>
                </div>
                <div class="col-sm-3 c-num-con">
                    <div class="c-num">
                        <label class="counter-tx" ><?php echo getTextValue('counter', $lang); ?></label>
                        <label class="counter" >counterTXT</label>
                    </div>
                </div>
            </div>
        </div>
        <script src="../js/jquery-3.1.1.min.js" type="text/javascript"></script>
        <script src="../js/clock-1.1.0-mon-mod.min.js" type="text/javascript"></script>
        <script type="text/javascript">

            $(document).ready(function () {

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
                    width: 180,
                    height: 250,
                    theme: 't2',
                    date: d
                });
            });
            //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| refresh page

            var refreshPageXML = new XMLHttpRequest();
            //var co = 0;

            setInterval(function () {
                refreshPage();
            }, 1000);
            function refreshPage() {
                refreshPageXML.open('GET', '../api/checkupdate.php?type=bigdisplay&id=' + <?php echo $displayID; ?>);
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
            //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| last Transactions
            var maxTransactions =<?php echo getSetting('maxTransactions'); ?>;
            var lastTransactionsXML = new XMLHttpRequest();
//            var numberOfTickets = 0;

            setInterval(function () {
                getLastTransactions(<?php echo $displayID; ?>)
            }, 3500);
            function getLastTransactions(id) {
                lastTransactionsXML.open("GET", "../api/bigdisplay/latest.php?id=" + id + "&max=" + maxTransactions, true);
                lastTransactionsXML.send();
            }

            lastTransactionsXML.onreadystatechange = function () {
                if (lastTransactionsXML.status == 200 && lastTransactionsXML.readyState == 4)
                {
                    var tickets = document.getElementById('ticks');
                    var retJSON = JSON.parse(lastTransactionsXML.responseText);
                    if (retJSON && retJSON.length)
                    {


                        tickets.innerHTML = '';
                        for (var i = 0; i < retJSON.length; i++) {
                            var ticket = document.getElementById('sc').innerHTML;

                            if (retJSON[i]) {
                                var ev = retJSON[i].ticket;
                                var c = retJSON[i].counter;
                                var pr = retJSON[i].priority;

                                ticket = ticket.replace("eventnoTXT", ev);
                                ticket = ticket.replace("counterTXT", c);
                                if (pr > 0) {
                                    ticket = ticket.replace("ticket", "ticket tick-red");
                                }


                                tickets.innerHTML = tickets.innerHTML + ticket;

                            }
                        }

                    } else {
                        tickets.innerHTML = '';
                    }
                }
            };



        </script>

    </body>

</html>
