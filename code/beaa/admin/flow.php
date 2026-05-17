<?php
$prev = 1;
require_once './common/php_head.php';

//-------------------------------------------------------------< common vars >---

$title = getTextValue("flow", $lang);
$view = "./views/flow/";
$parent = "";

//-------------------------------------------------------------< other includes >---
include_once $view . 'process.php';
//-------------------------------------------------------------< data >---
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include_once './common/head.php'; ?>
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="../css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
        <link href="../css/chartist.min.css" rel="stylesheet" type="text/css"/>
        <link href="../css/flow.css" rel="stylesheet" type="text/css"/>
    </head>
    <body style="direction:<?php echo $dir ?>;">
        <?php include_once './common/nav.php'; ?>
        <?php include_once './common/header.php'; ?>

        <div class="container marg-bottom-50">
            <div class="well well-header relative">
                <?php echo $title ?>
                <div class="corner-box-right pad-10 pad-h-20 no-print no-style no-shadow">
                    <button class="btn btn-default btn-sm" onclick="printChart('main-con')">
                        <i class="fa fa-print"></i> 
                        <?php echo $print ?>
                    </button>
                </div>
            </div>
            <div id="main-con">
                <div class="s-90 center-block ">
                    <form id="feedback-form" class="bg-white round-10" >
                        <div class="">
                            <div class="inline-block s-20 no-print">
                                <div class="input-group-btn">
                                    <button class="btn btn-primary pad-h-30" type="submit">
                                        <i class="glyphicon glyphicon-refresh"></i> 
                                        <?php echo $update ?> 
                                    </button>

                                </div>
                            </div>
                            <div class="inline-block s-35">
                                <div class="input-group sh-blue">
                                    <span class="input-group-addon bg-white-gray pad-h-10"> <?php echo $from ?></span>
                                    <input  id="date_start" name="date_start" maxlength="0" class=" form-control no-radius text-box single-line pad-h-5 font-md" placeholder="choose date" value="<?php echo $dateStart; ?>" type="text">
                                    <span class="input-group-addon bg-white-gray pad-h-10"><i class="glyphicon glyphicon-calendar"></i></span>
                                </div>
                            </div>
                            <div class="inline-block s-35">
                                <div class="input-group sh-blue">
                                    <span class="input-group-addon bg-white-gray pad-h-10"><?php echo $to ?></span>
                                    <input  id="date_end" name="date_end" maxlength="0" class=" form-control no-radius text-box single-line pad-h-5 font-md" placeholder="choose date" value="<?php echo $dateEnd; ?>" type="text">
                                    <span class="input-group-addon bg-white-gray pad-h-10"><i class="glyphicon glyphicon-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div id="top-row" class="row">
                    <div class="col-md-3">
                        <h3 class="sec-hd border-btm-blue"><?php echo $generalInfo ?> </h3>
                        <div id="general_print" class="pad-v-20">
                            <ul class="list-group small list-small no-pad center-block">

                                <li class="list-group-item">
                                    <span class="badge"><?php echo $clerksCounts ?></span>
                                    <?php echo $clerksNo ?>
                                </li>
                                <li class="list-group-item">
                                    <span class="badge"><?php echo $kiosksCounts ?></span>
                                    <?php echo $kiosksNo ?>
                                </li>
                                <li class="list-group-item">
                                    <span class="badge"><?php echo $countersCounts ?></span>
                                    <?php echo $countersNo ?>
                                </li>
                                <li class="list-group-item">
                                    <span class="badge"><?php echo $displaysCount ?></span>
                                    <?php echo $displaysNo ?>
                                </li>
                            </ul>
                            <div class="no-print">
                                <button class="btn btn-success btn-xs" id="subcats-btn" onclick="exportData('general_info');"> 
                                    <i class="fa fa-file-excel-o"></i>
                                    <?php echo $excelExport ?>
                                </button>
                                <button class="btn btn-default btn-sm" onclick="printBox('general_print', 0)">
                                    <i class="fa fa-print"></i> 
                                    <?php echo $print ?>
                                </button>
                            </div>
                            <div id="general_info" style="display: none;" class="no-print">
                                <table class="table table-striped table-100">
                                    <tr>
                                        <th colspan="2" style="background-color: blue; color: #fff;"><?php echo $generalInfo ?>  </td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $clerksCounts ?>  </td>
                                        <td><?php echo $clerksNo ?>  </td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $kiosksCounts ?>  </td>
                                        <td><?php echo $kiosksNo ?>  </td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $countersCounts ?>  </td>
                                        <td><?php echo $countersNo ?>  </td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $displaysCount ?>  </td>
                                        <td><?php echo $displaysNo ?>  </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-9" id="all_categories_print">
                        <h3 class="sec-hd border-btm-blue"><?php echo $allCategories ?> </h3>
                        <div  class="col-md-5 pad-v-20">
                            <ul class="list-group small no-pad list-small">
                                <li class="list-group-item">
                                    <span class="badge"><?php echo $count_eventsAll ?></span>
                                    <?php echo $eventsNo ?>
                                </li>
                                <li class="list-group-item">
                                    <span class="badge"><?php echo $count_eventsWaiting ?></span>
                                    <?php echo $eventsWaiting ?>
                                </li>
                                <li class="list-group-item">
                                    <span class="badge"><?php echo $count_eventsTransferred ?></span>
                                    <?php echo $eventsTransferred ?>
                                </li>
                                <li class="list-group-item">
                                    <span class="badge"><?php echo $count_eventsClosed ?></span>
                                    <?php echo $eventsClosed ?>
                                </li>
                            </ul>
                            <div class="no-print">
                                <button class="btn btn-success btn-xs" id="subcats-btn" onclick="exportData('allCategories');">
                                    <i class="fa fa-file-excel-o"></i> 
                                    <?php echo $excelExport ?>
                                </button>
                                <button class="btn btn-default btn-sm" onclick="printBox('all_categories_print', 1, '')">
                                    <i class="fa fa-print"></i> 
                                    <?php echo $print ?>
                                </button>
                            </div>
                            <div id="allCategories" style="display: none;" class="no-print">
                                <table class="table table-striped table-100">
                                    <tr>
                                        <th style="background-color: blue; color: #fff;" colspan='2'><?php echo $allCategories ?>  </th>
                                    </tr>
                                    <tr>
                                        <td><?php echo $count_eventsAll ?>  </td>
                                        <td><?php echo $eventsNo ?>  </td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $count_eventsWaiting ?>  </td>
                                        <td><?php echo $eventsWaiting ?>  </td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $count_eventsTransferred ?>  </td>
                                        <td><?php echo $eventsTransferred ?>  </td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $count_eventsClosed ?>  </td>
                                        <td><?php echo $eventsClosed ?>  </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-7 " >
                            <div id="fig1" class="h-100">
                                <canvas id="chart1" style="width: 100%; height: 100%;"></canvas>
                            </div>
                            <div id="img-fig1" class="pad-10" style="display: none;">
                                <img class="img" src=""/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row marg-v-10" id="categories_print">
                    <h3 class="sec-hd border-btm-blue"><?php echo $lang_categories ?>  </h3>
                    <div class="col-sm-6">
                        <div id="fig2" class="h-250">
                            <canvas id="chart2" style="width: 100%; height: 100%;"></canvas>
                        </div>
                        <div id="img-fig2" class="" style="display: none;">
                            <img class="img" src=""/>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div  id="categories">
                            <table class="table table-bordered table-striped table-100 table-close">
                                <tr class="no-style">
                                    <th ><?php echo $mainService ?>  </th>
                                    <th ><?php echo $eventsNo ?>  </th>
                                    <th ><?php echo $eventsWaiting ?>  </th>
                                    <th ><?php echo $eventsTransferred ?>  </th>
                                    <th ><?php echo $eventsClosed ?>  </th>
                                </tr>
                                <?php
                                for ($e = 0; $e < count($cat_names); $e++) {
                                    ?>
                                    <tr>
                                        <td class=""><?php echo $cat_names[$e] ?>  </td>
                                        <td ><?php echo $cat_ticket_no[$e] ?>  </td>
                                        <td ><?php echo $cat_ticket_no_waiting[$e] ?>  </td>
                                        <td ><?php echo $cat_ticket_no_transferred[$e] ?>  </td>
                                        <td ><?php echo $cat_ticket_no_served[$e] ?>  </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </table>
                        </div>
                        <div class="pad-v-10 no-print">
                            <a class="btn btn-success btn-xs" href="javascript:void(0);" id="cats" onclick="exportData('categories');">
                                <i class="fa fa-file-excel-o"></i> 
                                <?php echo $excelExport ?>
                            </a>
                            <button class="btn btn-default btn-sm" onclick="printBox('categories_print', 2, '')">
                                <i class="fa fa-print"></i> 
                                <?php echo $print ?>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row" id="counters_loads_print">
                    <h3 class="sec-hd border-btm-blue"><?php echo $lang_counters ?>  </h3>
                    <div class="col-sm-6 marg-v-20">
                        <div  id="counters-loads">
                            <table class="table table-bordered table-striped table-100 table-close" >
                                <tr>
                                    <th><?php echo $lang_counter ?>  </th>
                                    <th><?php echo $lang_clerkCounter ?>  </th>
                                    <th><?php echo $lang_lastSeen ?>  </th>
                                    <th><?php echo $lang_counterLoad ?>  </th>
                                    <th><?php echo $lang_pendingList ?>  </th>
                                </tr>
                                <?php
//charts vars
                                foreach ($counters as $counterRow) {

                                    $counterName = $counterRow['counter_name'];
                                    $isCounterActive = $counterRow['counter_active'];
                                    $currentClerkID = $counterRow['current_clerk'];
                                    $counterNum = $counterRow['counter_no'];
                                    $currentClerk = $counterRow['clerk_name'];
                                    $counterLoad = $counterRow['counter_load'];
                                    $counterPending = $counterRow['counter_pending'];
                                    $lastSeen = $lastSeen = ($counterRow['last_seen'] == NULL ? "-" : $counterRow['last_seen']);

                                    $clerkName = $clerkColor = "";

                                    if ($isCounterActive) {
                                        $clerkName = $currentClerk;
                                        $clerkColor = 'color:blue;';
                                    } else {
                                        $clerkName = $lang_closed;
                                        $clerkColor = 'color:red;';
                                    }

                                    $redText = ($counterPending > 0) ? "red-text" : "";

                                    // add counter load, counter number to charts arrays
                                    array_push($counter_loads, intval($counterLoad));
                                    array_push($counter_pendings, intval($counterPending));
                                    array_push($counterNumbers, "Counter $counterNum");
                                    ?>
                                    <tr>
                                        <td><?php echo $counterName; ?>  </td>
                                        <td style='<?php echo $clerkColor; ?>'><?php echo $clerkName ?> </td>
                                        <td><?php echo $lastSeen; ?>  </td>
                                        <td><?php echo $counterLoad; ?>  </td>
                                        <td class="<?php echo $redText ?>"><?php echo $counterPending; ?>  </td>
                                    </tr>
                                    <?php
                                }

                                executeQuery("Update counters SET counter_active = 0;");
                                ?>
                            </table>
                        </div>
                        <div class="pad-v-10 no-print">
                            <a class="btn btn-success btn-xs" href="javascript:void(0);" id="cats" onclick="exportData('counters-loads');">
                                <i class="fa fa-file-excel-o"></i> 
                                <?php echo $excelExport ?>
                            </a>
                            <button class="btn btn-default btn-sm" onclick="printBox('counters_loads_print', 3, '')">
                                <i class="fa fa-print"></i> 
                                <?php echo $print ?>
                            </button>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div id="fig3" class="h-250">
                            <canvas id="chart3" style="width: 100%; height: 100%;"></canvas>
                        </div>
                        <div id="img-fig3" class="" style="display: none;">
                            <img class="img" src=""/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include_once './common/footer.php'; ?>

        <?php include_once './common/foot_scripts.php'; ?>
        <script src="../js/jquery-ui-1.12.1.min.js" type="text/javascript"></script>
        <script src="../js/Chartjs.min.js" type="text/javascript"></script>
        <script src="../js/Chartjs_utils.js" type="text/javascript"></script>
        <script src="../js/FileSaver.min.js" type="text/javascript"></script>
        <script src="../js/jQuery.print.min.js" type="text/javascript"></script>
        <script type="text/javascript">

                                var todayDate = "<?php echo $todayDate ?>";
                                var title = "<?php echo $title ?>";

                                var lang_categories = "<?php echo $lang_categories ?>";
                                var lang_counterLoad = "<?php echo $lang_counterLoad ?>";
                                var lang_counterPending = "<?php echo $lang_pendingList ?>";

                                var ticketsNo = <?php echo json_encode($cat_ticket_no) ?>;
                                var ticketsWaiting = <?php echo json_encode($cat_ticket_no_waiting) ?>;
                                var ticketsTransfered = <?php echo json_encode($cat_ticket_no_transferred) ?>;
                                var ticketsServed = <?php echo json_encode($cat_ticket_no_served) ?>;
                                var categoryNames = <?php echo json_encode($cat_names, JSON_UNESCAPED_UNICODE); ?>;

                                var counterLoads = <?php echo json_encode($counter_loads) ?>;
                                var counterPendings = <?php echo json_encode($counter_pendings) ?>;
                                var counterNumbers = <?php echo json_encode($counterNumbers, JSON_UNESCAPED_UNICODE) ?>;


                                var allCategoriesData = <?php echo $allCategoriesCounts; ?>;
                                var allCategoriesDataNames = <?php echo json_encode(["$eventsNo", "$eventsWaiting", "$eventsTransferred", "$eventsClosed"], JSON_UNESCAPED_UNICODE); ?>;

                                //                                console.log(counterLoads);
                                //                                console.log(counterNumbers);
                                //                                console.log(allCategoriesData);
                                //                                console.log(allCategoriesDataNames);
                                //
                                //                                console.log(ticketsNo);
                                //                                console.log(ticketsWaiting);
                                //                                console.log(ticketsTransfered);
                                //                                console.log(ticketsServed);
        </script>

        <script src="../js/flow.js" type="text/javascript"></script>

    </body>
</html>
