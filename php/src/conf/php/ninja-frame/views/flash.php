<?
/**
 * Forward user to another page
 *
 * To over-ride this form and use your own, simply add a flash.php to your
 * application's views directory.
 *
 * @author Josh Moody <josh@ark.org>
 *
 * @package NinjaPHPFramework
 * @filesource
 */
?>
<? if ($delay):?>
	<meta http-equiv="refresh" content="<?=$delay?>; url=<?=$url?>">
<? else:?>
	<p><a href="javascript:void()" onclick="javascript:history.go(-1)">Back to previous page</a></p>
<? endif;?>

<? if ($msg):?>
	<p><?=$msg?></p>
<? else:?>
	<p>Processing...</p>
<? endif;?>