<?php

error_reporting(0);
require_once("../../language.php");

global $mysqli;

$fb = array();
for ($i = 0; $i < 5; $i++) {
    $v = intval(getRequestVal('fb' . $i, 0, 'post'));
    $fb[$i] = ($v >= 1 && $v <= 5) ? $v : null;
}

$rated = array_filter($fb, function ($v) {
    return $v !== null;
});

if (count($rated) === 0) {
    echo 0;
    exit;
}

$avg = round(array_sum($rated) / count($rated), 2);
$note = getRequestVal('note', '', 'post');
$lang = getRequestVal('lang', '', 'post');
$lang = in_array($lang, array('ar', 'en'), true) ? $lang : null;

// counter_id > 0  => /beaa/feedback/{counter_id}/ submission (feedback_scope='counter')
// counter_id absent/0 => /beaa/feedback/ submission, unchanged (feedback_scope='global')
$counterId = intval(getRequestVal('counter_id', 0, 'post'));
$scope = 'global';
$counterIdParam = null;
$counterName = null;
$counterNo = null;
$counterZone = null;

if ($counterId > 0) {
    $stmt = $mysqli->prepare(
        "SELECT counter_name, counter_no,
            (SELECT zone_name FROM zones WHERE zone_id = counters.counter_zone) AS zone_name
         FROM counters WHERE counter_id = ?"
    );
    $stmt->bind_param('i', $counterId);
    $stmt->execute();
    // bind_result(), not get_result(): this build's mysqli lacks mysqlnd.
    $stmt->bind_result($foundName, $foundNo, $foundZone);
    $found = $stmt->fetch();
    $stmt->close();

    if (!$found) {
        // Unknown or deleted counter: never silently attach feedback to it.
        http_response_code(404);
        echo 0;
        exit;
    }

    $scope = 'counter';
    $counterIdParam = $counterId;
    $counterName = $foundName;
    $counterNo = (int) $foundNo;
    $counterZone = $foundZone;
}

$stmt = $mysqli->prepare(
    "INSERT INTO feedback
        (feedback_scope, counter_id, counter_name_snapshot, counter_number_snapshot, counter_zone_snapshot,
         feedback_language, fb0, fb1, fb2, fb3, fb4, feedback_score, feedback_note, feedback_date)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
);
$stmt->bind_param(
    'sisissiiiiids',
    $scope, $counterIdParam, $counterName, $counterNo, $counterZone, $lang,
    $fb[0], $fb[1], $fb[2], $fb[3], $fb[4], $avg, $note
);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    setSetting('feedbackUpdated', '1');
    echo 1;
} else {
    echo 0;
}
?>
