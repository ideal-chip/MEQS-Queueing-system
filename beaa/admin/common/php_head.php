<?php
error_reporting(0);
session_start();

//-------------------------------------------------------------< includes >---
require_once("../language.php");

//-------------------------------------------------------------< user info >---
$username = $_SESSION['username'];

//-----------------------------------------< lang >-------
$languages = getColumn("SELECT DISTINCT text_language FROM texts;");

$lang = getSetting('defaultLanguage');

if (isset($_GET['language'])) {
    
    $lang = $_GET['language'];
    $_SESSION['language'] = $lang;
    
}else if(isset($_SESSION['language'])){
    $lang = $_SESSION['language'];
}
//-------------------------------------------------------------< main vars >---

$filesPath = "../files/";

$dir = trim(getTextValue('dir', $lang));
//-------------------------------------------------------------< auth checks >---

//$prev = 8;
if (!isset($_SESSION['username'])) {
    header("location: ./account/login.php");
    exit();
} else if (!($_SESSION['userpriv'] & $prev)) {
    header("location: ./noauth.php");
    exit();
}




