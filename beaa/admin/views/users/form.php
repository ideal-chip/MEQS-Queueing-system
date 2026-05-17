<div class="well well-header"><?php echo getTextValue("addEditUsers", $lang) ?></div>
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
        <!--<tr><th colspan='2'><?php echo getTextValue("addEditUsers", $lang) ?></th></tr>-->
        <tr><td><?php echo getTextValue("userName", $lang) ?></td><td><input type='text' name='name' value='<?php echo $userName ?>'></td></tr>
        <tr><td><?php echo getTextValue("userPassword", $lang) ?></td><td><input type='password' name='password' value='<?php echo $userPassword ?>'></td></tr>
        <tr><td><?php echo getTextValue("userFullName", $lang) ?></td><td><input type='text' name='fullname' value='<?php echo $userFullName ?>'></td></tr>
        <tr><td><?php echo getTextValue("userDesc", $lang) ?></td><td><input type='text' name='desc' value='<?php echo $userDesc ?>'></td></tr>
        <tr><td><?php echo getTextValue("phoneNumber", $lang) ?></td><td><input type='text' name='phone' value='<?php echo $userPhone ?>'></td></tr>
    </table>

    <h3 class="sec-hd"><?php echo getTextValue("userPrivileges", $lang) ?>  </h3>

    <table class="table">
        <tr><td><?php echo getTextValue("userPrivileges1", $lang) ?></td><td><input type='checkbox' name='privileges[]' value='1' <?php if ($userPrivileges & 1) echo 'checked' ?>></td></tr>
        <tr><td><?php echo getTextValue("userPrivileges2", $lang) ?></td><td><input type='checkbox' name='privileges[]' value='2' <?php if ($userPrivileges & 2) echo 'checked' ?>></td></tr>
        <tr><td><?php echo getTextValue("userPrivileges4", $lang) ?></td><td><input type='checkbox' name='privileges[]' value='4' <?php if ($userPrivileges & 4) echo 'checked' ?>></td></tr>
        <tr><td><?php echo getTextValue("userPrivileges8", $lang) ?></td><td><input type='checkbox' name='privileges[]' value='8' <?php if ($userPrivileges & 8) echo 'checked' ?>></td></tr>
        <tr><td><?php echo getTextValue("userPrivileges16", $lang) ?></td><td><input type='checkbox' name='privileges[]' value='16' <?php if ($userPrivileges & 16) echo 'checked' ?>></td></tr>
        <tr><td><?php echo getTextValue("userPrivileges32", $lang) ?></td><td><input type='checkbox' name='privileges[]' value='32' <?php if ($userPrivileges & 32) echo 'checked' ?>></td></tr>
        <tr><td><?php echo getTextValue("userPrivileges64", $lang) ?></td><td><input type='checkbox' name='privileges[]' value='64' <?php if ($userPrivileges & 64) echo 'checked' ?>></td></tr>
        <tr><td><?php echo getTextValue("userPrivileges128", $lang) ?></td><td><input type='checkbox' name='privileges[]' value='128' <?php if ($userPrivileges & 128) echo 'checked' ?>></td></tr>
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