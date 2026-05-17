<?php
$prev = 1;

require_once './common/php_head.php';
//-------------------------------------------------------------< common vars >---
$title = getTextValue("tickets", $lang);
$view = "./views/tickets/";
$parent = "";
$error = '';
$message = '';
$result = '';
//-------------------------------------------------------------< other includes >---
include_once $view . 'process.php';

//-------------------------------------------------------------< data >---
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include_once './common/head.php'; ?>

    </head>
    <body style="direction:<?php echo $dir ?>;">
        <?php include_once './common/nav.php'; ?>
        <?php include_once './common/header.php'; ?>

        <div class="container marg-bottom-50">
            <div class="well well-header"><?php echo $title ?></div>

            <!--<h3 class="panel-title"><?php echo getTextValue("setpriority", $lang) ?></h3>-->

            <div class="">
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group form-inline">
                        <input type="text"  autofocus placeholder="<?php echo getTextValue("inputteckitnumber", $lang) ?>" name="ticketNumber" required maxlength="<?php echo $ticketSize?>" class="form-control" />
                        <!--<input name="ticketNumber" type="text" placeholder="input ticket number"   class="form-control" />-->
                        <select name="ticketPriority" class="form-control" >
                            <optgroup label="<?php echo getTextValue("choosepriority", $lang) ?>">
                                <option value="0"><?php echo getTextValue("priority", $lang) ?> <?php echo 0 ?></option>
                                <?php
                                for ($i = 10; $i >= 1; $i--) {
                                    ?>
                                    <option value="<?php echo $i ?>"><?php echo getTextValue("priority", $lang) ?> <?php echo showPriority($i, 10); ?></option>
                                    <?php
                                }
                                ?>
                            </optgroup>
                        </select>

                        <button class="btn btn-primary" type="submit" name="submit" value="info"><?php echo getTextValue("getticketinfo", $lang) ?></button>
                        <button class="btn btn-danger" type="submit" name="submit" value="priority"><?php echo getTextValue("setpriority", $lang) ?></button>
                        <button class="btn btn-warning" type="submit" name="submit" value="back" hidden=""><?php echo getTextValue("returnToQueue", $lang) ?></button>
                    </div>
                </form>
                <div id="error-p" class="alert alert-danger center-block no-pad pad-h-10"><span class="glyphicon glyphicon-alert"></span><span id="error"><?php echo " " . $error ?></span></div>
                <div id="result-p" class="alert alert-success center-block no-pad pad-h-10"><span class="glyphicon glyphicon-record"></span><span id="result"><?php echo " " . $result ?></span></div>
                <div id="message-p" class="alert alert-info center-block no-pad pad-h-10">
                    
                    <span class="glyphicon  <?php echo $inQueue; ?>" style="color:white;font-size: 15px;"></span>
                    <span id="message" ><?php echo " " . $message; ?></span>
                    <?php
                    if ($isDetails) {
                        ?>
                        <a class="btn btn-warning btn-xs" id="details-btn"><span class="glyphicon glyphicon-arrow-down"></span> <?php echo getTextValue("details", $lang) ?></a>
                        <?php
                    }
                    ?>
                </div>
                <div class="row s-60 center-block marg-v-20 hidden" id="details">
                    <?php
                    if ($countCalls > 0) {
                        
                        //var_dump($calls);
                        $countCallTxt = ($countCalls > 1 ? getTextValue("times", $lang) : getTextValue("time", $lang));
                        $callsHeader = getTextValue('ticket', $lang) . ": $ticketNumber " . getTextValue("hasbeencalledby", $lang) . " $countCalls $countCallTxt";
                        ?>
                        <!--<div class="panel panel-default">-->
                            <div class=""  id="ticket-calls">
                                <table class="table table-striped s-100" >
                                    <tr >
                                        <td colspan="3" align="center" style='font-weight:bold;' bgcolor="#99ebff"><?php echo $callsHeader ?>  </td>
                                    </tr>
                                    <tr>
                                        <th ><?php echo getTextValue("counter", $lang) ?></th>
                                        <th ><?php echo getTextValue("clerkName", $lang) ?></th>
                                        <th ><?php echo getTextValue("clock", $lang) ?></th>
                                    </tr>

                                    <?php
                                    foreach ($calls as $call) {
                                        $callCounter = $call['counter_name'];
                                        $callClerk = $call['clerk_name'];
                                        $callTime = $call['log_time'];
                                        ?>
                                        <tr>
                                            <td><?php echo $callCounter ?></td>
                                            <td><?php echo $callClerk ?></td>
                                            <td><?php echo $callTime ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </table>
                            </div>
                            <div class="panel-footer s-100 center-block pad-3">
                                <a class="btn btn-info btn-xs" href="javascript:void(0);" id="calls" onclick="exportData('ticket-calls');"><?php echo getTextValue('excelExport', $lang) ?></a>
                            </div>
                            <?php
                            if ($countTransfers > 0) {
                                $transfers = getArray("SELECT transfers.*,
                                                        events.event_category as 'transfer_cat'
                                                        FROM transfers, events 
                                                        WHERE transfer_event = $eventID 
                                                        AND transfers.transfer_event = events.event_id 
                                                        AND DATE(transfer_time)=DATE($date) 
                                                        ORDER BY transfer_time;");
                                
                                $countTransferTxt = ($countTransfers > 1 ? getTextValue("times", $lang) : getTextValue("time", $lang));
                                $transfersHeader = getTextValue('ticket', $lang) . ": $ticketNumber " . getTextValue("transferred", $lang) . " $countTransfers  $countTransferTxt";
                                ?>
                                <div class="panel-body" id="ticket-transfers">
                                    <table class="table table-striped s-100" >
                                        <tr >
                                            <td colspan="7" align="center" style='font-weight:bold;' bgcolor="#99ebff"><?php echo $transfersHeader ?></td>
                                        </tr>
                                        <tr>
                                            <th ><?php echo getTextValue("clock", $lang) ?></th>
                                            <th ><?php echo getTextValue("clerkName", $lang) ?></th>
                                            <th ><?php echo getTextValue("counter", $lang) ?></th>
                                            <th ><?php echo getTextValue("toCounter", $lang) ?></th>
                                            <th ><?php echo getTextValue("category", $lang) ?></th>
                                            <th ><?php echo getTextValue("toCategory", $lang) ?></th>
                                            <th ><?php echo getTextValue("transferDone", $lang) ?></th>
                                        </tr>

                                        <?php
                                        foreach ($transfers as $transfer) {
                                            
                                            $transferTime = $transfer['transfer_time'];
                                            $transferClerk = getValue("SELECT clerk_name FROM clerks WHERE clerk_id = " . $transfer['transfer_clerk']);
                                            $transferFromCounter = getValue("SELECT counter_name FROM counters WHERE counter_id = " . $transfer['transfer_counter']);
                                            $transferToC = getValue("SELECT counter_name FROM counters WHERE counter_id = " . $transfer['transfer_new_counter']);
                                            $transferToCounter = (!is_null($transferToC) ? $transferToC : "-");
                                            $transferFromCategory = getTextValue(getValue("SELECT category_key FROM categories WHERE category_id = " . $transfer['transfer_cat']), $lang);
                                            $transferToCat = getValue("SELECT category_key FROM categories WHERE category_id = " . $transfer['transfer_new_category']);
                                            $transferToCategory = (!is_null($transferToCat) ? getTextValue($transferToCat, $lang) : "-");
                                            $transferDone = ($transfer['transfer_done'] == 1 ? getTextValue('yes', $lang) : getTextValue('no', $lang));
                                            
                                            ?>
                                            <tr>
                                                <td><?php echo $transferTime ?></td>
                                                <td><?php echo $transferClerk ?></td>
                                                <td><?php echo $transferFromCounter ?></td>
                                                <td><?php echo $transferToCounter ?></td>
                                                <td><?php echo $transferFromCategory ?></td>
                                                <td><?php echo $transferToCategory ?></td>
                                                <td>
                                                    <?php echo $transferDone ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </table>
                                </div>
                                <div class="panel-footer s-100 center-block pad-3">
                                    <a class="btn btn-info btn-xs" href="javascript:void(0);" id="transfers" onclick="exportData('ticket-transfers');"><?php echo getTextValue('excelExport', $lang) ?></a>
                                </div>
                                <?php
                            }
                            ?>
                        <!--</div>-->
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo getTextValue("prioritysummary", $lang) ?></h3>
                </div>
                <div class="panel-body" id="tickets-priority">
                    <table class="table table-striped" >
                        <tr>
                            <th ><?php echo getTextValue("ticket", $lang) ?></th>
                            <th ><?php echo getTextValue("newpriority", $lang) ?></th>
                            <th ><?php echo getTextValue("updateTimes", $lang) ?></th>
                        </tr>
                        <?php
                        $ink = 0;
                        $priorityListQry = "SELECT 
                                events.event_priority AS 'Priority',
                                priority_updated AS 'times',
                                CONCAT(categories.category_char,
                                LPAD(MOD(event_no,1000),3,'0')) AS 'Ticket' 
                                    FROM events, categories 
                                    WHERE events.priority_updated >= 1 
                                    AND events.event_category=categories.category_id 
                                    AND DATE(events.event_time)=DATE($date)
                                    ORDER BY event_priority DESC;";
                        
                        $priorityList = getArray($priorityListQry);

                        foreach ($priorityList as $row) {
                            $ticket = $row['Ticket'];
                            $priority = $row['Priority'];
                            ?>
                            <tr>
                                <td><?php echo $ticket ?></td>
                                <td><?php echo showPriority($priority, 10); ?></td>
                                <td><?php echo $row['times'] ?></td>
                            </tr>
                            <?php
                            $ink++;
                        }

                        $priorityFooter = getTextValue("totalupdatedtickets", $lang) . ": $ink";
                        ?>
                        <tr >
                            <td colspan="3" align="center" style='font-weight:bold;' bgcolor="#99ebff"><?php echo $priorityFooter ?></td>
                        </tr>
                    </table>
                </div>
                <div class="panel-footer center-block  pad-3">
                    <a class="btn btn-info btn-xs" href="javascript:void(0);" id="priorities" onclick="exportData('tickets-priority');"><?php echo getTextValue('excelExport', $lang) ?></a>
                </div>
            </div>
        </div>
        <?php include_once './common/footer.php'; ?>

        <?php include_once './common/foot_scripts.php'; ?>
        <script src="../js/FileSaver.min.js" type="text/javascript"></script>
        <script type="text/javascript">
                            var error;
                            var result;
                            var message;
                            init();
                            function init() {
                                error = '<?php echo $error ?>';
                                result = '<?php echo $result ?>';
                                message = '<?php echo $message ?>';
                                errorP = document.getElementById('error-p');
                                resultP = document.getElementById('result-p');
                                messageP = document.getElementById('message-p');
                                //alert("er " + error.innerHTML + "m " + message + "r " + result);
                                if (error != "") {
                                    errorP.style.display = 'block';
                                }
                                if (result != "") {
                                    resultP.style.display = 'block';
                                }
                                if (message != "") {
                                    messageP.style.display = 'block';
                                }
                            }

                            function exportData(report_id) {
                                var blob = new Blob([document.getElementById(report_id).innerHTML], {
                                    type: "text/plain;charset=utf-8;"
                                });
                                saveAs(blob, "Report-" + report_id + ".xls");
                            }

                            $("#details-btn").click(function ()
                            {
                                $('#details').toggleClass('hidden');

                            });

        </script>
    </body>
</html>
