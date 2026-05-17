<?php

error_reporting(0);
require_once("../../language.php");

$data = array();

if (isset($_GET['id'])) {

    $eventMod = getEventDigitMod();
    $displayID = $_GET['id'];

    // defualts  ---------------------------------------------------------------

    $data['updated'] = 0;
    $data['event'] = '';

    // check updated status ----------------------------------------------------
    if (getValue("SELECT display_updated FROM displays WHERE display_id=$displayID;")) {
        executeQuery("UPDATE displays SET display_updated=0 WHERE display_id=$displayID;");
        $data['updated'] = 1;
    }

    // check last called ticket ------------------------------------------------
    $query = "SELECT * FROM displays_logs
            WHERE log_display=$displayID
            AND DATE(NOW())=DATE(log_time)
            order by log_time DESC,log_event DESC LIMIT 1;";

    $row = getRow($query);

    if ($row && $row['log_event']) {

        $eventID = $row['log_event'];
        $counterID = $row['log_counter'];
        $logID = $row['log_id'];

        $eventObject = getRow("SELECT $logID as 'ID',
                    CONCAT(category_char, LPAD($eventMod,'0')) AS 'ticket',
                    counter_no as 'CounterNo', counter_active AS 'active'
                        from events,categories,counters 
                        where events.event_category = categories.category_id 
                        and events.event_id = $eventID 
                        and counter_id = $counterID;");

        $data['event'] = $eventObject;
    }

    echo json_encode($data);
} else {
    echo 0;
}
?>