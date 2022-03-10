<?
/**
 * Simple template that uses the Yahoo! User Interface (YUI) styles.
 *
 * @author Josh Moody <josh@ark.org>
 *
 * Date Written: Feb, 2007
 *
 * @package NinjaPHPFramework
 * @filesource
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Default Theme</title>
	<link rel="stylesheet" type="text/css" href="<?=$image_path?>/css/reset.css" />
	<link rel="stylesheet" type="text/css" href="<?=$image_path?>/css/fonts.css" />
	<link rel="stylesheet" type="text/css" href="<?=$image_path?>/css/grids.css" />
	<link rel="stylesheet" type="text/css" href="<?=$image_path?>/css/decoration.css" />
	<link rel="stylesheet" type="text/css" href="<?=$image_path?>/css/forms.css" />
	<link rel="stylesheet" type="text/css" href="<?=$image_path?>/css/common.css" />
	<script type="text/javascript" language="Javascript" src="<?=$image_path?>/js/fat.js"></script>
</head>
<body>

<div id="doc" class="yui-t7">
	<div id="hd"><h2><span>Default Theme</span></h2></div> <!-- header -->
	<div id="info">
	</div>
	<div id="bd">
	<?=$start_form?>
	<?=$body?>
	<?=$end_form?>
	</div>
 <!-- body -->
   <div id="ft" style="text-align: center">Copyright &copy; <?=date('Y')?></div> <!-- footer -->
</div>
</body>
</html>

