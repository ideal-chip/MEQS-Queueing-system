<div id="subcategories-list" class="table-responsive marg-v-10 center-block">
    <table class="table table-bordered table-striped s-100 table-close" style="font-size: 11px;">
        <tr class="bg-green-light text-gray text-center-th ">
            <th colspan="9"><?php echo $avarageOfFinished ?>  </th>
        </tr>
        <tr class="bg-green-light text-gray text-center-th">
            <th rowspan="2" class="s-20"><?php echo $mainService ?>  </th>
            <th class="s-30" rowspan="2"><?php echo $subcategories ?>  </th>
            <th rowspan="2" class="s-10"><?php echo $waitTime ?>  </th>
            <th colspan="2" class="s-10"><?php echo $lessWaittime ?> </th>
            <th colspan="2" class="s-10"><?php echo $moreWaittime ?></th>
            <th colspan="2" class="s-10"><?php echo $total ?></th>
        </tr>
        <tr class="bg-green-light text-gray text-center-th border-btm-hdr">
            <th ><?php echo $counts ?></th>
            <th ><?php echo $avarage ?></th>
            <th ><?php echo $counts ?></th>
            <th ><?php echo $avarage ?></th>
            <th ><?php echo $counts ?></th>
            <th ><?php echo $avarage ?></th>
        </tr>
        <?php
        $index = 0;

        foreach ($categoriesList as $cat) {

            $catId = $cat['cat'];
            $name = $cat['name'];
            $size = CheckInReportCount($catId);

            $subIds = getColumn("SELECT subcategory_id FROM subcategories WHERE main_category_id = $catId;");

            $waitTime_below = 0;
            $waitTime_above = 0;
            $waitTime_all = 0;

            $avgTotal_below = 0;
            $avgTotal_above = 0;
            $avgTotal_all = 0;

            $total_days_1 = 0;
            $total_days_2 = 0;
            $total_days_3 = 0;

            $count = 0;
            foreach ($subIds as $subId) {

                $subData = getSubData($subId, $dateStart, $dateEnd);

                $subInfo = getRow("SELECT subcategory_name, wait_time_days, in_report  FROM subcategories WHERE subcategory_id = $subId;");
                $subName = $subInfo['subcategory_name'];
                $subWait = $subInfo['wait_time_days'];

                $belowTotal = $subData[0]['total'];
                $belowAvg = number_format($subData[0]['avg'], 1);
                $aboveTotal = $subData[1]['total'];
                $aboveAvg = number_format($subData[1]['avg'], 1);
                $allTotal = $subData[2]['total'];
                $allAvg = number_format($subData[2]['avg'], 1);

                $subEff_Below = GetPercenatage($belowAvg, $subWait, $belowTotal);
                $subEff_Above = GetPercenatage($aboveAvg, $subWait, $aboveTotal);
                $subEff_All = GetPercenatage($allAvg, $subWait, $allTotal);

                if ($subInfo['in_report'] > 0) {

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

                    <tr>
                        <?php if ($count == 0) { ?>
                            <td rowspan="<?php echo ($size + 1) ?>" class="vertical-middel"><?php echo $name ?>  </td>
                        <?php } ?>

                        <td><?php echo $subName ?>  </td>
                        <td><?php echo $subWait ?>  </td>

                        <?php if ($allTotal > 0) { ?>

                            <td><?php echo $belowTotal ?></td>
                            <td><?php echo $belowAvg ?></td>
                            <td><?php echo $aboveTotal ?></td>
                            <td><?php echo $aboveAvg ?></td>
                            <td><?php echo $allTotal ?></td>
                            <td><?php echo $allAvg . $subEff_All ?></td>


                        <?php } else { ?>

                            <td colspan="6"><?php echo "NO DATA" ?></td>

                        <?php } ?>


                    </tr>

                    <?php
                    
                    $count++;
                }
            }

            if ($count > 0) {

                //==============================| calculate for all
                $eff_below = GetPercenatage($avgTotal_below, $waitTime_below, $total_days_1);
                $eff_above = GetPercenatage($avgTotal_above, $waitTime_above, $total_days_2);
                $eff_all = GetPercenatage($avgTotal_all, $waitTime_all, $total_days_3);
                ?>

                <!--=======================================|| TOTAL per MAIN SERVICE-->
                <tr class="total-row">
                    <td colspan="2">
                        <span class="<?php echo ($dir == 'rtl') ? 'text-left' : 'text-right'; ?> font-bold">
                            <?php echo "TOTAL" ?>
                        </span>
                    </td>
                    <td><?php echo $total_days_1 ?></td>
                    <td colspan="1"><?php // echo $eff_below             ?></td>
                    <td><?php echo $total_days_2 ?></td>
                    <td colspan="1"><?php // echo $eff_above              ?></td>
                    <td><?php echo $total_days_3 ?></td>
                    <td colspan="1"><?php echo $eff_all ?></td>
                </tr>
                <?php
            }
        }
        ?>
    </table>
</div>