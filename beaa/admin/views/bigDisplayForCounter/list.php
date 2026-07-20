<div class="well well-header"><?php echo getTextValue("bigdisplayForCounter", $lang) ?></div>
<table class="table table-striped">
    <tr>
        <th></th>
        <th ><?php echo getTextValue("bigDisplayName", $lang) ?></th>
        <th ><?php echo getTextValue("number", $lang) ?></th>
        <th ><?php echo getTextValue("counter", $lang) ?></th>
        <th ><?php echo getTextValue("quantity", $lang) ?></th>
    <tr>
        <?php
        $query = "SELECT bigdisplayforcounter.*, display_name, display_number, counter_name FROM bigdisplayforcounter, bigdisplays, counters WHERE display_id=bigdisplayforcounter.bd_id AND counters.counter_id = bigdisplayforcounter.counter_id;";
        $bd_list = getArray($query);
        foreach ($bd_list as $bd) {
//                                    $bigdisplayType = getBigDisplayType($row['display_type']);
//                                    $goto = $row['goto'] != null ? $row['goto'] : "-";
            ?>  
        <tr>
            <td>
                <img style='width:16px;height:16px;cursor:pointer;' src='<?php echo $filesPath . "/delete.png"; ?>' onclick='if (confirm("<?php echo getTextValue("deleteQuestion", $lang) . getTextValue("questionMark", $lang) ?> "))
                                                        location.replace("<?php echo "?mode=delete&id=" . $bd['id'] ?>");' >
            </td>
            <td ><a href="?mode=edit&id=<?php echo $bd['id']; ?>" ><?php echo $bd['display_name']; ?></a>
            <td ><?php echo $bd['display_number'] ?> </td>
            <td ><?php echo $bd['counter_name'] ?> </td>
            <td ><?php echo $bd['quantity'] ?> </td>
        <tr>
            <?php
        }
        ?>
</table>
<br>
<a href='?mode=add'>
    <img src='<?php echo $filesPath . "/add.png" ?>' title='<?php echo getTextValue("add", $lang) ?> '>
</a>