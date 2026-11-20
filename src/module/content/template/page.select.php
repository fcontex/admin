<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>查看页面 - <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
<meta name="robots" content="nofollow">
<?php if (0) { ?><link rel="stylesheet" type="text/css" href="../../system/skins/default/style.css" /><?php }else{ ?>
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN; ?>style.css?v=<?php echo ASSET_VERSION; ?>" /><?php } ?>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>"></script>
<script type="text/javascript">
function content(mode, args)
{
	switch (mode)
	{
		case 'fc.content.delete':
			args = args || $$.selectval('#table_list :checked:enabled[name=select]');
			if (args == '')
			{
				$$.alert({text:'请选择记录。'});
				return false;
			}
			$$.confirm({text:'确定删除[ #'+args+' ]？', ok:function()
			{
				$$.post('<?php echo $R->getCtrlUrl(); ?>?mode=' + mode, {args:args}, function(){$$.redirect();});
			}});
			break;
		default:
			$$.alert({text:'请选择操作。'});
	}
	
	return false;
}

$(function()
{
	$('.table_list tr').hover(function()
	{
		$(this).find('.operate').show();	
	},
	function()
	{
		$(this).find('.operate').hide();
	});
});
</script>
</head>
<body>

<div class="table_scroll">
<table id="table_list" class="table_list" width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr id="list_caption">
		<th width="4%"><input type="checkbox" class="checkbox" id="selectall" onchange="$('#table_list :checkbox:enabled[name=select]').prop('checked', !$(this).prop('checked')).click();" /></th>
		<th width="6%">#</th>
		<th>标题</th>
		<th width="21%">所属分类</th>
		<th width="17%">最后更新</th>
	</tr>
	<?php
	$n = 0;
	foreach (DB::table('content')->field(array('ct_title', 'ct_id', 'ct_cid', 'ct_updatetime'))->where('ct_type = ?', array(CONTENT_PAGE))->order('ct_inserttime', 'desc')->rows() as $rst)
	{
		$rstT = DB::table('category')->field('cg_title')->where('cg_id = ?', array(intval($rst['ct_cid'])))->row();
	?>
	<tr>
		<td><input type="checkbox" class="checkbox" name="select" id="select_<?php echo $rst['ct_id']; ?>" value="<?php echo $rst['ct_id']; ?>" onchange="var _this = $(this); _this.prop('checked') ? _this.parent().parent().addClass('S') : _this.parent().parent().removeClass('S');" /></td>
		<td><?php echo $rst['ct_id']; ?></td>
		<td>
			<a class="update" href="<?php echo $R->getPageUrl('content/fc.page.update/'.$rst['ct_id']); ?>"><?php echo $rst['ct_title']; ?></a><br />
			<div class="operate"><a class="update" href="<?php echo $R->getPageUrl('content/fc.page.update/'.$rst['ct_id']); ?>">修改</a><span>|</span><a class="delete" href="javascript:void(0);" onclick="content('fc.content.delete', <?php echo $rst['ct_id']; ?>);">删除</a></div>
        </td>
		<td><?php echo $rstT['cg_title'] ? $rstT['cg_title'] : '未分类'; ?></td>
		<td><?php echo $A->transDate($rst['ct_updatetime']); ?>
		</td>
	</tr>
	<?php
		$n++;
	}
	if ($n == 0)
	{
	?>
	<tr>
		<td colspan="5" align="center">暂无记录。</td>
	</tr>
	<?php
	}
	?>
    <tr id="list_caption">
		<th width="4%"><input type="checkbox" class="checkbox" id="selectall" onchange="$('#table_list :checkbox:enabled[name=select]').prop('checked', !$(this).prop('checked')).click();" /></th>
		<th width="6%">#</th>
		<th>标题</th>
		<th width="21%">所属分类</th>
		<th width="17%">最后更新</th>
	</tr>
</table>
</div>

<table class="table_tools" width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<th>
            <select class="bnt"><option value="">批量操作</option>
            	<option value="fc.content.delete">删除</option>
            </select>
            <input type="button" value="应用" class="button" onclick="content($(this).parent().find('.bnt').val());" />
		</th>
	</tr>
</table>

</body>
</html>
