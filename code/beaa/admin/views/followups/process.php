<?php

//========================================================================================| functions

function getSubData($subId, $dateStart, $dateEnd) {

    $query = "SELECT COUNT(*) AS 'total', IFNULL(AVG(DATEDIFF(DATE(date_done), DATE(date_created))), 0) AS 'avg' 
                FROM followups, subcategories
                    WHERE followups.subcategory_id = $subId
                    AND followups.subcategory_id = subcategories.subcategory_id
                    AND date_done IS NOT NULL
                    AND  DATE(date_created) 
                        between '$dateStart' AND '$dateEnd'
                    AND DATEDIFF(DATE(date_done), DATE(date_created)) <= subcategories.wait_time_days
            UNION ALL
            SELECT COUNT(*) AS 'total', IFNULL(AVG(DATEDIFF(DATE(date_done), DATE(date_created))), 0) AS 'avg' FROM followups, subcategories
                WHERE followups.subcategory_id = $subId
                AND followups.subcategory_id = subcategories.subcategory_id
                AND date_done IS NOT NULL
                AND  DATE(date_created) 
                    between '$dateStart' AND '$dateEnd'
                AND DATEDIFF(DATE(date_done), DATE(date_created)) > subcategories.wait_time_days
            UNION ALL
            SELECT COUNT(*) AS 'total', IFNULL(AVG(DATEDIFF(DATE(date_done), DATE(date_created))), 0) AS 'avg' FROM followups, subcategories
                WHERE followups.subcategory_id = $subId
                AND followups.subcategory_id = subcategories.subcategory_id
                AND date_done IS NOT NULL
                AND  DATE(date_created) 
                    between '$dateStart' AND '$dateEnd'";

//    var_dump($query);
    $data = getArrayAssoc($query);
    return $data;
}

function getChartData($subId, $dateStart, $dateEnd) {
    
    $query = "SELECT DATEDIFF(DATE(date_done), DATE(date_created)) AS 'days'
                FROM followups, subcategories
                    WHERE followups.subcategory_id = $subId
                    AND followups.subcategory_id = subcategories.subcategory_id
                    AND date_done IS NOT NULL
                    AND  DATE(date_created) 
                    BETWEEN '$dateStart' AND '$dateEnd'
                    ORDER BY date_done ";

    
    $data = getColumn($query);

    return $data;
}

//========================================================================================| texts
//$dir = trim(getTextValue('dir', $lang));
//$title = getTextValue('followupCards', $lang);

$from = getTextValue('from', $lang);
$to = getTextValue('to', $lang);
$submit = getTextValue('submit', $lang);
$update = trim(getTextValue("update", $lang));
$print = getTextValue('print', $lang);

$mainService = getTextValue('mainService', $lang);
$subcategories = getTextValue('subcategories', $lang);
$avarageOfFinished = getTextValue('avarageOfFinished', $lang);
$issuingCards = getTextValue('issuingCards', $lang);

$total = getTextValue("total", $lang);
$counts = getTextValue('counts', $lang);
$avarage = getTextValue('avarage', $lang);
$lessWaittime = getTextValue('lessWaittime', $lang);
$moreWaittime = getTextValue('moreWaittime', $lang);
$waitTime = getTextValue('waitTime', $lang);
$reports = getTextValue('reports', $lang);
$waitTimeReport = "$reports : $waitTime";

$errorFromToDate = getTextValue("errorFromToDate", $lang);

$effeciencySpeed = getTextValue("effeciencySpeed", $lang);

//========================================================================================| reqs

$todayDate = date("Y-m-d");

$dateStart = getRequestVal('date_start', $todayDate);
$dateEnd = getRequestVal('date_end', $todayDate);
if ($dateStart > $dateEnd) {
    $temp = $dateEnd;
    $dateEnd = $dateStart;
    $dateStart = $temp;
}

//========================================================================================| DB init data [total cards for services]

$query = "SELECT followups.category_id,
                COUNT(followups.category_id) AS 'total',
                texts.text_value AS 'category_name'
                    FROM followups, categories, texts 
                        WHERE followups.category_id = categories.category_id 
                        AND categories.category_key = texts.text_key 
                        AND texts.text_language = '$lang'
                        AND  DATE(date_created) 
                        between '$dateStart' AND '$dateEnd' 
                        GROUP BY followups.category_id;";

$cards = getArrayAssoc($query);

$labels = array();
$values = array();

