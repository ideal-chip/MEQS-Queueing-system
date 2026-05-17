<?php
error_reporting(0);
require_once('../language.php');
require_once('ticket.php');
$eventID = $_GET['event'];
$kioskID = $_GET['kiosk'];
$uploadsPath = "../uploads/";

if ($kioskInfo = getRow("SELECT * FROM kiosks WHERE kiosk_id=$kioskID;")) {
    $type = $kioskInfo['kiosk_printer_type'];
    $location = $kioskInfo['kiosk_printer_location'];
    $param = $kioskInfo['kiosk_printer_parameters'];
    if ($eventInfo = getRow("SELECT * FROM events WHERE event_id=$eventID;")) {
        $eventNo = $eventInfo['event_no'];
        $eventArrival = $eventInfo['event_time'];
        $eventCategory = $eventInfo['event_category'];
        $categoryChar = getValue("select category_char from categories where category_id=$eventCategory;");
        
        createTicket("tmp.png", $eventID);
        
        switch (strtoupper($type)) {
            case "TCP":
                $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                if (socket_connect($socket, $location, $param)) {
                    $msg = pngToRaster("tmp.png") . "\x0A\x0A\x0A\x1DV1\x1DV1";
                    if (socket_write($socket, $msg)) {
                        echo 1;
                    } else {
                        echo 0;
                    }
                    socket_close($socket);
                } else {
                    echo 0;
                }
                break;
            case "SERIAL":
                exec("stty -F $location $param");
                $fp = fopen($location, 'r+'); // Open Printer File for Read/Write
                fwrite($fp, chr(0x1B) . "@");
                //$msg=loadLogo($uploadsPath."logo.raw");
                $msg = fileToESC("\n\n\n*CENTER**NORMAL*Your Number\n*BIG**CATEGORYCHAR**EVENTNO*\n*NORMAL*Arrival Time\n\n*EVENTTIME*" . "\n\n\n\n\n\n\x1DV1");
                echo (fwrite($fp, $msg) ? 1 : 0);
                fclose($fp);
                break;
            case "USB":
                $os = php_uname('s');
                switch (substr(strtoupper($os), 0, 3)) {
                    case "WIN":
                        $fp = printer_open(""); //Open Default Printer
                        fwrite($fp, chr(0x1B) . "@");
                        $msg = pngToRaster("tmp.png") . "\x0A\x0A\x0A\x1DV1\x1DV1";
                        echo (printer_write($fp, $msg) ? 1 : 0);
                        printer_close($fp);
                        break;
                    case "LIN":
                        $fp = fopen($location, 'r+'); // Open Printer File for Read/Write
                        fwrite($fp, chr(0x1B) . "@");
                        //$msg = pngToRaster("tmp.png") . "\x0A\x0A\x0A\x0A\x0A\x0A\x1DV1";
                        $msg = pngToRaster("tmp.png") . "\x0A\x0A\x0A\x1DV1\x1DV1";
                        echo (fwrite($fp, $msg) ? 1 : 0);
                        fclose($fp);
                        break;
                    default:
                        echo 0;
                }
                break;
            default:
                echo 0;
                break;
        }
    } else {
        echo 'no event id!';
    }
} else {
    echo 'no kiosk id!';
}

function loadLogo($pathName) {
    $imgArray = file($pathname);
    $msg = "";
    $msg .= chr(27);
    $msg .= chr(51);
    $msg .= chr(0);
    for ($i = 0; $i < 3; $i++) {
        $msg .= chr(27);
        $msg .= chr(42);
        $msg .= chr(0); //m
        $msg .= chr(200); //nL
        $msg .= chr(0); //nH
        for ($j = 0; $j < 200; $j++) {
            $msg .= chr($imgArray[($i * 200) + $j]);
        }
        $msg .= chr(10); //Print Buffer
    }
    $msg .= chr(27);
    $msg .= chr(51);
    $msg .= chr(16);
    return $msg;
}

