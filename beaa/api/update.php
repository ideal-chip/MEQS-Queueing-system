<?php

//error_reporting(0);
//require_once('db.php');
require_once('../language.php');

if (isset($_GET['id']) && $_GET['id'] > 0 && isset($_GET['type'])) {
    switch (strtolower($_GET['type'])) {
        case 'kiosk':
            $kioskID = $_GET['id'];
            if (executeQuery("UPDATE kiosks SET kiosk_updated=1 WHERE kiosk_id=$kioskID;")) {
                echo 1;
            } else {
                echo 0;
            }
            break;
        case 'bigdisplay':
            $displayID = $_GET['id'];
            if (executeQuery("UPDATE bigdisplays SET display_updated=1 WHERE display_id=$displayID;")) {
                echo 1;
            } else {
                echo 0;
            }
            break;
        case 'display':
            $displayID = $_GET['id'];
            if (executeQuery("UPDATE displays SET display_updated=1 WHERE display_id=$displayID;")) {
                echo 1;
            } else {
                echo 0;
            }
            break;
        case 'allbigdisplay':
            $addedQry = ';';

            if (isset($_GET['bdtype'])) {
                $displayType = filter_input(INPUT_GET, 'bdtype');
                $addedQry = " WHERE display_type=$displayType;";
            }

            if (executeQuery("UPDATE bigdisplays SET display_updated=1" . $addedQry)) {
                echo 1;
            } else {
                echo 0;
            }
            break;
        case 'allsystem':

            $displaysQry = "UPDATE displays SET display_updated=1;";
            $bigdisplaysQry = "UPDATE bigdisplays SET display_updated=1;";
            $displayQry = "UPDATE settings SET set_value = '1' WHERE set_key = 'feedbackUpdated';";

            if (executeMultiQuery($displaysQry . $bigdisplaysQry . $displayQry)) {
                echo 1;
            } else {
                echo 0;
            }
            break;
        case 'language':
            //$displayID = $_GET['id'];
            $lang = filter_input(INPUT_GET, strtolower('lang'));
            if (!is_null($lang)) {
                if (executeQuery("UPDATE settings SET set_value='$lang' WHERE set_key='defaultLanguage';")) {
                    echo 1;
                } else {
                    echo 0;
                }
            } else {
                echo 0;
            }

            break;
        case 'counter':
            $counterID = filter_input(INPUT_GET, 'id');
            $clerkID = filter_input(INPUT_GET, 'clerkid');
            if (executeQuery("UPDATE counters SET counter_active=1, current_clerk=$clerkID, last_seen= NOW() WHERE counter_id=$counterID;")) {
                echo 1;
            } else {
                echo 0;
            }
            break;
        case 'flow':
            if (executeQuery("Update settings SET set_value = 1 WHERE set_key = 'isFlowRead';")) {
                echo 1;
            } else {
                echo 0;
            }
            break;
        case 'catstatus':
            $cc_id = filter_input(INPUT_GET, 'id');
            $enabled = filter_input(INPUT_GET, 'enabled');
            if (executeQuery("UPDATE countercategories SET cc_enabled = $enabled WHERE cc_id = $cc_id;")) {
                echo $cc_id;
            } else {
                echo 0;
            }
            break;
        case 'bulkstatus':
            $status = filter_input(INPUT_GET, 'status');
            if (executeQuery("Update settings SET set_value = '$status' WHERE set_key = 'bulkStatus';")) {
                $lastStatus = ($status == 0) ? "not active" : "active";
                echo $lastStatus;
            } else {
                echo 0;
            }
            break;
        case 'bulkdelay':
            $value = filter_input(INPUT_GET, 'value');
            $value = $value == 0 ? 'zero' : $value;
            if (executeQuery("Update settings SET set_value = '$value' WHERE set_key = 'bulkDelay';")) {
                echo json_encode($value);
            } else {
                echo 0;
            }
            break;
        case 'printer':
            $id = filter_input(INPUT_GET, 'id');
            $value = filter_input(INPUT_GET, 'value');
            if (strlen($value) > 0) {
                $port = substr($value, -1);
                $printerLocation = "/dev/usb/lp$port";
//                echo $printerLocation;
                $query = "UPDATE kiosks SET kiosk_printer_location = '$printerLocation' WHERE kiosk_id = $id;";
                executeQuery($query);
            }
            break;
        case 'shortaudio':

            $value = filter_input(INPUT_GET, 'value');

            if (setSetting('audioShortBeep', $value)) {
                echo json_encode($value);
            } else {
                echo 0;
            }

            break;
        case 'feedback':

            $values = array();
            $values = getRequestVal('values', 0);

            if (count($values) > 0) {
                
                $valuesTxt = implode(',', $values);
                
                $score = array_sum($values)/count($values);
                $query = "INSERT INTO `feedback` 
                        (`fb1`, `fb2`, `fb3`, `fb4`, `fb5`, `feedback_score`, `feedback_date`) 
                        VALUES ($valuesTxt, $score, NOW());";

                if (executeQuery($query)) {
                    echo json_encode($score);
                } else {
                    echo 0;
                }
            } else {
                echo 0;
            }

            break;
        case 'sms_active':

            $id = getRequestVal('id', 0);
            $state = getRequestVal('state', 0);

            $value = $state > 0 ? 1 : 0;
            $query = "UPDATE sms_setting SET is_active = $value WHERE sms_id = $id;";

            if (executeMultiQuery($query)) {
                echo 1;
            } else {
                echo 0;
            }
            break;
        case 'sms_default':

            $id = getRequestVal('id', 0);
            if ($id > 0) {
                $finalQuery = "UPDATE sms_setting SET is_defualt = 0;";
                $finalQuery = $finalQuery . "UPDATE sms_setting SET is_defualt = 1 WHERE sms_id = $id;";

                if (executeMultiQuery($finalQuery)) {
                    echo 1;
                } else {
                    echo 0;
                }
            } else {
                echo 0;
            }

            break;
    }
} else {
    echo 0;
}
?>
