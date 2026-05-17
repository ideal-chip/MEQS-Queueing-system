<?php

require_once __DIR__ . "/api/db.php";

$genders = array(1 => 'female', 2 => 'male');
$KioskPrinterTypes = array(1 => 'TCP', 2 => 'SERIAL', 3 => 'USB');

function getTextValue($key, $lang) {
    $ret = getValue("SELECT IFNULL(text_value,'$key') FROM texts WHERE text_key='$key' AND text_language='$lang';");
    if ($ret)
        return $ret;
    else
        return $key;
}

function getTextsObj($arr, $lang) {
    $items = json_encode($arr);
    $items = str_replace("[", "", $items);
    $items = str_replace("]", "", $items);
    $items = str_replace("\"", "'", $items);

    $query = "SELECT text_key, text_value FROM texts WHERE text_key IN ($items) AND text_language='$lang';";
    $resultAll = getArrayAssoc($query);
    if ($resultAll) {

        $result = getKeyValArray($resultAll, "text_key", "text_value");
        $object = (object) $result;
        return $object;
    } else {
        return NULL;
    }
}

function getSetting($key) {
    $ret = getValue("SELECT IFNULL(set_value,'$key') FROM settings WHERE set_key='$key';");
    if ($ret)
        return $ret;
    else
        return $key;
}

function setSetting($key, $newVal) {
    if (executeQuery("UPDATE settings SET set_value= '$newVal' WHERE set_key = '$key';")) {
        return 1;
    } else {
        return 0;
    }
}

function getEventDigitMod() {
    $eventMod = '';
    switch (getSetting('displayDigits')) {
        case '4':
            $eventMod = 'MOD(event_no,10000),4';
            break;
        case '3':
            $eventMod = 'MOD(event_no,1000),3';
            break;
        default :
            $eventMod = 'MOD(event_no,1000),3';
            break;
    }
    return $eventMod;
}

function showPriority($num, $size) {
    return ($num != 0) ? $size - $num + 1 : 0;
}

function getRequestVal($var, $defualtval = '', $type = 'get') {
    $value = '';

    if ($type == 'post') {
        if (isset($_POST["$var"]) && !empty($_POST["$var"])) {
            $value = $_POST["$var"];
        } else {
            $value = $defualtval;
        }
    } else {
        if (isset($_GET["$var"]) && !empty($_GET["$var"])) {
            $value = $_GET["$var"];
        } else {
            $value = $defualtval;
        }
    }

    return $value;
}

function createLink($name, $page, $icon = '', $title = '', $class = '', $attr = '') {
    $page = $page . ".php";
    $iconFull = (!empty($icon)) ? "<i class='$icon'></i>" : '';
    echo "<a href='$page' title='$title' class='$class' $attr > $name $iconFull</a>";
}

?>
