<div class="well well-header"><?php echo getTextValue("kiosks", $lang) ?></div>
<table class="table table-striped">
    <tr>
        <th></th>
        <th ><?php echo getTextValue("kioskName", $lang) ?></th>
        <th ><?php echo getTextValue("kioskPrinterType", $lang) ?></th>
        <th ><?php echo getTextValue("kioskPrinterLocation", $lang) ?></th>
        <th ><?php echo getTextValue("kioskPrinterParam", $lang) ?></th>
        <th ><?php echo getTextValue("kioskZone", $lang) ?></th>
    </tr>
    <?php
    $kioskList = getArrayAssoc("SELECT * FROM kiosks,zones WHERE kiosk_zone=zone_id;");
    foreach ($kioskList as $row) {
        ?>
        <tr>
            <td >
                <img style='width:16px;height:16px;cursor:pointer' 
                     src='<?php echo $filesPath . '/delete.png' ?>' 
                     onclick='if (confirm("<?php echo getTextValue("deleteQuestion", $lang) . getTextValue("questionMark", $lang) ?>"))
                                     location.replace("<?php echo "?mode=delete&id=" . $row['kiosk_id'] ?>")'>
            </td>
            <td >
                <a href='<?php echo "?mode=edit&id=" . $row['kiosk_id'] ?>'>
                    <?php echo $row['kiosk_name'] ?>
                </a>
            </td>
            <td ><?php echo $row['kiosk_printer_type'] ?></td>
            <td style='direction:ltr;'><?php echo $row['kiosk_printer_location'] ?></td>
            <td ><?php echo $row['kiosk_printer_parameters'] ?></td>
            <td ><?php echo $row['zone_name'] ?></td>
        </tr>
        <?php
    }
    ?>
</table>
<br>
<a href='?mode=add'>
    <img src='<?php echo $filesPath . "/add.png" ?>' title='<?php echo getTextValue("add", $lang) ?>'>
</a>