<div class="well well-header"><?php echo getTextValue("zones", $lang) ?></div>
<table class="table table-striped">
    <tr>
        <th ></th>
        <th ><?php echo getTextValue("zoneName", $lang) ?></th>
        <th ><?php echo getTextValue("zoneDesc", $lang) ?></th>
    </tr>
    <?php
    $conn = mysqli_connect($dbhost, $dbusername, $dbpassword, $dbname, DB_PORT);
    mysqli_set_charset($conn, "utf8");
    $query = mysqli_query($conn, "SELECT * FROM zones;");
    while ($row = mysqli_fetch_assoc($query)) {
        ?>
        <tr>
            <td >
                <img style='width:16px;height:16px;cursor:pointer;' src='<?php echo $filesPath . "/delete.png"; ?>' onclick='if (confirm("<?php echo getTextValue("deleteQuestion", $lang) . getTextValue("questionMark", $lang) ?> "))
                                                        location.replace("<?php echo "?mode=delete&id=" . $row['zone_id'] ?>");' >
            </td>
            <td >
                <a href="?mode=edit&id=<?php echo $row['zone_id']; ?>" ><?php echo $row['zone_name']; ?>
                </a>
            </td>
            <td >
                <?php echo $row['zone_desc']; ?> 
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