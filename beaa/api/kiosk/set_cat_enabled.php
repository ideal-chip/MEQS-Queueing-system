<?php

error_reporting(0);
session_start();
require_once("../db.php");
$kioskID = $_GET['kiosk'];
$categoryID = $_GET['category'];
$priority = $_GET['priority'];
$level = $_GET['level'];
$lang = $_SESSION['lang'];

$isCategoryEnabled = getValue("SELECT category_enabled FROM categories WHERE category_id = $categoryID;");

if ($isCategoryEnabled) {
    $zoneID = getValue("select kiosk_zone from kiosks where kiosk_id=$kioskID;");
    if ($no = getValue("select IFNULL(max(event_no),0)+1 from events where event_zone=$zoneID and event_category=$categoryID and DATE(event_time)=DATE(NOW());")) {
        if (!($no % 1000))
            $no++;
        $qr = "INSERT INTO events(event_time,event_category,event_no,event_priority,event_level,event_language,event_zone,event_kiosk) VALUES(NOW(),$categoryID, $no,$priority,$level,'$lang',$zoneID,$kioskID);";
        $qr = executeQuery($qr);
        if ($qr) {
            echo $lastID;
        } else {
            echo 0;
        }
    } else {
        echo 0;
    }
} else {
    echo 0;
}
?>
