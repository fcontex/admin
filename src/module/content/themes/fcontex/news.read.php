<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $SITE['site_title']; ?></title>
<meta name="generator" content="<?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?>" />
<meta name="viewport" content="width=1024" />
<meta name="description" content="<?php echo $SITE['site_description']; ?>" />
<meta name="keywords" content="<?php echo $SITE['site_keywords']; ?>" />
<link href="<?php echo URL_THEME; ?>images/style.css" rel="stylesheet" type="text/css" />
<!--highlight_js-->
<link rel="stylesheet" type="text/css" href="<?php echo URL_MODULES; ?>content/scripts/prettify/prettify.css" />
<script type="text/javascript" src="<?php echo URL_MODULES; ?>content/scripts/prettify/prettify.js"></script>
<!--highlight_js-->
<script type="text/javascript" src="<?php echo URL_THEME; ?>images/jquery.min.js"></script>
<script type="text/javascript">
<!--comment_js-->
var comment = 
{
	//读取评论
	fetch : function(url)
	{
		$.get(url, function(data)
		{
			$('#comment_box').html(data);
			$('#cm_cid').val('<?php echo $page['ct_id']; ?>');
			$('#cm_ctitle').val('<?php echo $page['ct_title']; ?>');
			$('.button input').click(function()
			{
				$('#tips').removeClass().html('');
				var form = $('#form_comment').clone(true);
				$('#form_comment').remove();
				$(this).parent().after(form);
				$('#cm_topid').val($(this).attr('topid'));
				$('#cm_toid').val($(this).attr('toid'));
				$('#cm_toname').val($(this).attr('toname'));
				$('#cancel').css('display', 'inline').bind('click', function()
				{
					$('#tips').removeClass().html('');
					$('#form_comment').remove();
					$('#comment_form_box').after(form);
					$('#cm_topid').val('0');
					$('#cm_toid').val('0');
					$(this).css('display', 'none');
				});
			});
		});
		
	},
	
	//提交评论
	submit : function()
	{
		$('#tips').removeClass().html('<img src="<?php echo URL_THEME; ?>images/loading.gif" />').show();
		$.ajax
		({
			type    : 'post',
			url     : $('#cm_control').val()+'?mode=comment.insert',
			cache   : false,
			data    : $('#form_comment').serialize(),
			success : function(data, textStatus)
			{
				var a = data ? data.split('|') : ['无效的服务器响应。'];
				if (a[0] == 'YES')
				{
					$('#tips').addClass('yes').html(a[1]);
					setTimeout(comment.loader, 800);
				}
				else if (a[0] == 'ERR')
				{
					$('#tips').addClass('err').html(a[1]);
					if (a[2]) $('#'+a[2]).focus();
				}
				else
				{
					$('#tips').html(data);
				}
			},
			error  : function(XMLHttpRequest, textStatus, errorThrown)
			{
				$('#tips').addClass('err').html('超求超时.');
			}
		});
	},
	
	loader : function()
	{
		comment.fetch('<?php echo $R->getPageUrl('message/comment-'.$page['ct_id'].'-1'); ?>?'+Math.random());
	}
};
<!--comment_js-->

$(function()
{
	<!--comment_js-->
	comment.loader();
	<!--comment_js-->
	
	<!--highlight_js-->
	prettyPrint();
	<!--highlight_js-->
	
	<!--count_js-->
	var atts = '', dot = '', fcatts = $('#content .fcattached');
	fcatts.each(function(){atts += dot+$(this).attr('key'); dot=',';});
	$.get('<?php echo $R->getCtrlUrl()?>?mode=count&id=<?php echo $page['ct_id']; ?>&atts='+atts, null, function(data)
	{
		eval('obj = ' + data);
		if (typeof obj == 'object')
		{
			$('#talks').html(obj.talks || 0);
			$('#hits').html(obj.hits || 0);
			if (typeof obj.atts == 'object' && obj.atts.length == fcatts.length)
			{
				var i = 0;
				fcatts.each(function(){$(this).find('span').html(' (已下载'+(obj.atts[i++])+'次)');});
			}
		}
	});
	<!--count_js-->
});
</script>
</head>
<body>
<?php include 'inc.header.php'; ?>
<div class="container banner"><img src="<?php echo URL_THEME; ?>images/<?php echo $banner; ?>" alt="<?php echo $SITE['site_keywords']; ?>" width="980" height="265" /></div>
<div class="container content">
	<div class="position">
		当前位置：
		<a href="<?php echo URL_SITE; ?>">首页</a> &#8250; 
		<a href="<?php echo $R->getPageUrl('news/list-'.$page['ct_cid']); ?>"><?php echo $page['ct_ctitle']; ?></a> &#8250; 
		正文
	</div>
	<div class="L">
		<ul>
			<?php
			foreach ($categories as $cate)
			{
			?>
			<li><a<?php if($page['ct_cid']==$cate['cg_id']) echo ' class="C"' ;?> href="<?php echo $R->getPageUrl('news/list-'.$cate['cg_id']); ?>"><?php echo $cate['cg_title']; ?></a></li>
			<?php
			}
			?>
		</ul>
	</div>
	<div class="R">
		<div class="page">
			<div class="title"><h1><?php echo $page['ct_title']; ?></h1></div>
			<?php if (!empty($page['ct_summary'])) { ?><div class="summary"><?php echo $page['ct_summary']; ?></div><?php } ?>
			<div class="read" id="content"><?php echo $page['ct_content']; ?></div>
			<div class="attr">
				<span class="date"><?php echo date('Y-m-d', $page['ct_inserttime']); ?></span> &nbsp;
				<span class="hits" id="hits"><?php echo $page['ct_hits']; ?></span> &nbsp;
				<span class="tags">
				<?php
				$tags = explode(',', $page['ct_tags']);
				foreach ($tags as $tag)
				{
				?>
				<a href="<?php echo $R->getPageUrl('search/'.$tag, ''); ?>"><?php echo $tag; ?></a>&nbsp;
				<?php
				}
				?>
				</span>
			</div>
			<div id="comment_box"><!--comment--><b style="padding:20px 0; display:block;">正在加载评论...</b><!--comment--></div>
		</div>
	</div>
	<div class="clear"></div>
</div>
<?php include 'inc.footer.php'; ?>
</body>
</html>
