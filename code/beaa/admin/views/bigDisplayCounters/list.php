<div class="well well-header"><?php echo getTextValue("bigDisplayCounters", $lang) ?></div>
<a href='?mode=add'>
    <img src='<?php echo $filesPath . "add.png" ?>' title='<?php echo getTextValue("add", $lang) ?>'>
</a>
<br><br>
<div class="cats-c-con">
    <?php
    $bd_ids = getColumn("SELECT distinct bdc_bigdisplay FROM bigdisplayscounters order by bdc_bigdisplay;");

    for ($i = 0; $i < count($bd_ids); $i++) {
        $bd_counters = getColumn("SELECT counter_id FROM counters, bigdisplayscounters WHERE bdc_bigdisplay=$bd_ids[$i] AND bdc_counter=counter_id order by counter_id;");
        //var_dump($bd_counters);
        $count = count($bd_counters);
        $countTxt = getTextValue("countersNo", $lang) . " [ " . $count . " ]";
//                                $bd_name = getValue("SELECT display_name  from bigdisplays WHERE display_id=$bd_ids[$i];");

        $bdID = $bd_ids[$i];
        $bd_row = getRow("SELECT * from bigdisplays WHERE display_id=$bdID;");
        $bd_name = $bd_number = $bd_type = '';
        if ($bd_row) {
            $bd_number = $bd_row['display_number'];
            $bd_type = $bd_row['display_type'];
            $bd_name = " [ " . $bd_number . " ] " . $bd_row['display_name'];
        }
        //check if type = 1 or 4 // 1:latest, 4: latest + waiting
        $activeTxt = getTextValue("active", $lang) . " (" . getTextValue(getBigDisplayType($bd_type), $lang) . ")";
        $inActiveTxt = getTextValue("inactive", $lang) . " (" . getTextValue(getBigDisplayType($bd_type), $lang) . ")";
        $active = (($bd_type == 1 || $bd_type == 4) ? 1 : 0);
        $recallText = ($active ? $activeTxt : $inActiveTxt);
        $isEnabled = ($active ? '' : 'disabled');
        ?>

        <div class="row cats-item">
            <div class="row cats-c-hd">
                <div class="col-sm-4"><div class="status marg-h-10 <?php echo $isEnabled ?>"><?php echo $recallText ?></div></div>
                <div class="col-sm-4"><?php echo $countTxt ?></div>
                <div class="col-sm-4"><?php echo $bd_name ?></div>
                <a class="moreBtn" title="See less" href="javascript:void(0);">
                    <img src="../files/upDown.png">
                </a>
            </div>
            <div class="row cats-content">
                <?php
                $n = 0;
                while ($n < $count) {
                    $counter_name = getValue("SELECT counter_name FROM counters where counter_id = $bd_counters[$n];");
                    $dbc_id = getValue("SELECT bdc_id FROM bigdisplayscounters WHERE bdc_bigdisplay=$bd_ids[$i] AND bdc_counter=$bd_counters[$n];")
                    ?>
                    <div class="col-sm-4 ">
                        <div class="bdc">
                            <div class="bdc-del">
                                <img style='width:16px;height:16px;cursor:pointer' 
                                     src='<?php echo $filesPath . "delete.png" ?>' 
                                     onclick='if (confirm("<?php echo getTextValue("deleteQuestion", $lang) . getTextValue("questionMark", $lang) ?>"))
                                                         location.replace("<?php echo "?mode=delete&id=" . $dbc_id ?>");'>
                            </div>
                            <div class="bdc-counter"><a href='<?php echo "?mode=edit&id=" . $dbc_id ?>'><?php echo $counter_name ?></a></div>
                        </div>
                    </div>
                    <?php
                    $n++;
                }
                ?>
            </div>
        </div>
        <?php
    }
    ?>

</div>

