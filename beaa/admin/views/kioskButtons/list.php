<div class="well well-header"><?php echo getTextValue("kioskbuttons", $lang) ?></div>
<table class="table table-striped table-80">
    <tr>
        <th></th>
        <th ><?php echo getTextValue("kbKiosk", $lang) ?></th>
        <th ><?php echo getTextValue("kbCategory", $lang) ?></th>
        <th ><?php echo getTextValue("kbPriority", $lang) ?></th>
    <tr>
        <?php

        $query = "SELECT kb_id, kiosk_name, category_key, kb_priority FROM kioskbuttons,kiosks,categories WHERE kb_kiosk=kiosk_id AND kb_category=category_id ORDER BY kiosk_id, category_id;";
        $kioskBtns = getArray($query);
        foreach ($kioskBtns as $row) {
            $catkey = $row['category_key'];
            $catName = $catkey . " - " . getTextValue($catkey, $lang);
            $priority = showPriority($row['kb_priority'], 10);
            
            ?>
        <tr>
            <td >
                <img 
                    style='width:16px;height:16px;cursor:pointer' 
                    src='<?php echo $filesPath . "delete.png" ?>' 
                    onclick='if (confirm("<?php echo getTextValue("deleteQuestion", $lang) . getTextValue("questionMark", $lang) ?>"))
                                                            location.replace("<?php echo "?mode=delete&id=" . $row['kb_id'] ?>")'>
            </td>
            <td >
                <a href='<?php echo "?mode=edit&id=" . $row['kb_id'] ?>'>
                    <?php echo $row['kiosk_name'] ?>
                </a>
            </td>
            <td class="text-left"><?php echo $catName ?>  </td>
            <td ><?php echo $priority ?>  </td>
        </tr>
        <?php
    }
    ?>
</table>
<br>
<a href='?mode=add'>
    <img src='<?php echo $filesPath . "add.png" ?>' title='<?php echo getTextValue("add", $lang) ?>'>
</a>