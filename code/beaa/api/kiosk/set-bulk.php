<?php

error_reporting(0);
session_start(); 
require_once("../db.php");
$kioskID = $_GET['kiosk'];
$categoryID = $_GET['category'];
$priority = $_GET['priority'];
$level = $_GET['level'];
$lang = $_SESSION['lang'];
$zoneID = getValue("select kiosk_zone from kiosks where kiosk_id=$kioskID;");
if ($no = getValue("select IFNULL(max(event_no),0)+1 from events where event_zone=$zoneID and event_category=$categoryID and DATE(event_time)=DATE(NOW());")) {
    if (!($no % 1000))
        $no++;
    $qr = "INSERT INTO events(event_time,event_category,event_no,event_priority,event_level,event_language,event_zone,event_kiosk) VALUES(NOW(),$categoryID, $no,$priority,$level,'$lang',$zoneID,$kioskID);";
    $qr = executeQuery($qr);
    if ($qr) {
        $event = $lastID;
        $bdsDisplaysQry = "SELECT bigdisplayservices.bd_id as 'ID', bigdisplays.display_type as 'TYPE' FROM bigdisplayservices, bigdisplays WHERE bigdisplayservices.category_id = $categoryID AND bigdisplayservices.bd_id = bigdisplays.display_id AND bigdisplays.display_type = 2;";
        $displays = getArray($bdsDisplaysQry);
        foreach ($displays as $dis) {
            if ($dis['TYPE'] == 2) {
                executeQuery("INSERT INTO audios_logs_bulk(log_event,log_category, log_audio,log_seen,log_zone, bd_id) VALUES($event, $categoryID, 1, 0, $zoneID," . $dis['ID'] . ");");
            }
        }
        echo $event;
    } else {
        echo 0;
    }
} else {
    echo 0;
}
?>
