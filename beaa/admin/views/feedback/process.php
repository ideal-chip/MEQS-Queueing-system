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

//========================================================================================| DB init data [display form data]

$fbQuestions = getArrayAssoc("SELECT text_key, text_value FROM texts WHERE text_key IN ('fb0','fb1','fb2','fb3','fb4') AND text_language='$lang' ORDER BY text_key;");

$ratings = array();
$allValues = array();
$finalTotalCount = 0;


for ($index = 1; $index <= 5; $index++) {
    $fb = "fb" . $index;
    
    $ratingQry = "SELECT $fb, COUNT($fb) AS 'total' 
        FROM feedback WHERE DATE(feedback_date) between '$dateStart' AND '$dateEnd' GROUP BY $fb;";
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


