<?php
error_reporting(0);
session_start();
require_once("../language.php");

$lang = isset($_GET['language']) ? $_GET['language'] : getSetting('defaultLanguage');
$filesPath = "../files/";
$uploadsPath = "../uploads/";
$_SESSION['lang'] = $lang;

// =================================================================================================| counter scope
// /beaa/feedback/{counter_id}/ passes counter_id via router.php / .htaccess.
// Absent or 0 => the unchanged global feedback page.
$counterId = isset($_GET['counter_id']) ? (int) $_GET['counter_id'] : 0;
if ($counterId <= 0) {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (preg_match('#/feedback/(?:counter_id=)?([0-9]+)/?$#', $requestPath, $counterMatch)) {
        $counterId = (int) $counterMatch[1];
    }
}
$counter = null;
if ($counterId > 0) {
    $counter = getRow("SELECT counter_id, counter_name, counter_no, counter_zone,
                        (SELECT zone_name FROM zones WHERE zone_id = counters.counter_zone) AS zone_name
                        FROM counters WHERE counter_id = $counterId;");
    if (!$counter) {
        http_response_code(404);
        header('Content-Type: text/html; charset=UTF-8');
        $notFoundMsg = ($lang === 'ar') ? 'الطاولة المطلوبة غير موجودة.' : 'The requested counter does not exist.';
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>404</title></head>'
           . '<body style="font-family:sans-serif;text-align:center;padding:60px;color:#555;">'
           . '<h1 style="font-size:48px;margin-bottom:10px;">404</h1><p>' . htmlspecialchars($notFoundMsg) . '</p>'
           . '</body></html>';
        exit;
    }
}

$pageTitle = 'feedback';
$pageHeading = null; // set below, after $feedbackOpinion is loaded

// =================================================================================================

$dir = trim(getTextValue('dir', $lang));
$questionMark = getTextValue("questionMark", $lang);
$ok = getTextValue('ok', $lang);
$cancel = getTextValue('cancel', $lang);
$clear = getTextValue('clear', $lang);
$close = getTextValue('close', $lang);
$back = getTextValue('back', $lang);

$pleaseRateAllQuestions = getTextValue('pleaseRateAllQuestions', $lang);
$feedbackQuestion = getTextValue("feedbackQuestion", $lang);
$thanks = getTextValue("thanks", $lang);
$feedbackOpinion = getTextValue("feedbackOpinion", $lang);
$yourRating = getTextValue("yourRating", $lang);
$happy = getTextValue("happy", $lang);
$unhappy = getTextValue("unhappy", $lang);

if ($counter) {
    $pageTitle = ($lang === 'ar') ? ('تقييم الطاولة ' . $counter['counter_name']) : ($counter['counter_name'] . ' Feedback');
    $pageHeading = $pageTitle;
} else {
    $pageHeading = $feedbackOpinion;
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$feedbackBaseUrl = defined('BASE_URL')
    ? BASE_URL . '/feedback/'
    : $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/feedback/';

if ($dir == 'ltr') {
    $logo = "../files/logos/moenv-logo-en.jpg";
} else {
    $logo = "../files/logos/moenv-logo-ar.jpg";
}

// =================================================================================================| BD data
$keys = array();
for ($i = 0; $i < 5; $i++) {
}

$fbQuestions = getArrayAssoc(
    "SELECT text_key, text_value FROM texts " .
    "WHERE text_key IN ('fb0', 'fb1', 'fb2', 'fb3', 'fb4') " .
    "AND text_language='$lang' ORDER BY text_key;"
);
//var_dump($fbQuestions);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <?php if ($counter) {
        // /beaa/feedback/{counter_id}/ has one extra path segment versus
        // /beaa/feedback/, so the template's unchanged "../css/..." style
        // relative links would otherwise resolve one level too shallow
        // (/beaa/feedback/css/... instead of /beaa/css/...). Pin the base
        // so every relative link resolves exactly as it does on the
        // unmodified global page, without touching a single href/src.
        ?>
    <base href="<?php echo htmlspecialchars($feedbackBaseUrl) ?>">
    <?php } ?>
    <title><?php echo htmlspecialchars($pageTitle) ?></title>
    <link href="../css/paper.bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <?php
    if ($dir == 'rtl') {
        ?>
        <link href="../css/bootstrap-rtl.min.css" rel="stylesheet" type="text/css"/>
        <link href="../css/bootstrap-flipped.min.css" rel="stylesheet" type="text/css"/>
        <?php
    }
    ?>
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="../css/themes/fontawesome-stars-o.css" rel="stylesheet" type="text/css"/>
    <link href="../css/themes/fontawesome-stars.css" rel="stylesheet" type="text/css"/>
    <link href="../css/common.css" rel="stylesheet" type="text/css"/>
    <link href="../css/animate.css" rel="stylesheet" type="text/css"/>
    <link href="../css/feedback.css" rel="stylesheet" type="text/css"/>
    <link rel="shortcut icon" href="../files/shortcut_icons/star.png"/>
</head>
<!--<body class="segoeUILight noselect no-cursor-r">-->
<body class="segoeUILight">
    <div class="kiosk-lang corner-bottom">
        <button onclick="changeLang('en');" class="btn btn-default pad-h-10">English</button>
        <button onclick="changeLang('ar');" class="btn btn-default pad-h-10">عربي</button>
    </div>

    <!--<div id='digital-clock' class="corner-box corner-bottom">
        <span id="time">11:45:23</span>
    </div>-->

    <div class="corner-box-right">
        <h1 class="ribbon-left">
            <strong class="ribbon-left-content pad-20-100"><?php echo htmlspecialchars($pageHeading) ?></strong>
        </h1>
    </div>

    <div class="container-full pad-h-30 pad-v-5 relative">
        <div class="bg-white pad-3 sh-blue s-10 center-block round-10 pad-20">
            <img class="img-responsive" src="<?php echo $logo ?>" alt="">
        </div>

        <?php if ($counter) { ?>
        <div class="text-center pad-v-5 font-md text-gray">
            <?php
            $counterInfoParts = [$counter['counter_name']];
            if ($counter['counter_no'] != $counter['counter_id']) {
                $counterInfoParts[] = '#' . $counter['counter_no'];
            }
            if (!empty($counter['zone_name'])) {
                $counterInfoParts[] = $counter['zone_name'];
            }
            echo htmlspecialchars(implode(' · ', $counterInfoParts));
            ?>
        </div>
        <?php } ?>

        <div id="feedback-main" class="marg-v-50 text-left" style="display: block;">
            <?php
            $index = 0;
            foreach ($fbQuestions as $Row) {
                $q = $Row['text_value'];
                $starId = "stars" . $index;
                $rateId = "s" . ($index + 1);
                $index++;
                ?>
                <div class="row card font-lg pad-10 pad-h-20">
                    <div class="col-md-8">
                        <span class="">
                            <?php echo $q ?>
                        </span>
                    </div>
                    <div class="col-md-3">
                        <select id="<?php echo $starId ?>" class="">
                            <option value="" selected></option>
                            <option data-html="1" value="1">1</option>
                            <option data-html="2" value="2">2</option>
                            <option data-html="3" value="3">3</option>
                            <option data-html="4" value="4">4</option>
                            <option data-html="5" value="5">5</option>
                        </select>
                    </div>
                    <div class="col-md-1 rateval">
                        <span id="<?php echo $rateId ?>" class="" style="display: block;"></span>
                    </div>
                </div>
                <?php
            }
            ?>

            <div class="text-center">
                <a id="show_fb" href="javascript:void(0)" class="btn btn-primary font-lg">Submit</a>
            </div>
        </div>

        <div id="feedback-note" class="font-huge-md" style="display: none;">
            <div class=""><?php echo $thanks ?></div>
        </div>
    </div>

    <div id="feedback-modal" class="modal marg-v-50">
        <div class="modal-center modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-yellow-heavy pad-3">
                    <button type="button" class="close text-white marg3" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body bg-white-gray text-gray">
                    <h4 class="modal-title "><?php echo $yourRating ?></h4>
                    <div id="final-score" class="text-primary font-huge-md">0/5</div>
                    <div id="final-note" class="text-primary font-lg pad-v-20"><?php echo $pleaseRateAllQuestions ?></div>
                    <div class="text-center">
                        <button type="button" onclick="sendFeedback()" class="btn btn-success font-lg marg-h-20 pad-h-20" data-dismiss="modal"><?php echo $ok ?></button>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="clearRating()" class="btn btn-default marg-h-20 pad-h-20" data-dismiss="modal"><?php echo $back ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p class="text-center">
            <span>iDEAL-Q ® Queue Management System</span>
            <span class="ftr-logo-con"><img src="../files/logos/logo-idealchip.png" alt=""></span>
            <span>IdealChip Electronics, Inc. &copy; 1997-<?php echo date('Y') ?></span>
        </p>
    </div>

    <script src="../js/jquery-3.1.1.min.js" type="text/javascript"></script>
    <script src="../js/jquery.barrating.min.js" type="text/javascript"></script>
    <script src="../js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../js/common.js" type="text/javascript"></script>
    <script type="text/javascript">
        var currentLang = "<?php echo $lang ?>";
        var feedbackCounterId = <?php echo $counter ? (int) $counter['counter_id'] : 'null' ?>;
    </script>
    <script src="../js/feedback.js" type="text/javascript"></script>
</body>
</html>
