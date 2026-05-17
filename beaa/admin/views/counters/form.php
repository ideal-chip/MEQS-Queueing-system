<div class="well well-header"><?php echo getTextValue("addEditCounters", $lang) ?></div>
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
    <table class="table s-70">
        <tr>
            <td><?php echo getTextValue("counterName", $lang) ?></td>
            <td><input type='text' name='name' value='<?php echo $counterName ?>'></td>
        </tr>
        <tr>
            <td><?php echo getTextValue("counterNo", $lang) ?></td>
            <td><input type='text' name='no' value='<?php echo $counterNo ?>'></td>
        </tr>
        <tr>
            <td><?php echo getTextValue("counterDisplay", $lang) ?></td>
            <td>
                <select style='width:100%;' name='display'>
                    <?php
                    $displays = getColumn("SELECT display_id FROM displays;");
                    foreach ($displays as $display) {
                        echo "<option value='$display'" . ($counterDisplay == $display ? ' selected' : '') . ">" . getValue("SELECT display_name FROM displays WHERE display_id=$display;") . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?php echo getTextValue("counterAudio", $lang) ?></td>
            <td>
                <select style='width:100%;' name='audio'>
                    <?php
                    $audios = getColumn("SELECT audio_id FROM audios;");
                    foreach ($audios as $audio) {
                        echo "<option value='$audio'" . ($counterAudio == $audio ? ' selected' : '') . ">" . getValue("SELECT audio_name FROM audios WHERE audio_id=$audio;") . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?php echo getTextValue("counterZone", $lang) ?></td>
            <td>
                <select style='width:100%;' name='zone'>
                    <?php
                    $zones = getColumn("SELECT zone_id FROM zones;");
                    foreach ($zones as $zone) {
                        echo "<option value='$zone'" . ($counterZone == $zone ? ' selected' : '') . ">" . getValue("SELECT zone_name FROM zones WHERE zone_id=$zone;") . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?php echo getTextValue("directTransferCategory", $lang) ?></td>
            <td>
                <select style='width:100%;' name='transferdirect' >
                    <option value='0' selected> <?php echo " - " ?> </option>
                    <?php
                    $categoryInfo = getArray("SELECT category_id, category_key FROM categories;");

                    foreach ($categoryInfo as $category) {
                        $categoryId = $category['category_id'];
                        $catKey = $category['category_key'];
                        ?>
                        <option value='<?php echo $categoryId ?>' <?php echo ($counterCategoryDirect == $categoryId ? ' selected' : '') ?>  > <?php echo $catKey . " - " . getTextValue($catKey, $lang); ?> </option>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?php echo getTextValue("canPickTickets", $lang) ?></td>
            <td>
                <input type='checkbox' name='counter-picker' <?php echo ($counterPicker ? 'checked' : '' ) ?>>
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