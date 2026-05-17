<?php
if (!isset($counterID)) {
    header("Location: ./");
    exit(1);
}
//========================================================================================| Session Data

$clerkID = $_SESSION['clerkID'];

//========================================================================================| DB data

$clerk = getRow("SELECT * FROM clerks WHERE clerk_id=$clerkID;");
$counterProps = getRow("SELECT can_pick_tickets, direct_transfer_category FROM counters WHERE counter_id=$counterID;");
$languages = getColumn("SELECT DISTINCT text_language FROM texts;");
$counterCategories = getArray("SELECT cc_id, text_value as 'name', cc_enabled FROM 
        categories,countercategories,texts where cc_counter=$counterID 
        AND cc_category=category_id AND category_key=text_key AND text_language='$lang';");
$otherCounters = getArrayAssoc("SELECT counter_id, counter_name FROM counters WHERE counter_id <> $counterID;");
$categories = getArrayAssoc("SELECT category_id, text_value AS 'catName' FROM categories, texts WHERE category_key=text_key AND text_language='$lang' AND category_zone=$zoneID;");

//========================================================================================| Vars

if ($clerk) {
    $clerkName = $clerk['clerk_name'];
    $clerkFullName = $clerk['clerk_fullname'];
}
if ($counterProps) {
    $isPicker = $counterProps['can_pick_tickets'];
    $directTransferCat = $counterProps['direct_transfer_category'];
}
if ($directTransferCat) {
    $directCatName = getTextValue(getValue("SELECT category_key FROM categories WHERE category_id=$directTransferCat;"), $lang);
}

//========================================================================================| settings
$counterSwitchServices = getSetting("counterSwitchServices") == '1' ? TRUE : FALSE;

//========================================================================================| langs
$title = getTextValue('interface', $lang);
$dir = trim(getTextValue('dir', $lang));

$open = getTextValue('open', $lang);
$closeCounter = getTextValue('closeCounter', $lang);
$close = getTextValue('close', $lang);
$logout = getTextValue('logout', $lang);
$empty = getTextValue('empty', $lang);
$hello = getTextValue('hello', $lang);
$counter = getTextValue('counter', $lang);
$pendingList = getTextValue('pendingList', $lang);
$servedCategory = getTextValue('servedCategory', $lang);
$textAlign = getTextValue("textAlign", $lang);
$call = getTextValue('call', $lang);
$autocall = getTextValue('autocall', $lang);
$recall = getTextValue('recall', $lang);
$addPending = getTextValue('addPending', $lang);
$transfer = getTextValue('transfer', $lang);
$opened = getTextValue('opened', $lang);
$directTransfer = getTextValue('directTransfer', $lang);
$eventsWaiting = getTextValue('eventsWaiting', $lang);
$counterLoad = getTextValue('counterLoad', $lang);
$lastCalled = getTextValue('lastCalled', $lang);
$transferClient = getTextValue('transferClient', $lang);
$distCounter = getTextValue('distCounter', $lang);
$distCategory = getTextValue('distCategory', $lang);
$ok = getTextValue('ok', $lang);
$cancel = getTextValue('cancel', $lang);
$clerkNameLang = getTextValue('clerkName', $lang);
$serialNo = getTextValue('serialNo', $lang);
$phoneNumber = getTextValue('phoneNumber', $lang);
$dateTime = getTextValue('dateTime', $lang);
$clientName = getTextValue('clientName', $lang);
$subService = getTextValue('subService', $lang);
$mainService = getTextValue('mainService', $lang);
$requiredPapers = getTextValue('requiredPapers', $lang);
$followupCard = getTextValue('followupCard', $lang);
$followupCards = getTextValue('followupCards', $lang);
$issueFollowupCard = getTextValue('issueFollowupCard', $lang);
$estimatdWaitTime = getTextValue("waitTime", $lang);
$submit = getTextValue('submit', $lang);
$print = getTextValue('print', $lang);
$edit = getTextValue('edit', $lang);
$update = getTextValue("update", $lang);
$clear = getTextValue('clear', $lang);
$delete = getTextValue('delete', $lang);
$firstPage = getTextValue('firstPage', $lang);
$lastPage = getTextValue('lastPage', $lang);
$deleteQuestion = getTextValue("deleteQuestion", $lang);
$questionMark = getTextValue("questionMark", $lang);

$issue = getTextValue('issue', $lang);
$directorate = getTextValue('directorate', $lang);
$serviceType = getTextValue('serviceType', $lang);
$followupFootnote1 = getTextValue('followupFootnote1', $lang);
$followupFootnote2 = getTextValue('followupFootnote2', $lang);
$followupFootnote3 = getTextValue('followupFootnote3', $lang);
$followupFootnote4 = getTextValue('followupFootnote4', $lang);

$followupFootnote5 = getTextValue('followupFootnote5', $lang);
$subFone = getTextValue('subFone', $lang);

$morepapers = getTextValue('morepapers', $lang);

if ($dir == 'ltr') {
    $logo = "../files/logos/ideal-q.png";
} else {
    $logo = "../files/logos/ideal-q.png";
}

//-----------------------------------------------------< uploaded files >-----
$acceptedFileTypes = "application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document";
$filesPath = "../files/";
$uploadsPath = "../uploads/pdf/";
$listtype = array(
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'pdf' => 'application/pdf');
//-----------------------------< functions >---

function human_filesize($bytes, $decimals = 2) {
    $size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

function checkType($file, $types) {
    
    $type = strtolower(pathinfo($file)['extension']);
    return array_key_exists($type, $types);
}

//-----------------------------< data >---
$items = scandir($uploadsPath);
$all_files = array();
$images = array();
$otherFiles = array();

foreach ($items as $item) {
    $full_path = $uploadsPath . $item;
    if (is_file($full_path)) {
        
        if (checkType($full_path, $listtype)) {
            
//            $item_name = str_replace('.pdf', '', $item);
            array_push($all_files, $item);
        }
    }
}

foreach ($all_files as $value) {
    if ($value == 'bigbg.jpg' || $value == 'head.png' || $value == 'logo.png' || $value == 'star.png') {
        array_push($images, $value);
    } else {
        array_push($otherFiles, $value);
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $counterNo ?> - iDEAL-Q&reg; <?php echo $title; ?></title>
        <!--<link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css"/>-->
        <link href="../css/paper.bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <?php
        if ($dir == 'rtl') {
            ?> 
            <link href="../css/bootstrap-rtl.min.css" rel="stylesheet" type="text/css"/>
            <?php
        }
        ?>
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="../css/common.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" type="text/css" href="../css/counter.css">
        <link rel="shortcut icon" href="../files/shortcut_icons/counter.png"/>
    </head>
    <body class="bdy-main" style="direction:<?php echo $dir; ?>" oncontextmenu='return true;'>
        <div class="row hd">
            <div class="pull-right space-200">
                <div class="logoContainer pad-h-10 h-full">
                    <a href="http://www.idealchip.com" target="_blank" class="btn btn-link no-radius block no-pad "><img src="<?php echo $filesPath ?>/logos/systemlogo-md.png" alt="logo"></a>
                </div> 
            </div>
            <div class="lang-con s-10 relative bg-white-gray">
                <span class="inline-block"><i class="fa-box fa fa-globe"></i></span>
                <span class="inline-block s-80">
                    <select id="lang" onchange='changeLang()' class="form-control no-pad-up text-primary s-100">
                        <?php
                        foreach ($languages as $language) {
                            ?>
                            <option value="<?php echo $language ?>"  <?php echo ($language == $lang ? "selected" : "") ?> >
                                <?php echo getTextValue('languageName', $language) ?>  
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </span>
            </div>
            <div class="dropdown line no-pad pad-h-3">
                <button class="btn btn-default dropdown-toggle no-radius pad-5-10" type="button" data-toggle="dropdown">
                    <span class="glyphicon glyphicon-cog"></span>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><button id='open' class='btn btn-warning btn-full btn-sm' onclick='openCounter();' disabled><?php echo $open; ?>  </button></li>
                    <li><button id='close' class='btn btn-warning btn-full btn-sm' onclick='closeCounter();'><?php echo $closeCounter; ?> </button></li>
                    <li><button id='logout' class='btn btn-danger btn-full btn-sm' onclick='logout();'><?php echo $logout; ?> </button></li>
                </ul>
            </div>
            <div class="dropdown  line no-pad">
                <button class="btn btn-primary dropdown-toggle no-radius pad-5" type="button" data-toggle="dropdown"> <?php echo getTextValue('latest', $lang) ?> <span id="latest-size" class=' badge'>0</span>
                    <span class="caret"></span>
                </button>
                <ul id="called-list" class="dropdown-menu" >
                    <li class="empty">
                        <span class='pad-5 text-danger'><?php echo $empty; ?></span>
                    </li>
                </ul>
            </div>
            <div class="line-h-pad pull-right">
                <label class="badge-red bg-blue-light"><?php echo $hello . " : " . $clerkName ?></label>
                <label class="badge-red bg-blue-light"> <?php echo $counter . ": " . $counterNo; ?></label>
            </div>
        </div>
        <div class="bdy">
            <div class="row no-marg"> 
                <div class="col-lg-2">
                    <div class="card">
                        <div class="card-bdy marg-v-10">
                            <button type="button" onclick="showBooking();" class="btn btn-sm text-primary text-wrap s-80">
                                <i class="fa-box fa fa-book"></i> <?php echo $followupCard ?>
                            </button>
                        </div>
                    </div>
                    <div class="pend-con">
                        <div class="pending-hd"><?php echo $pendingList ?> <span id="pend-count" class="badge bg-blue marg3"></span></div>
                        <ul id="pending-list" class="">

                        </ul>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="row marg-v-10">
                        <div class=" center-block round-10 pad-5 s-35">
                            <img class="img-responsive" src="<?php echo $logo ?>" alt=""/>
                        </div>
                    </div>
                    <div class="row marg-v-20 pad-20  border-top">
                        <div class="col-md-3">
                            <div class="">
                                <button id='call' class='btn btn-success btn-xs pad-5 relative btn-counter' onclick='call();'><?php echo $call ?>  <span id="call-timer" class="badge">0</span></button>
                                <label style="color: gray !important;" class="hidden">
                                    <input id='autocall' type='checkbox' checked disabled="disabled" >   <?php echo $autocall ?></label>
                                <br>
                                <button id='recall' class='btn btn-success btn-xs pad-5 relative btn-counter' onclick='recall();'><?php echo $recall ?> <span id="recall-timer" class="badge">0</span> </button><br>
                                <button id='pending' class='btn btn-info btn-xs pad-5 btn-counter' onclick='addPending();'><?php echo $addPending ?>  </button><br>
                                <button id='transfer' class='btn btn-warning btn-xs pad-5 btn-counter' onclick='showTransferDialog();'><?php echo $transfer ?> </button><br>
                                <?php
                                if ($directTransferCat) {
                                    ?>
                                    <button id='direct-transfer' class='btn btn-warning btn-xs pad-5 btn-counter' onclick='transfer(<?php echo $directTransferCat; ?>);'><?php echo getTextValue('directTransfer', $lang) ?>  <span class="glyphicon glyphicon-transfer font-md"></span>  </button><br>
                                    <?php
                                }
                                ?> 
                            </div>
                        </div>
                        <div class="col-md-4 relative">
                            <div class="shader" style="display: none;"></div>
                            <table class=" table-bordered-bottom  table-100 table-close" border='0' id='eventItems'></table>
                        </div>
                        <div class="col-md-5 ">
                            <div class="pad-10 relative">
                                <div  id='eventno' class="pad-v-20">
                                    <?php echo $opened ?>
                                </div>
                                <div id='eventdate' class="corner-box-full"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cat-con-sm bg-red"><?php echo $servedCategory ?></div>
                        <div class="cat-con-sm arc-top-8">
                            <?php
                            foreach ($counterCategories as $cat) {
                                $cc_id = $cat['cc_id'];
                                $name = $cat['name'];
                                $enabled = $cat['cc_enabled'];
                                $pressed = ($enabled == 1 ? "pressed" : "");

                                if ($counterSwitchServices) {
                                    ?>
                                    <div class="inline-block marg83"><button id="cc<?php echo $cc_id; ?>" class="btn btn-xs cat-radio curser <?php echo $pressed; ?>"><?php echo $name; ?></button> </div>
                                    <?php
                                } else {
                                    ?>
                                    <div class="inline-block marg83"><button  class="btn btn-xs cat-radio pressed"><?php echo $name; ?></button> </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="cards-con">
                        <?php
                        if ($directTransferCat) {

                            if (strlen($directCatName) > 40) {
                                $directCatName = substr($directCatName, 0, 40) . "...";
                            }
                            ?>
                            <div class="panel panel-danger panel-collapse">
                                <div class="panel-heading pad-5"><?php echo $directTransfer ?></div>
                                <div class="panel-body text-center font-small card-bdy"><?php echo $directCatName; ?></div>
                            </div> 
                            <?php
                        }
                        ?>
                        <div class="panel panel-info panel-collapse">
                            <div class="panel-heading pad-5"><?php echo $eventsWaiting ?></div>
                            <div id='waiting' class="panel-body text-center font-lg card-bdy">...</div>
                        </div> 
                        <div class="panel panel-warning panel-collapse">
                            <div class="panel-heading pad-5"><?php echo $counterLoad ?></div>
                            <div id="c-load" class="panel-body text-center font-lg card-bdy">...</div>
                        </div>
                        <div class="panel panel-success panel-collapse">
                            <div class="panel-heading pad-5"><?php echo $lastCalled ?></div>
                            <div id="last-called" class="panel-body text-center font-lg card-bdy">...</div>
                            <div id="last-call-type" class="panel-body text-center font-lg card-bdy small">
                                <span class="badged" data-toggle="tooltip" title="<?php echo $call ?>">C</span>
                                <span class="badged" data-toggle="tooltip" title="<?php echo $recall ?>">R</span>
                                <span class="badged" data-toggle="tooltip" title="<?php echo $addPending ?>">P</span>
                                <span class="badged" data-toggle="tooltip" title="<?php echo $transfer ?>">T</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php require_once './_transferModal.php'; ?>
        <?php require_once './_bookingModal.php'; ?>

        <div class="footer bg-white-gray pad-v-5">
            <p class="text-center blue"> iDEAL-Q ® Queue Management System  <span class="ftr-logo-con"><a href="http:\\www.idealchip.com" title="idealchip website" target="_blank"><img src="../files/logos/logo-ideal.ico" alt=""></a></span> IdealChip Electronics, Inc.  &copy; 1997-<?php echo date("Y") ?></p>
        </div>

        <ul id="pend-element-hid" class="hidden">
            <li class="pending-item">
                <a class="p-remove inline-block" onclick="removePending('--event--');" href="javascript:void(0);">
                    <span class="glyphicon glyphicon-share-alt"></span>
                </a>
                <span class="p-line inline-block"></span>
                <span class="p-tick inline-block pad-5-10">--ticket--</span>
            </li>
        </ul>
        <div class="hidden">
            <p id="hid-print-header" class='text-center small navbar-fixed-top '>
                <span class="pull-left">iDEAL-Q: <?php echo date('Y'); ?></span>
            </p>
            <p id="hid-print-footer" class='text-center small navbar-fixed-bottom'><hr> ® iDEALChip Electronics, Inc. © 1997 - <?php echo date('Y'); ?></p>
        </div>
        <script src="../js/jquery-3.1.1.min.js" type="text/javascript"></script>
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <script src="../js/jQuery.print.min.js" type="text/javascript"></script>
        <script src="../js/common.js" type="text/javascript"></script>
        <script type="text/javascript">
                    var counterID =<?php echo $counterID; ?>;
                    var clerkID =<?php echo $clerkID; ?>;
                    var val_maxCount = <?php echo getSetting('maxCount') ?>;
                    var isPicker = <?php echo $isPicker; ?>;
                    var interface_lang = '<?php echo $lang; ?>';

                    var langPathReplace = "<?php echo "?id=" . $counterID . "&language=" ?>";
                    var path_audio_notification = '<?php echo $filesPath ?>notification.mp3';
                    var path_img_transferred = '<?php echo $filesPath ?>transferred.png';
                    var call_delay_def = parseInt('<?php echo getSetting('counter_callDelaySeconds') ?>');
                    var recall_times_def = parseInt('<?php echo getSetting('counter_recallTimes') ?>');

                    var alert_errorTransfer = "<?php echo getTextValue('errorTransfer', $lang); ?>";
                    var alert_pleaseCall = "<?php echo getTextValue('pleaseCall', $lang); ?>";
                    var alert_noClients = "<?php echo getTextValue('noClients', $lang); ?>";
                    var alert_errorInOperation = "<?php echo getTextValue('errorInOperation', $lang); ?>";
                    var alert_errorClose = "<?php echo getTextValue('errorClose', $lang); ?>";
                    var alert_errorLogout = "<?php echo getTextValue('errorLogout', $lang); ?>";

                    var lang_enterTime = "<?php echo getTextValue('enterTime', $lang); ?> ";
                    var lang_eventPriority = '<?php echo getTextValue('eventPriority', $lang); ?>';
                    var lang_eventNo = '<?php echo getTextValue('eventNo', $lang); ?>';
                    var lang_eventsWaiting = '<?php echo $eventsWaiting; ?>';
                    var lang_closed = "<?php echo getTextValue('closed', $lang); ?>";
                    var lang_opened = "<?php echo $opened; ?>";
                    var lang_empty = "<?php echo $empty ?>";
                    var lang_active = '<?php echo getTextValue('active', $lang) ?>';
                    var lang_inactive = '<?php echo getTextValue('inactive', $lang) ?>';
                    var lang_recall = '<?php echo $recall ?>';

                    var lang_clientName = '<?php echo $clientName ?>';
                    var lang_phoneNumber = '<?php echo $phoneNumber ?>';
                    var lang_directorate = '<?php echo $directorate ?>';
                    var lang_serviceType = '<?php echo $serviceType ?>';
                    var lang_dateTime = '<?php echo $dateTime ?>';
                    var lang_serialNo = '<?php echo $serialNo ?>';
                    var lang_clerkName = '<?php echo $clerkNameLang ?>';
                    var lang_edit = '<?php echo $edit ?>';
                    var lang_delete = '<?php echo $delete ?>';
                    var lang_firstPage = '<?php echo $firstPage ?>';
                    var lang_lastPage = '<?php echo $lastPage ?>';
                    var lang_deleteQuestion = '<?php echo $deleteQuestion . $questionMark ?>';

                    var msg_logoutMessage = "<?php echo getTextValue('logoutMessage', $lang); ?>";
        </script>
        <script src="../js/counter.js" type="text/javascript"></script>
        <script src="../js/followup.js" type="text/javascript"></script>
    </body>
</html> 


