<?php
$prev = 1;

require_once './common/php_head.php';
//-------------------------------------------------------------< common vars >---
$title = getTextValue('satisfactionScore', $lang);
$view = "./views/feedback/";
$parent = "";
//-------------------------------------------------------------< other includes >---
include_once $view . 'process.php';


//-------------------------------------------------------------< data >---
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include_once './common/head.php'; ?>
        <link href="../css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
        <link href="../css/report.css" rel="stylesheet" type="text/css"/>
        <!--        <link rel="shortcut icon" href="../files/shortcut_icons/report.png"/>-->

    </head>
    <body style="direction:<?php echo $dir ?>;">
        <?php include_once './common/nav.php'; ?>
        <?php include_once './common/header.php'; ?>

        <div id="main-con" class="container-full marg-bottom-50">
            <div class="well-header no-print"><?php echo $title ?></div>
            <div class="s-90 center-block">
                <form id="feedback-form" class="bg-white round-10 pad-10" >
                    <div class="">
                        <div class="inline-block s-20 no-print">
                            <div class="input-group-btn">
                                <button class="btn btn-primary round-5-sh pad-h-30" type="submit">
                                    <?php echo $update ?> <i class="glyphicon glyphicon-refresh"></i>
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
                        <div class="inline-block s-20">
                            <div class="input-group sh-blue">
                                <span class="input-group-addon bg-white-gray pad-h-10">Scope</span>
                                <select name="scope" id="scope-select" class="form-control no-radius" onchange="$('#counter-select').prop('disabled', this.value !== 'counter');">
                                    <option value="all" <?php echo $scope === 'all' ? 'selected' : '' ?>>All Feedback</option>
                                    <option value="global" <?php echo $scope === 'global' ? 'selected' : '' ?>>Global Only</option>
                                    <option value="counter" <?php echo $scope === 'counter' ? 'selected' : '' ?>>Counter Only</option>
                                </select>
                            </div>
                        </div>
                        <div class="inline-block s-20">
                            <div class="input-group sh-blue">
                                <span class="input-group-addon bg-white-gray pad-h-10">Counter</span>
                                <select name="counter_id" id="counter-select" class="form-control no-radius" <?php echo $scope !== 'counter' ? 'disabled' : '' ?>>
                                    <option value="0">All Counters</option>
                                    <?php foreach ($countersList as $c) { ?>
                                        <option value="<?php echo $c['counter_id'] ?>" <?php echo $filterCounterId == $c['counter_id'] ? 'selected' : '' ?>><?php echo htmlspecialchars($c['counter_name']) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="s-90 center-block pad-v-10 no-print">
                <span class="badge bg-blue pad-5">Global: <?php echo $globalCount ?></span>
                <span class="badge bg-primary pad-5">Counter: <?php echo $counterCount ?></span>
            </div>
            <?php if (count($counterComparison) > 0) { ?>
            <div class="s-90 center-block pad-v-10 no-print">
                <table class="table table-striped table-bordered table-condensed">
                    <tr class="bg-primary text-center-th">
                        <th>Counter</th>
                        <th>Submissions</th>
                        <th>Average Score</th>
                        <th>Last Feedback</th>
                    </tr>
                    <?php foreach ($counterComparison as $row) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['counter_name_snapshot']) ?><?php echo $row['counter_id'] === null ? ' <span class="text-gray small">(deleted)</span>' : '' ?></td>
                        <td><?php echo $row['total'] ?></td>
                        <td><?php echo $row['avg_score'] ?>/5</td>
                        <td><?php echo $row['last_feedback'] ?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
            <?php } ?>
            <hr>
            <div id="charts" class="">
                <?php
                for ($t = 1; $t <= 5; $t++) {
                    $fig = "fig$t";
                    $chart = "chart$t";
                    $img = "img-fig$t";
                    ?> 
                    <div class="col-1 inline-block border round-10 pad-v-10 ">
                        <h5 class="text-gray"><i class="fa fa-quora "></i> <?php echo $t ?></h5>
                        <div id="<?php echo $fig ?>" class="h-200 relative small " style="display: block;">
                            <canvas id="<?php echo $chart ?>" style="width: 100%; height: 100%;"></canvas>
                        </div>
                        <div id="<?php echo $img ?>" style="display: none;">
                            <img class="img" src=""/>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <hr>
            <div id="chart-data" class="row s-80 center-block">
                <div class="s-70 inline-block small ">
                    <?php
                    $index = 0;
                    foreach ($fbQuestions as $Row) {
                        $q = $Row['text_value'];
                        $starId = "stars" . $index;
                        $rateId = "s" . ($index + 1);
                        $rating = $ratings[$index];
                        $index++;
                        ?> 
                        <div class="row border-btm-blue marg-5  ">
                            <div class="col-md-2 relative ">
                                <span class="pull-left pad-h-10">
                                    <i class="fa fa-quora font-lg text-gray"></i> <?php echo $index ?>
                                </span>

                            </div>
                            <div class="col-md-8 no-align ">
                                <span class=" text-left">
                                    <?php echo $q ?>
                                </span>
                            </div>
                            <div  class="col-md-2 rateval">
                                <span id="<?php echo $rateId ?>" class="font-md pull-right" style="display: block;"><?php echo "$rating /5" ?></span>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div class="s-25 inline-block no-pad  pad-h-10">
                    <!--<div class="row">-->
                    <div class="pad-5 no-print ">
                        <button class="btn btn-success btn-sm pad-h-10" onclick="PrintChart('main-con')"><i class="glyphicon glyphicon-print"></i> <?php echo $print ?></button>
                    </div>
                    <div class="panel panel-info panel-collapse border">
                        <div class="panel-heading pad-5 border-btm-blue"><?php echo $feedbacks ?></div>
                        <div id='waiting' class="text-center font-md"><?php echo $finalTotalCount; ?></div>
                    </div> 
                    <div class="panel panel-warning panel-collapse border">
                        <div class="panel-heading pad-5 border-btm-blue"><?php echo $finalScore ?></div>
                        <div id="c-load" class="text-center font-md "><?php echo "$finalTotalScore/5"; ?></div>
                    </div>
                    <!--</div>-->
                </div>
            </div>

        </div>
        <?php include_once './common/footer.php'; ?>

        <?php include_once './common/foot_scripts.php'; ?>

        <script src="../js/moment.min.js" type="text/javascript"></script>
        <script src="../js/jquery-ui.min.js" type="text/javascript"></script>
        <script src="../js/jQuery.print.min.js" type="text/javascript"></script>
        <script src="../js/Chartjs.min.js" type="text/javascript"></script>
        <script src="../js/Chartjs_utils.js" type="text/javascript"></script>
        <script type="text/javascript">

                            var values = <?php echo $valuesJson ?>;

                            var lang_title = "<?php echo $title; ?>";
                            var lang_feedbacks = "<?php echo $feedbacks; ?>";
                            var max = <?php echo $max; ?> + 1;
                            var todayDate = "<?php echo $todayDate ?>";
                            var title = "<?php echo $title ?>";

        </script>   
        <script src="../js/report.js" type="text/javascript"></script>
    </body>
</html>
