<div class="well well-header"><?php echo getTextValue("counterscategories", $lang) ?></div>

<a href='?mode=add'>
    <img src='<?php echo $filesPath . "/add.png" ?>' title='<?php echo getTextValue("add", $lang) ?>'>
</a>
<br><br>
<div class="cats-c-con">

    <?php
    $counterIds = getColumn("SELECT distinct cc_counter FROM countercategories order by cc_counter;");
    for ($i = 0; $i < count($counterIds); $i++) {
        $c_cats = getColumn("SELECT * FROM countercategories,counters,categories WHERE cc_counter=counter_id AND cc_category=category_id AND cc_counter=$counterIds[$i] order by cc_counter;");
        
        $count = count($c_cats);
        
        $countTxt = getTextValue("categoriesCount", $lang) . " [ " . $count . " ]";
        $counter_name = getValue("SELECT counter_name  from counters WHERE counter_id=$counterIds[$i];");
        ?>

        <div class="row cats-item">
            <div class="row cats-c-hd">
                <div class="col-sm-8"><?php echo $countTxt ?></div>
                <div class="col-sm-4"><?php echo $counter_name ?></div>
                <a class="moreBtn" title="See less" href="javascript:void(0);">
                    <img src="../files/upDown.png">
                </a>
            </div>
            <div class="row cats-content">
                <table class="table table-striped table-100">
                    <?php
                    $n = 0;
                    while ($n < $count) {
                        $cat_key = getValue("SELECT category_key FROM categories, countercategories WHERE cc_id = $c_cats[$n] AND cc_category=category_id;");
                        $cat_lang = getTextValue($cat_key, $lang);
                        ?>
                        <tr>
                            <td class="s-10">
                                <img style='width:16px;height:16px;cursor:pointer' 
                                     src='<?php echo $filesPath . "/delete.png" ?>' 
                                     onclick='if (confirm("<?php echo getTextValue("deleteQuestion", $lang) . getTextValue("questionMark", $lang) ?>"))
                                                                                 location.replace("<?php echo "?mode=delete&id=" . $c_cats[$n] ?>");'>
                            </td>
                            <td><?php echo $cat_lang ?></td>
                            <td class="s-20"><a href='<?php echo "?mode=edit&id=" . $c_cats[$n] ?>'><?php echo $cat_key ?></a></td>

                        </tr>
                        <?php
                        $n++;
                    }
                    ?>
                </table>
                <div class="col-sm-4"></div>
            </div>
        </div>
        <?php
    }
    ?>

</div>