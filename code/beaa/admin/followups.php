<?php
$prev = 1;

require_once './common/php_head.php';
//-------------------------------------------------------------< common vars >---
$title = getTextValue('followupCards', $lang);
$view = "./views/followups/";
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
        <link href="../js/chartist/chartist.min.css" rel="stylesheet" type="text/css"/>
        <link href="../css/report.css" rel="stylesheet" type="text/css"/>
        <!--<link rel="shortcut icon" href="../files/shortcut_icons/report.png"/>-->

    </head>
    <body style="direction:<?php echo $dir ?>;">
        <?php include_once './common/nav.php'; ?>
        <?php include_once './common/header.php'; ?>

        <div id="main-con" class="container marg-bottom-50">
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
                    </div>
                </form>
            </div>
            <div id="q-result" class="bg-white-gray sh-blue" >
                <div class="s-30 inline-block no-pad  pad-h-10 vertical-t-bottom">
                    <div class="">
                        <div class="pad-5 no-print">
                            <button class="btn btn-success btn-sm" onclick="PrintChart('main-con')"><i class="glyphicon glyphicon-print"></i> <?php echo $print ?></button>
                        </div>
                        <div class="panel panel-warning panel-collapse ">
                            <div class="panel-heading pad-5 border-btm-blue"><?php echo $total . " ($issuingCards)" ?></div>
                            <div id="c-load" class="text-center font-md "><?php echo $totalCount ?></div>
                        </div>
                    </div>
                </div>
                <div class="s-60 inline-block">
                    <div class="pad-10">
                        <div id="fig1" class="h-250 relative small " style="display: block;">
                            <canvas id="chart1" style="width: 100%; height: 100%;"></canvas>
                        </div>
                        <div id="img-fig" style="display: none;">
                            <img id="img" src=""/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="well well-sm round-5-sh marg-v-10 no-print">
                <button class="btn btn-info btn-xs" id="subcats-btn" onclick="exportData('subcategories-list');"> <i class="fa fa-file-excel-o"></i> <?php echo getTextValue('excelExport', $lang) ?></button>
                <!--<button class="btn btn-success btn-xs" onclick="printElement('subcategories-list', '<?php echo $waitTimeReport ?>')"> <i class="glyphicon glyphicon-print"></i> <?php echo $print ?></button>-->
            </div>
            <div >
                <ul class="nav nav-tabs nav-justified no-print">
                    <li ><a data-toggle="tab" href="#table_tab">Table</a></li>
                    <li class="active"><a data-toggle="tab" href="#chart_tab">Charts</a></li>
                </ul>
                <div class="tab-content">
                    <div id="table_tab" class="tab-pane fade">
                        <!--<h5>table </h5>-->
                        <?php require_once './views/followups/table_form.php'; ?>
                    </div>
                    <div id="chart_tab" class="tab-pane fade in active">
                        <!--<h5>Charts </h5>-->
                        <?php require_once './views/followups/charts.php'; ?>
                    </div>
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
        <script src="../js/FileSaver.min.js" type="text/javascript"></script>
        <script src="../js/chartist/chartist.min.js" type="text/javascript"></script>
        <!--<script src="../js/echarts.common.min.js" type="text/javascript"></script>-->
        <script type="text/javascript">

                    var values = <?php echo $valuesJson ?>;
                    var labels = <?php echo $labelsJson ?>;

                    var lang_title = "<?php echo $title; ?>";
                    var lang_mainService = "<?php echo $mainService; ?>";
                    var max =<?php echo $max; ?> + 1;

                    var todayDate = "<?php echo $todayDate ?>";
                    var title = "<?php echo $title ?>";
                    
                    var chartsData = <?php echo json_encode($charts)?>;

        </script>   
        <script src="../js/report_followups.js" type="text/javascript"></script>
        <script src="../js/report_followups_charts.js" type="text/javascript"></script>
        <!--<script src="../js/my.echarts.js" type="text/javascript"></script>-->
    </body>
</html>
