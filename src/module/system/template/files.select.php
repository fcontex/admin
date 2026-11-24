<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>附件管理</title>
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN ?>style.css?v=<?php echo ASSET_VERSION; ?>" />
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo URL_TOOLS; ?>datepicker/WdatePicker.js"></script>
<script type="text/javascript">
function attacheds(mode, args)
{
	switch (mode)
	{
		case 'fc.file.update':
			var input = $('#at_filename_'+args);
			if (input.val() != input.attr('value2'))
			{
				$$.post('<?php echo $R->getCtrlUrl(); ?>?mode=' + mode, {args:args, at_filename:input.val()}, function()
				{
					input.attr('value2', input.val());
				});
			}
			input.removeClass('c');
			break;
		case 'fc.file.delete':
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

function newUploadCallback(param)
{
	//$$.filesUploadLayer.close();
	$$.redirect();
}

</script>
</head>
<body>

<?php
$type	   = $A->strGet('type');
$starttime = $A->strGet('starttime');
$stoptime  = $A->strGet('stoptime');
$pager = FCApplication::sharedPageTurnner();
$Q = DB::table('attached')->field('*');
if ($type)
{
	$types = array_filter(array_map('trim', explode(',', $type)));
	if ($types)
	{
		$Q->whereIn('at_suffix', $types);
	}
}
if ($starttime) $Q->where('at_time >= ?', array(intval(strtotime($starttime))));
if ($stoptime) $Q->where('at_time <= ?', array(intval(strtotime($stoptime) + 60*60*24)));
$Q->order('at_id', 'desc');
$items = $Q->page($pager);
//筛选下拉的类型串读自 system.settings.php 的 uploadSuffix 分组 勿再硬编码
$suffixGroups = $A->system['uploadSuffix'];
$typeImage = isset($suffixGroups['image']) ? $suffixGroups['image'] : '';
$typeVideo = isset($suffixGroups['video']) ? $suffixGroups['video'] : '';
$typeAudio = isset($suffixGroups['audio']) ? $suffixGroups['audio'] : '';
$typeFile  = isset($suffixGroups['file']) ? $suffixGroups['file'] : '';
$typeArchive = isset($suffixGroups['archive']) ? $suffixGroups['archive'] : '';
?>
<form method="get" action="###" onsubmit="$$.redirect('<?php echo $R->getPageUrl(); ?>&'+$(this).serialize());return false;">
<table class="table_tools" width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
		<th>
        	<input type="hidden" name="mode" value="file" />
            <select id="bnt"><option value="">批量操作</option>
            	<option value="fc.file.delete">删除</option>
            </select>
            <input type="button" value="应用" class="button" onclick="attacheds($(this).parent().find('select').val());" />
			<select name="type">
            	<option value="">全部类型</option>
                <option value="<?php echo $typeImage; ?>"<?php if ($type==$typeImage)echo ' selected="selected"'; ?>>图像</option>
                <option value="<?php echo $typeVideo; ?>"<?php if ($type==$typeVideo)echo ' selected="selected"'; ?>>视频</option>
                <option value="<?php echo $typeAudio; ?>"<?php if ($type==$typeAudio)echo ' selected="selected"'; ?>>音频</option>
                <option value="<?php echo $typeFile; ?>"<?php if ($type==$typeFile)echo ' selected="selected"'; ?>>文档</option>
                <option value="<?php echo $typeArchive; ?>"<?php if ($type==$typeArchive)echo ' selected="selected"'; ?>>压缩文件</option>
            </select>
            <input name="starttime" type="text" class="text date" style="width:80px;" onClick="WdatePicker({skin:'<?php echo DATE_SKIN; ?>'})" value="<?php echo htmlspecialchars($starttime, ENT_QUOTES, 'UTF-8'); ?>" />-<input name="stoptime" type="text" class="text date" style="width:80px;" onClick="WdatePicker({skin:'<?php echo DATE_SKIN; ?>'})" value="<?php echo htmlspecialchars($stoptime, ENT_QUOTES, 'UTF-8'); ?>" />
            <input type="submit" value="筛选" class="button" />
			<input type="button" value="重置" class="button" onclick="$$.redirect('<?php echo $R->getPageUrl(); ?>');" />
		</th>
		<td align="right" class="simple"><?php echo $pager->turnner; ?></td>
	</tr>
</table>
</form>
<div class="table_scroll">
<table id="table_list" class="table_list" width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr id="list_caption">
		<th width="2%"><input type="checkbox" class="checkbox" id="selectall" onchange="$('#table_list :checkbox:enabled[name=select]').prop('checked', !$(this).prop('checked')).click();" /></th>
		<th width="4%">#</th>
		<th width="13%">&nbsp;</th>
		<th width="22%" style="text-align:left;">文件</th>
		<th width="10%">大小</th>
		<th width="19%">链接</th>
		<th width="8%">下载</th>
		<th width="22%">日期</th>
	</tr>
	<?php
	$hasData = false;
	foreach ($items as $rst)
	{
		$hasData = true;
	?>
	<tr>
		<td><input type="checkbox" class="checkbox" name="select" id="select_<?php echo $rst['at_id']; ?>" value="<?php echo $rst['at_id']; ?>" onchange="var _this = $(this); _this.prop('checked') ? _this.parent().parent().addClass('S') : _this.parent().parent().removeClass('S');" /></td>
		<td><?php echo $rst['at_id']; ?></td>
		<td><?php if ($rst['at_isimage']){ ?><img src="<?php echo $A->getThumb($rst['at_dir'].$rst['at_filenewname'], 200, 200); ?>" width="60" /><?php } else { ?><img src="<?php echo $A->fileIcon($rst['at_suffix']); ?>" /><?php } ?></td>
		<td class="status files_name">
			<?php
			if ($rst['at_remote'])
			{
			?>
			<a href="<?php echo $rst['at_url']; ?>" target="_blank"><b><?php echo $rst['at_title']; ?></b></a><br /><span class="status_y">远程文件</span>
			<div class="operate"><a href="<?php echo $rst['at_url']; ?>" target="_blank">查看</a><span>|</span><a href="#" class="delete" onclick="attacheds('fc.file.delete', <?php echo $rst['at_id']; ?>); return false;">删除</a></div>
			<?php
			}
			else
			{
			?>
			<input type="text" class="text" value="<?php echo $rst['at_filename']; ?>" value2="<?php echo $rst['at_filename']; ?>" id="at_filename_<?php echo $rst['at_id']; ?>" onfocus="$(this).addClass('c');" onblur="attacheds('fc.file.update', <?php echo $rst['at_id']; ?>);" /><br /><?php echo strtoupper($rst['at_suffix']); ?>
			<div class="operate"><a href="<?php echo $A->getThumb($rst['at_dir'].$rst['at_filenewname']); ?>" target="_blank">查看</a><span>|</span><a href="#" class="delete" onclick="attacheds('fc.file.delete', <?php echo $rst['at_id']; ?>); return false;">删除</a></div>
			<?php
			}
			?>		</td>
		<td><?php echo $A->transSize($rst['at_size']); ?></td>
		<td class="files_url"><input type="text" class="text" readonly="readonly" value="<?php echo $R->getPageUrl('system/file/'.$rst['at_token'],''); ?>" onclick="this.select();" /></td>
		<td><?php echo $rst['at_hits']; ?></td>
		<td><?php echo $A->transDate($rst['at_time']); ?></td>
	</tr>
	<?php
	}
	if (!$hasData)
	{
	?>
    <tr>
		<td colspan="8" align="center">暂无记录。</td>
	</tr>
    <?php
	}
	?>
    <tr id="list_caption">
		<th width="2%"><input type="checkbox" class="checkbox" id="selectall" onchange="$('#table_list :checkbox:enabled[name=select]').prop('checked', !$(this).prop('checked')).click();" /></th>
		<th width="4%">#</th>
		<th width="13%">&nbsp;</th>
		<th width="22%" style="text-align:left;">文件</th>
		<th width="10%">大小</th>
		<th width="19%">链接</th>
		<th width="8%">下载</th>
		<th width="22%">日期</th>
	</tr>
</table>
</div>

<table class="table_tools" width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<th>
            <select><option value="">批量操作</option>
            	<option value="fc.file.delete">删除</option>
            </select>
			<input type="button" value="应用" class="button" onclick="attacheds($(this).parent().find('select').val());" />
			<input type="button" value="上传新文件" class="button" onclick="$$.filesUploadLayer.open({url:'<?php echo $R->getPageUrl('system/fc.files.upload'); ?>',callback:'newUploadCallback',opener:window,close:newUploadCallback,label:'',type:'all'});" />
		</th>
		<td align="right"><?php echo $pager->turnner; ?></td>
	</tr>
</table>

</body>
</html>
