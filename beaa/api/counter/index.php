<?php

error_reporting(0);
require_once("../../language.php");
require_once("../db.php");
if (isset($_GET['op'])) {
    $op = $_GET['op'];

    $eventMod = getEventDigitMod();

    function updateDisplaySerial($msg, $displayNo, $close = 0, $priority = 0) {
        $location = '/dev/ttyUSB0';
        $retval = 0;
    
        $os = php_uname('s');
        switch (substr(strtoupper($os), 0, 3)) {
            case "WIN":
                break;
            case "LIN":
                exec("stty -F $location cs8 19200 -cstopb -parenb");
    
                $fp = fopen($location, 'r+');
    
                if (!$fp) {
                    error_log("Failed to open serial port at $location");
                    return 0;
                }
    
                $fullMsg = getFullMessage($msg, $displayNo, $close, $priority);
    
                $binaryData = pack('H*', $fullMsg);
    
                $retval = (fwrite($fp, $binaryData) ? 1 : 0);
                if (!$retval) {
                    error_log("Failed to write to serial port at $location");
                }
                sleep(1);
    
                fclose($fp);
                break;
        }
        return $retval;
    }
    
    

    function getFullMessage($msg, $displayNo, $close, $priority) {
        $charColor = '01';
        $numColor = '02';
    
        $fullMessage = '';
        
        if ($close > 0) {
            $fullMessage = "38FD0000000000" . str_pad(dechex($displayNo), 2, "0", STR_PAD_LEFT);
        } else {
            $numbers = $msg;
            
            $hexNumbers = '';
            for ($i = 0; $i < strlen($numbers); $i++) {
                $hexNumbers .= strtoupper(dechex(ord($numbers[$i])));
            }
    
            $colorCode = $charColor . $numColor;
            
            $fullMessage = "" . $hexNumbers . $colorCode . str_pad(dechex($displayNo), 2, "0", STR_PAD_LEFT);
        }
    
        return $fullMessage;
    }
    

    function clearCounter($counterID, $displayID, $zoneID) {
        $displayType = strtolower(getSetting('displayType'));
        switch ($displayType) {
            case 'serial':
                //================================================|| Serial                           
                updateDisplaySerial('closed', $displayID, 1);

                break;
            case 'tcp':
                //================================================|| Ethernet and logs                            
                executeQuery("INSERT INTO displays_logs(log_event,log_counter,log_display,log_zone) VALUES(0,$counterID,$displayID,$zoneID);");
                break;
            default :
                break;
        }
    }

    switch ($op) {
        case 1://Call
            $counterID = $_GET['counter'];
//            $clerkID = $_GET['clerk'];
            $zone = getValue("select counter_zone from counters where counter_id=$counterID;");

            $eventObj = getRow("SELECT event_id AS 'eventID',category_char AS 'eventChar',LPAD($eventMod,'0') AS 'eventNo',event_time AS 'eventTime',event_category AS 'eventCategory',event_priority
								FROM events,categories,countercategories
								WHERE event_zone=$zone
								AND event_level= 0
								AND cc_counter=$counterID
								AND event_category IN (SELECT cc_category FROM countercategories WHERE cc_counter=$counterID AND cc_enabled = 1)
								AND 
                                                                    (event_id IN (SELECT transfer_event FROM transfers WHERE transfer_done = 1 AND DATE(transfer_time)=DATE(NOW()))
                                                                    OR 
                                                                    event_id NOT IN (SELECT transfer_event FROM transfers WHERE transfer_done = 0 AND DATE(transfer_time)=DATE(NOW())))
								AND DATE(event_time)=DATE(NOW())
								AND event_category=category_id
								AND event_category=cc_category
								UNION
								SELECT transfer_event AS 'eventID',category_char AS 'eventChar',LPAD($eventMod,'0') AS 'eventNo',event_time AS 'eventTime',event_category AS 'eventCategory',event_priority
								FROM transfers,events,categories,countercategories
								WHERE transfer_zone=$zone
                                                                AND transfer_done = 0
								AND event_level = 1
								AND cc_counter=$counterID
								AND (transfer_new_counter=$counterID OR transfer_new_category IN (SELECT cc_category FROM countercategories WHERE cc_counter=$counterID AND cc_enabled = 1))
								AND DATE(transfer_time)=DATE(NOW())
								AND event_category=category_id
								AND event_id=transfer_event
								ORDER BY event_priority DESC,eventTime, eventID LIMIT 1;");
            if ($eventObj) {
                $ticketNumber = $eventObj['eventChar'] . $eventObj['eventNo'];
                $displayID = getValue("SELECT counter_display FROM counters WHERE counter_id = $counterID;");
                updateDisplaySerial($ticketNumber, $displayID, 0, $eventObj['event_priority']); 
                echo json_encode($eventObj);
            } else {
                echo 0;
            }
            break;
        case 2: //Recall
            $counterID = filter_input(INPUT_GET, 'counter');
            $clerkID = filter_input(INPUT_GET, 'clerk');
            $eventID = filter_input(INPUT_GET, 'event');
            $type = filter_input(INPUT_GET, 'type');

            $row = getRow("select counter_zone,counter_display,counter_audio from counters where counter_id=$counterID;");

            $zone = $row['counter_zone'];
            $displayID = $row['counter_display'];
            $audioID = $row['counter_audio'];
//================================================================================||  Update Transfers

            if ($row = getRow("SELECT * FROM transfers WHERE transfer_event=$eventID AND transfer_done = 0;")) {
                $transferID = $row['transfer_id'];
                $newcategory = $row['transfer_new_category'];
                $newCounter = $row['transfer_new_counter'];

                if (!is_null($newcategory)) {
                    $counterCat = getRow("SELECT * FROM countercategories WHERE cc_category=$newcategory;");
                    $requestedLevel = $counterCat['cc_requested_level'];
                }
                if ((!is_null($newCounter)) && ($newCounter == $counterID)) {
                    $counterCat = getRow("SELECT * FROM countercategories,events WHERE cc_category=event_category AND event_id=$eventID;");
                    $requestedLevel = $counterCat['cc_requested_level'];
                }
                $newLevel = $counterCat['cc_next_level'];
                if (executeQuery("UPDATE transfers set transfer_done = 1 WHERE transfer_id=$transferID;")) {
                    
                } else {
                    
                }
            } else {

                // set new level
                $newLevel = 1;
            }

//================================================================================||  Update Logs

            if (executeQuery("UPDATE events set event_level=$newLevel WHERE event_id=$eventID;")) {

                $ip_address = "";
                if (isset($_SERVER['REMOTE_ADDR'])) {
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                }
                $logType = 3;
                if ($type == 1) {
                    $logType = 2;
                }

                // log audio [normal / bigdisplays] / eventlogs / displays [based on type]
//================================================================================||  Update Audios
                if (executeMultiQuery("INSERT INTO events_logs(log_type,log_event,log_clerk,log_counter,log_zone, log_ip_address) VALUES($logType,$eventID,$clerkID,$counterID,$zone,'$ip_address');")) {

//==================================// log audio once
                    executeQuery("INSERT INTO audios_logs(log_event,log_counter,log_audio,log_seen,log_zone, bd_id) VALUES
                            ($eventID,$counterID,$audioID,0,$zone,0);");

//==================================// log audio for bigdisplays type 1 - latest calls
//                    $latestDisplays = getColumn("SELECT bdc_bigdisplay FROM bigdisplayscounters, bigdisplays WHERE bdc_counter = $counterID AND bdc_bigdisplay = display_id AND display_type IN (1, 4)");
//                    if (count($latestDisplays) > 0) {
//                        foreach ($latestDisplays as $dis) {
//                            executeQuery("INSERT INTO audios_logs(log_event,log_counter,log_audio,log_seen,log_zone, bd_id) VALUES($eventID,$counterID,$audioID,0,$zone," . $dis['display_id'] . ");");
//                        }
//                    }
//==================================// log audio for bigdisplays type 3 - counter calls
//                    $counterDisplays = getColumn("SELECT bd_id FROM bigdisplayforcounter, bigdisplays
//                            WHERE counter_id = $counterID
//                            AND bigdisplays.display_id = bigdisplayforcounter.bd_id 
//                            AND display_type IN (3);");
//                    if (count($counterDisplays) > 0) {
//                        foreach ($counterDisplays as $dis) {
//                            executeQuery("INSERT INTO audios_logs(log_event,log_counter,log_audio,log_seen,log_zone, bd_id) VALUES($eventID,$counterID,$audioID,0,$zone," . $dis['bd_id'] . ");");
//                        }
//                    }
//================================================================================||  Update counter Displays
                    $displayType = strtolower(getSetting('displayType'));
                    switch ($displayType) {
                        case 'serial':
                            //================================================|| Serial                                                     
                            $ticketInfo = getRow("SELECT CONCAT(category_char, LPAD($eventMod,'0')) AS 'ticket',
                                                    event_priority 
                                                    FROM categories,events WHERE event_id=$eventID
                                                    AND event_category=category_id;");
                            $ticket = $ticketInfo['ticket'];
                            $ticketPriority = $ticketInfo['event_priority'];

                            updateDisplaySerial($ticket, $displayID, 0, $ticketPriority);

                            break;
                        case 'tcp':
                            //================================================|| Ethernet and logs                            
                            executeQuery("INSERT INTO displays_logs
                                            (log_event,log_counter,log_display,log_zone)
                                            VALUES($eventID,$counterID,$displayID,$zone);");

                            break;
                        default :
                            break;
                    }

                    echo 1;
                } else {
                    echo 0;
                }
            } else {
                echo 0;
            }
            break;
        case 3: //Transfer

            $counterID = filter_input(INPUT_GET, 'counter');
            $clerkID = filter_input(INPUT_GET, 'clerk');
            $eventID = filter_input(INPUT_GET, 'event');
            $counterRow = getRow("SELECT counter_zone, counter_display FROM counters WHERE counter_id=$counterID;");
            $zoneID = $counterRow['counter_zone'];
            $displayID = $counterRow['counter_display'];

            if (isset($_GET['tocounter']) && $_GET['tocounter'] > 0) {
                $toCounter = filter_input(INPUT_GET, 'tocounter');
                if (executeQuery("INSERT INTO transfers("
                                . "transfer_time,transfer_event,transfer_counter,transfer_clerk,transfer_new_counter,transfer_zone, transfer_done) "
                                . "VALUES(NOW(),$eventID,$counterID,$clerkID,$toCounter,$zoneID, 0);")) {
                    clearCounter($counterID, $displayID, $zoneID);
                    exit("1");
                } else {
                    exit("0");
                }
            }
            if (isset($_GET['tocategory']) && $_GET['tocategory'] > 0) {
                $toCategory = filter_input(INPUT_GET, 'tocategory');
                if (executeQuery("INSERT INTO transfers("
                                . "transfer_time,transfer_event,transfer_counter,transfer_clerk,transfer_new_category,transfer_zone, transfer_done) "
                                . "VALUES(NOW(),$eventID,$counterID,$clerkID,$toCategory,$zoneID, 0);")) {
                    clearCounter($counterID, $displayID, $zoneID);
                    exit("1");
                } else {
                    exit("0");
                }
            }
            break;
        case 4: //Refresh Data
            $counterID = $_GET['counter'];
            $clerkID = $_GET['clerk'];
            $zone = getValue("select counter_zone from counters where counter_id=$counterID;");

            $query = "SELECT event_id, event_priority, event_time
                        FROM events,categories,countercategories
                            WHERE event_zone=$zone
                            AND event_level= 0
                            AND cc_counter=$counterID
                            AND event_category IN (SELECT cc_category FROM countercategories WHERE cc_counter=$counterID AND cc_enabled = 1)
                            AND 
                                (event_id IN (SELECT transfer_event FROM transfers WHERE transfer_done = 1 AND DATE(transfer_time)=DATE(NOW()))
                                OR 
                                event_id NOT IN (SELECT transfer_event FROM transfers WHERE transfer_done = 0 AND DATE(transfer_time)=DATE(NOW())))
                            AND DATE(event_time)=DATE(NOW())
                            AND event_category=category_id
                            AND event_category=cc_category
                        UNION
                        SELECT transfer_event,event_priority,event_time
                        FROM transfers,events,countercategories
                            WHERE transfer_zone=$zone
                            AND transfer_done = 0
                            AND event_level = 1
                            AND cc_counter=$counterID
                            AND (transfer_new_counter=$counterID OR transfer_new_category IN (SELECT cc_category FROM countercategories WHERE cc_counter=$counterID AND cc_enabled = 1))
                            AND DATE(transfer_time)=DATE(NOW())
                            AND event_id=transfer_event
                        ORDER BY event_priority DESC, event_time, event_id LIMIT 10;";

            $countQry = "SELECT COUNT(*)
                            FROM events,categories,countercategories
                                WHERE event_zone=$zone
                                AND event_level= 0
                                AND cc_counter=$counterID
                                AND event_category IN (SELECT cc_category FROM countercategories WHERE cc_counter=$counterID AND cc_enabled = 1)
                                AND 
                                (event_id IN (SELECT transfer_event FROM transfers WHERE transfer_done = 1 AND DATE(transfer_time)=DATE(NOW()))
                                OR 
                                event_id NOT IN (SELECT transfer_event FROM transfers WHERE transfer_done = 0 AND DATE(transfer_time)=DATE(NOW())))
                                AND DATE(event_time)=DATE(NOW())
                                AND event_category=category_id
                                AND event_category=cc_category
                            UNION ALL
                            SELECT count(*)
                            FROM transfers 
                            WHERE transfer_zone=$zone 
                            AND transfer_done = 0 
                            AND DATE(transfer_time)=DATE(NOW())
                            AND (transfer_new_counter=$counterID 
                                OR transfer_new_category IN 
                                    (SELECT cc_category FROM countercategories 
                                    WHERE cc_counter=$counterID AND cc_enabled = 1))";
            $eventsArray = [];
//            var_dump($eventsArray);
            if ($events = getColumn($query)) {

                for ($i = 0; $i < count($events); $i++) {
                    $eventObj = getRow("select event_id AS 'eventID', event_priority as 'eventPriority',LPAD($eventMod,'0') as 'eventNo',TIME(event_time) as 'eventTime',category_char as 'eventChar',IF((SELECT transfer_event FROM transfers WHERE transfer_done = 0 AND transfer_event=" . $events[$i] . "),1,0) as 'eventTransferred' from events,categories where event_id=" . $events[$i] . " and event_category=category_id;");
                    array_push($eventsArray, $eventObj);
                }
            }
//$c = getValue("SELECT COUNT(*) FROM events WHERE DATE(event_time)=DATE(NOW()) AND event_level= 0");

            $counterLoad = getValue("SELECT COUNT(*) FROM events_logs
                                        WHERE log_counter=$counterID 
                                        AND (log_type = 3 OR log_type=2) AND DATE(log_time)= DATE(NOW());");

            $count = getColumn($countQry);
//            var_dump($count);
            $c = $count[0] + $count[1];

            $lastCalled = getValue("SELECT CONCAT(category_char, LPAD($eventMod, '0')) AS 'lastCalled'
                                FROM events_logs, events, categories 
                                WHERE log_event = event_id
                                AND event_category = category_id
                                AND log_type IN (2, 3)
                                AND log_counter = $counterID
                                AND DATE(log_time) = DATE(NOW())
                                ORDER BY log_time DESC, log_id DESC LIMIT 1;");
            $lastCalled = !is_null($lastCalled) ? $lastCalled : "-";
//                var_dump($lastCalled);
//                $lastCalled = $lastCalled['lastCalled'];
//                var_dump($lastCalled);
            $countAll = array("counterload" => "$counterLoad", "eventQty" => "$c", "refresh" => "0", "lastCalled" => "$lastCalled", "eventChar" => "", "eventTransferred" => "");
            array_push($eventsArray, $countAll);
            echo json_encode($eventsArray);
//} else {
//echo 0;
//}
            break;
        case 5: //Open Counter

            break;
        case 6: //Close Counter
            $counterID = filter_input(INPUT_GET, 'counter');

            $row = getRow("SELECT * FROM counters WHERE counter_id=$counterID;");
            $zone = $row['counter_zone'];
            $displayID = $row['counter_display'];

            $displayType = strtolower(getSetting('displayType'));
            switch ($displayType) {
                case 'serial':
                    //=============================================================================|| Curl                           
                    updateDisplaySerial('closed', $displayID, 1);

                    if (executeQuery("UPDATE counters SET counter_active=0, current_clerk=0 WHERE counter_id=$counterID;")) {
                        echo 1;
                    } else {
                        echo 0;
                    }
                    break;
                case 'tcp':
                    //=============================================================================|| Ethernet and logs
                    if (executeMultiQuery("INSERT INTO displays_logs(log_event,log_counter,log_display,log_zone) VALUES(0,$counterID,$displayID,$zone);"
                                    . "UPDATE counters SET counter_active=0, current_clerk=0 WHERE counter_id=$counterID;")) {
                        echo 1;
                    } else {
                        echo 0;
                    }
                    break;
                default :
                    break;
            }

            break;
        case 7: //  enable/ disable category
            $counterID = $_GET['counter'];
            $row = getRow("SELECT * FROM counters WHERE counter_id=$counterID;");
            $zone = $row['counter_zone'];
            $displayID = $row['counter_display'];
            if (executeQuery("INSERT INTO displays_logs(log_event,log_counter,log_display,log_zone) VALUES(0,$counterID,$displayID,$zone);")) {

                echo 1;
            } else {
                echo 0;
            }
            break;
        case 8: //  add event to pending
            $counterID = filter_input(INPUT_GET, 'counter');
            $eventID = filter_input(INPUT_GET, 'event');
            $clerkID = filter_input(INPUT_GET, 'clerk');
            $zoneID = getValue("SELECT event_zone FROM events WHERE event_id = $eventID");

            $qry = "UPDATE events SET event_level=2 WHERE event_id= $eventID;
                    INSERT INTO events_logs(log_type,log_event,log_clerk,log_counter,log_zone)
                    VALUES(4, $eventID, $clerkID, $counterID, $zoneID);";

            if (executeMultiQuery($qry)) {
                echo json_encode($eventID);
            } else {
                echo 0;
            }
            break;
        case 9: //  get list of event pending
            $counterID = filter_input(INPUT_GET, 'counter');
            $qry = "SELECT log_event AS 'eventID', CONCAT(category_char, LPAD($eventMod,'0')) AS 'ticket'  
                      FROM events_logs, events, categories 
                       WHERE log_counter = $counterID 
                        AND log_type = 4
                        AND event_level = 2
                        AND DATE(log_time)= DATE(NOW())
                        AND log_event = event_id
                        AND event_category = category_id;";
//            $pendingEventsIDs = getColumn("SELECT log_event FROM events_logs  WHERE log_counter = $counterID AND log_type = 4 AND DATE(log_time)= DATE(NOW());");
            $pendingEvents = getArrayAssoc($qry);

            if ($pendingEvents && count($pendingEvents) > 0) {
                echo json_encode($pendingEvents);
            } else {
                echo 0;
            }
            break;
        case 10: //  remove pending event
            $eventID = filter_input(INPUT_GET, 'event');
            $counterID = filter_input(INPUT_GET, 'counter');

            $ev = getValue("SELECT event_id FROM events WHERE event_id=$eventID AND DATE(event_time) = DATE(NOW());");

            if ($ev && executeQuery("DELETE FROM events_logs WHERE log_event = $eventID AND log_type=4;")) {

                $zoneID = getValue("select counter_zone from counters where counter_id=$counterID;");
                $qryCall = "SELECT event_id AS 'eventID', CONCAT(category_char, LPAD($eventMod, '0')) AS 'Ticket',event_time AS 'eventTime',event_category AS 'eventCategory',event_priority
								FROM events,categories
								WHERE event_zone=$zoneID
                                                                AND event_id = $eventID
								AND event_level= 2
                                                                AND DATE(event_time) = DATE(NOW())
                                                                AND event_category=category_id";
                $eventObj = getRow($qryCall);

                if (executeQuery("UPDATE events SET event_level=1 WHERE event_id = $eventID;")) {
                    if ($eventObj) {
                        echo json_encode($eventObj);
                    } else {
                        echo json_encode("NO");
                    }
                } else {
                    echo 0;
                }
            } else {
                if (getValue("SELECT event_id FROM events WHERE event_id=$eventID AND DATE(event_time) < DATE(NOW());")) {
                    echo 'OLD';
                } else {
                    echo 0;
                }
            }

            break;
        case 11: //Login
            $username = $_POST['username'];
            $password = $_POST['password'];
            $autoLogin = $_POST['autologin'] == 'true' ? true : false;
            $counter = $_POST['counter'];
            $ip_address = '';
            if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
                $ip_address = $_SERVER['REMOTE_ADDR'];
            }

            if (getValue("SELECT clerk_id FROM clerks WHERE clerk_name = '$username';")) {
                if ($row = getRow("SELECT clerk_id,clerk_name,clerk_password,clerk_zone,SHA2('$password',256) as 'newpass' FROM clerks WHERE clerk_name='$username';")) {
                    if ($row['clerk_password'] == $row['newpass']) {
                        if (executeQuery("INSERT INTO counters_logs(log_type,log_clerk,log_counter,log_zone, ip_address) VALUES(1," . $row['clerk_id'] . "," . $_POST['counter'] . "," . $row['clerk_zone'] . ", '$ip_address');")) {

                            $clerkID = $row['clerk_id'];
                            session_start();
                            session_regenerate_id(true);

                            $_SESSION['clerkID'] = $clerkID;
                            $_SESSION['counterID'] = $counter;

                            executeQuery("UPDATE counters SET  
                                        counter_active = 1,
                                        current_clerk = $clerkID,
                                        ip_address = '$ip_address'
                                        WHERE counter_id = $counter;");
                            echo 1;
                        } else {
                            echo 0;
                        }
                    } else {
                        echo 3;
                    }
                } else {
                    echo 0;
                }
            } else {
                echo 2;
            }
            break;
        case 12: //Logout
            session_start();
            if (isset($_SESSION['clerkID'])) {
                $clerkID = $_SESSION['clerkID'];
                $counterID = $_SESSION['counterID'];

                $zone = getValue("select clerk_zone from clerks where clerk_id=$clerkID;");
                if (executeQuery("INSERT INTO counters_logs(log_type,log_clerk,log_counter,log_zone) VALUES(2,$clerkID,$counterID,$zone);")) {

                    executeQuery("UPDATE counters 
                        SET 
                            counter_active = 0,
                            current_clerk = 0,
                            ip_address = ''
                            WHERE counter_id = $counterID;");

                    if (isset($_COOKIE['clerkID'])) {
                        setcookie('clerkID', null, -1, "/");
                        setcookie('clerkPassword', null, -1, "/");
                    }
                    if (session_destroy()) {

                        $counterRow = getRow("SELECT counter_zone, counter_display FROM counters WHERE counter_id=$counterID;");
                        if ($counterRow) {
                            $zoneID = $counterRow['counter_zone'];
                            $displayID = $counterRow['counter_display'];
                            clearCounter($counterID, $displayID, $zoneID);
                        }


                        echo 1;
                    } else {
                        echo 0;
                    }
                } else {
                    echo 0;
                }
            } else {
                echo 0;
            }

            break;
        case 15: //  call by eventID
            $eventID = filter_input(INPUT_GET, 'event');
            $counterID = filter_input(INPUT_GET, 'counter');

            // check if eventID is inside queue [can be called]
            $query = "SELECT event_id FROM events 
                        WHERE event_id=$eventID
                        AND DATE(event_time) = DATE(NOW());";
            $result = getValue($query);
            if ($result) {

                $zoneID = getValue("select counter_zone from counters where counter_id=$counterID;");

                $qryCall = "SELECT event_id AS 'eventID',category_char AS 'eventChar',LPAD($eventMod,'0') AS 'eventNo',event_time AS 'eventTime',event_category AS 'eventCategory',event_priority
								FROM events,categories
								WHERE event_zone=$zoneID
                                                                AND event_id = $eventID
                                                                AND DATE(event_time) = DATE(NOW())
                                                                AND event_category=category_id";
                $eventObj = getRow($qryCall);

                if ($eventObj) {
                    echo json_encode($eventObj);
                } else {
                    echo "NO";
                }
            } else {
                echo 0;
            }

            break;
        case 16: //  lastcalled by counter
            $counterID = filter_input(INPUT_GET, 'counter');

            $query = "SELECT DISTINCT Ticket,ID "
                    . "FROM "
                    . "(SELECT CONCAT(category_char, LPAD($eventMod, '0')) AS 'Ticket',"
                    . "event_id AS 'ID',"
                    . " event_level,"
                    . " log_type,"
                    . " log_counter,"
                    . " log_time,"
                    . " log_id"
                    . " FROM events_logs, categories,events "
                    . "WHERE log_event = event_id "
                    . "AND event_id NOT IN (SELECT transfer_event FROM `transfers`, events WHERE `transfer_event` = event_id AND transfer_done = 0 AND DATE(transfer_time)=DATE(NOW())) "
                    . "AND event_category = category_id "
                    . "AND log_type IN (2, 3) "
                    . "AND log_counter = $counterID "
                    . "AND DATE(log_time) = DATE(NOW()) "
                    . "AND event_level != 2 "
                    . "ORDER BY log_time DESC, log_id DESC LIMIT 30) RESULT LIMIT 10;";

            $list = getArrayAssoc($query);

            echo json_encode($list);

            break;
        case 20: //cat for event_id
            $eventID = $_GET['event'];
            $category = getValue("SELECT event_category FROM events WHERE event_id=$eventID;");

            if ($category) {
                echo $category;
            } else {
                echo 0;
            }
            break;
        default :
            break;
    }
} else {
    echo 0;
}
?>
