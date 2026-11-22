<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>操作日志</title>
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN ?>style.css?v=<?php echo ASSET_VERSION; ?>" />
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo URL_TOOLS; ?>datepicker/WdatePicker.js"></script>
<script type="text/javascript">
function logs(mode, args)
{
	switch (mode)
	{
		case 'fc.logs.delete':
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

<?php
$keyw = $A->strGet('keyw');
$user = $A->strGet('user');
$starttime = $A->strGet('starttime');
$stoptime  = $A->strGet('stoptime');
$pager = FCApplication::sharedPageTurnner();
$Q = DB::table('logs')->field('*')->where('1=1');
if ($keyw)
{
	$Q->where('lg_event like ?', array('%'.$keyw.'%'));
}
if ($starttime) $Q->where('lg_time >= ?', array(intval(strtotime($starttime))));
if ($stoptime) $Q->where('lg_time <= ?', array(intval(strtotime($stoptime) + 60*60*24)));
if ($user) $Q->where('lg_uid >= ?', array(intval($user)));
$Q->order('lg_id', 'desc');
$items = $Q->page($pager);
?>
<form method="get" action="###" onsubmit="$$.redirect('<?php echo $R->getPageUrl(); ?>&'+$(this).serialize());return false;">
<table class="table_tools" width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
		<th>
        	<input type="hidden" name="mode" value="log" />
            <!--<select><option value="">批量操作</option>
            	<option value="fc.logs.delete">删除</option>
            </select>
            <input type="button" value="应用" class="button" onclick="logs($(this).parent().find('select').val());" />-->
			<input type="text" class="text" name="keyw" id="search_key" size="10" value="<?php echo htmlspecialchars($keyw, ENT_QUOTES, 'UTF-8'); ?>" />
            <select name="user"><option value="">用户</option>
            	<?php
                foreach (DB::table('user')->field(array('us_id', 'us_username'))->rows() as $rstu)
				{
				?>
                <option value="<?php echo $rstu['us_id']; ?>"<?php if ($user == $rstu['us_id'])echo ' selected="selected"'; ?>><?php echo $rstu['us_username']; ?></option>
                <?php
				}
				?>
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
		<th width="4%"><input type="checkbox" class="checkbox" id="selectall" onchange="$('#table_list :checkbox:enabled[name=select]').prop('checked', !$(this).prop('checked')).click();" /></th>
		<th width="5%">#</th>
		<th width="26%">事件</th>
		<th width="18%">用户</th>
		<th colspan="2">IP</th>
		<th width="17%">时间</th>
	</tr>
	<?php
	$hasRow = FALSE;
	foreach ($items as $rst)
	{
	$hasRow = TRUE;
	$rstu = DB::table('user')->field(array('us_name', 'us_username'))->where('us_id = ?', array(intval($rst['lg_uid'])))->row();
	if (!is_array($rstu)) $rstu = array('us_name' => '', 'us_username' => '');
	?>
	<tr>
		<td><input type="checkbox" class="checkbox" name="select" id="select_<?php echo $rst['lg_id']; ?>" value="<?php echo $rst['lg_id']; ?>" onchange="var _this = $(this); _this.prop('checked') ? _this.parent().parent().addClass('S') : _this.parent().parent().removeClass('S');" /></td>
		<td><?php echo $rst['lg_id']; ?></td>
		<td>
			<?php echo $rst['lg_event']; ?>
			<div class="operate"><a href="#" class="delete" onclick="logs('fc.logs.delete', <?php echo $rst['lg_id']; ?>); return false;">删除</a></div>        </td>
		<td><?php
		$logUserName = $rstu['us_name'] != '' ? $rstu['us_name'] : $rstu['us_username'];
		echo $logUserName != '' ? $logUserName : '-';
		?></td>
		<td colspan="2"><?php echo $rst['lg_ip']; ?> <a href="http://www.baidu.com/s?wd=<?php echo $rst['lg_ip']; ?>" target="_blank" class="link">&nbsp;</a></td>
		<td><?php echo $A->transDate($rst['lg_time']); ?></td>
	</tr>
	<?php
	}
	if (!$hasRow)
	{
	?>
   <tr>
		<td colspan="7" align="center">暂无记录。</td>
	</tr>
    <?php
	}
	?>
    <tr id="list_caption">
		<th width="4%"><input type="checkbox" class="checkbox" id="selectall" onchange="$('#table_list :checkbox:enabled[name=select]').prop('checked', !$(this).prop('checked')).click();" /></th>
		<th width="5%">#</th>
		<th width="26%" style="text-align:left;">事件</th>
		<th width="18%">用户</th>
		<th width="14%">IP</th>
		<th width="16%">地点</th>
		<th width="17%">时间</th>
	</tr>
</table>
</div>

<table class="table_tools" width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
		<th>
            <select><option value="">批量操作</option>
            	<option value="fc.logs.delete">删除</option>
            </select>
			<input type="button" value="应用" class="button" onclick="logs($(this).parent().find('select').val());" />
			<input type="button" value="全部清理" class="button" onclick="logs('fc.logs.delete', '*');" />
		</th>
		<td align="right"><?php echo $pager->turnner; ?></td>
	</tr>
</table>

</body>
</html>
