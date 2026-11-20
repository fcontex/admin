<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>模块管理 - <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
<meta name="robots" content="nofollow">
<?php if (0) { ?><link rel="stylesheet" type="text/css" href="../skins/default/style.css" /><?php }else{ ?>
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN ?>style.css?v=<?php echo ASSET_VERSION; ?>" /><?php } ?>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>jquery.ui.min.js"></script>
<script type="text/javascript">
function modules(mode, args)
{
	switch (mode)
	{
		case 'fc.module.insert':
			$$.post('<?php echo $ctrlurl = $R->getCtrlUrl(); ?>?mode=' + mode, {args:args}, function(){top.naviUpdate(); $$.redirect();});
			break;
		case 'fc.module.delete':
		case 'fc.module.disable':
			$$.post('<?php echo $ctrlurl; ?>?mode=' + mode, {args:args}, function(){top.naviUpdate();$$.redirect();});
			break;
		case 'fc.module.update':
			$$.post('<?php echo $ctrlurl; ?>?mode=' + mode, {args:args}, function(){top.naviUpdate();$$.redirect();});
			break;
		case 'fc.module.default':
			$$.post('<?php echo $ctrlurl; ?>?mode=' + mode, {args:args}, function(){$$.redirect();});
			break;
		case 'fc.module.order':
			$$.post('<?php echo $ctrlurl; ?>?mode=' + mode, {args:args}, function(){top.naviUpdate();});
			break;
		default:
			$$.alert({text : '无效参数 [ '+mode+' ]。'});
			break;
	}
	
	return false;
}

function moduleOrder()
{
	var order = '', dot = '';
	$('#modules dl').each(function(){order+=dot+$(this).attr('module');dot=',';});
	
	return order;
}

$(function()
{
	window.__order_module = moduleOrder();
	
	//触屏无拖拽能力，不初始化排序
	if (!$$.isTouch())
	{
		$("#modules").sortable(
		{
			containment:'parent',
			axis:'y',
			start: function(){clearTimeout(window.__sort_timer||0);},
			stop: function()
			{
				var order = moduleOrder();
				if(window.__order_module != order)
				{
					window.__sort_timer = setTimeout(function(){modules('fc.module.order', order); window.__order_module = order;}, 1500);
				};
			}
		});
	}
});
</script>
</head>
<body>

<div class="modules" id="modules">
	<?php
	$modules = $A->loadConfig('system.modules');
	$default = $R->getDefaultModule();
	foreach ($modules as $name => $module)
	{
		$dir = PATH_MODULES.$name;
		$file = $dir.'/module.php';
		if (!file_exists($file)) continue;
	?>
	<dl module="<?php echo $name; ?>">
		<dt><img src="<?php echo $A->moduleIcon($name, $module['icon']); ?>" /></dt>
		<dd>
			<span>
			<?php
			if ($name == $default)
			{
			?>
			<a class="ins" href="javascript:void(0);" style="opacity:0.5; filter:alpha(opacity=50); color:#ccc;">设为默认</a> &nbsp;
			<?php
			}
			else
			{
			?>
			<a class="ins" href="javascript:void(0);" onclick="return modules('fc.module.default', '<?php echo $name; ?>');">设为默认</a> &nbsp;
			<?php
			}
			?>
			<a class="rst" href="javascript:void(0);" onclick="return modules('fc.module.update', '<?php echo $name; ?>');">更新</a> &nbsp;
			<?php
			if (in_array(strtoupper($name), explode('|', SYSTEM_MODULE)) || $name == $default)
			{
			?>
			<a class="del" href="javascript:void(0);" style="opacity:0.5; filter:alpha(opacity=50); color:#ccc;">禁用</a>
			<?php
			}
			else
			{
			?>
			<a class="del" href="javascript:void(0);" onclick="return modules('fc.module.disable', '<?php echo $name; ?>');">禁用</a>
			<?php
			}
			?>
			</span>
			<strong><?php echo ucfirst($name).' v'.$module['version'].($name == $default ? ' [默认模块]' : ''); ?></strong> &nbsp; <small>for <?php echo SYSTEM_NAME.' '.$module['for']; ?></small>
		</dd>
		<dd><cite>作者：</cite><?php echo $module['author']; ?> (<?php echo $module['contact']; ?>) &nbsp; <cite>官方：</cite><a href="http://<?php echo $module['support']; ?>/" target="_blank"><?php echo $module['support']; ?></a> &nbsp; <cite>更新：</cite><?php echo $module['update']; ?></dd>
		<dd><?php echo $module['desc']; ?></dd>
	</dl>
	<?php
	}
	$handle = opendir(PATH_MODULES);
	while (($name = readdir($handle)) !== false)
	{
		$dir = PATH_MODULES.$name;
		if ($name == '.' || $name == '..' || !is_dir($dir) || isset($modules[$name])) continue;
		$file = $dir.'/module.php';
		if (!file_exists($file)) continue;
		include $file;
		$class = 'FCModule'.ucfirst($name);
		if (!class_exists($class)) continue;
		$module = new $class();
	?>
	<dl class="disabled" module="<?php echo $name; ?>">
		<dt><img src="<?php echo $A->moduleIcon($name, $module->basic['icon']); ?>" /></dt>
		<dd>
			<span><a class="ins" href="javascript:void(0);" onclick="return modules('fc.module.insert', '<?php echo $name; ?>');">启用</a></span>
			<strong><?php echo ucfirst($name).' v'.$module->basic['version']; ?></strong> &nbsp; <small>for <?php echo SYSTEM_NAME.' '.$module->basic['for']; ?></small>
		</dd>
		<dd><cite>作者：</cite><?php echo $module->basic['author']; ?> (<?php echo $module->basic['contact']; ?>) &nbsp; <cite>官方：</cite><a href="http://<?php echo $module->basic['support']; ?>/" target="_blank"><?php echo $module->basic['support']; ?></a> &nbsp; <cite>更新：</cite><?php echo $module->basic['update']; ?></dd>
		<dd><?php echo $module->basic['desc']; ?></dd>
	</dl>
	<?php
	}
	closedir($handle);
	?>
</div>

</body>
</html>
