<?php

error_reporting(0);
require_once("../../language.php");
require_once("../db.php");

//$arr = array();
$finArray = array();
//display digits
$eventMod = getEventDigitMod();

if (isset($_GET['id']) && $_GET['id'] > 0) {
    $displayID = $_GET['id'];
//    $maxTransactions = $_GET['max'];
    $maxTransactions = 5;
    $zone = getValue("SELECT display_zone FROM bigdisplays WHERE display_id=$displayID;");

    $ticketsQry = "select CONCAT(category_char, LPAD($eventMod,'0')) as 'ticket', log_counter as 'counter', event_priority AS 'priority'
            from events_logs, events, categories 
            where 
            event_id NOT IN (SELECT transfer_event FROM transfers WHERE transfer_done = 0 AND DATE(transfer_time)=DATE(NOW()))
            AND
            log_id in (SELECT max(log_id) FROM events_logs
            where log_type IN (2,3) AND log_zone=$zone AND DATE(log_time)=DATE(NOW()) AND log_counter In(SELECT bdc_counter FROM bigdisplayscounters WHERE bdc_bigdisplay=$displayID)
            group by log_counter order by log_time DESC) 
            AND event_id = log_event AND event_category = category_id order by log_time DESC LIMIT $maxTransactions;";

//    var_dump($ticketsQry);
    $tickets = getArrayAssoc($ticketsQry);
    //$tickets = array_reverse($tickets);

    $nums = array();
    foreach ($tickets as $tick) {
        array_push($nums, $tick['ticket']);
    }

    $nums = array_unique($nums, SORT_REGULAR);

    foreach ($nums as $number) {
        foreach ($tickets as $tick) {
            if ($tick['ticket'] == $number) {
                array_push($finArray, $tick);
                break;
            }
        }
    }

    $waitingQry = "SELECT event_id AS 'eventID',
            CONCAT(category_char, LPAD($eventMod,'0')) AS 'ticket',
            event_time AS 'eventTime',
            event_category AS 'eventCategory',
            event_priority 
	FROM events,categories,countercategories
            WHERE event_zone=$zone
            AND event_level= 0
            AND cc_counter In(SELECT bdc_counter FROM bigdisplayscounters WHERE bdc_bigdisplay=$displayID) 
            AND event_category IN (SELECT cc_category FROM countercategories WHERE cc_counter In(SELECT bdc_counter FROM bigdisplayscounters WHERE bdc_bigdisplay=$displayID) AND cc_enabled = 1)
            AND 
            (event_id IN (SELECT transfer_event FROM transfers WHERE transfer_done = 1 AND DATE(transfer_time)=DATE(NOW()))
                OR 
            event_id NOT IN (SELECT transfer_event FROM transfers WHERE transfer_done = 0 AND DATE(transfer_time)=DATE(NOW())))
            AND DATE(event_time)=DATE(NOW())
            AND event_category=category_id
            AND event_category=cc_category
        UNION
        SELECT transfer_event AS 'eventID',
            CONCAT(category_char, LPAD($eventMod,'0')) AS 'ticket',
            event_time AS 'eventTime',
            event_category AS 'eventCategory',
            event_priority 
        FROM transfers,events,categories,countercategories
            WHERE transfer_zone=$zone
            AND transfer_done = 0
            AND event_level = 1
            AND cc_counter In(SELECT bdc_counter FROM bigdisplayscounters WHERE bdc_bigdisplay=$displayID) 
            AND (transfer_new_counter In(SELECT bdc_counter FROM bigdisplayscounters WHERE bdc_bigdisplay=$displayID) OR transfer_new_category IN (SELECT category_id FROM categories WHERE category_enabled = 1))
            AND DATE(transfer_time)=DATE(NOW())
            AND event_category=category_id
            AND event_id=transfer_event
	ORDER BY event_priority DESC,eventTime, eventID LIMIT 10;";


    $ticksWaitingAll = getArrayAssoc($waitingQry);
//     var_dump($ticksWaitingAll);
    $ticksWaiting = array();
    $count = 0;
    foreach ($ticksWaitingAll as $item) {
        $temp = array();
        $temp['ticket'] = $item['ticket'];
        $temp['priority'] = $item['event_priority'];
//            var_dump($temp);
        array_push($ticksWaiting, $temp);
    }

//    var_dump($ticksWaiting);
//    var_dump($ticksWaiting);
//
//    echo json_encode($ticksWaiting);

    $alldata = array();
    array_push($alldata, $finArray);
    array_push($alldata, $ticksWaiting);
//    var_dump($alldata);
    echo json_encode($alldata);
} else {
    echo 0;
}
?>
