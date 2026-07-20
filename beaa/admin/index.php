<?php
//error_reporting(0);
session_start();
//-------------------------------------------------------------< includes >---
require_once("../router.php"); // Auto-fix URLs
require_once("../language.php");

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

$filesPath = defined('FILES_PATH') ? FILES_PATH : "../files";
$dir = trim(getTextValue('dir', $lang));

if (!isset($_SESSION['username'])) {
    header("location: ./account/login.php");
    exit();
} else {
    $username = $_SESSION['username'];
}

//-------------------------------------------------------------< other includes >---
//-------------------------------------------------------------< common vars >---
$title = getTextValue('mainPage', $lang);
$parent = "";

$serverTime = date('d/m/Y H:i:s');

//-------------------------------------------------------------< data >---
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include_once './common/head.php'; ?>

    </head>
    <body style="direction:<?php echo $dir ?>;">
        <?php include_once './common/nav.php'; ?>
        <?php include_once './common/header.php'; ?>

        <div class="container-full marg-bottom-50">
            <div class="pad-v-20">
                <img src="../files/logos/systemlogo-md.png" alt=""/>
            </div>
            <h2 class="well well-sm"><?php echo getTextValue("hello", $lang) . ", $username" ?> </h2>
            <div class="well well-sm">
                <div>server time : <span class="badge" id="s-time"><?php echo $serverTime; ?></span></div>
                <div>client time: <span class="badge" id="c-time"></span></div>
                <!--<div><button class="btn btn-danger">sync time</button></div>-->
            </div>
        </div>
        <?php include_once './common/footer.php'; ?>

        <?php include_once './common/foot_scripts.php'; ?>
        <script type="text/javascript">
            $('document').ready(function () {

                var datetime = getDate() + " " + getTime();
                $('#c-time').text(datetime);
            });
        </script>
    </body>
</html>
