<div class="well well-header"><?php echo getTextValue("addEditCategories", $lang) ?></div>
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
    <table class="table s-80">
        <tr class="bg-danger">
            <td><?php echo getTextValue("categoryKey", $lang) ?></td>
            <td class="cat-key-con">
                <input class="form-control form-control-sm" type='text' maxlength='6' placeholder="CAT#" name='key' value='<?php echo $categoryKey ?>'>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="bg-danger"><?php echo getTextValue($categoryKey, $lang) ?></td>
        </tr>
        <tr>
            <td><?php echo getTextValue("serialNoRef", $lang) ?></td>
            <td><input class="form-control form-control-sm" type='text' name='serial-no-ref' maxlength='1' value='<?php echo $serialNoRef ?>'></td>
        </tr>
        <tr>
            <td><?php echo getTextValue("categoryChar", $lang) ?></td>
            <td><input class="form-control form-control-sm" type='text' name='char' maxlength='1' value='<?php echo $categoryChar ?>'></td>
        </tr>
        <tr class="hidden">
            <td><?php echo getTextValue("categoryParent", $lang) ?></td>
            <td><input class="form-control form-control-sm" type='number' name='parent' hidden="hidden" value='0'></td>
        </tr>
        <tr>
            <td><?php echo getTextValue("categoryZone", $lang) ?></td>
            <td>
                <select class="form-control form-control-sm" name='zone'>
                    <?php
                    $zones = getArray("SELECT zone_id, zone_name FROM zones;");
                    foreach ($zones as $zone) {
                        ?>
                        <option value='<?php echo $zone['zone_id'] ?>' <?php echo ($categoryZone == $zone['zone_id'] ? ' selected' : '') ?> > <?php echo $zone['zone_name'] ?> </option>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?php echo getTextValue("categoryEnabled", $lang) ?></td>
            <td>
                <input class="form-control form-control-sm" type='checkbox' name='enabled' <?php echo ($categoryEnabled ? 'checked' : '' ) ?>>
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