$labels = getColumn("SELECT text_value AS 'cat_name' FROM texts, categories WHERE text_key = category_key AND text_language = '$lang';");
//var_dump($labels);
for ($index = 0; $index < count($labels); $index++) {
    $values[] = 0;
}
//var_dump($values);
//var_dump($cards);

$totalCount = 0;
$max = 5;
foreach ($cards as $Row) {
    $totalCount = $totalCount + (intval($Row['total']));
    $catName = $Row['category_name'];
    $location = 0;

    foreach ($labels as $key => $value) {
        if ($value == $catName) {
            $location = $key;
        }
    }

    $values[$location] = intval($Row['total']);
//    $labels[] = $Row['category_name'];
    if (intval($Row['total']) > $max) {
        $max = intval($Row['total']);
    }
}
//var_dump($values);
$valuesJson = json_encode($values);
$labelsJson = json_encode($labels, JSON_UNESCAPED_UNICODE);

//
//var_dump($valuesJson);
//var_dump($labelsJson);
//========================================================================================| data [total subservices]

$categoriesList = getArrayAssoc("SELECT COUNT(main_category_id) AS 'size',
                                main_category_id AS 'cat',
                                text_value AS 'name' 
                                    FROM subcategories , texts
                                    WHERE texts.text_key = 
                                        (SELECT category_key 
                                        FROM categories 
                                        WHERE category_id = main_category_id)
                                    AND texts.text_language = '$lang'
                                    GROUP BY main_category_id");

//$categories = getKeyValArray($categoriesList, 'category_id', 'text_value');
//var_dump($categoriesList);
//var_dump($categories);


function GetPercenatage($avgDays, $waitTime, $totalDays, $br) {
//    $commonStyle = "";
//    $styleLow = "style='background-color:lightblue;color:#555;width:100%;display:block;position:relative;text-align:right; padding-right:3px;'";
//    $styleHigh = "style='background-color:lightcoral;color:white;width:100%;display:block;position:relative;text-align:right;padding-right:3px;'";

    $styleLow = "low-style";
    $styleHigh = "high-style";
    
    $brTrue = "";
    if ($br == 1) {
        $brTrue = "<br>";
    }
    if ($totalDays > 0 && $waitTime > 0) {
        $val = 100 - (($avgDays / $waitTime) * 100);
        
        if ($val < 0) {
            $style = $styleHigh;
            $arrowStyle = "glyphicon glyphicon-arrow-down";
        }else{
            $style = $styleLow;
            $arrowStyle = "glyphicon glyphicon-arrow-up";
        }
        return "$brTrue<span class='result-style $style'>" . number_format($val, 1) . " %<i class='over-left ".$arrowStyle."'></i></span>";
    }


    return "<br>-";
}

function GetPercenatageNumber($avgDays, $waitTime, $totalDays) {
   
    if ($totalDays > 0 && $waitTime > 0) {
        $val = 100 - (($avgDays / $waitTime) * 100);
        
//        return number_format($val, 2);
        return $val;
    }

    return "-";
}

function Accumulate($sub, $wait) {

    if ($sub > 0) {
        return $wait;
    }

    return 0;
}

//========================================================================================| chart data

$allSubCats = getArrayAssoc("SELECT subcategory_id, 
         wait_time_days 
         FROM subcategories 
         ORDER BY main_category_id");

//var_dump($allSubCats);
$charts = array();

for ($index1 = 0; $index1 < count($allSubCats); $index1++) {
    $charts[$index1]['data'] = getChartData($allSubCats[$index1]['subcategory_id'], $dateStart, $dateEnd);
    $charts[$index1]['wait'] = $allSubCats[$index1]['wait_time_days'];
    $charts[$index1]['id'] = $allSubCats[$index1]['subcategory_id'];
}

function CheckInReportAll($cat_id){
    $reportAll = getColumn("SELECT in_report FROM subcategories WHERE main_category_id = $cat_id;");
    $repoCount = 0;
    foreach ($reportAll as $repo) {
        
        if ($repo == 0) {
            $repoCount++;
        }
    }
    
    if ($repoCount == count($reportAll)) {
        return FALSE;
    }
    
    return TRUE;
}

function CheckInReportCount($cat_id){
    
    $reportAll = getColumn("SELECT in_report FROM subcategories WHERE main_category_id = $cat_id;");
    $repoCount = count($reportAll);
    
    foreach ($reportAll as $repo) {
        
        if ($repo == 0) {
            $repoCount--;
        }
    }
    
    return $repoCount;
}
//$chart1 = json_encode($charts[0]);
