<div class="well well-header"><?php echo getTextValue("addEditExtensionNumbers", $lang) ?></div>

<form method='POST' action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" > 
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
    <table class="table">
        <tr>
            <td><?php echo getTextValue("subFone", $lang) ?></td>
            <td><input type='text' id='ext_number' name='ext_number' maxlength="3" value='<?php echo $ext_number ?>'></td>
            
        </tr>
        <tr>
            <td><?php echo getTextValue("name", $lang) ?></td>
            <td><input type='text' id='ext_name' name='ext_name'  value='<?php echo $ext_name ?>'></td>
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