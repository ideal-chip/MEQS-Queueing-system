<?php

//-------------------------------------------------------------< requests >---

$ticketNumber = strtoupper(getRequestVal('ticketNumber', '', 'post'));
$ticketPriority = intval(getRequestVal('ticketPriority', 0, 'post'));
$submit = getRequestVal('submit', '', 'post');

//-------------------------------------------------------------< common vars >---

$pendingCounter = 0;
$date = 'NOW()';
$transferDone = 1; // default done
$messageShown = 0;
$isDetails = 0;
$ticketSize = getSetting('displayDigits');
$isBackToQueue = false;

//-------------------------------------------------------------< data checks >---

if (!empty($ticketNumber)) {

    $size = strlen($ticketNumber);
    $categoryChar = substr($ticketNumber, 0, 1);
    $eventNoStr = substr($ticketNumber, 1, $size);

    if ($size > $ticketSize || !ctype_alpha($categoryChar) || !ctype_digit($eventNoStr)) {
        $error = getTextValue('wrongformat', $lang) . "[$ticketNumber], " . getTextValue('ticketformaterror', $lang);
    } else {

        $eventNo = intval($eventNoStr);
        $categoryID = intval(getValue("SELECT category_id FROM categories WHERE category_char='$categoryChar';"));
        $eventID = getValue("SELECT event_id FROM events WHERE event_no = $eventNo AND event_category=$categoryID AND DATE(event_time)= DATE($date);");

        if ($eventID > 0) {


            $isTicketCalled = getValue("SELECT event_level FROM events WHERE event_id = $eventID AND DATE(event_time)=DATE($date);");

            $countCalls = getValue("SELECT count(*) FROM events_logs WHERE log_type IN (2, 3) AND log_event = $eventID AND DATE(log_time)=DATE($date);");
            $countTransfers = getValue("SELECT COUNT(*) FROM transfers WHERE transfer_event=$eventID;");


            if ($countTransfers > 0) {
                $transferDone = getValue("SELECT transfer_done FROM transfers WHERE transfer_event = $eventID ORDER BY transfer_time DESC LIMIT 1;");
            }

            // check ticket status
            $isBackToQueue = ($isTicketCalled == 0 ) || ($isTicketCalled == 1 && $transferDone == 0);

            // check pending status
            $isPendingRow = getRow("SELECT * FROM events_logs WHERE log_type = 4 AND log_event = $eventID AND DATE(log_time)=DATE($date);");
            if ($isPendingRow) {
                $pendingCounter = $isPendingRow['log_counter'];
                $pendingClerkID = $isPendingRow['log_clerk'];
                $pendingClerk = getValue("SELECT clerk_name FROM clerks WHERE clerk_id = $pendingClerkID;");
            }

            if ($submit == 'priority') {
                //todo : check if it's not called , and transfer not done to be true
                if ($pendingCounter) {
                    $error = getTextValue('ticket', $lang) . ": $ticketNumber " . getTextValue('errorInPendingState', $lang) . " " . getTextValue('counter', $lang) . " $pendingCounter, " . getTextValue('clerkCounter', $lang) . ": $pendingClerk";

                } elseif ($isBackToQueue == true) {

                    $lastPriorityQry = $ticketPriority > 0 ? "priority_updated + 1" : '0';

                    if (executeQuery("UPDATE events SET event_priority=$ticketPriority, priority_updated=($lastPriorityQry) WHERE event_no=$eventNo AND event_category=$categoryID AND DATE(event_time)=DATE($date);")) {
                        $result = $ticketNumber . " " . getTextValue('wasgivenapriorityof', $lang) . showPriority($ticketPriority, 10) . "!";
                    } else {
                        $error = 'wrong sql';
                    }
                } else {
                    $error = getTextValue('ticket', $lang) . ": $ticketNumber " . getTextValue('errorPrioritySet', $lang);
                    $messageShown = 1;
                }
            }
            // todo: log eventBack to queue
            if ($submit == 'back') {
                if ($pendingCounter) {
                    $error = getTextValue('ticket', $lang) . ": $ticketNumber " . getTextValue('errorInPendingState', $lang) . " " . getTextValue('counter', $lang) . " $pendingCounter, " . getTextValue('clerkCounter', $lang) . ": $pendingClerk";
                } elseif ($isBackToQueue == false) {
                    if (executeQuery("UPDATE events SET event_level=0 WHERE event_id=$eventID;")) {
                        $result = $ticketNumber . " : " . getTextValue('backToQueueSuccess', $lang) . "!";
                        $isBackToQueue = true;
                    }
                } else {
                    $error = getTextValue('ticket', $lang) . ": $ticketNumber " . getTextValue('notcalledyet', $lang);
                    $messageShown = 1;
                }
            }

            if ($countCalls > 0) {
                $message = getTextValue('ticket', $lang) . ": $ticketNumber " . getTextValue('hasbeencalledby', $lang) . " $countCalls " . ($countCalls > 1 ? getTextValue('times', $lang) : getTextValue('time', $lang)) . ".";
                $isDetails = 1;

                $calls = getArray("SELECT clerk_name, counter_name, log_time
                                        FROM events_logs, clerks, counters
                                        WHERE log_event = $eventID
                                        AND log_type IN (2, 3)
                                        AND events_logs.log_clerk = clerks.clerk_id
                                        AND events_logs.log_counter = counters.counter_id
                                        AND DATE(log_time)=DATE($date) ORDER BY log_time;");
                
                if ($countTransfers > 0) {
                    // get transfers array
                    $message = $message . " " . getTextValue('transferred', $lang) . " $countTransfers " . ($countTransfers > 1 ? getTextValue('times', $lang) : getTextValue('time', $lang)) . ".";
                }
                if ($isBackToQueue == true) {
                    $message = $message . " " . getTextValue('backToQueueSuccess', $lang) . ".";
                }
                if ($pendingCounter) {
                    $message = $message . getTextValue('ticket', $lang) . ": $ticketNumber " . getTextValue('errorInPendingState', $lang) . " " . getTextValue('counter', $lang) . " $pendingCounter, " . getTextValue('clerkCounter', $lang) . ": $pendingClerk";
                }
            } else {
                if (!$messageShown) {
                    $message = getTextValue('ticket', $lang) . ": $ticketNumber , " . getTextValue('notcalledyet', $lang);
                }
            }
        } else {
            $error = getTextValue('ticket', $lang) . ": $ticketNumber " . getTextValue('doesnotexit', $lang);
        }
    }
}

//-----------------------------------------------------< summary >------------------
if ($isBackToQueue) {
    $inQueue = "glyphicon-ok-sign";
    $inQStyle = "color: green;";
} else {
    $inQueue = "glyphicon-remove-sign";
    $inQStyle = "color: red;";
}
?>
