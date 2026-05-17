<?php
//error_reporting(0);
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
$title = "not autherized";
$dir = trim(getTextValue('dir', $lang));

if (!isset($_SESSION['username'])) {
    header("location: ./account/login.php");
    exit();
} else {
    $username = $_SESSION['username'];
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include_once('./common/head.php'); ?>

    </head>
    <body class="" >
        <?php include_once('./common/nav.php'); ?>

        <div class="container">
            <!--<h4 class="border-btm-blue pad-v-5 text-left">Screen <span class="glyphicon glyphicon-th-large"></span></h4>-->
            <div class="relative pad-10">
                <div class="shade-upper">
                    <div class=" alert alert-danger  center-block s-25 font-lg text-white marg-v-50">
                        NOT Autherized!
                        <li class="fa fa-warning"></li>
                    </div>
                </div>
                <div class="shade bg-red"></div>
            </div>
        </div>

        <?php include_once('./common/footer.php'); ?>
        <?php include_once('./common/foot_scripts.php'); ?>

    </body>
</html>
