<?php

// api/bigdisplay/bulk.php?id=1&type=tickets
error_reporting(0);
require_once("../../language.php");
require_once("../db.php");

$arr = array();
$allRows = array();
//display digits
$eventMod = getEventDigitMod();

function getQuery($cat, $limit, $priority) {
    global $eventMod;
    $txt = "(SELECT 
			CONCAT(categories.category_char, LPAD($eventMod,'0')) as 'Ticket', '$priority' as 'priority'
        
			FROM events,categories
                        
			WHERE events.event_category=categories.category_id
			AND DATE(events.event_time)=DATE(NOW())
			AND events.event_category =$cat 
                        AND events.event_level = 0 LIMIT $limit)";
    return $txt;
}

function getFullBulkQry($allRows) {
    global $lang, $eventMod;
    
    $q = "SELECT Ticket FROM (";
    $size = count($allRows);
    for ($i = 0; $i < $size; $i++) {

        $cat = $allRows[$i][0];
        $catQty = $allRows[$i][1];
        $catPriority = $allRows[$i][2];

        $q = $q . getQuery($cat, $catQty, $catPriority);
        if ($i < ($size - 1)) {
            $q = $q . " UNION ";
        } else if ($i == ($size - 1)) {
            $q = $q . ") results ORDER BY priority desc, Ticket;";
        }
    }

    return $q;
}

if (isset($_GET['id']) && $_GET['id'] > 0) {

    $displayID = $_GET['id'];

    $queryAll = "SELECT * FROM bigdisplayservices WHERE bd_id = $displayID";
    $result = getArray($queryAll);

    foreach ($result as $row) {
        $tmp = array();
        array_push($tmp, intval($row[2]));
        array_push($tmp, intval($row[3]));
        array_push($tmp, intval($row[4]));

        array_push($allRows, $tmp);
    }

    if (isset($_GET['type'])) {
        $type = strtolower(filter_input(INPUT_GET, 'type'));
        switch ($type) {
            case 'tickets':
                $querytxt = getFullBulkQry($allRows);
                $tickets = getArray($querytxt);
                foreach ($tickets as $row) {
                    array_push($arr, $row['Ticket']);
                }
                echo json_encode($arr);
                break;
            case 'audio':
                $audioID = filter_input(INPUT_GET, 'audioid');
                $audioArr = array();
                $adquery = "SELECT audios_logs_bulk.log_id as 'logID',CONCAT(categories.category_char, LPAD($eventMod,'0')) as 'Ticket'
			FROM audios_logs_bulk,events,categories
			WHERE DATE(audios_logs_bulk.log_time)=DATE(NOW())
			AND audios_logs_bulk.log_audio=$audioID
			AND log_seen=0
                        AND bd_id = $displayID
			AND audios_logs_bulk.log_event=events.event_id
			AND events.event_category=categories.category_id
			AND audios_logs_bulk.log_category=categories.category_id
			ORDER BY Ticket, audios_logs_bulk.log_time;";


                $querytxt = getFullBulkQry($allRows);
                $tickets = getArray($querytxt);

                $audios = getArray($adquery);
                foreach ($audios as $row) {
                    foreach ($tickets as $value) {
                        if ($row['Ticket'] == $value['Ticket']) {
                            array_push($audioArr, $row['Ticket']);
                            executeQuery("UPDATE audios_logs_bulk SET log_seen=1 WHERE log_id=" . $row['logID'] . ";");
                        }
                    }
                }

                echo json_encode($audioArr);
                break;
            case 'recall':
                break;
        }
    }
}
?>
