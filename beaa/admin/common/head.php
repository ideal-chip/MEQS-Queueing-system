<?php 
$cssPath = defined('CSS_PATH') ? CSS_PATH : '../css';
$filesPath = defined('FILES_PATH') ? FILES_PATH : '../files';
?>
<meta charset="UTF-8">
<title>iDEAL-Q&reg; <?php echo $title; ?></title>
<link href="<?php echo $cssPath; ?>/paper.bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $cssPath; ?>/font-awesome.min.css" rel="stylesheet" type="text/css"/>

<?php if ($dir == "rtl") { ?>
    <link href="<?php echo $cssPath; ?>/bootstrap-rtl.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $cssPath; ?>/bootstrap-flipped.min.css" rel="stylesheet" type="text/css"/>
<?php }; ?>
    
<link href="<?php echo $cssPath; ?>/common.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $cssPath; ?>/admin.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $filesPath; ?>/shortcut_icons/admin.png" rel="shortcut icon"/>
