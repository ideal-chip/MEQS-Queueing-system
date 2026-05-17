<?php

//========================================================================================| requests
    $todayDate = date("Y-m-d");
    $dateStart = getRequestVal('date_start', $todayDate);
    $dateEnd = getRequestVal('date_end', $todayDate);
    if ($dateStart > $dateEnd) {
        $temp = $dateEnd;
        $dateEnd = $dateStart;
        $dateStart = $temp;
    }

//========================================================================================| DB data
    //-----------< general info >---------
    $zoneCounts = getValue("SELECT COUNT(*) FROM zones;");
    $clerksCounts = getValue("SELECT COUNT(*) FROM clerks;");
    $kiosksCounts = getValue("SELECT COUNT(*) FROM kiosks;");
    $countersCounts = getValue("SELECT COUNT(*) FROM counters;");
    $bigdisplaysCounts = getValue("SELECT COUNT(*) FROM bigdisplays;");
    $displaysCount = getValue("SELECT COUNT(*) FROM displays;");

    //-----------< general info >---------
    $count_eventsAll = intval(getValue("SELECT COUNT(*) FROM events WHERE DATE(event_time) BETWEEN '$dateStart' AND '$dateEnd';"));
    $count_eventsWaiting = intval(getValue("SELECT COUNT(*) FROM events WHERE DATE(event_time) BETWEEN '$dateStart' AND '$dateEnd' AND event_level=0;"));
    $count_eventsTransferred = intval(getValue("SELECT COUNT(*) FROM transfers WHERE DATE(transfer_time) BETWEEN '$dateStart' AND '$dateEnd';"));
    $count_eventsClosed = intval(getValue("SELECT COUNT(*) FROM events WHERE DATE(event_time) BETWEEN '$dateStart' AND '$dateEnd' AND event_level <> 0;"));

    $allCategoriesCounts = json_encode([$count_eventsAll, $count_eventsWaiting, $count_eventsTransferred, $count_eventsClosed]);

    //-----------< categories details >---------

    $categoryList = getArrayAssoc("SELECT category_id,
                                    category_enabled,
                                    category_char,
                                    text_value AS 'category_name',
                                        (SELECT COUNT(*) FROM events WHERE event_category=categories.category_id AND DATE(event_time) BETWEEN '$dateStart' AND '$dateEnd') AS 'total_no',
                                        (SELECT COUNT(*) FROM events WHERE event_category=categories.category_id AND DATE(event_time) BETWEEN '$dateStart' AND '$dateEnd' AND event_level=0) AS 'total_waiting',
                                        (SELECT COUNT(*) FROM transfers,events WHERE event_id=transfer_event AND event_category=categories.category_id AND DATE(event_time) BETWEEN '$dateStart' AND '$dateEnd') AS 'total_transfer',
                                        (SELECT COUNT(*) FROM events WHERE event_category=categories.category_id AND DATE(event_time) BETWEEN '$dateStart' AND '$dateEnd' AND event_level<>0) AS 'total_done'
                                    FROM categories, texts 
                                        WHERE category_key = text_key 
                                        AND text_language = '$lang';");

    $cat_names = array();
    $cat_ticket_no = array();
    $cat_ticket_no_waiting = array();
    $cat_ticket_no_transferred = array();
    $cat_ticket_no_served = array();

    foreach ($categoryList as $category) {

        $t_name = $category['category_name'];
        $t_no = $category['total_no'];
        $t_no_waiting = $category['total_waiting'];
        $t_no_transferred = $category['total_transfer'];
        $t_no_served = $category['total_done'];

        array_push($cat_names, $t_name);
        array_push($cat_ticket_no, $t_no);
        array_push($cat_ticket_no_waiting, $t_no_waiting);
        array_push($cat_ticket_no_transferred, $t_no_transferred);
        array_push($cat_ticket_no_served, $t_no_served);
    }

    //-----------< counter loads details >---------

    $counter_loads = array();
    $counter_pendings = array();
    $counterNumbers = array();

    $counters = getArrayAssoc("SELECT counter_id, counter_name, counter_active, current_clerk, last_seen, counter_no,
                            (SELECT clerk_name FROM clerks WHERE clerk_id = counters.current_clerk) AS 'clerk_name',
                            (SELECT COUNT(*) FROM events_logs 
                                WHERE log_counter=counters.counter_id AND (log_type IN (2,3)) 
                             AND DATE(log_time) 
                                BETWEEN '$dateStart' AND '$dateEnd') AS 'counter_load',
                            (SELECT COUNT(*) FROM events_logs 
                                WHERE log_counter=counters.counter_id AND log_type = 4 AND DATE(log_time) 
                                BETWEEN '$dateStart' AND '$dateEnd') AS 'counter_pending'
                            FROM counters;");


//========================================================================================| settings
//$counterSwitchServices = getSetting("counterSwitchServices") == '1' ? TRUE : FALSE;
//========================================================================================| langs
//    $title = getTextValue("flow", $lang);
//    $dir = trim(getTextValue('dir', $lang));
    $selectDates = getTextValue("selectDates", $lang);
    $update = getTextValue("update", $lang);
    $print = getTextValue('print', $lang);
    $to = getTextValue('to', $lang);
    $from = getTextValue('from', $lang);
    $excelExport = getTextValue('excelExport', $lang);
    $lang_closed = getTextValue('closed', $lang);

    $generalInfo = getTextValue("generalInfo", $lang);
    $zonesNo = getTextValue('zonesNo', $lang);
    $clerksNo = getTextValue('clerksNo', $lang);
    $kiosksNo = getTextValue('kiosksNo', $lang);
    $countersNo = getTextValue('countersNo', $lang);
    $bigDisplayNo = getTextValue('bigDisplayNo', $lang);
    $displaysNo = getTextValue('displaysNo', $lang);

    $lang_categories = getTextValue("categories", $lang);
    $category = getTextValue('category', $lang);
    $mainService = getTextValue('mainService', $lang);

    $lang_counters = getTextValue('counters', $lang);
    $lang_counter = getTextValue('counter', $lang);
    $lang_clerkCounter = getTextValue('clerkCounter', $lang);
    $lang_lastSeen = getTextValue('lastSeen', $lang);
    $lang_counterLoad = getTextValue('counterLoad', $lang);
    $lang_pendingList = getTextValue('pendingList', $lang);


    $allCategories = getTextValue("allCategories", $lang);
    $eventsNo = getTextValue('eventsNo', $lang);
    $eventsWaiting = getTextValue('eventsWaiting', $lang);
    $eventsTransferred = getTextValue('eventsTransferred', $lang);
    $eventsClosed = getTextValue('eventsClosed', $lang);
