<div class="well well-header"><?php echo getTextValue("addEditKiosks", $lang) ?></div>
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
            <td><?php echo getTextValue("kioskName", $lang) ?></td>
            <td><input type='text' name='name' value='<?php echo $kioskName ?>' class="form-control form-control-sm"></td>
        </tr>
        <tr>
            <td><?php echo getTextValue("kioskPrinterType", $lang) ?></td>
            <td>
                <select style='width:100%;' name='printertype' class="form-control form-control-sm">
                    <?php
                    foreach ($KioskPrinterTypes as $key => $value) {
                        ?>
                        <option value="<?php echo $key ?>" <?php echo ($key == $printerType) ? ' selected' : ''; ?> ><?php echo $value ?></option>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?php echo getTextValue("kioskPrinterLocation", $lang) ?></td>
            <td><input type='text' name='printerlocation' value='<?php echo $printerLocation ?>' style='text-align:left; direction:ltr;' class="form-control form-control-sm"></td>
        </tr>
        <tr>
            <td><?php echo getTextValue("printerParameters", $lang) ?></td>
            <td><input type='text' name='printerparam' value='<?php echo $printerParam ?>' class="form-control form-control-sm"></td>
        </tr>
        <tr>
            <td><?php echo getTextValue("kioskZone", $lang) ?></td>
            <td>
                <select style='width:100%;' name='zone' class="form-control form-control-sm">
                    <?php
                    $zones = getColumn("SELECT zone_id FROM zones;");
                    foreach ($zones as $zone) {
                        ?>
                        <option value='<?php echo $zone ?>' <?php echo ($kioskZone == $zone ? ' selected' : '') ?>  ><?php echo getValue("SELECT zone_name FROM zones WHERE zone_id=$zone;") ?>  </option>
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