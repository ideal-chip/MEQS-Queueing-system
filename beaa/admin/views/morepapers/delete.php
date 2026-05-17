<?php
error_reporting(0);

require_once("../../../language.php");
$lang = isset($_SESSION['language']) ? $_SESSION['language'] : getSetting('defaultLanguage');

$uploadsPath = "../../../uploads/pdf/";
if (isset($_GET['fn'])) {
    if (unlink($uploadsPath . $_GET['fn'])) {
        echo json_encode('OK');
    } else {
        echo json_encode(getTextValue("errorDelete", $lang));
    }
} else {
    echo json_encode(getTextValue("errorDelete", $lang));
}

?>
