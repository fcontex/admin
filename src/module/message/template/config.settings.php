<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>互动设置 - <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
<meta name="robots" content="nofollow">
<?php if (0) { ?><link rel="stylesheet" type="text/css" href="../../system/skins/default/style.css" /><?php }else{ ?>
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN; ?>style.css?v=<?php echo ASSET_VERSION; ?>" /><?php } ?>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>"></script>
<script type="text/javascript">
function settings(mode, args)
{
	switch (mode)
	{
		case 'fc.settings':
			$$.post('<?php echo $R->getCtrlUrl(); ?>?mode=' + mode, $('#form_detail').serialize());
			break;
		default:
			$$.alert({text : '无效参数 [ '+mode+' ]。'});
			break;
	}
	
	return false;
}

</script>
</head>
<body>
<?php
$comment_disable = $A->loadConfig('message.settings', 'comment_disable');
$gbook_disable = $A->loadConfig('message.settings', 'gbook_disable');
?>
<form autocomplete="off" id="form_detail" name="form_detail" method="post" action="###" onsubmit="return settings('fc.settings');">
<table class="table_form" width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<th>评论功能</th>
		<td>
			<input type="checkbox" class="checkbox" value="1" name="comment_disable" id="comment_disable"<?php if ($comment_disable) echo ' checked="checked"'; ?> /> <label for="comment_disable">关闭全站评论功能</label>
		</td>
	</tr>
	<tr>
		<th>留言功能</th>
		<td>
			<input type="checkbox" class="checkbox" value="1" name="gbook_disable" id="gbook_disable"<?php if ($gbook_disable) echo ' checked="checked"'; ?> /> <label for="gbook_disable">关闭留言发表功能</label>
		</td>
	</tr>
	<tr class="action"><th>&nbsp;</th><td><input type="submit" class="button" value="保存更改" /><input type="button" class="button cancle" value="放弃更改" onclick="$$.confirm({text:'放弃更改并关闭窗口？', ok:function(){top.naviShut('message/fc.settings');}, icon:'WAR'});" /></td></tr>
</table>
</form>
</body>
</html>
