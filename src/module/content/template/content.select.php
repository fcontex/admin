<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>查看内容 - <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
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
		case 'fc.content.check_1':
		case 'fc.content.check_0':
		case 'fc.content.fixed_1':
		case 'fc.content.fixed_0':
		case 'fc.content.refresh':
			args = args || $$.selectval('#table_list :checked:enabled[name=select]');
			if (args == '')
			{
				$$.alert({text:'请选择记录。'});
				return false;
			}
			if (mode == 'fc.content.delete')
			{
				$$.confirm({text:'确定删除[ #'+args+' ]？', ok:function()
				{
					$$.post('<?php echo $R->getCtrlUrl(); ?>?mode=' + mode, {args:args}, function(){$$.redirect();});
				}});	
			}
			else 
			{
				$$.post('<?php echo $R->getCtrlUrl(); ?>?mode=fc.content.check', {save:mode, args:args}, function(){$$.redirect();});
			}
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
$cate = isset($_GET['cate']) ? intval($A->strGet('cate')) : intval(isset($_COOKIE['cate'])?$_COOKIE['cate']:0);
$keyw = isset($_GET['keyw']) ? trim($A->strGet('keyw')) : (isset($_COOKIE['keyw'])?$_COOKIE['keyw']:'');
$stat = isset($_GET['stat']) ? intval($A->strGet('stat')) : intval(isset($_COOKIE['stat'])?$_COOKIE['stat']:0);
$page = isset($_GET['page']) ? intval($A->strGet('page')) : intval(isset($_COOKIE['page'])?$_COOKIE['page']:0);

setcookie('cate', $cate, 0, '/');
setcookie('keyw', $keyw, 0, '/');
setcookie('stat', $stat, 0, '/');
setcookie('page', $page, 0, '/');

$pager = FCApplication::sharedPageTurnner();
$pager->page = $page;
$Q = DB::table('content')
  ->field(array('ct_title', 'ct_id', 'ct_cid', 'ct_check', 'ct_updatetime', 'ct_fixed'))
  ->where('ct_type > ?', array(CONTENT_PAGE));
if ($keyw != '')
{
	$Q->where('ct_title like ?', array('%'.$keyw.'%'));
}
if ($cate)
{
	$Q->where('(ct_cid = ? or ct_cid in (select cg_id from T[category] where cg_pid=?))', array($cate, $cate));
}
if ($stat == 1) $Q->where('ct_check = 0');
if ($stat == 2) $Q->where('ct_fixed = 1');
$Q->order('ct_inserttime', 'desc');
$items = $Q->page($pager);
?>
<form method="get" action="###" onsubmit="$$.redirect('<?php echo $R->getPageUrl(); ?>&'+$(this).serialize());return false;">
<table class="table_tools" width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<th>
		<select class="bnt"><option value="">批量操作</option>
            <option value="fc.content.delete">删除</option>
            <option value="fc.content.check_0">草稿</option>
            <option value="fc.content.check_1">发布</option>
            <option value="fc.content.fixed_1">置顶</option>
            <option value="fc.content.fixed_0">取消置顶</option>
        </select>
        <input type="button" value="应用" class="button" onclick="content($(this).parent().find('.bnt').val());" />
		<select name="cate">
        	<option>全部分类</option>
			<?php
			foreach (DB::table('category')->field(array('cg_id', 'cg_title'))->where('cg_pid=?', array(0))->order('cg_order', 'asc')->order('cg_id', 'asc')->rows() as $rstP)
			{
            ?>
            <option value="<?php echo $rstP['cg_id']; ?>"<?php if ($cate == $rstP['cg_id'])echo ' selected="selected"'; ?>><?php echo $rstP['cg_title']; ?></option>
			<?php
				foreach (DB::table('category')->field(array('cg_id', 'cg_title'))->where('cg_pid=?', array($rstP['cg_id']))->order('cg_order', 'asc')->order('cg_id', 'asc')->rows() as $rstC)
				{
			?>
				<option value="<?php echo $rstC['cg_id']; ?>"<?php if ($cate == $rstC['cg_id'])echo ' selected="selected"'; ?>>&nbsp; <?php echo $rstC['cg_title']; ?></option>
			<?php
				}
			}
            ?>
		</select>
        <select name="stat">
        	<option value="0">全部状态</option>
        	<option value="1"<?php if ($stat == 1)echo ' selected="selected"'; ?>>草稿</option>
            <option value="2"<?php if ($stat == 2)echo ' selected="selected"'; ?>>置顶</option>
        </select>
        <input type="text" class="text" name="keyw" id="search_key" size="20" value="<?php echo htmlspecialchars($keyw, ENT_QUOTES, 'UTF-8'); ?>" />
        <input type="submit" value="筛选" class="button" />
		<input type="button" value="重置" class="button" onclick="$$.redirect('<?php echo $R->getPageUrl(); ?>&cate=&stat=&keyw=&page=1');" />
		</th>
		<td align="right" class="simple"><?php echo $pager->turnner; ?></td>
	</tr>
