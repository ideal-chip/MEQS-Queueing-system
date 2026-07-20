<?php

error_reporting(0);
require_once("../../language.php");

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

$avg = array_sum($rated) / count($rated);
$note = getRequestVal('note', '', 'post');

$vals = array();
foreach ($fb as $v) {
    $vals[] = ($v === null) ? 'NULL' : intval($v);
}

$qr = "INSERT INTO feedback (fb0, fb1, fb2, fb3, fb4, feedback_score, feedback_note, feedback_date) " .
        "VALUES (" . implode(',', $vals) . ", " . round($avg, 2) . ", '" . addslashes($note) . "', NOW());";

$result = executeQuery($qr);
if ($result) {
    setSetting('feedbackUpdated', '1');
    echo 1;
} else {
    echo 0;
}
?>
