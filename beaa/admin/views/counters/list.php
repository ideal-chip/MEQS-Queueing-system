<div class="well well-header"><?php echo getTextValue("counters", $lang) ?></div>
<table class="table table-striped s-80">
    <tr>
        <th ></th>
        <th ><?php echo getTextValue("counterName", $lang) ?></th>
        <th ><?php echo getTextValue("counterNo", $lang) ?></th>
        <th ><?php echo getTextValue("counterDisplay", $lang) ?></th>
        <th ><?php echo getTextValue("counterAudio", $lang) ?></th>
        <th ><?php echo getTextValue("directTransferCategory", $lang) ?></th>
        <th ><?php echo getTextValue("canPickTickets", $lang) ?></th>
        <th ><?php echo getTextValue("counterZone", $lang) ?></th>
        <th>Feedback</th>
    <tr>
        <?php

        $query = "SELECT * FROM counters,displays,zones,audios WHERE counter_zone=zone_id AND counter_audio=audio_id AND counter_display=display_id;";
        $counterInfo = getArray($query);
        foreach ($counterInfo AS $row) {
            $dirCatKey = getValue("SELECT category_key FROM categories WHERE category_id=" . $row['direct_transfer_category']);
            //var_dump($dirCatKey);
            $dirCat = (is_null($dirCatKey) ? "-" : getTextValue($dirCatKey, $lang));
            $isPicker = ($row['can_pick_tickets'] == 1 ? getTextValue('yes', $lang) : getTextValue('no', $lang));
            $redBox = ($row['can_pick_tickets'] == 1 ? "red-box" : "");
            if (strlen($dirCat) > 40) {
                $dirCat = substr($dirCat, 0, 40) . "...";
            }
            ?>
        <tr>
            <td >
                <img style='width:16px;height:16px;cursor:pointer' 
                     src='<?php echo $filesPath . "/delete.png" ?>' 
                     onclick='if (confirm("<?php echo getTextValue("deleteQuestion", $lang) . getTextValue("questionMark", $lang) ?>"))
                                                             location.replace("<?php echo "?mode=delete&id=" . $row['counter_id'] ?>")'>
            </td>
            <td >
                <a href='<?php echo "?mode=edit&id=" . $row['counter_id'] ?>'><?php echo $row['counter_name'] ?>  </a>
            </td>
            <td ><?php echo $row['counter_no'] ?>  </td>
            <td ><?php echo $row['display_name'] ?>  </td>
            <td ><?php echo $row['audio_name'] ?>  </td>
            <td ><?php echo $dirCat ?>  </td>
            <td class="<?php echo $redBox ?>"><?php echo $isPicker ?>  </td>
            <td ><?php echo $row['zone_name'] ?>  </td>
            <td class="single-line">
                <?php
                $feedbackBase = defined('BASE_URL')
                    ? BASE_URL
                    : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']);
                $fbUrl = $feedbackBase . '/feedback/' . $row['counter_id'] . '/';
                ?>
                <a href="<?php echo $fbUrl ?>" target="_blank" class="btn btn-xs btn-info" title="Open"><i class="glyphicon glyphicon-new-window"></i></a>
                <button type="button" class="btn btn-xs btn-default" onclick="copyFeedbackLink('<?php echo $fbUrl ?>', this)" title="Copy"><i class="glyphicon glyphicon-copy"></i></button>
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
