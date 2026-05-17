<div class="well well-header"><?php echo getTextValue("addEditKioskButtons", $lang) ?></div>
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
        <tr>
            <td><?php echo getTextValue("kbKiosk", $lang) ?></td>
            <td>
                <select style='width:100%;' name='kiosk' class="form-control form-control-sm">
                    <?php
                    $kiosks = getArrayAssoc("SELECT kiosk_id, kiosk_name FROM kiosks;");
                    foreach ($kiosks as $kiosk) {
                        $kiosk_id = $kiosk["kiosk_id"];
                        $kiosk_name = $kiosk["kiosk_name"];
                        ?>
                        <option value="<?php echo $kiosk_id ?>" <?php echo ($kbKiosk == $kiosk_id ? ' selected' : '') ?> >
                            <?php echo $kiosk_name ?>
                        </option>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?php echo getTextValue("kbCategory", $lang) ?></td>
            <td>
                <select style='width:100%;' name='category' class="form-control form-control-sm">
                    <?php
                    $catListQuery = "SELECT category_id, category_key FROM categories;";
                    $categoryListInfo = getKeyValArray(getArrayAssoc($catListQuery), 'category_id', 'category_key');

                    foreach ($categoryListInfo as $categoryID => $categoryKey) {
                        $catName = $categoryKey . " - " . getTextValue($categoryKey, $lang);
                        ?>
                        <option value='<?php echo $categoryID ?>' <?php echo ($kbCategory == $categoryID ? ' selected' : '') ?>  > <?php echo $catName; ?> </option>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?php echo getTextValue("choosepriority", $lang) ?></td>
            <td>
                <select name="priority" class="form-control form-control-sm" >
                    <optgroup label="<?php echo getTextValue("choosepriority", $lang) ?>">
                        <option <?php echo $kbPriority == 0 ? 'selected' : ''; ?> value="0"><?php echo getTextValue("noPriority", $lang) ?></option>
                        <?php
                        var_dump($kbPriority);
                        for ($i = 10; $i >= 1; $i--) {

                            $selected = $kbPriority == $i ? 'selected' : '';
                            ?>
                            <option value="<?php echo $i ?>" <?php echo $selected ?>><?php echo getTextValue("priority", $lang) ?> <?php echo showPriority($i, 10); ?></option>
                            <?php
                        }
                        ?>
                    </optgroup>
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