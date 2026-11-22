<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>查看评论 - <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
<meta name="robots" content="nofollow">
<?php if (0) { ?><link rel="stylesheet" type="text/css" href="../../system/skins/default/style.css" /><?php }else{ ?>
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN; ?>style.css?v=<?php echo ASSET_VERSION; ?>" /><?php } ?>
<link rel="stylesheet" type="text/css" href="<?php echo URL_MODULE; ?>template/images/style.css?v=<?php echo filemtime(PATH_MODULE.'template/images/style.css'); ?>" />
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo URL_TOOLS; ?>datepicker/WdatePicker.js"></script>
<script type="text/javascript">
function comment(mode, args)
{
	switch (mode)
	{
		case 'fc.comment.delete':
			/*args = args || $$.selectval('#table_list :checked:enabled[name=select]');
			if (args == '')
			{
				$$.alert({text:'请选择记录。'});
				return false;
			}*/
			$$.confirm({text:'确定删除[ #'+args+' ]？', ok:function()
			{
				$$.post('<?php echo $ctrlurl = $R->getCtrlUrl(); ?>?mode=' + mode, {args:args}, function(){$$.redirect();});
			}});
			break;
		/*case 'fc.comment.check_1':
		case 'fc.comment.check_0':
			var check = mode == 'fc.comment.check_1' ? 1 : 0;
			args = args || $$.selectval('#table_list :checked:enabled[name=select]');
			if (args == '')
			{
				$$.alert({text:'请选择记录。'});
				return false;
			}
			$$.post('<?php echo $ctrlurl; ?>?mode=' + mode, {args:args, check:check}, function(){$$.redirect();});
			break;*/
		default:
			$$.alert({text:'请选择操作。'});
	}
	
	return false;
}
</script>
</head>
<body>

<?php
$keyw = trim($A->strGet('keyw'));
$starttime = $A->strGet('starttime');
$stoptime  = $A->strGet('stoptime');

$pager = FCApplication::sharedPageTurnner();
$Q = DB::table('comment')->field('*')->where('cm_topid = 0');
if ($keyw)
{
	$Q->where('cm_content like ?', array('%'.$keyw.'%'));
}
if ($starttime) $Q->where('cm_time >= ?', array(intval(strtotime($starttime))));
if ($stoptime) $Q->where('cm_time <= ?', array(intval(strtotime($stoptime) + 60*60*24)));
$Q->order('cm_update', 'desc');
$items = $Q->page($pager);
?>
<form method="get" action="###" onsubmit="$$.redirect('<?php echo $R->getPageUrl(); ?>&'+$(this).serialize());return false;">
<table class="table_tools" width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<th>
			<!--<select class="bnt"><option value="">批量操作</option>
            	<option value="fc.comment.delete">删除</option>
                <option value="fc.comment.check_1">通过审核</option>
                <option value="fc.comment.check_0">取消审核</option>
            </select>
			<input type="button" value="应用" class="button" onclick="comment($(this).parent().find('.bnt').val());" />-->
            <input type="text" class="text" name="keyw" id="search_key" size="20" value="<?php echo htmlspecialchars($keyw, ENT_QUOTES, 'UTF-8'); ?>" />
            <input name="starttime" type="text" class="text date" style="width:80px;" onClick="WdatePicker({skin:'<?php echo DATE_SKIN; ?>'})" value="<?php echo htmlspecialchars($starttime, ENT_QUOTES, 'UTF-8'); ?>" />-<input name="stoptime" type="text" class="text date" style="width:80px;" onClick="WdatePicker({skin:'<?php echo DATE_SKIN; ?>'})" value="<?php echo htmlspecialchars($stoptime, ENT_QUOTES, 'UTF-8'); ?>" />
            <input type="submit" value="筛选" class="button" />
			<input type="button" value="重置" class="button" onclick="$$.redirect('<?php echo $R->getPageUrl(); ?>');" />
		</th>
		<td align="right" class="simple"><?php echo $pager->turnner; ?></td>
	</tr>
</table>
</form>
<?php
$n = 0;
foreach ($items as $rstP)
{
	$children = DB::table('comment')->field('*')->where('cm_topid = ?', array(intval($rstP['cm_id'])))->order('cm_id', 'asc')->rows();
?>
<dl class="comment" id="comment_<?php echo $rstP['cm_id']; ?>">
	<dt ondblclick="$(this).find('.B a').click();">
		<span class="A"># <?php echo $rstP['cm_id']; ?></span>
		<span class="B"><?php echo $A->strLeft($rstP['cm_ctitle'], 20, '...'); ?></span>
		<span class="C"><?php echo $rstP['cm_name']; ?></span>
		<span class="D"><?php echo $A->strLeft($A->trim($rstP['cm_content'],TRUE), 68, '...'); ?></span>
		<span class="E status">
		<?php if (!$rstP['cm_check']) echo ' <cite class="status_f">屏蔽</cite>'; ?>
		<a href="javascript:void(0);" onclick="$('#comment_<?php echo $rstP['cm_id']; ?> dd').toggle();$(this).toggleClass('d');" class="ud">&nbsp;</a> &nbsp; 
		<a class="update" href="<?php echo $R->getPageUrl('message/fc.comment.update/'.$rstP['cm_id']); ?>">查看</a> &nbsp; 
		<?php
		if ($children)
		{
		?>
		<a class="delete" href="javascript:void(0);" style="opacity:0.5; filter:alpha(opacity=50); color:#ccc;">删除</a>
		<?php
		}
		else
		{
		?>
		<a class="delete" href="javascript:void(0);" onclick="comment('fc.comment.delete', <?php echo $rstP['cm_id']; ?>);">删除</a>
		<?php
		}
		?>
		</span>
	</dt>
	<?php
	if ($children) foreach ($children as $rst)
	{
	?>
	<dd id="comment_<?php echo $rst['cm_id']; ?>">
		<span class="A"># <?php echo $rst['cm_id']; ?></span>
		<span class="B"><?php echo $A->transDate($rst['cm_time']); ?></span>
		<span class="C"><?php echo $rst['cm_name']; ?></span>
		<span class="D"><?php echo $A->strLeft(strip_tags($rst['cm_content']), 68, '...'); ?></span>
		<span class="E status">
			<?php if (!$rst['cm_check']) echo ' <cite class="status_f">屏蔽</cite>'; ?>
			<a class="update" href="<?php echo $R->getPageUrl('message/fc.comment.update/'.$rst['cm_id']); ?>">查看</a> &nbsp; 
			<a class="delete" href="javascript:void(0);" onclick="comment('fc.comment.delete', <?php echo $rst['cm_id']; ?>);">删除</a>
		</span>
	</dd>
	<?php
	}
	?>
</dl>
<?php
	$n++;
}
if ($n == 0)
{
?>
	<dl class="comment">
		<dt><span>暂无记录。</span></dt>
	</dl>
<?php
}
?>

<table class="table_tools" width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<th>
           <!-- <select class="bnt"><option value="">批量操作</option>
            	<option value="fc.comment.delete">删除</option>
                <option value="fc.comment.check_1">通过审核</option>
                <option value="fc.comment.check_0">取消审核</option>
            </select>
            <input type="button" value="应用" class="button" onclick="comment($(this).parent().find('.bnt').val());" />-->
		</th>
		<td align="right"><?php echo $pager->turnner; ?></td>
	</tr>
</table>

</body>
</html>
