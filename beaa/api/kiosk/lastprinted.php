<?php
 
// api/kiosk/lastprinted.php?id=<id>&cat=<cat>
// {"ID":"254","No":"036","Char":"A","CounterNo":"1"}

error_reporting(0);
require_once("../db.php");
if (isset($_GET['id']) && $_GET['id'] > 0 && isset($_GET['cat']) && $_GET['cat'] > 0) {
    $kioskID = filter_input(INPUT_GET, 'id');
    $categoryID = filter_input(INPUT_GET, 'cat');
    //$zoneID=getValue("select kiosk_zone from kiosks where kiosk_id=$kioskID;");
    if ($row = getRow("SELECT events.event_id as 'ID', LPAD(MOD(events.event_no,1000),3,'0') as 'No',categories.category_char as 'Char', '1' as 'CounterNo' FROM events,categories WHERE events.event_category=categories.category_id AND event_category = $categoryID AND event_kiosk=$kioskID AND DATE(event_time)= DATE(NOW()) ORDER BY event_time DESC LIMIT 1;")) {
        echo json_encode($row);
    } else {
        echo 0;
    }
} else {
    echo 0;
}
?>
