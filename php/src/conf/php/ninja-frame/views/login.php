<?
/**
 * Login Form
 *
 * To over-ride this form and use your own, simply add a login.php to your
 * application's admin views directory.
 *
 * @author Josh Moody <josh@ark.org>
 *
 * Date Written: Feb, 2007
 *
 * @package NinjaPHPFramework
 * @filesource
 */
?>
<h1>Please Login</h1>
<?=show_warnings()?>
<?if ($note):?>
<p id="displaynote" class="warn fade"><?=$note?></p>
<?endif;?>
<table>
	<tr>
		<th><label for="un">Username:</label></th>
		<td><input type="text" name="un" id="un" value="<?=@$_POST['un']?>"></td>
	</tr>
	<tr>
		<th><label for="pw">Password:</label></th>
		<td><input type="password" name="pw" id="pw" value=""></td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" name="login" value="Login" /></td>
	</tr>
</table>
<? if ($public_url):?>
<p><a href="<?=$public_url?>">Public Site</a></p>
<? endif;?>