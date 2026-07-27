<?php

// settings
// active sending hours
date_default_timezone_set("Asia/Amman");
$hourStart = 8;
$hourEnd = 17;

// Gateway moved to the national bulk-SMS domain. The previous host
// (https://185.109.192.36) still answers on "/" but its API path now returns
// 404, which is why sending stopped working. Path and payload are unchanged.
$sendURL = "https://bulk-sms.gov.jo/index.php/api/send_sms/send";

//==================================================================|| Functions Requests

function sendCurl($post, $smsSetting) {

    global $sendURL;
    $user = $smsSetting['sms_username'];
    $password = $smsSetting['sms_password'];

    $fields = (is_array($post)) ? http_build_query($post) : $post;

    $header = array(
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Basic ' . base64_encode("$user:$password")
    );

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    curl_setopt($ch, CURLOPT_URL, $sendURL);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // for debugging...
    //  curl_setopt($ch, CURLOPT_HEADER, true);
    //  curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');

    $output = curl_exec($ch);

    if ($output === false) {
        // throw new Exception('Curl error: ' . curl_error($crl));
        print_r('Curl error: ' . curl_error($ch));
    }
    curl_close($ch);

    //var_dump($output);
    return $output;
}

//==================================================================|| Functions Messages
function checkResponse($response) {
    $json = json_decode($response, true);

    $status = $json['status'];
    $message = $json['message'];

    $res1 = strpos($message, 'I01-Job');
    $res2 = strpos($message, 'I02-Job');

    if ($status == 201 && ($res1 !== false || $res2 !== false)) {
        return true;
    }

    return false;
}

function createMsg($serialNo) {
    $t1 = getTextValue('smsText1', 'ar');
    $t2 = getTextValue('smsText2', 'ar');
    $t3 = getTextValue('smsText3', 'ar');

    $msg = "$t2" . " ($serialNo) - " . $t3 . " - $t1";
    return $msg;
}

function getAllMsgs($array) {
    $txt = '';
    for ($index = 0; $index < count($array); $index++) {
        $txt = $txt . $array[$index];
        if ($index != count($array) - 1) {
            $txt = $txt . ",";
        }
    }
    return $txt;
}

function CreatePost($Row, $smsSenderID) {

    $post = array();
    $post['mobile_number'] = $Row['mobile_number'];
    $post['msg'] = createMsg($Row['serial_no']);
    $post['from'] = "$smsSenderID";
    $post['tag'] = 1;
    //$post['dlr'] = 1;

    return $post;
}

function SuccessMsg($Row) {
    $serial = $Row['serial_no'];
    $name = $Row['client_name'];
    $number = $Row['mobile_number'];
    return "Success,[$serial] message sent for $name with number $number";
}

function FailMsg($Row) {
    $serial = $Row['serial_no'];
    $name = $Row['client_name'];
    $number = $Row['mobile_number'];
    return "Failed,[$serial] message was NOT sent for $name with number $number";
}

function SendSMS($Row, $smsSenderID, $smsSetting) {
    // create msg - fill post query
    $postBody = CreatePost($Row, $smsSenderID);
    //var_dump($postBody);
    // send it
    $response = sendCurl($postBody, $smsSetting);

    // check response
    if (checkResponse($response)) {

        echo SuccessMsg($Row) . "<br>";

        $id = $Row['followup_id'];
        // update db
        $res = SetCardSent($id);
    } else {
        echo FailMsg($Row) . "<br>";
    }
}

function CheckValidDate() {

    global $hourStart, $hourEnd;
    // get current hour to compare with
    $currentTime = (int)date('H');
    $dayName = strtolower(date("l"));

    $hour = $currentTime >= $hourStart && $currentTime <= $hourEnd;
    $day = $dayName != "saturday" && $dayName != "friday";

//    var_dump($hourStart);
//    var_dump($hourEnd);
//    var_dump($currentTime);
//
//    var_dump($hour);
//    var_dump($day);

    if ($hour && $day) {
        return true;
    }

    return false;
}
