<?php
foreach ($categoriesList as $cat) {
    $catId = $cat['cat'];
    $name = $cat['name'];
    $size = $cat['size'];

    $subIds = getColumn("SELECT subcategory_id FROM subcategories WHERE main_category_id = $catId;");
    $count = 0;

    $waitTime_below = 0;
    $waitTime_above = 0;
    $waitTime_all = 0;

    $avgTotal_below = 0;
    $avgTotal_above = 0;
    $avgTotal_all = 0;
//
    $total_days_1 = 0;
    $total_days_2 = 0;
    $total_days_3 = 0;
    ?>
    <div class="marg-10 relative">
        <?php
        if (CheckInReportAll($catId)) {
            ?>
            <div style="margin-right: 100px;" class="bg-white-gray text-dark pad-10 text-right border-btm-hdr">
                <?php echo $name ?>
            </div>
            <?php
        }
        ?> 

        <?php
        $allSubs = 0;
        foreach ($subIds as $subId) {

            $subInfo = getRow("SELECT subcategory_name, wait_time_days, in_report FROM subcategories WHERE subcategory_id = $subId;");

            $domID = "chart_$subId";
            $subData = getSubData($subId, $dateStart, $dateEnd);

            $subName2 = $subInfo['subcategory_name'];
            $subWait = $subInfo['wait_time_days'];

            $allTotal = $subData[2]['total'];
            $allAvg = number_format($subData[2]['avg'], 1);

            $subEff_All = GetPercenatageNumber($allAvg, $subWait, $allTotal);

            if ($subEff_All != '-') {
                $arrowStyle = "glyphicon glyphicon-arrow-up";
                if (floatval($subEff_All) < 0) {
                    $arrowStyle = "glyphicon glyphicon-arrow-down";
                }

                $subEff_All = number_format($subEff_All, 1);
            } else {
                $arrowStyle = "";
            }

            if ($subInfo['in_report'] > 0) {
                $allSubs++;

                $waitTime_Total += $subWait;

                $avgTotal_below += $belowAvg;
                $avgTotal_above += $aboveAvg;
                $avgTotal_all += $allAvg;

                $waitTime_below += Accumulate($belowTotal, $subWait);
                $waitTime_above += Accumulate($aboveTotal, $subWait);
                $waitTime_all += Accumulate($allTotal, $subWait);

                $total_days_1 += $belowTotal;
                $total_days_2 += $aboveTotal;
                $total_days_3 += $allTotal;
                ?>

                <div class="sh-white bg-white marg-bottom-10 text-info" style="height: 175px; padding-bottom: 10px;">
                    <div class="s-50 inline-block vm pad-v-5">
                        <div id="<?php echo $domID ?>" class="marg-v-20"></div>
                    </div>
                    <div class="s-40 inline-block vm">
                        <div class="border-btm-blue"><?php echo $subName2 . " | $waitTime: $subWait " ?></div>
                        <div class="small">
                            <div class="round-box">
                                <div class="round-hd"><?php echo $effeciencySpeed ?></div>
                                <div class="round-bdy round-big bg-aqua pad-v-20"><i class="round-over-top <?php echo $arrowStyle ?>"></i> <span><?php echo $subEff_All ?></span><i class="round-over font-small">%</i></div>
                            </div>
                            <div class="round-box">
                                <div class="round-hd"><?php echo $total ?></div>
                                <div class="round-bdy bg-aqua font-md"><span><?php echo $allTotal ?></span><i class="round-over font-small">cards</i></div>
                            </div>
                            <div class="round-box">
                                <div class="round-hd"><?php echo $avarage ?></div>
                                <div class="round-bdy bg-aqua font-md"><span><?php echo $allAvg ?></span><i class="round-over font-small">days</i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="marg-5">
                <?php
            }
        }

        if ($allSubs > 0) {
            //==============================| calculate for all
//            $eff_below = GetPercenatage($avgTotal_below, $waitTime_below, $total_days_1);
//            $eff_above = GetPercenatage($avgTotal_above, $waitTime_above, $total_days_2);


            $eff_all = GetPercenatageNumber($avgTotal_all, $waitTime_all, $total_days_3);

            if ($eff_all != '-') {
                $arrowStyle = "glyphicon glyphicon-arrow-up";
                if (floatval($eff_all) < 0) {
                    $arrowStyle = "glyphicon glyphicon-arrow-down";
                }

                $eff_all = number_format($eff_all, 1);
            } else {
                $arrowStyle = "";
            }
            ?>
            <div class="over-mid">
                <div class="round-bdy round-sm round-bdy-blue bg-info font-small text-center sh-white marg-h-20">
                    <i class="round-over-top <?php echo $arrowStyle ?>"></i>
                    <span><?php echo $eff_all ?></span>
                    <i class="round-over-down font-small">%</i>
                </div>
<!--                <div class="round-box small no-marg">
                    <div class="round-hd"><?php echo $effeciencySpeed ?></div>
                    <div class="round-bdy round-bdy-blue bg-info font-small text-center pad-v-20 sh-white"><i class="round-over-top <?php echo $arrowStyle ?>"></i> <span><?php echo $eff_all ?></span><i class="round-over font-small">%</i></div>
                </div>-->
            </div>
        <?php
    }
    ?>
    </div>
        <?php
    }
    ?>


