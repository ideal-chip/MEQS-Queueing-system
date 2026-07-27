<?php

// settings
// active sending hours
date_default_timezone_set("Asia/Amman");
$hourStart = 8;
$hourEnd = 17;

// The gateway moved from ArabiaCell to the national A2A Messaging Platform
// (bulk-sms.gov.jo). That is not just a new address -- it is a different
// protocol. The old one was a single POST with HTTP Basic auth and form
// fields; A2A is two steps:
//
//   1. POST {host}/authenticate         {"username":..,"password":..}
//      -> {"token":"Bearer eyJ..."}     (valid for ONE MINUTE only)
//   2. POST {host}/sendSmsNotifications  Authorization: <that token>
//      -> {"<code>":"mobile number[9627...] messagesId[55886834]"}
//
// Both are application/json. The old ArabiaCell endpoint is dead (404 on its
// API path), so the old flow could never have been made to work by changing
// the URL alone.
$smsHost = "https://bulk-sms.gov.jo";
$authURL = $smsHost . "/authenticate";
$sendURL = $smsHost . "/sendSmsNotifications";

// A2A requires the international format (962XXXXXXXXX). The followups table
// holds locally-typed numbers ("0796188021"), and a few malformed ones.
$smsMessageTypeId = 3;

//==================================================================|| Functions Requests

/**
 * Normalise a Jordanian mobile number to the international MSISDN format
 * A2A expects (962 7X XXX XXXX). Returns '' when the number cannot be
 * salvaged, so the caller can skip it instead of sending it into the void.
 */
function normalizeMsisdn($raw) {
    $digits = preg_replace('/\D+/', '', (string) $raw);

    if (strpos($digits, '00962') === 0) {
        $digits = substr($digits, 2);
    }
    if (strpos($digits, '962') === 0) {
        $local = substr($digits, 3);
    } elseif (strpos($digits, '0') === 0) {
        $local = substr($digits, 1);
    } else {
        $local = $digits;
    }

    // Jordanian mobiles are 9 digits after the country code and start with 7.
    if (strlen($local) !== 9 || $local[0] !== '7') {
        return '';
    }
    return '962' . $local;
}

/**
 * Step 1: exchange the stored username/password for a bearer token.
 * The token lives for one minute, so it is cached only for the duration of a
 * single cron run and re-fetched once it is close to expiring.
 */
function a2aToken($smsSetting, $force = false) {
    global $authURL;
    static $cached = '';
    static $fetchedAt = 0;

    // Re-authenticate with 15s of headroom before the 60s expiry.
    if (!$force && $cached !== '' && (time() - $fetchedAt) < 45) {
        return $cached;
    }

    $body = json_encode(array(
        'username' => $smsSetting['sms_username'],
        'password' => $smsSetting['sms_password'],
    ));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $authURL);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $out = curl_exec($ch);
    if ($out === false) {
        print_r('Curl error (authenticate): ' . curl_error($ch) . "<br>");
    }
    curl_close($ch);

    $json = json_decode($out, true);
    if (!is_array($json) || empty($json['token'])) {
        // Surface the gateway's own wording (e.g. "Invalid Credentials").
        print_r('SMS auth failed: ' . trim((string) $out) . "<br>");
        $cached = '';
        return '';
    }

    $cached = $json['token'];
    $fetchedAt = time();
    return $cached;
}

/**
 * Step 2: send one message. Keeps the original name/signature so index.php
 * and SendSMS() are unchanged.
 */
function sendCurl($post, $smsSetting) {

    global $sendURL, $smsMessageTypeId;

    $msisdn = normalizeMsisdn($post['mobile_number']);
    if ($msisdn === '') {
        return json_encode(array('error' => 'invalid mobile number [' . $post['mobile_number'] . ']'));
    }

    $token = a2aToken($smsSetting);
    if ($token === '') {
        return json_encode(array('error' => 'authentication failed'));
    }

    $payload = json_encode(array(
        'data1' => array(
            'msisdn'        => $msisdn,
            'text'          => $post['msg'],
            'header'        => (string) $post['from'],
            'messageTypeId' => $smsMessageTypeId,
        ),
    ), JSON_UNESCAPED_UNICODE);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sendURL);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: ' . $token,
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $output = curl_exec($ch);

    if ($output === false) {
        print_r('Curl error: ' . curl_error($ch) . "<br>");
    }
    curl_close($ch);

    return $output;
}

//==================================================================|| Functions Messages
/**
 * A2A answers with a single {"<status code>": "<descriptive result>"} pair.
 * A delivered message carries a message id, e.g.
 *   {"s001":"mobile number[962798495860] messagesId[55886834]"}
 * whereas every failure path (bad credentials, bad number, quota) returns a
 * descriptive string with no messagesId. Treating "has a messagesId" as the
 * success signal therefore works without hard-coding the status codes -- and
 * it is what stops a failed send from being marked as sent in followups.
 */
function checkResponse($response) {
    if (!is_string($response) || $response === '') {
        return false;
    }

    $json = json_decode($response, true);
    $text = is_array($json) ? implode(' ', array_map('strval', $json)) : $response;

    return stripos($text, 'messagesId[') !== false;
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
