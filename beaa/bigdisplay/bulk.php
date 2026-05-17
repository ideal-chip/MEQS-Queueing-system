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
        <link rel="stylesheet" type="text/css" href="../css/bd-bulk-slides.css">
        <link rel="shortcut icon" href="icon.png"/>
        <?php
//        if (isset($_GET['id']) && $_GET['id'] > 0) {
//            $displayID = $_GET['id'];
        $goto_place = getValue("SELECT goto FROM bigdisplays WHERE display_id = $displayID;");
        ?>
    </head>
    <body style="direction:<?php echo getTextValue('dir', $lang); ?>;">
        <div class="row hd">
            <div class="logoContainer">
                <img src="<?php echo $filesPath ?>/logos/systemlogo.png" class="logo" alt="">
            </div>
        </div>
        <div class="row bdy bdy-black">
            <!--<div class="row">-->
                <div class="row bulk-hd">
                    <div class="col-lg-2">
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
                    </div>
                    <div class="col-lg-8 ">
                        <div class="bulk-msg">
                            <p><?php echo getTextValue('goToHall', $lang); ?></p>
                            <p id="status"></p>
                        </div>
                        <div class="bulk-place">
                            <p><?php echo $goto_place; ?></p>
                        </div>
                    </div>
                    <div class="col-lg-2">
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
                    </div>
                </div>
            <!--</div>-->
            <div class="">
                <div class="col-lg-12">
                    <div class="tickets" id="ticks"> </div>
                </div>
            </div>
        </div>
        <div class="row ftr">
            <div class="logo-sml <?php echo ($lang == 'ar' ? 'p-left' : 'p-right') ?>">
                <!--<img src="../files/unhcr logo-sm.png" alt=""/>-->
                <!--<img src="../files/systemlogo.png" alt=""/>-->
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
            <div class="row ticket-sm">
                <label class="event" >--ticket--</label>
            </div>
        </div>
        <?php
        if (isset($_GET['ad']) && $_GET['ad'] > 0) {
            $audioID = filter_input(INPUT_GET, 'ad');
            if ($row = getRow("SELECT * FROM audios WHERE audio_id=$audioID limit 1;")) {
                $audioPath = $row['audio_path'];
                $audioLanguage = $row['audio_language'];
            }
        }
        ?>
        <audio id="ad" style="display:none;" onended='playNext();' preload="auto" ></audio>
        <script type="text/javascript">

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| vars
            var displayID = <?php echo $displayID ?>;
            var maxTransactions =<?php echo getSetting('maxTransactions'); ?>;
            var delayMinute = "<?php echo getSetting('bulkDelay'); ?>";
            var checkRate = 1000 * (delayMinute === 'zero' ? 1 : 60 * parseInt(delayMinute));

            //alert(checkRate);

            var playList = [];
            var playPointer = 0;
            var _audioElement;
            var isBulkCalled = 0;

            var refreshPageXML = new XMLHttpRequest();
            var lastTransactionsXML = new XMLHttpRequest();
            var checkAudioXML = new XMLHttpRequest();
            var bulkStatusXML = new XMLHttpRequest();

            var lastInterval;
            var audioInterval;
            var isSetInterval = 0;
            var isClearInterval = 0;


            setInterval(function () {
                checkStatus();
            }, checkRate);
            setInterval(function () {
                refreshPage();
            }, 1000);
