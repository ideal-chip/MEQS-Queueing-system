<div class="well well-header"><?php echo getTextValue("displays", $lang) ?></div>

<table class="table table-striped s-50">
    <tr>
        <th></th>
        <th ><?php echo getTextValue("displayName", $lang) ?></th>
        <th ><?php echo getTextValue("displayZone", $lang) ?></th>
    <tr>
        <?php
        $conn = mysqli_connect($dbhost, $dbusername, $dbpassword, $dbname);
        mysqli_set_charset($conn, "utf8");
        $query = mysqli_query($conn, "SELECT * FROM displays,zones WHERE display_zone=zone_id;");
        while ($row = mysqli_fetch_assoc($query)) {
            ?>
        <tr>
            <td ><img style='width:16px;height:16px;cursor:pointer' 
                      src='<?php echo $filesPath . "delete.png" ?>' 
                      onclick='if (confirm("<?php echo getTextValue("deleteQuestion", $lang) . getTextValue("questionMark", $lang) ?> "))
                                                              location.replace("<?php echo "?mode=delete&id=" . $row['display_id'] ?>");'></a></td>
            <td ><a href='<?php echo "?mode=edit&id=" . $row['display_id'] ?>'><?php echo $row['display_name'] ?>  </a></td>
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