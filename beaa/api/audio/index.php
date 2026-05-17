<?php

error_reporting(0);
require_once("../db.php");

$arr = array();
$query = "SELECT audios_logs.log_id as 'logID', counters.counter_audio as 'audioID', MOD(events.event_no,1000) as 'eventNo',categories.category_char as 'eventChar',counters.counter_no as 'Counter'
			FROM audios_logs,events,categories,counters
			WHERE DATE(audios_logs.log_time)=DATE(NOW())
			
			AND log_seen=0
			AND audios_logs.log_event=events.event_id
			AND events.event_category=categories.category_id
			AND audios_logs.log_counter=counters.counter_id
			ORDER BY audios_logs.log_time ASC,events.event_no ASC LIMIT 1;";
if ($conn = mysqli_connect($dbhost, $dbusername, $dbpassword, $dbname)) {
    if ($result = mysqli_query($conn, $query)) {
        while ($row = mysqli_fetch_assoc($result)) {
            executeQuery("UPDATE audios_logs SET log_seen=1 WHERE log_id=" . $row['logID'] . ";");
            array_push($arr, $row);
        }
    }
}
echo json_encode($arr);
?>
