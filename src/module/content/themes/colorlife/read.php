<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $SITE['site_title']; ?></title>
<meta name="generator" content="<?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?>" />
<meta name="viewport" content="width=1136" />
<meta name="description" content="<?php echo $SITE['site_description']; ?>" />
<meta name="keywords" content="<?php echo $SITE['site_keywords']; ?>" />
<?php if(0){ ?><link rel="stylesheet" type="text/css" href="images/style.css" /><?php }else{ ?>
<link rel="stylesheet" type="text/css" href="<?php echo URL_THEME; ?>images/style.css" /><?php } ?>
<script type="text/javascript" src="<?php echo URL_THEME; ?>images/jquery.js"></script>
<!--scrolltop_js--><script type="text/javascript" src="<?php echo URL_THEME; ?>images/scrolltop.js"></script><!--scrolltop_js-->
<!--highlight_js-->
<link rel="stylesheet" type="text/css" href="<?php echo URL_MODULES; ?>content/scripts/prettify/prettify.css" />
<script type="text/javascript" src="<?php echo URL_MODULES; ?>content/scripts/prettify/prettify.js"></script>
<!--highlight_js-->
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
				$('#tips').addClass('err').html('超求超时。');
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
	
	<!--scrolltop_js-->
	scrolltop.init();
	<!--scrolltop_js-->
	
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

	<div class="toper"></div>
	
	<div class="container">
	<!--container-->
	
		<?php include 'inc.side.php'; ?>
		
		<div class="main">
		<!--main-->
		
			<?php include 'inc.head.php'; ?>
			
			<?php
			if ($position)
			{
			?>
			<div class="position">
			<!--position-->
				<a href="<?php echo URL_SITE; ?>">首页</a>
				<?php
				foreach ($position as $pos)
				{
					if ($pos['link'])
					{
				?>
				<a href="<?php echo $pos['link']; ?>"><?php echo $pos['text']; ?></a>
				<?php
					}
					else echo $pos['text'];
				}
				?>
			<!--position-->
			</div>
			<?php
			}
			?>
		
			<?php
			if (empty($page))
			{
				echo '<div>信息不存在或已被删除。</div>';
			}
			else
			{
			?>
			<div class="post">
			<!--post-->
				<?php
				if ($page['ct_type'] > CONTENT_PAGE)
				{
				?>
				<div class="hits" id="hits"><?php echo $page['ct_hits']; ?></div>
				<h1><?php echo $page['ct_title']; ?></h1>
				<ul>
					<li class="author"><?php echo $page['ct_username']; ?></li>
					<li class="time"><?php echo date('Y-m-d', $page['ct_inserttime']); ?></li>
					<li class="comments"><a href="#comment_box" id="talks"><?php echo $page['ct_talks']; ?></a></li>
					<li class="tags"><span></span>
					<?php
					$tags = explode(',', $page['ct_tags']);
					foreach ($tags as $tag)
					{
					?>
					<a href="<?php echo $R->getPageUrl('content/tag/'.$tag, ''); ?>"><?php echo $tag; ?></a>&nbsp;
					<?php
					}
					?>
					</li>
				</ul>
				<?php
				}
				?>
				<div class="content" id="content"><!--content--><?php echo $page['ct_content']; ?><!--content--></div>
				
			
			<!--post-->
			</div>
			<?php
			}
			?>
			
			<p class="delimiter"></p>
			
			<div id="comment_box"><!--comment--><b style="padding:20px 0; display:block;">正在加载评论...</b><!--comment--></div>
		
		<!--main-->
		</div>
		
		<div class="clear"></div>
	
	<!--container-->
	</div>
	
	<?php include 'inc.foot.php'; ?>

</body>
</html>
