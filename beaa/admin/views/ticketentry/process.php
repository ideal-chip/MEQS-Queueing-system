<?php

$ticketQty = getRequestVal('ticketqty', NULL, 'post');
$ticketCategory = getRequestVal('ticketcategory', NULL, 'post');
$submit = getRequestVal('submit', NULL, 'post');

if ($ticketQty != NULL && $ticketCategory != NULL) {

    $size = strlen($ticketQty);
    if ($size > 4 || !ctype_digit($ticketQty)) {
        $error = getTextValue('errorInOperation', $lang) . ",[ $ticketQty ]: " . getTextValue('errorNumber', $lang) . "!";
    } else {

        if (insertTicketBulk($ticketCategory, 1, $ticketQty)) {
            $catName = getTextValue(getValue("SELECT category_key FROM categories WHERE category_id = $ticketCategory;"), $lang);
            $message = "[ $ticketQty ] " . getTextValue("ticktsForService", $lang) . " [ $catName ] " . getTextValue('insertSuccessfully', $lang) . "!";
        } else {
            $message = "";
        }
    }
}

//-------------------------------------------< functions >-----------
function insertTicketBulk($categoryID, $kioskID, $qty) {
    //$kioskID = $_GET['kiosk'];
    //$categoryID = $_GET['category'];
    global $lang;
    $priority = 0;
    $level = 0;
    //$lang = $lang;
    $zoneID = getValue("select kiosk_zone from kiosks where kiosk_id=$kioskID;");
    $index = 0;
    while ($index < $qty) {
        if ($no = getValue("select IFNULL(max(event_no),0)+1 from events where event_zone=$zoneID and event_category=$categoryID and DATE(event_time)=DATE(NOW());")) {
            if (!($no % 1000))
                $no++;
            $qr = "INSERT INTO events(event_time,event_category,event_no,event_priority,event_level,event_language,event_zone,event_kiosk) VALUES(NOW(),$categoryID, $no,$priority,$level,'$lang',$zoneID,$kioskID);";
            $qr = executeQuery($qr);
            if ($qr)
                $index++; //echo $lastID;
        }
        else {
            ; //echo 0;
        }
    }

    if ($index >= 1)
        return 1;
    else
        return 0;
}
