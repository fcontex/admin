<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>查看页面 - <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
<meta name="robots" content="nofollow">
<?php if (0) { ?><link rel="stylesheet" type="text/css" href="../../system/skins/default/style.css" /><?php }else{ ?>
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN; ?>style.css?v=<?php echo ASSET_VERSION; ?>" /><?php } ?>
<link rel="stylesheet" type="text/css" href="<?php echo URL_MODULE; ?>template/images/style.css?v=<?php echo filemtime(PATH_MODULE.'template/images/style.css'); ?>" />
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo URL_TOOLS; ?>excolor/jquery.modcoder.excolor.js"></script>
<script type="text/javascript">
function content(mode, args)
{
	switch (mode)
	{
		case 'fc.tags.delete':
			args = args || $$.selectval('#form_tags :checked:enabled[name=select]');
			if (args == '')
			{
				$$.alert({text:'请选择记录。'});
				return false;
			}
			$$.confirm({text:'确定删除[ #'+args+' ]？', ok:function()
			{
				$$.post('<?php echo $ctrlurl = $R->getCtrlUrl(); ?>?mode=' + mode, {args:args}, function(){$$.redirect();});
			}});
			break;
		case 'fc.tags.update':
			$$.post('<?php echo $ctrlurl; ?>?mode=' + mode, $('#form_tags').serialize());
			break;
		default:
			$$.alert({text:'请选择操作。'});
	}
	
	return false;
}

function liOver()
{
	$('.tags_select li').hover(function()
	{
		$(this).addClass('c');
		//$(this).find('span').animate({'bottom': 0}, 300);
	},
	function()
	{
		$(this).removeClass('c');
		//$(this).find('span').animate({'bottom': -26}, 300);		
	});	
}

$(function()
{
	liOver();
	$('.color').modcoder_excolor();
});
</script>
</head>
<body>
	<form id="form_tags">
	<div class="tags_select">
		<script type="text/javascript">
		function itemActive(index)
		{
			$('#tg_id_'+index).attr('name', 'tg_id[]');
			$('#tg_title_'+index).attr('name', 'tg_title[]');
			$('#tg_color_'+index).attr('name', 'tg_color[]');
			$('#item_'+index).addClass('a');
		}
		</script>
    	<ul>
        <?php
        foreach (DB::table('tags')->order('tg_id', 'desc')->rows() as $rst){
		?>
        	<li id="item_<?php echo $rst['tg_id']; ?>">
            <input type="hidden" value="<?php echo $rst['tg_id']; ?>" id="tg_id_<?php echo $rst['tg_id']; ?>" />
            <input type="text" class="text" value="<?php echo $rst['tg_title']; ?>" id="tg_title_<?php echo $rst['tg_id']; ?>" onfocus="itemActive(<?php echo $rst['tg_id']; ?>);" />
            <input type="text" class="text color"  value="<?php echo $rst['tg_color']; ?>" style="float:left; width:50px;" id="tg_color_<?php echo $rst['tg_id']; ?>" onfocus="itemActive(<?php echo $rst['tg_id']; ?>);" />
            <span style="float:right; margin:5px 10px 0 0;"><input type="checkbox" class="checkbox" name="select" value="<?php echo $rst['tg_id']; ?>" /></span></li>
        <?php
		}
		?>
        	<li style="line-height:2em; text-align:center;">
            	<a href="" onclick="content('fc.tags.update'); return false;">保存更改</a><br />
            	<a href="" onclick="content('fc.tags.delete'); return false;">删除所选</a>
            </li>
        </ul>
    </div>
	</form>
</body>
</html>
