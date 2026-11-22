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
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>jquery.ui.min.js"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo URL_TOOLS; ?>quill/quill.js"></script>
<script type="text/javascript" src="<?php echo URL_TOOLS; ?>datepicker/WdatePicker.js"></script>
<script type="text/javascript">
function content(mode, args)
{
	switch (mode)
	{
		case 'fc.content.update':
			window.__editor_summary.sync();
			window.__editor_content.sync();
			$$.post('<?php echo $R->getCtrlUrl(); ?>?mode=' + mode, $('#form_detail').serialize() + '&args=' + args, function()
			{
				$$.redirect('<?php echo $R->getPageUrl('content/fc.content.select'); ?>');
			});
			break;
		default:
			$$.alert({text:'无效参数 [ '+mode+' ]。'});
			break;
	}
	
	return false;
}

function coverUploadCallback(param)
{
	$('#thumb_img').html('<a href="'+param.url+'" target="_blank"><img src="'+param.url+'" /></a>');
	$('#ct_cover').val(param.dir+param.name);
	$$.filesUploadLayer.close();
}

function coverClear()
{
	$('#thumb_img').html('');
	$('#ct_cover').val('');
}

function pictureUploadCallback(param)
{
	var pictures = $('#pictures .thumb');
	var url = '', dot = '';
	for (var i=0; i<pictures.length; i++)
	{
		var hidden  = $(pictures.get(i)).find('input:hidden');
		var picture = $(pictures.get(i)).find('.thumb_img');
		if (hidden.val() == '')
		{
			url += dot + (param.dir+param.name); dot = '|';
			hidden.val(param.dir+param.name);
			picture.html('<a href="'+param.url+'" target="_blank"><img src="'+param.url+'" /></a>');
			$('#pictures').append($('#picture_new').html());
			break;
		}
		else
		{
			url += dot + hidden.val(); dot = '|';
		}
	}
	$('#ct_pictures').val(url);
	$$.filesUploadLayer.close();
}

function pictureClear(ele)
{
	var ele = $(ele);
	if (ele.find('.thumb_img').html() == '')
	{
		return;
	}
	ele.remove();
	var pictures = $('#pictures .thumb');
	var url = '', dot = '';
	for (var i=0; i<pictures.length; i++)
	{
		var hidden  = $(pictures.get(i)).find('input:hidden');
		if (hidden.val() != '')
		{
			url += dot + hidden.val(); dot = '|';
		}
	}
	$('#ct_pictures').val(url);
}

