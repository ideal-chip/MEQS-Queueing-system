<div class="well well-header"><?php echo getTextValue("users", $lang) ?></div>

<table class="table table-striped table-100 table-bordered">
    <tr>
        <th  rowspan="2"></th>
        <th  rowspan="2"><?php echo getTextValue("userName", $lang) ?></th>
        <th  rowspan="2"><?php echo getTextValue("userDesc", $lang) ?></th>
        <th  rowspan="2"><?php echo getTextValue("phoneNumber", $lang) ?></th>
        <th  colspan="8"><?php echo getTextValue("userPrivileges", $lang) ?></th>
    </tr>
    <tr>
        <th ><?php echo getTextValue("userPrivileges1", $lang) ?></th>
        <th ><?php echo getTextValue("userPrivileges2", $lang) ?></th>
        <th ><?php echo getTextValue("userPrivileges4", $lang) ?></th>
        <th ><?php echo getTextValue("userPrivileges8", $lang) ?></th>
        <th ><?php echo getTextValue("userPrivileges16", $lang) ?></th>
        <th ><?php echo getTextValue("userPrivileges32", $lang) ?></th>
        <th ><?php echo getTextValue("userPrivileges64", $lang) ?></th>
        <th ><?php echo getTextValue("userPrivileges128", $lang) ?></th>
    </tr>
    <?php
    $userList = getArrayAssoc("SELECT * FROM users;");
    foreach ($userList as $row) {
//        $fullName = $row['user_fullname'] ? $row['user_fullname'] : '-';
        ?>
        <tr>
            <td >
                <img style='width:16px;height:16px;cursor:pointer' 
                     src='<?php echo $filesPath . 'delete.png' ?>' 
                     onclick='if (confirm("<?php echo getTextValue("deleteQuestion", $lang) . getTextValue("questionMark", $lang) ?>"))
                                     location.replace("<?php echo "?mode=delete&id=" . $row['user_id'] ?>")'>
            </td>
            <td >
                <a href='<?php echo "?mode=edit&id=" . $row['user_id'] ?>'>
                    <?php echo $row['user_name'] ?>
                </a>
            </td>
            <td ><?php echo $row['user_desc'] ?></td>
            <td ><?php echo $row['user_phone'] ?></td>
            <?php for ($i = 0; $i < 8; $i++) { ?>
                <td ><img src='<?php echo $filesPath . (($row['user_privileges'] & (2 ** $i)) ? "check.png" : "uncheck.png"); ?>'></td>
            <?php }; ?>
        </tr>
        <?php
    }
    ?>
</table>
<br>
<a href='?mode=add'>
    <img src='<?php echo $filesPath . "add.png" ?>' title='<?php echo getTextValue("add", $lang) ?> '>
</a>