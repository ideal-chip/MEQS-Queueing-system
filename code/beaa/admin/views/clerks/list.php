<div class="well well-header"><?php echo getTextValue("clerks", $lang) ?></div>
<table class="table table-striped table-100 table-bordered s-60">
    <tr>
        <th ></th>
        <th colspan="2"><?php echo getTextValue("clerkName", $lang) ?></th>
        <th ><?php echo getTextValue("clerkDesc", $lang) ?></th>
        <th ><?php echo getTextValue("phoneNumber", $lang) ?></th>
        <th ><?php echo getTextValue("clerkZone", $lang) ?></th>
    <tr>
        <?php
        $conn = mysqli_connect($dbhost, $dbusername, $dbpassword, $dbname);
        mysqli_set_charset($conn, "utf8");
        $query = mysqli_query($conn, "SELECT * FROM clerks,zones WHERE clerk_zone=zone_id;");
        while ($row = mysqli_fetch_assoc($query)) {
            ?>
        <tr>
            <td >
                <img style='width:16px;height:16px;cursor:pointer' 
                     src='<?php echo $filesPath . "delete.png" ?>' 
                     onclick='if (confirm("<?php echo getTextValue("deleteQuestion", $lang) . getTextValue("questionMark", $lang) ?>"))
                                     location.replace("<?php echo "?mode=delete&id=" . $row['clerk_id'] ?>")'></a>
            </td>
            <td >
                <a href='<?php echo "?mode=edit&id=" . $row['clerk_id'] ?>' ><?php echo $row['clerk_name'] ?>  </a>

            </td>
            <td ><?php echo $row['clerk_fullname'] ?>  </td>
            <td ><?php echo $row['clerk_desc'] ?>  </td>
            <td ><?php echo $row['clerk_phone'] ?>  </td>
            <td ><?php echo $row['zone_name'] ?>  </td>
        <tr>
            <?php
        }
        ?>
</table>
<br>
<a href='?mode=add'>
    <img src='<?php echo $filesPath . "add.png" ?>' title='<?php echo getTextValue("add", $lang) ?>'>
</a>