<div class="well well-header"><?php echo getTextValue("addEditDisplays", $lang) ?></div>
<?php
if (count($errorList) > 0) {
    ?>
    <div class = "s-50 center-block no-pad">
        <div class = "alert alert-danger no-pad no-marg text-uppercase"><strong>Errors</strong></div>
        <ul id = "error-list" class = "list-unstyled alert alert-danger">
            <?php
            foreach ($errorList as $error) {
                ?> 
                <li class = "error-item"><?php echo $error ?></li>
                <?php
            }
            ?>
        </ul>
    </div>
    <?php
}
?>
<form method='POST' action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" >
    <table class="table">
        <tr>
            <td><?php echo getTextValue("displayName", $lang) ?></td>
            <td><input type='text' name='name' value='<?php echo $displayName ?>'></td></tr>
        <tr>
            <td><?php echo getTextValue("displayZone", $lang) ?></td>
            <td>
                <select style='width:100%;' name='zone'>
                    <?php
                    $zones = getColumn("SELECT zone_id FROM zones;");
                    foreach ($zones as $zone) {
                        ?>

                        <option value='<?php echo $zone ?>' <?php echo ($displayZone == $zone ? ' selected' : '') ?>  ><?php echo getValue("SELECT zone_name FROM zones WHERE zone_id=$zone;") ?>  </option>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan='2'>
                <div class="btn-group s-99">
                    <input type='hidden' name='mode' value='<?php echo $editmode ? "edit" : "add" ?>'>
                    <input type='hidden' name='id' value='<?php echo $id ?>'>
                    <div class="center-block s-100 well well-sm ">
                        <a href="?mode=list" class="btn btn-default btn-sm"><?php echo getTextValue("cancel", $lang) ?></a>
                        <input class="btn btn-primary btn-sm s-30" type='submit' value='<?php echo getTextValue($editmode ? "edit" : "add", $lang) ?>'>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</form>