$(function()
{
	window.__editor_summary = $$.editor({url:'<?php echo $R->getPageUrl('system/fc.files.upload'); ?>',target:'#ct_summary',mode:2});
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
		<th>主标题</th>
		<td><input class="text" type="text" name="ct_title" id="ct_title" value="<?php echo $rst['ct_title']; ?>" /> <span><cite>*</cite> 必填</span></td>
	</tr>
	<tr>
		<th>副标题</th>
		<td><input class="text" type="text" name="ct_title2" id="ct_title2" value="<?php echo $rst['ct_title2']; ?>" /></td>
	</tr>
	<tr>
		<th>下载地址</th>
		<td><input class="text" type="text" name="ct_title3" id="ct_title3" value="<?php echo $rst['ct_title3']; ?>" /></td>
	</tr>
    <tr>
		<th>栏目</th>
		<td>
		<input type="hidden" name="ct_ctitle" id="ct_ctitle" value="<?php echo $rst['ct_ctitle']; ?>" />
        <select name="ct_cid" id="ct_cid" onchange="$('#ct_ctitle').val($.trim($(this).find('option:selected[value!=0]').text()));">
        	<option value="0">=选择栏目=</option>
            <?php
			foreach (DB::table('category')->field(array('cg_id', 'cg_title'))->where('cg_pid=?', array(0))->order('cg_order', 'asc')->order('cg_id', 'asc')->rows() as $rstP)
			{
            ?>
            <option value="<?php echo $rstP['cg_id']; ?>"<?php if ($rst['ct_cid'] == $rstP['cg_id'])echo ' selected="selected"'; ?>><?php echo $rstP['cg_title']; ?></option>
			<?php
				foreach (DB::table('category')->field(array('cg_id', 'cg_title'))->where('cg_pid=?', array($rstP['cg_id']))->order('cg_order', 'asc')->order('cg_id', 'asc')->rows() as $rstC)
				{
			?>
				<option value="<?php echo $rstC['cg_id']; ?>"<?php if ($rst['ct_cid'] == $rstC['cg_id'])echo ' selected="selected"'; ?>>&nbsp; <?php echo $rstC['cg_title']; ?></option>
			<?php
				}
			}
            ?>
        </select>
		<script type="text/javascript">$('#ct_cid').change();</script>
		</td>
	</tr>
    <tr>
		<th>封面</th>
		<td>
			<div class="thumb">
            	<div class="thumb_img" id="thumb_img"><?php if ($rst['ct_cover']){ ?><a target="_blank" href="<?php echo $A->getThumb($rst['ct_cover']); ?>"><img src="<?php echo $A->getThumb($rst['ct_cover'], 200, 200); ?>" /></a><?php } ?></div>
                <h1><a href="#"  onclick="$$.filesUploadLayer.open({url:'<?php echo $R->getPageUrl('system/fc.files.upload'); ?>',callback:'coverUploadCallback',opener:window});return false;">设置封面</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="coverClear();return false;">清除</a></h1>
            </div>
            <input type="hidden" name="ct_cover" id="ct_cover" value="<?php echo $rst['ct_cover']; ?>">
		</td>
	</tr>
	<tr>
		<th>多图</th>
		<td>
			<div id="picture_new" style="display:none;">
				<div class="thumb">
					<div class="thumb_img"></div>
					<h1><a href="#" onclick="$$.filesUploadLayer.open({url:'<?php echo $R->getPageUrl('system/fc.files.upload'); ?>',callback:'pictureUploadCallback',opener:window});return false;">添加图片</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="pictureClear(this.parentNode.parentNode);return false;">清除</a></h1>
					<input type="hidden" />
				</div>
			</div>
			<div id="pictures">
				<?php
				$pictures = explode('|', $rst['ct_pictures']);
				foreach ($pictures as $picture)
				{
					if (trim($picture) != '')
					{
				?>
				<div class="thumb">
					<div class="thumb_img"><a target="_blank" href="<?php echo $A->getThumb($picture); ?>"><img src="<?php echo $A->getThumb($picture, 200, 200); ?>" /></a></div>
					<h1><a href="#" onclick="$$.filesUploadLayer.open({url:'<?php echo $R->getPageUrl('system/fc.files.upload'); ?>',callback:'pictureUploadCallback',opener:window});return false;">添加图片</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="pictureClear(this.parentNode.parentNode);return false;">清除</a></h1>
					<input type="hidden" value="<?php echo $picture; ?>" />
				</div>
				<?php
					}
				}
				?>
				<div class="thumb">
					<div class="thumb_img"></div>
					<h1><a href="#" onclick="$$.filesUploadLayer.open({url:'<?php echo $R->getPageUrl('system/fc.files.upload'); ?>',callback:'pictureUploadCallback',opener:window});return false;">添加图片</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="pictureClear(this.parentNode.parentNode);return false;">清除</a></h1>
					<input type="hidden" />
				</div>
			</div>
			<input type="hidden" name="ct_pictures" id="ct_pictures" value="<?php echo $rst['ct_pictures']; ?>" />
		</td>
	</tr>
    <tr>
		<th>标签</th>
		<td>
        	<input class="text" type="text" name="ct_tags" id="ct_tags" value="<?php echo $rst['ct_tags']; ?>" onfocus="$('#tags_selector').slideDown(200);" /> <span>手工输入时逗号分隔</span>
			<div id="tags_selector" style="display:none;">
				<script type="text/javascript">
				function tagSelector(tag)
				{
					var input = $('#ct_tags');
					var tags = $.trim(input.val());
					var tagsA = tags ? tags.replace('，',',').split(',') : [];
					var tags='', dot='';
					for (var i in tagsA)
					{
						if (tagsA[i] == tag) continue;
						tags += dot + tagsA[i];
						dot = ',';
					}
					input.val(tags+dot+tag);
				}
				</script>
				<a href="#" onclick="$('#tags_selector').slideUp(200);return false;" class="close">&nbsp;</a>
				<div>
				<?php
				$n = 0;
				foreach (DB::table('tags')->order('tg_id', 'desc')->rows() as $rstT){
				?>
				<a href="#" onclick="tagSelector('<?php echo $rstT['tg_title']; ?>');return false;"><?php echo $rstT['tg_title']; ?></a>
				<?php
					$n++;
				}
				if ($n == 0) echo '暂无标签';
				?>
				</div>
			</div>
        </td>
	</tr>
	<tr>
		<th>摘要</th>
		<td>
			<input type="checkbox" class="checkbox" value="1" id="ct_summary_auto" name="ct_summary_auto" onchange="$('#ct_summary_box').toggle(200);" /> <label for="ct_summary_auto">自动从正文中截取摘要</label> &nbsp; 
			<div id="ct_summary_box" style="margin-top:6px;"><textarea name="ct_summary" id="ct_summary" style="width:720px; height:120px;"><?php echo str_replace(array('&lt;','&gt;'), array('&amp;lt;','&amp;gt;'), $rst['ct_summary']); ?></textarea></div>
		</td>
	</tr>
	<tr>
		<th>内容</th>
		<td><textarea name="ct_content" id="ct_content" style="width:720px; height:340px;"><?php echo str_replace(array('&lt;','&gt;'), array('&amp;lt;','&amp;gt;'), $rst['ct_content']); ?></textarea> <span><cite>*</cite> 必填</span></td>
	</tr>
	<tr>
    	<th>发布时间</th>
		<td><input class="text" type="text" name="ct_inserttime" id="ct_inserttime" value="<?php echo date('Y-m-d H:i:s', $rst['ct_inserttime']); ?>" readonly="readonly" onclick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss', skin:'<?php echo DATE_SKIN; ?>'});" /> <span><cite>*</cite> 必填 发布时按此时间倒排序</span> </td>
	</tr>
	<tr>
		<th>附加属性</th>
		<td>
			<input type="checkbox" class="checkbox" value="1" id="ct_check" name="ct_check"<?php if ($rst['ct_check'] == 0)echo ' checked="checked"'; ?> /> <label for="ct_check">草稿</label>&nbsp;&nbsp;&nbsp;
        	<input type="checkbox" class="checkbox" value="1" id="ct_fixed" name="ct_fixed"<?php if ($rst['ct_fixed'] == 1)echo ' checked="checked"'; ?> /> <label for="ct_fixed">置顶</label>&nbsp;&nbsp;&nbsp;
            <input type="checkbox" class="checkbox" value="1" id="ct_quiet" name="ct_quiet"<?php if ($rst['ct_quiet'] == 0)echo ' checked="checked"'; ?> /> <label for="ct_quiet">允许评论</label>&nbsp;&nbsp;&nbsp;
			<input type="checkbox" class="checkbox" name="ct_seo"<?php if ($rst['ct_seo'])echo ' checked="checked"'; ?> value="1" id="ct_seo" onclick="if (this.checked) $('#seo').slideDown(300); else $('#seo').slideUp(300);" /> <label for="ct_seo">自定义页面SEO属性</label>
		</td>
	</tr>
    <tbody id="seo" <?php if (!$rst['ct_seo'])echo 'style="display:none;"'; ?>>
    <tr>
		<th>页面标题</th>
		<td><input class="text" type="text" name="ct_pagetitle" id="ct_pagetitle" value="<?php echo $rst['ct_pagetitle']; ?>" /> </td>
	</tr>
    <tr>
		<th>页面关键字</th>
		<td><textarea name="ct_keywords" id="ct_keywords" style="height:60px;"><?php echo $rst['ct_keywords']; ?></textarea></td>
	</tr>
    <tr>
		<th>页面描述</th>
		<td><textarea name="ct_description" id="ct_description" style="height:60px;"><?php echo $rst['ct_description']; ?></textarea></td>
	</tr>
    </tbody>
	<tr class="action"><th>&nbsp;</th><td><input type="submit" class="button" value="确认修改" /><input type="button" class="button cancle" value="放弃修改" onclick="$$.redirect('<?php echo $R->getPageUrl('content/fc.content.select'); ?>');" /></td></tr>
	<?php
		}
	}
	?>
</table>
</form>

</body>
</html>
