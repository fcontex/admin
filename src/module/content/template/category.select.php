<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>查看栏目 - <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
<meta name="robots" content="nofollow">
<?php if (0) { ?><link rel="stylesheet" type="text/css" href="../../system/skins/default/style.css" /><?php }else{ ?>
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN; ?>style.css?v=<?php echo ASSET_VERSION; ?>" /><?php } ?>
<link rel="stylesheet" type="text/css" href="<?php echo URL_MODULE; ?>template/images/style.css?v=<?php echo filemtime(PATH_MODULE.'template/images/style.css'); ?>" />
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>jquery.ui.min.js"></script>
<script type="text/javascript">
function category(mode, args)
{
	switch (mode)
	{
		case 'fc.category.delete':
			$$.confirm({text:'确定删除[ #'+args+' ]？', ok:function()
			{
				$$.post('<?php echo $ctrlurl = $R->getCtrlUrl(); ?>?mode=' + mode, {args:args}, function(){$$.redirect();});
			}});
			break;
		case 'fc.category.show':
			$$.post('<?php echo $ctrlurl; ?>?mode=' + mode, {args:args.id, show:args.show});
			break;
		case 'fc.category.order':
			$$.post('<?php echo $ctrlurl; ?>?mode=' + mode, {args:args});
			break;
		default:
			$$.alert({text:'无效参数 [ '+mode+' ]。'});
	}
	
	return false;
}

function dlOrder()
{
	var order = '', dot = '';
	$('dl[id]').each(function(){order+=dot+$(this).attr('id').replace('category_','');dot=',';});
	
	return order;
}

function ddOrder(dl)
{
	var selector = dl ? '#category_'+dl+' dd' : 'dd';
	var order = '', dot = '';
	$(selector).each(function(){order+=dot+$(this).attr('id').replace('category_','');dot=',';});
	
	return order;
}

$(function()
{
	window.__order_dl = dlOrder();
	window.__order_dd = ddOrder();
	
	$('body').sortable(
	{
		containment:'window',
		helper:'clone',
		items:'dl',
		axis:'y',
		start: function(){clearTimeout(window.__sort_dl_timer||0);},
		stop: function()
		{
			var order = dlOrder();
			if (window.__order_dl != order)
			{
				window.__sort_dl_timer = setTimeout(function(){category('fc.category.order', order); window.__order_dl = order;}, 1500);
			}
		}
	});
	
	$('dl').sortable(
	{
		containment:'parent',
		items:'dd',
		axis:'y',
		start: function(){clearTimeout(window.__sort_dd_timer||0);},
		stop: function()
		{
			var order = ddOrder();
			var dl = $(this).attr('id').replace('category_','');
			if (window.__order_dd != order)
			{
				window.__sort_dd_timer = setTimeout(function(){category('fc.category.order', ddOrder(dl)); window.__order_dd = order;}, 1500);
			}
		}
	});
});
</script>
</head>
<body>

<?php
$n = 0;
foreach (DB::table('category')->where('cg_pid = ?', array(0))->order('cg_order', 'asc')->order('cg_id', 'asc')->rows() as $rstP)
{
	$children = DB::table('category')->where('cg_pid = ?', array($rstP['cg_id']))->order('cg_order', 'asc')->order('cg_id', 'asc')->rows();
?>
<dl class="category" id="category_<?php echo $rstP['cg_id']; ?>">
	<dt ondblclick="$(this).find('.B a').click();">
		<span class="A"># <?php echo $rstP['cg_id']; ?></span>
		<span class="B"><a href="javascript:void(0);" onclick="$('#category_<?php echo $rstP['cg_id']; ?> dd').toggle();$(this).toggleClass('d');">展开</a></span>
		<span class="C"><cite class="type">[<?php echo $rstP['cg_type'] == 0 ? '栏目' : '链接'; ?>]</cite> <?php echo $rstP['cg_title']; ?>[<?php echo $rstP['cg_count']; ?>]</span>
		<span class="D"><?php echo $A->strLeft($rstP['cg_desc'], 42, '...'); ?></span>
		<span class="E">
		<input type="checkbox" name="check" value="1" id="category_show_<?php echo $rstP['cg_id']; ?>" class="checkbox"<?php if ($rstP['cg_show'])echo ' checked="checked"'; ?> onclick="category('fc.category.show', {id:'<?php echo $rstP['cg_id']; ?>', show:this.checked ? 1 : 0});" /><label for="category_show_<?php echo $rstP['cg_id']; ?>">显示</label> &nbsp; &nbsp; &nbsp;
		<a class="update" href="<?php echo $R->getPageUrl('content/fc.category.update/'.$rstP['cg_id']); ?>">修改</a> &nbsp; 
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
		<a class="delete" href="javascript:void(0);" onclick="category('fc.category.delete', <?php echo $rstP['cg_id']; ?>);">删除</a>
		<?php
		}
		?>
		</span>
	</dt>
	<?php
	if ($children) foreach ($children as $rst)
	{
	?>
	<dd id="category_<?php echo $rst['cg_id']; ?>">
		<span class="A"># <?php echo $rst['cg_id']; ?></span>
		<span class="B"></span>
		<span class="C"><cite class="type">[<?php echo $rst['cg_type'] == 0 ? '栏目' : '链接'; ?>]</cite> <?php echo $rst['cg_title']; ?>[<?php echo $rst['cg_count']; ?>]</span>
		<span class="D"><?php echo $A->strLeft($rst['cg_desc'], 42, '...'); ?></span>
		<span class="E">
			<input type="checkbox" name="check" value="1" id="category_show_<?php echo $rst['cg_id']; ?>" class="checkbox"<?php if ($rst['cg_show'])echo ' checked="checked"'; ?> onclick="category('fc.category.show', {id:'<?php echo $rst['cg_id']; ?>', show:this.checked ? 1 : 0});" /><label for="category_show_<?php echo $rst['cg_id']; ?>">显示</label> &nbsp; &nbsp; &nbsp;
			<a class="update" href="<?php echo $R->getPageUrl('content/fc.category.update/'.$rst['cg_id']); ?>">修改</a> &nbsp; 
			<a class="delete" href="javascript:void(0);" onclick="category('fc.category.delete', <?php echo $rst['cg_id']; ?>);">删除</a>
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
	<dl class="category">
		<dt><span>没有添加栏目。&nbsp;&nbsp;<a href="#" onclick="top.naviSwitcher('content/fc.category.select', 'content/fc.category.insert');return false;">立即添加</a></span></dt>
	</dl>
<?php
}
?>

</body>
</html>
