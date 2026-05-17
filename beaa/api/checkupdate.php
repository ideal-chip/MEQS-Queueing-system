<?php

//error_reporting(0);
require_once('../language.php');
$uploadsPath = "../uploads/";
$slides;

function getImages() {
    global $uploadsPath, $slides;
    $items = scandir($uploadsPath);
    $images = array();
    $slides = array();

    foreach ($items as $item) {
        if (is_file($uploadsPath . $item)) {
            if (strtolower(pathinfo($uploadsPath . $item)['extension']) == 'jpg' || strtolower(pathinfo($uploadsPath . $item)['extension']) == 'png' || strtolower(pathinfo($uploadsPath . $item)['extension']) == 'bmp' || strtolower(pathinfo($uploadsPath . $item)['extension']) == 'raw') {
                if ($item == 'bigbg.jpg' || $item == 'head.png' || $item == 'logo.png' || $item == 'star.png') {
                    array_push($images, $item);
                } else {
                    array_push($slides, $item);
                }
            }
        }
    }
}

if (isset($_GET['id']) && $_GET['id'] > 0 && isset($_GET['type'])) {
    switch (strtolower($_GET['type'])) {
        case 'kiosk':
            $kioskID = $_GET['id'];
            if (getValue("SELECT kiosk_updated FROM kiosks WHERE kiosk_id=$kioskID;")) {
                executeQuery("UPDATE kiosks SET kiosk_updated=0 WHERE kiosk_id=$kioskID;");
                echo 1;
            } else {
                echo 0;
            }
            break;
        case 'bigdisplay':
            //$displayID = $_GET['id'];
            $displayID = filter_input(INPUT_GET, 'id');

            if (getValue("SELECT display_updated FROM bigdisplays WHERE display_id=$displayID;")) {
                executeQuery("UPDATE bigdisplays SET display_updated=0 WHERE display_id=$displayID;");
                echo 1;
            } else {
                echo 0;
            }
            break;
        case 'display':
            $displayID = $_GET['id'];
            if (getValue("SELECT display_updated FROM displays WHERE display_id=$displayID;")) {
                executeQuery("UPDATE displays SET display_updated=0 WHERE display_id=$displayID;");
                echo 1;
            } else {
                echo 0;
            }
            break;
        case 'feedback':
            if (getSetting('feedbackUpdated') == "1") {
                $res = setSetting('feedbackUpdated', '0');
                echo 1;
            } else {
                echo 0;
            }
            break;
        case 'bigdisplayimgs':
            $displayID = $_GET['id'];
            getImages();

            if (count($slides) > 0) {
                echo json_encode($slides);
            } else {
                echo 0;
            }
            break;

        case 'bulkstatus':
            $status = getValue("SELECT set_value FROM settings WHERE set_key='bulkStatus';");
            if (intval($status) == 1) {
                echo 1;
            } else {
                echo 0;
            }
            break;
        case 'audio':
            $status = getValue("SELECT set_value FROM settings WHERE set_key='audioShortBeep';");
            if ($status) {
                echo json_encode($status);
            } else {
                echo 0;
            }

            break;
    }
} else {
    echo 0;
}
?>
