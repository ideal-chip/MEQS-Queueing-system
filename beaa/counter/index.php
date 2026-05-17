<?php
header("Access-Control-Allow-Origin: *");
error_reporting(0);
require_once("../language.php");
$lang = isset($_GET['language']) ? $_GET['language'] : getSetting('defaultLanguage');
$filesPath = "../files/";
$uploadsPath = "../uploads/";
session_start();
if (isset($_GET['id']) && $_GET['id'] > 0) {
    $counterNo = $_GET['id'];
    $counterID = getValue("SELECT counter_id FROM counters WHERE counter_no=$counterNo;");
    if ($counterID > 0) {
        $zoneID = getValue("SELECT counter_zone FROM counters WHERE counter_id=$counterID;");
        

        if (isset($_SESSION['clerkID'])) {

//            $clerkID = $_SESSION['clerkID'];
            $sessionCounter = $_SESSION['counterID'];
            $Status = getRow("SELECT counter_id, counter_no, ip_address FROM counters WHERE counter_id = $sessionCounter;");
            $currentCounterNo = $Status['counter_no'];

            if ($currentCounterNo != $counterNo) {
                header("Location: ./?id=$currentCounterNo");
            }

            if (isset($_GET['new'])) {
                require_once("./counter.php");
            }else{
                require_once("./main.php");
            }
            
        } else {
            require_once("./login.php");
        }
    } else {
        echo "Error: [ $counterNo ] " . getTextValue("errorCounterDoesntExist", $lang);
    }
} else {
    echo "No Counter Selected";
}
?>