//            setInterval(function () {
//                getLastTransactions(displayID);
//            }, 1000);
//            setInterval(function () {
//                checkAudios(displayID);
//            }, 1000);

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| check bulk status
            function checkStatus() {
                bulkStatusXML.open('GET', '../api/checkupdate.php?id=1&type=bulkstatus');
                bulkStatusXML.send();
            }

            bulkStatusXML.onreadystatechange = function () {
                if (bulkStatusXML.readyState == 4 && bulkStatusXML.status == 200) {
                    val = parseInt(bulkStatusXML.responseText);
                    pStat = document.getElementById('status');
                    //alert(val);
                    if (val === 1) {
                        if (!isSetInterval) {
                            lastInterval = setInterval(function () {
                                getLastTransactions(displayID);
                            }, 1000);
                            audioInterval = setInterval(function () {
                                checkAudios(displayID);
                            }, 1000);
                            isSetInterval = 1;
                            isClearInterval = 0;

                            //pStat.innerHTML = 'active';
                        }
                        //pStat.innerHTML = 'active-done';
//                        
//                        if (!shown) {
//                            alert('active');
//                            shown = 1;
//                        }

                    } else {
                        if (!isClearInterval) {
                            clearInterval(lastInterval);
                            clearInterval(audioInterval);
                            isSetInterval = 0;
                            isClearInterval = 1;
                            //pStat.innerHTML = 'NOT-active';
                        }
                        //pStat.innerHTML = 'NOT-active-Done';
//                        location.reload();
//                        if (!shown) {
//                            alert('not active');
//                            shown = 1;
//                        }

                        var tickets = document.getElementById('ticks');
                        tickets.innerHTML = '';
                    }

                }
            };
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| refresh page
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

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| last Transactions

            function getLastTransactions(id) {
                lastTransactionsXML.open("GET", "../api/bigdisplay/bulk.php?id=" + id + "&type=tickets", true);
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
                            //alert(ticket);
                            if (retJSON[i]) {
                                var ticket_no = retJSON[i];

                                ticket = ticket.replace("--ticket--", ticket_no);

                                tickets.innerHTML = tickets.innerHTML + ticket;
//                                preparePlaylist("<?php echo $audioPath; ?>", retJSON[i].Ticket);
                            }
                        }
//                        if (isBulkCalled == 0) {
//                            isBulkCalled = 1;
//                            playAudio();
//                        }


                    } else {
                        tickets.innerHTML = '';
                    }
                }
            };

            //getLastTransactions(<?php echo $displayID; ?>);

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| Voice


            checkAudioXML.onreadystatechange = function () {
                if (checkAudioXML.status == 200 && checkAudioXML.readyState == 4)
                {
                    var retJSON = JSON.parse(checkAudioXML.responseText);
                    if (retJSON && retJSON.length)
                    {
                        for (var i = 0; i < retJSON.length; i++) {
                            //alert(retJSON[i]);
                            preparePlaylist("<?php echo $audioPath; ?>", retJSON[i]);
                        }
                        //playAll();
                    }
                }
            };

            function checkAudios(displayID) {
                //alert(displayID);
                checkAudioXML.open("GET", "../api/bigdisplay/bulk.php?id=" + displayID + "&type=audio&audioid=<?php echo $audioID; ?>", true);
                checkAudioXML.send();
            }
            function pad(num, size) {
                var s = num + "";
                while (s.length < size)
                    s = "0" + s;
                return s;
            }

//            function playAudio() {
//                while (!playPointer) {
//                    //alert(!playPointer + " : " + playPointer);
//                    playNext();
//                }
//
//            }

            //function preparePlaylist(audioPath, language, service, number, counter) {
            function preparePlaylist(audioPath, ticket) {

                var language = 'ar';
                var service = ticket.substr(0, 1);
                var number = ticket.substr(1, ticket.length);
                number = parseInt(number);
                //alert('s: ' + service + ' n: ' + number);

                var d1, d2;
                var ext = "ogg";
                d2 = (Math.floor(number / 100)) * 100;
                d1 = number % 100;
                _audioElement = document.getElementById('ad');
                playList.push(audioPath + "/" + "att." + ext);
                playList.push(audioPath + "/" + language + "/num." + ext);
                if (service.length == 1)
                {
                    playList.push(audioPath + "/" + "characters/" + service.toLowerCase() + "." + ext);
                } else
                {
                    srv = service.toLowerCase();
                    for (var c = 0; c < srv.length; c++)
                    {
                        playList.push(audioPath + "/" + "characters/" + srv.substr(c, c + 1) + "." + ext);
                    }
                }
                if (d2)
                {
                    playList.push(audioPath + "/" + language + "/" + d2 + "." + ext);
                    if (d1 != 0)
                        playList.push(audioPath + "/" + language + "/and." + ext);
                }
                if (d1 != 0)
                {
                    playList.push(audioPath + "/" + language + "/" + pad(d1, 3) + "." + ext);
                }

                while (!playPointer) {
                    //alert(!playPointer + " : " + playPointer);
                    playNext();
                }

            }

            function playNext() {  //Added on onended events for audio element
                if (playPointer < playList.length)
                {
                    _audioElement.src = playList[playPointer];
                    // alert(playList[playPointer]);
                    playPointer += 1;
                    _audioElement.load();
                    _audioElement.play();
                } else
                {
                    playList = [];
                    playPointer = 0;
                }
            }

        </script>
    </body>

</html>
