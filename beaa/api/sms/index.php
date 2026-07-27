<?php

error_reporting(0);
require_once("../../language.php");
require_once './queries.php';
require_once './methods.php';

        
if (CheckValidDate()) {

    $smsSetting = GetSmsSettings();
    $cards = GetCards();

    $smsSenderID = $smsSetting['sender_name'];

    if ($smsSetting['is_active'] == 1) {

        if (count($cards) > 0) {
            foreach ($cards as $Row) {
                SendSMS($Row, $smsSenderID, $smsSetting);
                //var_dump($Row);
            }
        } else {
            echo "Nothing to send!";
        }
    }
} else {
    echo "Outside sending hours";
}