function fileToESC($msg) {
    global $eventNo, $eventArrival, $eventCategory, $categoryChar;
//$msg=file_get_contents($filePath);
//$imgData=jpgToRaster($logoFilePath);
    $msg = str_replace("*LOGO*", $imgData, $msg); //Load Image
    $msg = str_replace("*EVENTNO*", sprintf("%03d", $eventNo), $msg); //Event No
    $msg = str_replace("*CATEGORYCHAR*", $categoryChar, $msg); //Category Character
    $msg = str_replace("*EVENTTIME*", $eventArrival, $msg); //Event Arrival Time
    $msg = str_replace("*NORMAL*", "\x1D\x21\x00", $msg); //Normal Size Font
    $msg = str_replace("*BIG*", "\x1D\x21\x11", $msg); //Double Size Character
    $msg = str_replace("*BIG3*", "\x1D\x21\x22", $msg); //Tribble Size Character
    $msg = str_replace("*BIG4*", "\x1D\x21\x33", $msg); //Quadrable Size Character
    $msg = str_replace("*BIG5*", "\x1D\x21\x44", $msg); //Quintuple Size Character
    $msg = str_replace("*LEFT*", "\ea\x00", $msg); //Align Left
    $msg = str_replace("*CENTER*", "\ea\x01", $msg); //Align Center
    $msg = str_replace("*RIGHT*", "\ea\x02", $msg); //Align Right
    $msg = str_replace("*PARTIAL*", "\x1DV1", $msg); //Partial Cut
    return $msg;
}

function jpgToBin($jpgFilePath) {
    $size = getimagesize($jpgFilePath);
    $imgWidth = $size[0];
    $imgHeight = $size[1];
    $imgHeight8 = $imgHeight / 8;
    $imgWidthH = intval($imgWidth / 256);
    $imgWidthL = $imgWidth % 256;
    if ($img = imagecreatefromjpeg($jpgFilePath)) {
        $imgData = "";
        for ($y = 0; $y < $imgHeight8; $y++) {
            $printerCommand = chr(0x1B) . "*" . chr(1) . chr($imgWidthL) . chr($imgWidthH);
            $imgOctets = "";
            for ($x = 0; $x < $imgWidth; $x++) {
                $imgOctet = 0;
                for ($yy = 0; $yy < 8; $yy++) {
                    $imgOctet += pow(2, (7 - $yy)) * (imagecolorat($img, $x, ($y * 8) + $yy) ? 0 : 1);
                }
                $imgOctets .= chr($imgOctet);
            }
            $lineData = $printerCommand . $imgOctets . chr(0x1B) . "J" . chr(1);
            $imgData .= $lineData;
        }
        return $imgData;
    }
}

function pngToRaster($pngFilePath) {
    $size = getimagesize($pngFilePath);
    $imgWidth = $size[0];
    $imgWidth8 = $imgWidth / 8;
    $imgHeight = $size[1];
    $imgWidth8H = intval($imgWidth8 / 256);
    $imgWidth8L = $imgWidth8 % 256;
    $imgHeightH = intval($imgHeight / 256);
    $imgHeightL = $imgHeight % 256;
    if ($img = imagecreatefrompng($pngFilePath)) {
        $printerCommand = chr(0x1B) . "@" . chr(0x1D) . "v0" . chr(0) . chr($imgWidth8L) . chr($imgWidth8H) . chr($imgHeightL) . chr($imgHeightH);
        $imgOctets = "";
        for ($y = 0; $y < $imgHeight; $y++) {
            for ($x = 0; $x < $imgWidth8; $x++) {
                $imgOctet = 0;
                for ($xx = 0; $xx < 8; $xx++) {
                    $imgOctet += pow(2, (7 - $xx)) * (imagecolorat($img, ($x * 8) + $xx, $y) ? 1 : 0);
                }
                $imgOctets .= chr($imgOctet);
            }
        }
        $imgData = $printerCommand . $imgOctets;
        return $imgData;
    }
}
?>
