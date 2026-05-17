<?php

//========================================================================================| texts

$lang_categories = getTextValue('categories', $lang);
$lang_clerks = getTextValue('clerks', $lang);
$created = getTextValue('created', $lang);
$finished = getTextValue('finished', $lang);
$smsSent = getTextValue('smsSent', $lang);
$add = getTextValue('add', $lang);
$save = getTextValue("save", $lang);
$adminOnly = getTextValue('adminOnly', $lang);
$allSystem = getTextValue('allSystem', $lang);
$days = getTextValue('days', $lang);
$excelExport = getTextValue('excelExport', $lang);

$type = getTextValue('type', $lang);
$selectDates = getTextValue('selectDates', $lang);
$from = getTextValue('from', $lang);
$to = getTextValue('to', $lang);
$ready = getTextValue('ready', $lang);
$notReady = getTextValue('notReady', $lang);
$notImportant = getTextValue('notImportant', $lang);
$processed = getTextValue('processed', $lang);
$notProcessed = getTextValue('notProcessed', $lang);
$markProcessed = getTextValue('markProcessed', $lang);
$clientInfo = getTextValue('clientInfo', $lang);

//$title = getTextValue('interface', $lang);
//$dir = trim(getTextValue('dir', $lang));
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
$totalProcessTime = getTextValue('totalProcessTime', $lang);
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
$advancedSearch = getTextValue('advancedSearch', $lang);
$searchOptions = getTextValue('searchOptions', $lang);
$search = getTextValue('search', $lang);
$areYouSure = getTextValue('areYouSure', $lang);

$directorate = getTextValue('directorate', $lang);
$serviceType = getTextValue('serviceType', $lang);
$followupFootnote1 = getTextValue('followupFootnote1', $lang);
$followupFootnote2 = getTextValue('followupFootnote2', $lang);
$followupFootnote3 = getTextValue('followupFootnote3', $lang);
$followupFootnote4 = getTextValue('followupFootnote4', $lang);
$followupFootnote5 = getTextValue('followupFootnote5', $lang);

//========================================================================================| Variables

$left = $dir == 'ltr' ? 'left' : 'right';
$right = $dir == 'ltr' ? 'right' : 'left';

//echo "<br><br><br>";
//var_dump($_GET);
//========================================================================================| GET Request Array


$searchQry = getRequestVal('q', '', 'get');

$category_id = getRequestVal('fcategory-id', getDefualtCategory(), 'get');
$subCategory_id = getRequestVal('fsubcategory-id', 0, 'get');
$clerk_id = getRequestVal('fclerk-id', 0, 'get');

$typeOption = getRequestVal('type-option', 0, 'get');

$createDateOption = getRequestVal('create_date_op', '', 'get');
$createDateFrom = getRequestVal('create_from_date', '', 'get');
$createDateTo = getRequestVal('create_to_date', '', 'get');

$doneDateOption = getRequestVal('done_date_op', '', 'get');
$doneDateFrom = getRequestVal('done_from_date', '', 'get');
$doneDateTo = getRequestVal('done_to_date', '', 'get');

$adv = getRequestVal('adv', '0', 'get');
$advShown = intval($adv) > 0 ? 'display' : 'none';
//========================================================================================| Search
//==========================|| search option

if (isset($_GET['fcategory-id']) && $_GET['fcategory-id'] > 0) {
    $req_category_id = $_GET['fcategory-id'];
} else {
    $req_category_id = 0;
}

$catQuery = getQuerySelect($req_category_id, 'category_id');
$subCatQuery = getQuerySelect($subCategory_id, 'subcategory_id');
$clerkQuery = getQuerySelect($clerk_id, 'clerk_id');


//==========================|| type option
$typeQuery = '';
if ($typeOption > 0) {
    if ($typeOption == 1) {
        $typeQuery = "AND date_done IS NOT NULL";
    } else {
        $typeQuery = "AND date_done IS NULL";
    }
}
//==========================|| dates option
$createDateQuery = getDateQuery($createDateOption, $createDateFrom, $createDateTo, 'date_created');
$doneDateQuery = getDateQuery($doneDateOption, $doneDateFrom, $doneDateTo, 'date_done');

