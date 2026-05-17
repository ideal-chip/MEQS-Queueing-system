<?php

error_reporting(0);
session_start();
require_once("../../language.php");

// requests
$categoryID = getRequestVal('category', 0);
$kioskID = getRequestVal('kiosk', 0);
$lang = getRequestVal('lang', 'ar');

$isCategoryEnabled = getValue("SELECT category_enabled FROM categories WHERE category_id = $categoryID;");

if ($isCategoryEnabled) {
    
    // more data from db
    $zoneID = getValue("select kiosk_zone from kiosks where kiosk_id=$kioskID;");
    $priority = getValue("SELECT kb_priority FROM kioskbuttons WHERE kb_category = $categoryID;");
    $level = 0;

    $no = getValue("select IFNULL(max(event_no),0)+1
        FROM events where event_zone=$zoneID
        AND event_category=$categoryID AND DATE(event_time)=DATE(NOW());");
    
    if ($no) {
        
        if (!($no % 1000)) {
            $no++;
        }

        $qr = "INSERT INTO events(event_time,event_category,event_no,
            event_priority,event_level,event_language,event_zone,event_kiosk)
            VALUES(NOW(),$categoryID, $no,$priority,$level,'$lang',$zoneID,$kioskID);";

        $result = executeQuery($qr);
        if ($result) {
            echo $lastID;
        } else {
            echo 0;
        }
    } else {
        echo 0;
    }
}else{
    echo 0;
}
?>
