<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>编辑内容 - <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
<meta name="robots" content="nofollow">
<link rel="stylesheet" type="text/css" href="<?php echo URL_TOOLS; ?>quill/quill.snow.css" />
<link rel="stylesheet" type="text/css" href="<?php echo URL_TOOLS; ?>quill/fc.quill.css" />
<?php if (0) { ?><link rel="stylesheet" type="text/css" href="../../system/skins/default/style.css" /><?php }else{ ?>
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN; ?>style.css?v=<?php echo ASSET_VERSION; ?>" /><?php } ?>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo URL_TOOLS; ?>quill/quill.js"></script>
<script type="text/javascript" src="<?php echo URL_TOOLS; ?>datepicker/WdatePicker.js"></script>
<script type="text/javascript">
function content(mode, args)
{
	switch (mode)
	{
		case 'fc.content.update':
			window.__editor_content.sync();
			$$.post('<?php echo $R->getCtrlUrl(); ?>?mode=' + mode, $('#form_detail').serialize() + '&args=' + args, function()
			{
				$$.redirect('<?php echo $R->getPageUrl('content/fc.page.select')?>');
			});
			break;
		default:
			$$.alert({text:'无效参数 [ '+mode+' ]。'});
			break;
	}
	
	return false;
}

$(function()
{
	window.__editor_content  = $$.editor({url:'<?php echo $R->getPageUrl('system/fc.files.upload'); ?>',target:'#ct_content'});
});
</script>
</head>
<body>

<form id="form_detail" name="form_detail" method="post" action="###" onsubmit="return content('fc.content.update', <?php echo $args = intval($R->getParam(2)); ?>);">
<table class="table_form" width="100%" border="0" cellspacing="0" cellpadding="0">
	<?php
	if (!is_numeric($args))
	{
	?>
	<tr>
		<th>&nbsp;</th>
		<td>无效参数[ <?php echo $args; ?> ]。</td>
	</tr>
	<?php
	}
	else
	{
		if ($rst = DB::table('content')->where('ct_id = ?', array($args))->row())
		{
	?>
	<tr>
		<th>标题</th>
		<td><input class="text" type="text" name="ct_title" id="ct_title" value="<?php echo $rst['ct_title']; ?>" /> <span><cite>*</cite> 必填</span></td>
	</tr>
	<tr>
		<th>内容</th>
		<td><textarea name="ct_content" id="ct_content" style="width:720px; height:340px;"><?php echo str_replace(array('&lt;','&gt;'), array('&amp;lt;','&amp;gt;'), $rst['ct_content']); ?></textarea> <span><cite>*</cite> 必填</span></td>
	</tr>
	<tr>
		<th>附加属性<input type="hidden" name="ct_inserttime" id="ct_inserttime" value="<?php echo date('Y-m-d H:i:s', $rst['ct_inserttime']); ?>" /></th>
		<td><input type="checkbox" class="checkbox" value="1" id="ct_quiet" name="ct_quiet"<?php if ($rst['ct_quiet'] == 0)echo ' checked="checked"'; ?> /> <label for="ct_quiet">允许评论</label></td>
	</tr>
	<tr class="action"><th>&nbsp;</th><td><input type="submit" class="button" value="确认修改" /><input type="button" class="button cancle" value="放弃修改" onclick="$$.redirect('<?php echo $R->getPageUrl('content/fc.page.select'); ?>');" /></td></tr>
	<?php
		}
	}
	?>
</table>
</form>

</body>
</html>
