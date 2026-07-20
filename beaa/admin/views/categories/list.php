<div class="well well-header"><?php echo getTextValue("categories", $lang) ?></div>
<table class="table table-striped">
    <tr>
        <th></th>
        <th  colspan="2"><?php echo getTextValue("categoryKey", $lang) ?></th>
        <th ><?php echo getTextValue("serialNoRef", $lang) ?></th>
        <th ><?php echo getTextValue("categoryChar", $lang) ?></th>
        <!--<th ><?php echo getTextValue("categoryParent", $lang) ?></th>-->
        <th ><?php echo getTextValue("categoryZone", $lang) ?></th>
        <th ><?php echo getTextValue("categoryEnabled", $lang) ?></th>
    </tr>
    <?php
    $catQuery = "SELECT * FROM categories,zones WHERE category_zone=zone_id;";
    $categories = getArrayAssoc($catQuery);
    $mainCatCounter = 0;

    foreach ($categories as $Row) {
        $rowID = $Row['category_id'];
        $rowKey = $Row['category_key'];
        $rowSerialNoRef = $Row['serial_no_ref'];
        $rowEnabled = $Row['category_enabled'];
        $rowCategoryChar = $Row['category_char'];
        $rowZoneName = $Row['zone_name'];

        $mainCatCounter++;
        ?>
        <tr>
            <?php
            if ($mainCatCounter > getSetting("minimumCategoriesCount")) {
                ?>
                <td >
                    <img style='width:16px;height:16px;cursor:pointer' 
                         src='<?php echo $filesPath . "/delete.png" ?>'  
                         onclick='if (confirm("<?php echo getTextValue("deleteQuestion", $lang) . getTextValue("questionMark", $lang) ?>"))
                                                                     location.replace("<?php echo "?mode=delete&id=$rowID" ?>")'>
                </td>
                <?php
            } else {
                ?>
                <td ></td>
                <?php
            }
            ?>

            <td >
                <a href='<?php echo "?mode=edit&id=$rowID" ?>'> <?php echo $rowKey ?> </a>
            </td>
            <td ><?php echo getTextValue($rowKey, $lang) ?> </td>
            <td ><?php echo $rowSerialNoRef ?> </td>
            <td ><?php echo $rowCategoryChar ?> </td>
            <td ><?php echo $rowZoneName ?> </td>
            <td >
                <img src='<?php echo $filesPath . ($rowEnabled ? 'check.png' : 'uncheck.png') ?> '>
            </td>
        <tr>
            <?php
        }
        ?>
</table>
<br>
<a href='?mode=add'>
    <img src='<?php echo $filesPath . "/add.png" ?>' title='<?php echo getTextValue("add", $lang) ?>'>
</a>