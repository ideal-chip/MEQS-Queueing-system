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
    $maxTransactions = $_GET['max'];
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
    
//    $ticketsQry = "select CONCAT(category_char, LPAD($eventMod,'0')) as 'ticket', log_counter as 'counter'
//            from events_logs, events, categories where log_id in
//            (SELECT max(log_id) FROM events_logs
//            where log_type IN (2,3) AND log_zone=$zone AND DATE(log_time)=DATE(NOW()) AND log_counter In(SELECT bdc_counter FROM bigdisplayscounters WHERE bdc_bigdisplay=$displayID)
//            group by log_counter order by log_time DESC) AND event_id = log_event AND event_category = category_id order by log_time DESC LIMIT $maxTransactions;";

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
    echo json_encode($finArray);

} else {
    echo 0;
}
?>
