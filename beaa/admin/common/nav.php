<?php
//-----------------------------------------< links >-------
$parent = "/admin/";
?>

<div class="navi bg-white-gray">
    <nav class="navbar bg-blue-deep navbar-fixed-top sh-gray-light ">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand bg-white" href="./" id="mainPage">
                    <span class="pad-5 round-5 "><img src="<?php echo $filesPath ?>/logos/ideal-q-small.png" class="" alt=""></span>
                </a>
            </div>
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

                <ul class="nav navbar-nav">
                    <!--<li class="active"><a href="#">Link <span class="sr-only">(current)</span></a></li>-->
                    <?php
                    if ($_SESSION['userpriv'] & 64) {
                    ?>
                    <li>
                        <?php createLink(getTextValue('search', $lang), $parent . "search", "glyphicon glyphicon-search") ?>
                        <?php // createLink('', $parent . "search", "glyphicon glyphicon-search", getTextValue('search', $lang)) ?>
                    </li>
                    <?php
                    }
                    if ($_SESSION['userpriv'] & 1) {
                        ?>
                        <li class = "dropdown">
                            <a href = "#" class = "dropdown-toggle" data-toggle = "dropdown" role = "button" aria-expanded = "false"><i class="fa fa-line-chart"></i> <?php echo getTextValue('userPrivileges1', $lang) ?> <span class = "caret"></span></a>
                            <ul class="dropdown-menu" role = "menu">
                                <li> <?php createLink(getTextValue('flow', $lang), $parent . "flow") ?> </li>
                                <li> <?php createLink(getTextValue('followupCards', $lang), $parent . 'followups') ?> </li>
                                <li> <?php createLink(getTextValue('feedbacks', $lang), $parent . 'feedbacks') ?> </li>
                                <li class = "divider"></li>
                                <li> <?php createLink(getTextValue('tickets', $lang), $parent . 'tickets') ?> </li>
                                <li> <?php createLink(getTextValue('ticketprinting', $lang), $parent . 'ticketentry') ?> </li>
                            </ul>
                        </li>
                        <?php
                    }
                    if ($_SESSION['userpriv'] & 16) {
                        ?>
                        <li class = "dropdown">
                            <a href = "#" class = "dropdown-toggle" data-toggle = "dropdown" role = "button" aria-expanded = "false"><i class="fa fa-gears"></i> <?php echo getTextValue('userPrivileges16', $lang) ?> <span class = "caret"></span></a>
                            <ul class = "dropdown-menu" role = "menu">
                                <li><?php createLink(getTextValue('categories', $lang), $parent . "categories") ?></li>
                                <li><?php createLink(getTextValue('subcategories', $lang), $parent . "subcategories") ?></li>
                                <li><?php createLink(getTextValue('morepapers', $lang), $parent . "morepapers") ?></li>
                                <li><?php createLink(getTextValue('extensionNumbers', $lang), $parent . "extension-numbers") ?></li>
                                <li class = "divider"></li>
                                <li><?php createLink(getTextValue('counters', $lang), $parent . "counters") ?></li>
                                <li><?php createLink(getTextValue('countersCategories', $lang), $parent . "countersCategories") ?></li>
                                <li class = "divider"></li>
                                <li><?php createLink(getTextValue('kioskButtons', $lang), $parent . "kioskButtons") ?></li>
                                <li><?php createLink(getTextValue('kiosks', $lang), $parent . "kiosks") ?></li>
                                <li class = "divider"></li>
                                <li><?php createLink(getTextValue('audios', $lang), $parent . "audios") ?></li>
                                <li class = "divider"></li>
                                <li><?php createLink(getTextValue('displays', $lang), $parent . "displays") ?></li>
                                <li><?php createLink(getTextValue('zones', $lang), $parent . "zones") ?></li>
                            </ul>
                        </li>
                        <?php
                    }
                    
                    if ($_SESSION['userpriv'] & 32 || $_SESSION['userpriv'] & 2) {
                        ?>
                        <li class = "dropdown">
                            <a href = "#" class = "dropdown-toggle" data-toggle = "dropdown" role = "button" aria-expanded = "false"><i class="fa fa-user-circle-o"></i> <?php echo getTextValue('users', $lang) . "/" . getTextValue('clerks', $lang) ?> <span class = "caret"></span></a>
                            <ul class = "dropdown-menu" role = "menu">
                                <?php if ($_SESSION['userpriv'] & 2) { ?>
                                    <li><?php createLink(getTextValue('clerks', $lang), $parent . "clerks") ?></li>
                                <?php }; ?>
                                <?php if ($_SESSION['userpriv'] & 32) { ?>
                                    <li><?php createLink(getTextValue('users', $lang), $parent . "users") ?></li>
                                <?php }; ?>
                            </ul>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <?php
                    if ($_SESSION['userpriv'] & 128) {
                        ?>
                        <li>
                            <?php // createLink(getTextValue('languages', $lang), $parent . "languages", "glyphicon glyphicon-text-size") ?>
                            <?php createLink('', $parent . "languages", "glyphicon glyphicon-text-size", getTextValue('languages', $lang)) ?>
                        </li>
                        <?php
                    }
                    ?>
                    <li>
                        <?php // createLink(getTextValue('logout', $lang), $parent . "account/logout", "glyphicon glyphicon-log-out") ?>
                        <?php createLink('', $parent . "account/logout", "glyphicon glyphicon-log-out", getTextValue('logout', $lang)) ?>
                    </li>
                    <li class = "dropdown">
                        <a href = "#" class = "dropdown-toggle" data-toggle = "dropdown" role = "button" aria-expanded = "false"><i class="fa fa-globe"></i> <?php echo $lang ?> <span class = "caret"></span></a>
                        <ul class = "dropdown-menu" role = "menu">
                            <li><?php include_once './common/lang_switch.php'; ?></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>