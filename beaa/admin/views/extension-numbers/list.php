<div class="well well-header"><?php echo getTextValue("extensionNumbers", $lang) ?></div>
<table class="table table-striped">
    <tr>
        <th></th>
        <th ><?php echo getTextValue("subFone", $lang) ?></th>
        <th ><?php echo getTextValue("name", $lang) ?></th>
    </tr>
    <?php
    $extNumbers = getArrayAssoc("SELECT * FROM extension_numbers;");
    foreach ($extNumbers as $row) {
        ?>
        <tr>
            <td >
                <img style='width:16px;height:16px;cursor:pointer' 
                     src='<?php echo $filesPath . 'delete.png' ?>' 
                     onclick='if (confirm("<?php echo getTextValue("deleteQuestion", $lang) . getTextValue("questionMark", $lang) ?>"))
                                     location.replace("<?php echo "?mode=delete&id=" . $row['extension_id'] ?>")'>
            </td>
            <td >
                <a href='<?php echo "?mode=edit&id=" . $row['extension_id'] ?>'>
                    <?php echo $row['extension_no'] ?>
                </a>
            </td>
            <td >
                <?php echo $row['extension_name']; ?> 
            </td>
        </tr>
        <?php
    }
    ?>
</table>
<br>
<a href='?mode=add'>
    <img src='<?php echo $filesPath . "/add.png" ?>' title='<?php echo getTextValue("add", $lang) ?>'>
</a>