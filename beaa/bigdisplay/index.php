<?php

error_reporting(0);
require_once("../language.php");
require_once ("../enums.php");
$lang = isset($_GET['language']) ? $_GET['language'] : getSetting('defaultLanguage');
$filesPath = "../files/";
$uploadsPath = "../uploads/";
header("Access-Control-Allow-Origin: *");

$big_displayNum = $_GET['id'];

if (isset($big_displayNum) && $big_displayNum > 0) {
    $displayNumber = filter_input(INPUT_GET, strtolower('id'));
    $displayInfo = getRow("SELECT display_id, display_type, arrow_dir FROM bigdisplays WHERE display_number=$displayNumber;");

    if ($displayInfo) {
        $displayID = $displayInfo['display_id'];
        $arrowDir = $displayInfo['arrow_dir'];
        $bd_type = $displayInfo['display_type'];

        switch ($bd_type) {
            case 1:
                require_once("./latest.php");
                break;
            case 2:
                require_once("./bulk.php");
                break;
            case 3:
                require_once("./countercalls.php");
                break;
            case 4:
                require_once("./latestwating.php");
                break;
            default :
                echo 'type does NOT exits';
                break;
        }
    } else {
        echo 'Display does NOT exist!';
    }
} else {
    echo 'no display is selected!';
}
?>
