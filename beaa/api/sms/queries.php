<?php

function GetSmsSettings() {
    $res = getRow("SELECT * FROM sms_setting WHERE is_default  = 1;");
    return $res;
}

function GetCards() {
    $query = "SELECT * FROM followups
            WHERE 
            date_done IS NOT NULL 
            AND date_sms_sent IS NULL
            AND date_done >= CURDATE()
            GROUP BY mobile_number
            ORDER BY date_done
            LIMIT 20;";

    $res = getArrayAssoc($query);

    return $res;
}

function SetCardSent($id) {
    $query = "UPDATE followups SET date_sms_sent = NOW() WHERE followup_id = $id;";
    $res = executeQuery($query);
    
    return $res;
}
