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
    $zone = getValue("SELECT display_zone FROM bigdisplays WHERE display_id=$displayID;");
    $counterInfo = getRow("SELECT counter_id, quantity FROM bigdisplayforcounter WHERE bd_id = $displayID;");
//    var_dump($counterInfo);
    
    $qty = $counterInfo['quantity'];
    $counter = $counterInfo['counter_id'];

    $qry = "SELECT log_event,CONCAT(category_char, LPAD($eventMod,'0')) as 'ticket', log_counter as 'counter', event_priority AS 'priority'
            FROM events_logs, events, categories 
            WHERE log_counter = $counter 
            AND log_type IN (2, 3)
            AND DATE(log_time) = DATE(NOW())
            AND log_event=events.event_id
            AND events.event_category=categories.category_id
            group by log_event
            order by max(log_time) DESC limit $qty";

    $tickets = getArrayAssoc($qry);
    if (count($tickets) > 0) {
        echo json_encode($tickets);
    } else {
        echo 0;
    }
} else {
    echo 0;
}
?>
