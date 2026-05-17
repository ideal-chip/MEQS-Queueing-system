
<?php
error_reporting(0);
require_once("../language.php");
$lang = isset($_GET['language']) ? $_GET['language'] : getSetting('defaultLanguage');
$filesPath = "../files/";
$uploadsPath = "../uploads/";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset = "UTF-8">
        <title><?php echo getTextValue('bulk', $lang); ?></title>
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" type="text/css" href="../css/bulkcall.css">
        <link rel="shortcut icon" href="icon.png"/>
    </head>
    <body>
        <div class="well well-sm center-block">
            <div class="line-bulk">
                <div class="inline-block marg3 left" id="bulk-active"> ... </div>
                <div class="inline-block marg3 left">
                    <button  id="blk-start" onclick="setBulkStatus(1);" class="btn btn-xs btn-danger arc-top"><?php echo getTextValue('startBulkCalling', $lang) ?></button>
                    <button style="display: none;" id="blk-stop" onclick="setBulkStatus(0);" class="btn btn-xs btn-primary arc-top"><?php echo getTextValue('stopBulkCalling', $lang) ?></button>
                </div>

            </div>
        </div>
        <script src="../js/jquery-3.1.1.min.js" type="text/javascript"></script>
        <script type="text/javascript">
                        //==============================================================  | BUlK calling start/ stop
                        var isBulkActive = 0;
                        var isBulkInctive = 0;

                        setInterval(function () {
                            checkStatus();
                        }, 1000);

                        var checkBulkStatusXML = new XMLHttpRequest();
                        function checkStatus() {
                            checkBulkStatusXML.open('GET', '../api/checkupdate.php?id=1&type=bulkstatus');
                            checkBulkStatusXML.send();
                        }

                        checkBulkStatusXML.onreadystatechange = function () {
                            if (checkBulkStatusXML.readyState == 4 && checkBulkStatusXML.status == 200) {
                                val = parseInt(checkBulkStatusXML.responseText);
                                //alert(val);
                                if (val === 1) {
                                    if (!isBulkActive) {
                                        $('#blk-start').hide();
                                        $('#blk-stop').show();
                                        document.getElementById('bulk-active').innerHTML = '<?php echo getTextValue('active', $lang) ?>';
                                        isBulkActive = 1;
                                        isBulkInctive = 0;
                                    }

                                } else {
                                    if (!isBulkInctive) {
                                        $('#blk-start').show();
                                        $('#blk-stop').hide();
                                        document.getElementById('bulk-active').innerHTML = '<?php echo getTextValue('inactive', $lang) ?>';

                                        isBulkActive = 0;
                                        isBulkInctive = 1;
                                    }

                                }

                            }
                        };

                        var bulkStatusXML = new XMLHttpRequest();

                        function setBulkStatus(status) {
                            bulkStatusXML.open("GET", "../api/update.php?id=1&status=" + status + "&type=bulkstatus", true);
                            bulkStatusXML.send();
                        }

                        bulkStatusXML.onreadystatechange = function () {
                            if (bulkStatusXML.status == 200 && bulkStatusXML.readyState == 4)
                            {
                                response = bulkStatusXML.responseText.trim();
                                if (response == 'active')
                                {
                                    //document.getElementById('bulk-active').innerHTML = 'active';
                                    //alert('active bulk-active');
                                    //location.reload();
                                } else if (response == 'not active')
                                {
                                    //document.getElementById('bulk-active').innerHTML = 'not active';
                                    //alert('NOT active');
                                    //location.reload();
                                }
                            }

                        };

        </script>
        <footer>
            <div class="row">
                <p class="text-center blue"> iDEAL-Q ® Queue Management System  <span class="ftr-logo-con"><a href="http:\\www.idealchip.com" title="idealchip website" target="_blank"><img src="../files/logos/logo-ideal.ico" alt=""></a></span> Idealchip Electronics, Inc.  &copy; 1997-<?php echo date("Y") ?></p>
            </div>
        </footer>

    </body>
</html> 

