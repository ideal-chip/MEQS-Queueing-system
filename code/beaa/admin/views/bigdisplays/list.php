
<div class="well well-header"><?php echo getTextValue("bigdisplays", $lang) ?></div>
<table class="table table-striped s-80">
    <tr>
        <th></th>
        <th ><?php echo getTextValue("bigDisplayName", $lang) ?></th>
        <th ><?php echo getTextValue("number", $lang) ?></th>
        <!--<th ><?php echo getTextValue("group", $lang) ?></th>-->
        <th ><?php echo getTextValue("type", $lang) ?></th>
        <th ><?php echo getTextValue("gotoPlace", $lang) ?></th>
        <th ><?php echo getTextValue("arrowDir", $lang) ?></th>
        <th ><?php echo getTextValue("bigDisplayZone", $lang) ?></th>
    <tr>
        <?php
        $bigdisplays = getArrayAssoc("SELECT * FROM bigdisplays,zones WHERE display_zone=zone_id;");

        for ($d = 0; $d < count($bigdisplays); $d++) {
            $row = $bigdisplays[$d];

            $bigdisplayType = getBigDisplayType($row['display_type']);
            $goto = $row['goto'] != null ? $row['goto'] : "-";
            $arrow_dir = getArrowDirection($row['arrow_dir']);
            $arrowStyle = ($row['arrow_dir'] == 0) ? 'asterisk' : 'arrow-' . strtolower($arrow_dir);
            ?>  
        <tr>
            <td>
                <img style='width:16px;height:16px;cursor:pointer;' src='<?php echo $filesPath . "delete.png"; ?>' onclick='if (confirm("<?php echo getTextValue("deleteQuestion", $lang) . getTextValue("questionMark", $lang) ?> "))
                                                        location.replace("<?php echo "?mode=delete&id=" . $row['display_id'] ?>");' >
            </td>
            <td ><a href="?mode=edit&id=<?php echo $row['display_id']; ?>" ><?php echo $row['display_name']; ?></a>
            <td ><?php echo $row['display_number'] ?> </td>
            <td ><?php echo getTextValue($bigdisplayType, $lang) ?> </td>
            <td ><?php echo $goto ?> </td>
            <td ><?php echo $arrow_dir ?> <i class="small glyphicon glyphicon-<?php echo $arrowStyle ?>"></i></td>
            <td ><?php echo $row['zone_name'] ?> </td>
        <tr>
            <?php
        }
        ?>
</table>
<br>
<a href='?mode=add'>
    <img src='<?php echo $filesPath . "add.png" ?>' title='<?php echo getTextValue("add", $lang) ?> '>
</a>

