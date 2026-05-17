<?php

require_once('./I18N/Arabic.php');
require_once('db.php');
$Arabic = new I18N_Arabic('Glyphs');

function getShortText($txt, $size) {
    return strlen($txt) > $size ? substr($txt, 0, $size) . "..." : $txt;
}

function createTicket($fn, $eventID) {
    putenv('GDFONTPATH=' . realpath('.'));
    $font = '../fonts/tahoma.ttf';
    $im = imagecreate(552, 550); //was 400x450 for 57mm paper roll,552x450 for 80mm
    $white = imagecolorallocate($im, 255, 255, 255);
    $black = imagecolorallocate($im, 0, 0, 0);
    $img = imagecreatefrompng("../uploads/head.png");
    imagecopy($im, $img, 0, 0, 0, 0, 552, 200);
    $event = getRow("SELECT category_id, CONCAT(category_char,LPAD(MOD(event_no,1000),3,'0')) as 'eventNo',event_time as 'arriveTime', category_key FROM events,categories WHERE event_category=category_id AND event_id=$eventID;");
    if ($event['category_id'] == 6) {
        $categoryNameAr = 'أخرى';
        $categoryNameEn = 'Others';
    } else {
        $categoryNameAr = getShortText(trim(getTextValue($event['category_key'], 'ar')), 50);
        $categoryNameEn = getShortText(getTextValue($event['category_key'], 'en'), 50);
    }

//    $categoryNameAr = getShortText(trim(getTextValue($event['category_key'], 'ar')), 40);
//    $categoryNameEn = getShortText(getTextValue($event['category_key'], 'en'), 40);

    $currentY = 200;
    $currentY = printText($im, 22, $font, $black, 200, $categoryNameAr) + $currentY + 20;
    $currentY = printText($im, 22, $font, $black, $currentY, $categoryNameEn) + $currentY + 20;
    $currentY = printText($im, 22, $font, $black, $currentY, '------------------------------------------') + $currentY + 5;
    $currentY = printText($im, 16, $font, $black, $currentY, 'رقم البطاقة') + $currentY + 20;
    $currentY = printText($im, 100, $font, $black, $currentY, $event['eventNo'], 1) + $currentY + 20;
    $currentY = printText($im, 22, $font, $black, $currentY, '------------------------------------------') + $currentY + 5;
    $currentY = printText($im, 16, $font, $black, $currentY, 'وقت الوصول') + $currentY + 20;
    $currentY = printText($im, 18, $font, $black, $currentY, $event['arriveTime'], 1) + $currentY + 20;

    imagepng($im, $fn);
    imagedestroy($im);
}

function printText($image, $size, $font, $color, $top, $text, $en = 0) {
    global $Arabic;
    if ($en) {
        $txt = $text;
    } else {
        $txt = $Arabic->utf8Glyphs($text);
    }

    $arr = imagettfbbox($size, 0, $font, $txt);
    $textWidth = $arr[2] - $arr[0];
    $textHeight = $arr[1] - $arr[5];
    imagettftext($image, $size, 0, 276 - ($textWidth / 2), $top + $textHeight, $color, $font, $txt);
    return $textHeight;
}

?>