</table>
</form>

<div class="table_scroll">
<table id="table_list" class="table_list" width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr id="list_caption">
		<th width="4%"><input type="checkbox" class="checkbox" id="selectall" onchange="$('#table_list :checkbox:enabled[name=select]').prop('checked', !$(this).prop('checked')).click();" /></th>
		<th width="6%">#</th>
		<th>标题</th>
		<th width="21%">所属分类</th>
		<th width="21%">状态</th>
		<th width="17%">最后更新</th>
	</tr>
	<?php
	$n = 0;
	foreach ($items as $rst)
	{
		$rstT = DB::table('category')->field('cg_title')->where('cg_id = ?', array(intval($rst['ct_cid'])))->row();
	?>
	<tr>
		<td><input type="checkbox" class="checkbox" name="select" id="select_<?php echo $rst['ct_id']; ?>" value="<?php echo $rst['ct_id']; ?>" onchange="var _this = $(this); _this.prop('checked') ? _this.parent().parent().addClass('S') : _this.parent().parent().removeClass('S');" /></td>
		<td><?php echo $rst['ct_id']; ?></td>
		<td>
			<a class="update" href="<?php echo $R->getPageUrl('content/fc.content.update/'.$rst['ct_id']); ?>"><?php echo $rst['ct_title']; ?></a>
			<div class="operate">
				<a class="update" href="<?php echo $R->getPageUrl('content/fc.content.update/'.$rst['ct_id']); ?>">修改</a><span>|</span>
				<a class="update" href="javascript:void(0);" onclick="content('fc.content.refresh', <?php echo $rst['ct_id']; ?>);">刷新</a><span>|</span>
				<a class="delete" href="javascript:void(0);" onclick="content('fc.content.delete', <?php echo $rst['ct_id']; ?>);">删除</a>
			</div>
        </td>
		<td><?php echo $rstT['cg_title'] ? $rstT['cg_title'] : '无'; ?></td>
		<td class="status">
			<?php echo $rst['ct_check'] ? '<span class="status_y">已发布</span>' : '<span class="status_f">草稿</span>'; if ($rst['ct_fixed']) echo '<span class="status_f">置顶</span>'; ?>
		</td>
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
		<td colspan="6" align="center">暂无记录。</td>
	</tr>
	<?php
	}
	?>
    <tr id="list_caption">
		<th width="4%"><input type="checkbox" class="checkbox" id="selectall" onchange="$('#table_list :checkbox:enabled[name=select]').prop('checked', !$(this).prop('checked')).click();" /></th>
		<th width="6%">#</th>
		<th>标题</th>
		<th width="21%">所属分类</th>
		<th width="21%">作者</th>
		<th width="17%">最后更新</th>
	</tr>
</table>
</div>

<table class="table_tools" width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<th>
			<select class="bnt"><option value="">批量操作</option>
				<option value="fc.content.delete">删除</option>
				<option value="fc.content.check_0">草稿</option>
				<option value="fc.content.check_1">发布</option>
				<option value="fc.content.fixed_1">置顶</option>
				<option value="fc.content.fixed_0">取消置顶</option>
			</select>
			<input type="button" value="应用" class="button" onclick="content($(this).parent().find('.bnt').val());" />
		</th>
		<td align="right"><?php echo $pager->turnner; ?></td>
	</tr>
</table>

</body>
</html>
