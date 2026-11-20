<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $SITE['site_title']; ?></title>
<meta name="generator" content="<?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?>" />
<meta name="viewport" content="width=1136" />
<meta name="description" content="<?php echo $SITE['site_description']; ?>" />
<meta name="keywords" content="<?php echo $SITE['site_keywords']; ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo URL_THEME; ?>images/style.css" />
<script type="text/javascript" src="<?php echo URL_THEME; ?>images/jquery.js"></script>
<!--scrolltop_js--><script type="text/javascript" src="<?php echo URL_THEME; ?>images/scrolltop.js"></script><!--scrolltop_js-->
<script type="text/javascript">
$(function()
{
	<!--scrolltop_js-->
	scrolltop.init();
	<!--scrolltop_js-->
	
	$('.list_img a').hover(function()
	{
		$(this).animate({'opacity':'0.1'}, 300);
	}
	,function()
	{
		$(this).stop(true,false).animate({'opacity':'0'}, 300);
	});
})
</script>
</head>
<body>

	<div class="toper"></div>
	
	<div class="container">
	<!--container-->
	
		<?php include 'inc.head.php'; ?>
		
		<?php
		if (isset($position))
		{
		?>
		<div class="center position">
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
		
		<div class="center">
		<!--center-->
		
			<div class="left">
			<!--left-->
			
			<?php
			foreach ($items as $item)
			{
			?>
				<div class="list">
					<div class="simplemodal-love"><a href="<?php echo $R->getPageUrl('content/read-'.$item['ct_id']); ?>"><?php echo $item['ct_hits']; ?></a></div>
					<h1><a href="<?php echo $R->getPageUrl('content/read-'.$item['ct_id']); ?>"><?php echo $item['ct_title']; ?></a></h1>
					<ul>
						<li class="author"><?php echo $item['ct_username']; ?></li>
						<li class="time"><?php echo date('Y-m-d', $item['ct_inserttime']); ?></li>
						<li class="comments"><a href="<?php echo $R->getPageUrl('content/read-'.$item['ct_id']); ?>#comment_box"><?php echo $item['ct_talks']; ?></a></li>
						<li class="litag"><span class="b_tag"></span>
						<?php
						$tags = explode(',', $item['ct_tags']);
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
					if ($item['ct_cover'])
					{
					?>
					<div class="list_img"><a href="<?php echo $R->getPageUrl('content/read-'.$item['ct_id']); ?>"></a><img src="<?php echo $A->getThumb($item['ct_cover']); ?>" width="620" /></div>
					<?php
					}
					if ($A->trim($item['ct_summary'], TRUE))
					{
					?>
					<div class="list_skim"><?php echo $item['ct_summary']; ?></div>
					<?php
					}
					?>
					
				</div>
				
				<p class="delimiter"></p>
			
			<?php
			}
			if (count($items) == 0)
			{
				echo '<div style="font-size:16px;">暂无记录。</div>';
			}
			else echo $turnner;
			?>
			
			<!--left-->
			</div>
			 
			<?php include 'inc.side.php'; ?>
		
		<!--center-->
		</div>
		
		<div class="clear"></div>
		
		<?php include 'inc.foot.php'; ?>
	
	<!--container-->
	</div>
	
</body>
</html>
