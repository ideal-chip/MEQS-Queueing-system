<?php
$prev = 64;

require_once './common/php_head.php';
//-------------------------------------------------------------< common vars >---
$title = getTextValue('search', $lang);
$view = "./views/search/";
$parent = "";
//-------------------------------------------------------------< other includes >---
include_once $view . 'process.php';

//-------------------------------------------------------------< data >---
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include_once './common/head.php'; ?>
        <link href="../css/themes/base/all.css" rel="stylesheet" type="text/css"/>
        <link href="../css/search.css" rel="stylesheet" type="text/css"/>
        <!--<link rel="shortcut icon" href="../files/shortcut_icons/search.png"/>-->
    </head>
    <body style="direction:<?php echo $dir ?>;">
        <?php include_once './common/nav.php'; ?>
        <?php include_once './common/header.php'; ?>

        <div class="container-full marg-bottom-50">

            <?php include_once './views/search/form.php'; ?>
            
            <div>
                <div class="marg3 pull-left pad-3">
                    <?php
                    if ($prevPage > 0) {
                        ?> 
                        <button  type="button" onclick="goToPage(<?php echo $prevPage ?>)" class="btn btn-default btn-xs no-radius sh-blue pad-v-5"><i class="glyphicon <?php echo "glyphicon-chevron-" . $left ?>"></i> <span> <?php echo $prevPage ?></span></button>
                        <?php
                    }
                    ?>
                    <?php
                    if ($totalPages > 1) {
                        ?>
                        <button  type="button" onclick="goToPage(<?php echo '1' ?>)" class="btn btn-default no-radius sh-blue pad-v-5 btn-xs"> <span> <?php echo $firstPage ?></span> </button>
                        <?php
                        $start = $page;
                        $skip = TRUE;
                        if (($totalPages - $page) < 10) {
                            $start = $totalPages <= 9 ? 1 : $totalPages - 9;
                            $skip = FALSE;
                        }
                        $pCounter = 0;
                        for ($p = $start; $p <= $totalPages; $p++) {
                            $marked = $page == $p ? 'btn-primary' : '';
                            $pCounter++;
                            ?>
                            <button  type="button" onclick="goToPage(<?php echo $p ?>)" class="btn btn-default btn-xs <?php echo $marked ?>"> <span> <?php echo $p ?></span> </button>
                            <?php
                            if ($totalPages > 10 && $pCounter == 5 && $skip) {
                                $p = $totalPages - 5;
                                ?>
                                <span> ... </span>
                                <?php
                            }
                        }
                        ?>
                        <button  type="button" onclick="goToPage(<?php echo $totalPages ?>)" class="btn btn-default btn-xs no-radius sh-blue pad-v-5"> <span> <?php echo $lastPage ?></span> </button>
                        <?php
                    }
                    ?>
                    <?php
                    if ($nextPage > 0) {
                        ?>
                        <button  type="button" onclick="goToPage(<?php echo $nextPage ?>)" class="btn btn-default btn-xs no-radius sh-blue pad-v-5"><i class="glyphicon <?php echo "glyphicon-chevron-" . $right ?>"></i> <span> <?php echo $nextPage ?></span> </button>
                        <?php
                    }
                    ?>
                </div> 
            </div>
            <div id="q-result" class="" >
                <table class="table table-bordered table-striped table-condensed small table-close text-center s-100"> 
                    <thead> 

                        <tr class="bg-primary text-center-th"> 
                            <th></th>
                            <th><?php echo $serialNo ?></th> 
                            <th><?php echo $clientInfo ?></th> 
                            <th><?php echo $lang_categories ?></th>
                            <th ><?php echo $clerkNameLang ?></th> 
                            <th ><?php echo $dateTime . "<br> [$created]" ?></th>
                            <th ><?php echo $dateTime . "<br> [$finished]" ?></th> 
                            <th><?php echo $estimatdWaitTime ?></th> 
                            <th><?php echo $totalProcessTime . "<br> ($days)" ?></th> 
                            <th class="no-print"></th>
                            <th class="no-print"></th>
                        </tr> 

                    </thead> 
                    <tbody> 
                        <?php
                        $order = (($page - 1) * $max ) + 1;
                        foreach ($searchResult as $Row) {
                            $followupId = $Row['followup_id'];
                            $dateCreated = $Row['date_created'];
                            $dateDone = $Row['date_done'] ? $Row['date_done'] : '-';
                            $done = $dateDone == '-';
                            $totalDays = $Row['total_days'] != NULL ? $Row['total_days'] : '-';

                            $smsDone = $Row['date_sms_sent'] ? true : false;

                            $subCat_ID = $Row['subcategory_id'];
                            $subCat = getRow("SELECT subcategory_name, wait_time_days FROM subcategories WHERE subcategory_id = $subCat_ID;");

                            $catID = $Row['category_id'];
                            $catName = getValue("SELECT text_value FROM texts WHERE text_language = '$lang'
                                     AND text_key = (SELECT category_key FROM categories WHERE category_id = $catID);");

                            $clerkID = $Row['clerk_id'];
                            $clerkName = getValue("SELECT clerk_name FROM clerks WHERE clerk_id = $clerkID;");

                            $rowId = "r-$order";
                            ?>

                            <tr id="<?php echo $rowId ?>" >
                                <td><?php echo $order ?></td>
                                <td>
                                    <?php echo $Row['serial_no'] . "<br>" ?>
                                    <?php
                                    if ($smsDone) {
                                        ?>
                                        <span class="badge pad-3  text-primary">SMS <i class="fa fa-check-circle"></i> </span>
                                        <?php
                                    }
                                    ?>
                                </td>
                                <td class="text-left">
                                    <div><?php echo $Row['client_name'] ?></div>
                                    <div class="bg-success pad-h-5"><?php echo $Row['mobile_number'] ?></div>
                                </td>
                                <td class="text-left s-15">
                                    <div><?php echo $catName ?></div>
                                    <div class="bg-success pad-h-5"><?php echo $subCat['subcategory_name'] ?></div>
                                </td>
                                <td><?php echo $clerkName ?></td>
                                <td ><?php echo $dateCreated ?></td>
                                <td ><?php echo $dateDone ?></td>
                                <td><?php echo $subCat['wait_time_days'] ?></td>
                                <td><?php echo $totalDays ?></td>
                                <td class="tb-remove text-left no-print">
                                    <a class="btn btn-success btn-xs" onclick="editBookingRow(<?php echo $followupId ?>, '<?php echo $rowId ?>')" href="javascript:void(0);"><?php echo $edit ?></a> |
                                    <a class="btn btn-danger btn-xs" onclick="deleteRow(<?php echo $followupId ?>, '<?php echo $rowId ?>')" href="javascript:void(0);"><?php echo $delete ?></a> |
                                    <a class="btn btn-link btn-xs" onclick="showFollowupPreview(<?php echo $followupId ?>, '<?php echo $rowId ?>')" href="javascript:void(0);">
                                        <i class="fa fa-print"></i>
                                    </a>

                                </td>
                                <td class="tb-remove text-left no-print">
                                    <?php
                                    if ($done) {
                                        ?>
                                        <a class="btn btn-info btn-xs" onclick="markProcessed(<?php echo $followupId ?>, '<?php echo $rowId ?>')" href="javascript:void(0);"><?php echo $markProcessed ?></a>
                                        <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                            $order++;
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr class="text-center-th">
                            <th colspan="11">
                                <div class="marg-v-10 pad-h-10 bg-green-darker text-whito  pull-right"><?php echo "$followupCards: " . $searchSize ?></div>
                                <div class="marg-v-10 pad-h-10 bg-white-gray pull-right text-dark"><?php echo "$page /($totalPages)" ?></div>
                                <div class="marg-10 pad-h-10 bg-white-gray pull-left round-5-sh text-dark"><?php echo $dateToday ?></div>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <?php require_once './views/search/_followupModal.php'; ?>
        <?php include_once './common/footer.php'; ?>

        <?php include_once './common/foot_scripts.php'; ?>

        <script src="../js/moment.min.js" type="text/javascript"></script>
        <script src="../js/jquery-ui-1.12.1.min.js" type="text/javascript"></script>
        <script src="../js/jQuery.print.min.js" type="text/javascript"></script>
        <script src="../js/FileSaver.min.js" type="text/javascript"></script>
        <script type="text/javascript">

                                            var currentLang = '<?php echo $lang ?>';
                                            var lang_enterTime = "<?php echo getTextValue('enterTime', $lang); ?>";
                                            var lang_eventPriority = '<?php echo getTextValue('eventPriority', $lang); ?>';
                                            var lang_eventNo = '<?php echo getTextValue('eventNo', $lang); ?>';
                                            var lang_eventsWaiting = '<?php echo $eventsWaiting; ?>';
                                            var lang_closed = "<?php echo getTextValue('closed', $lang); ?>";
                                            var lang_opened = "<?php echo $opened; ?>";
                                            var lang_empty = "<?php echo $empty ?>";
                                            var lang_active = '<?php echo getTextValue('active', $lang) ?>';
                                            var lang_inactive = '<?php echo getTextValue('inactive', $lang) ?>';
                                            var lang_recall = '<?php echo $recall ?>';

                                            var lang_clientName = '<?php echo $clientName ?>';
                                            var lang_phoneNumber = '<?php echo $phoneNumber ?>';
                                            var lang_directorate = '<?php echo $directorate ?>';
                                            var lang_serviceType = '<?php echo $serviceType ?>';
                                            var lang_dateTime = '<?php echo $dateTime ?>';
                                            var lang_serialNo = '<?php echo $serialNo ?>';
                                            var lang_clerkName = '<?php echo $clerkNameLang ?>';
                                            var lang_edit = '<?php echo $edit ?>';
                                            var lang_delete = '<?php echo $delete ?>';
                                            var lang_firstPage = '<?php echo $firstPage ?>';
                                            var lang_lastPage = '<?php echo $lastPage ?>';
                                            var lang_deleteQuestion = '<?php echo $deleteQuestion . $questionMark ?>';
                                            var lang_confirmQuestion = '<?php echo $areYouSure . $questionMark ?>';
                                            var lang_markProcessed = '<?php echo $markProcessed ?>';

                                            var msg_logoutMessage = "<?php echo getTextValue('logoutMessage', $lang); ?>";
                                            var langSearchReport = "<?php echo "$search - $followupCards" ?>";

                                            var todayDate = '<?php echo $dateToday ?>';
                                            var title = "<?php echo $title ?>";
        </script>   
        <script src="../js/search.js" type="text/javascript"></script>
    </body>
</html>