//==============================================|| search results
$queryCount = "SELECT COUNT(*) FROM followups
                WHERE
                (serial_no like '%$searchQry%'
                OR client_name like '%$searchQry%'
                OR mobile_number like '%$searchQry%')
                    $catQuery
                    $subCatQuery
                    $clerkQuery
                    $typeQuery
                    $createDateQuery
                    $doneDateQuery";

$searchSize = getValue($queryCount);
//==========================|| pager
$dateToday = date("d-m-Y");
$max = 10;
$offset = 0;
$page = 1;
if (isset($_GET['page']) && $_GET['page'] > 0 && $searchSize > $max) {
    $page = getRequestVal('page', '1', 'get');
}

$nextPage = 0;
$prevPage = 0;
$totalPages = 1;
if ($searchSize > $max) {
    $totalPages = ceil($searchSize / $max);
    $page = $page > $totalPages ? $totalPages : $page;
    $offset = $max * ($page - 1);

    $nextPage = $page + 1;
    $prevPage = $page - 1;
    if ($nextPage > $totalPages) {
        $nextPage = 0;
    }
    if ($prevPage <= 0) {
        $prevPage = 0;
    }
}

//==========================|| final results
$query = "SELECT followups.*, DATEDIFF(DATE(date_done), DATE(date_created)) AS 'total_days' FROM followups
            WHERE
            (serial_no like '%$searchQry%'
            OR client_name like '%$searchQry%'
            OR mobile_number like '%$searchQry%')
                $catQuery
                $subCatQuery
                $clerkQuery
                $typeQuery
                $createDateQuery
                $doneDateQuery
                    ORDER BY date_created DESC
                    LIMIT $max OFFSET $offset;";

//var_dump($query);


$searchResult = getArrayAssoc($query);

//========================================================================================| DB init data [display form data]

$zoneID = getValue("SELECT category_zone FROM categories WHERE category_id=$category_id;");
$subCateories = getArrayAssoc("SELECT subcategory_id, subcategory_name FROM subcategories WHERE main_category_id = $category_id;");
$categories = getArrayAssoc("SELECT category_id, text_value AS 'catName' FROM categories, texts WHERE category_key=text_key AND text_language='$lang' AND category_zone=$zoneID;");
$clerks = getArrayAssoc("SELECT clerk_id, clerk_name, clerk_fullname FROM clerks WHERE clerk_zone = $zoneID;");

//========================================================================================| Functions
function getDateDiff($start, $end) {
    $dStart = new DateTime($start);
    $dEnd = new DateTime($end);
    $dDiff = $dStart->diff($dEnd);
//    return $dDiff->format('%R'); // use for point out relation: smaller/greater
    return $dDiff->days;
}

function getQuerySelect($val, $cond) {
    $q = '';
    if (!empty($val)) {
        $q = "AND $cond = $val";
    }
    return $q;
}

function getDefualtCategory() {
    return getValue("SELECT MIN(category_id) FROM categories;");
}

function getDateQuery($on, $from, $to, $colName) {
    $dateQry = "";

    if (empty($on)) {
        return "";
    }

    // cond 1
    if (empty($from) && empty($to)) {
        return "";
    }
    // cond 2
    if (!empty($from) && !empty($to)) {
        if ($from > $to) {
            //echo "bigger";
            $dateQry = " AND DATE($colName) BETWEEN '$to' AND '$from' ";
        } else {
            //echo "smaller";
            $dateQry = " AND DATE($colName) BETWEEN '$from' AND '$to' ";
        }

        return $dateQry;
    }

    // cond 3
    if (empty($from) && !empty($to)) {

        $dateQry = " AND DATE($colName) = '$to'";
        return $dateQry;
    }

    // cond 4
    if (!empty($from) && empty($to)) {

        $dateQry = " AND DATE($colName) = '$from'";
        return $dateQry;
    }


    return $dateQry;
}
