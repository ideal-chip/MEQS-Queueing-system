<?php

$value = getSetting("bulkDelay");
$bulkDelay = $value == 'zero' ? 0 : $value;

?>

<div class="well well-header"><?php echo getTextValue("bigDisplayBulk", $lang) ?></div>
<div class="s-90 center-block">
    <div class="row ">
        <div class="col-xs-6  bg-info">
            <div class="input-group marg83 pad-h-10" >
                <!--<input type="number" min="0" max="100" maxlength="0" class="form-control" placeholder="waiting time">-->

                <div class="inline-block alert alert-danger no-marg"><?php echo getTextValue("callingDelay", $lang) ?></div>
                <div class="alert-info no-pad inline-block pad-h-10">
                    <span id="delayVal" class="badge pad-5 marg-5"><?php echo $bulkDelay ?></span> <?php echo getTextValue("minutes", $lang) ?>
                </div>

                <div class="btn-group btn-group-sm" role="group" aria-label="Spinner">
                    <button onclick="updateDelay('up');" type="button" class="btn btn-default"><i class="fa fa-arrow-up"></i></button>
                    <button onclick="updateDelay('down');" type="button" class="btn btn-default"><i class="fa fa-arrow-down"></i></button>
                </div>
                <div class="btn-group">
                    <button id="bulkBtn"  onclick="setDelay()" class="btn btn-primary btn-sm no-marg" type="submit">
                        <?php echo getTextValue("edit", $lang) ?>  <i class="fa fa-clock-o"></i>
                    </button>
                </div>

            </div>
        </div>
        <div class="col-xs-3">
            <div class="bg-info  pad-h-5">
                <div class=" marg3 left" id="bulk-active"> ... </div>
                <div class="inline-block marg3 left">
                    <button  id="blk-start" onclick="setBulkStatus(1);" class="btn btn-xs btn-danger arc-top"><?php echo getTextValue('startBulkCalling', $lang) ?></button>
                    <button style="display: none;" id="blk-stop" onclick="setBulkStatus(0);" class="btn btn-xs btn-primary arc-top"><?php echo getTextValue('stopBulkCalling', $lang) ?></button>
                </div> 
            </div>

        </div>
        <div class="col-xs-3 text-right">
            <a id="recall-all" href="javascript:void(0);" onclick="updateAll();" class="btn btn-info btn-sm marg-h-20 hidden">
                <span class="txt"><?php echo getTextValue('recallAll', $lang) ?></span>
                <span class="glyphicon glyphicon-refresh"></span>
            </a>
            <a href='?mode=add' class="btn">
                <img src='<?php echo $filesPath . "add.png" ?>' title='<?php echo getTextValue("add", $lang) ?>'>
            </a>
        </div>
    </div>
</div>

<br>
<div class="cats-c-con">
    <?php
    $conn = mysqli_connect($dbhost, $dbusername, $dbpassword, $dbname);
    mysqli_set_charset($conn, "utf8");
    $bd_ids = getColumn("SELECT distinct bd_id FROM bigdisplayservices order by bd_id;");


    for ($i = 0; $i < count($bd_ids); $i++) {

        $bdID = $bd_ids[$i];
        $bd_categires = getColumn("SELECT bigdisplayservices.category_id FROM categories, bigdisplayservices WHERE bd_id=$bdID AND categories.category_id = bigdisplayservices.category_id order by bigdisplayservices.category_id;");
        //var_dump($bd_counters);
        $count = count($bd_categires);
        $countTxt = getTextValue("categoriesCount", $lang) . " [ " . $count . " ]";
        $bd_row = getRow("SELECT * from bigdisplays WHERE display_id=$bdID;");
        $bd_name = $bd_number = $bd_type = '';
        if ($bd_row) {
            $bd_number = $bd_row['display_number'];
            $bd_type = $bd_row['display_type'];
            $bd_name = " [ " . $bd_number . " ] " . $bd_row['display_name'];
            $bd_goto = getTextValue('gotoPlace', $lang) . ": " . $bd_row['goto'];
        }
        //check if type = 1, 1:bulk
        $active = ($bd_type == 2 ? 1 : 0);
        $recallText = ($active ? getTextValue("recall", $lang) : getTextValue("inactive", $lang));
        $isEnabled = ($active ? '' : 'disabled');
        ?>

        <div class="row cats-item">
            <div class="row cats-c-hd">
                <div class="col-sm-2 cats-c-item">
                </div>
                <div class="col-sm-4 cats-c-item"><?php echo $bd_goto ?></div>
                <div class="col-sm-3 cats-c-item"><?php echo $countTxt ?></div>
                <div class="col-sm-3 cats-c-item"><?php echo $bd_name ?></div>

                <a class="moreBtn" title="See less" href="javascript:void(0);">
                    <img src="../files/upDown.png">
                </a>
            </div>
            <div class="row cats-content">

                <table class="table table-striped s-100">
                    <tr>
                        <th ></th>
                        <th ><?php echo getTextValue("category", $lang) ?></th>
                        <th ><?php echo getTextValue("ticketsNO", $lang) ?></th>
                        <th ><?php echo getTextValue("priority", $lang) ?></th>
                    </tr>
                    <?php
                    $conn = mysqli_connect($dbhost, $dbusername, $dbpassword, $dbname);
                    mysqli_set_charset($conn, "utf8");
                    $query = mysqli_query($conn, "SELECT * FROM bigdisplayservices WHERE bd_id=$bd_ids[$i] ;");
                    while ($row = mysqli_fetch_assoc($query)) {
                        $q = "SELECT category_key FROM categories WHERE category_id =" . $row['category_id'];
                        $categoryName = getTextValue(getValue($q), $lang);
                        ?>
                        <tr>
                            <td class="s-10">
                                <img style='width:16px;height:16px;cursor:pointer;' src='<?php echo $filesPath . "delete.png"; ?>' onclick='if (confirm("<?php echo getTextValue("deleteQuestion", $lang) . getTextValue("questionMark", $lang) ?> "))
                                                    location.replace("<?php echo "?mode=delete&id=" . $row['bds_id'] ?>");' >
                            </td>
                            <td class="s-50">
                                <a href="?mode=edit&id=<?php echo $row['bds_id']; ?>" ><?php echo $categoryName; ?>
                                </a>
                            </td>
                            <td class="s-20">
                                <?php echo $row['qty']; ?> 
                            </td>
                            <td class="s-20">
                                <?php echo $row['priority']; ?> 
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </div>
        </div>
        <?php
    }
    ?>

</div>
<script type="text/javascript">
    //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| PHP Variables
    var updateText = '<?php echo getTextValue('updated', $lang) ?>';
    var recallText = '<?php echo getTextValue('recall', $lang) ?>';
    var recallAllText = '<?php echo getTextValue('recallAll', $lang) ?>';
    var updateDelayText = '<?php echo getTextValue('edit', $lang) ?>';
    var lang_active = '<?php echo getTextValue('active', $lang) ?>';
    var lang_inactive = '<?php echo getTextValue('inactive', $lang) ?>';

</script>