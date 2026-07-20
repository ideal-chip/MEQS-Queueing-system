<?php

//========================================================================================| Sessions
$_SESSION['lang'] = $lang;
//========================================================================================| texts

//$dir = trim(getTextValue('dir', $lang));
//$title = getTextValue('satisfactionScore', $lang);

$from = getTextValue('from', $lang);
$to = getTextValue('to', $lang);
$submit = getTextValue('submit', $lang);
$update = trim(getTextValue("update", $lang));
$print = getTextValue('print', $lang);

$feedbacks = getTextValue("feedbacks", $lang);
$finalScore = getTextValue("finalScore", $lang);

$errorFromToDate = getTextValue("errorFromToDate", $lang);

//========================================================================================| get request data
$todayDate = date("Y-m-d");

$dateStart = getRequestVal('date_start', $todayDate);
$dateEnd = getRequestVal('date_end', $todayDate);

if ($dateStart > $dateEnd) {
    $temp = $dateEnd;
    $dateEnd = $dateStart;
    $dateStart = $temp;
}

//========================================================================================| scope / counter filter
// scope: all (default) | global | counter. counter_id: only applied when scope=counter.
$scope = getRequestVal('scope', 'all');
if (!in_array($scope, array('all', 'global', 'counter'), true)) {
    $scope = 'all';
}
$filterCounterId = (int) getRequestVal('counter_id', 0);

$scopeWhere = "DATE(feedback_date) BETWEEN '$dateStart' AND '$dateEnd'";
if ($scope === 'global') {
    $scopeWhere .= " AND feedback_scope = 'global'";
} elseif ($scope === 'counter') {
    $scopeWhere .= " AND feedback_scope = 'counter'";
    if ($filterCounterId > 0) {
        $scopeWhere .= " AND counter_id = $filterCounterId";
    }
}

$countersList = getArrayAssoc("SELECT counter_id, counter_name FROM counters ORDER BY counter_name;");

// Global vs counter split (independent of the scope filter above, always
// shown so the two are never silently averaged together unless "All" is picked).
$scopeCounts = getArrayAssoc("SELECT feedback_scope, COUNT(*) AS total
    FROM feedback WHERE DATE(feedback_date) BETWEEN '$dateStart' AND '$dateEnd' GROUP BY feedback_scope;");
$globalCount = 0;
$counterCount = 0;
foreach ($scopeCounts as $row) {
    if ($row['feedback_scope'] === 'global') {
        $globalCount = (int) $row['total'];
    } else {
        $counterCount = (int) $row['total'];
    }
}

// Per-counter comparison table (uses the snapshot name, so it still reports
// correctly for counters that have since been deleted).
$counterComparison = getArrayAssoc("SELECT
        counter_id, counter_name_snapshot,
        COUNT(*) AS total,
        ROUND(AVG(feedback_score), 2) AS avg_score,
        MAX(feedback_date) AS last_feedback
    FROM feedback
    WHERE feedback_scope = 'counter' AND DATE(feedback_date) BETWEEN '$dateStart' AND '$dateEnd'
    GROUP BY counter_id, counter_name_snapshot
    ORDER BY avg_score DESC;");

//========================================================================================| DB init data [display form data]

$fbQuestions = getArrayAssoc("SELECT text_key, text_value FROM texts WHERE text_key IN ('fb0','fb1','fb2','fb3','fb4') AND text_language='$lang' ORDER BY text_key;");

$ratings = array();
$allValues = array();
$finalTotalCount = 0;


for ($index = 1; $index <= 5; $index++) {
    $fb = "fb" . $index;

    $ratingQry = "SELECT $fb, COUNT($fb) AS 'total'
        FROM feedback WHERE $scopeWhere GROUP BY $fb;";
    $rateTabel = getArrayAssoc($ratingQry);
    
    $values = [0, 0, 0, 0, 0];

    $totalScore = 0;
    $totalCount = 0;
    $totalWeight = 0;
    $max = 5;   // max y-axes value

    foreach ($rateTabel as $Row) {
        $score = $Row["$fb"];
        $totalScoreCount = $Row['total'];

        $totalCount = $totalCount + (intval($score) * intval($totalScoreCount));
        $totalWeight = $totalWeight + intval($totalScoreCount);

        $values[$score - 1] = intval($totalScoreCount);

        if (intval($totalScoreCount) > $max) {
            $max = intval($totalScoreCount);
        }
    }

    if ($totalWeight > 0) {
        $totalScore = number_format(($totalCount / $totalWeight), 2);
    }

    
    $finalTotalCount = $finalTotalCount + intval($totalWeight);
//    var_dump($totalWeight);
//    var_dump($finalTotalCount);
    
    array_push($ratings, $totalScore);
    array_push($allValues, $values);
}


//var_dump($ratings);
//var_dump($allValues);

$finalTotalScore = array_sum($ratings)/5;
$valuesJson = json_encode($allValues);


