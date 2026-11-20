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
</head>
<body>
<?php include 'inc.header.php'; ?>
<div class="container banner"><img src="<?php echo URL_THEME; ?>images/<?php echo $banner; ?>" alt="<?php echo $SITE['site_keywords']; ?>" width="980" height="265" /></div>
<div class="container content">
	<div class="position">
		当前位置：
		<a href="<?php echo URL_SITE; ?>">首页</a> &#8250; 
		产品检索：“<strong><?php echo $keyw; ?></strong>”
	</div>
	<div class="L">
		<ul>
			<?php
			foreach ($categories as $cate)
			{
			?>
			<li><a href="<?php echo $R->getPageUrl('product/list-'.$cate['cg_id']); ?>"><?php echo $cate['cg_title']; ?></a></li>
			<?php
			}
			?>
		</ul>
	</div>
	<div class="R product">
		<?php
		foreach ($items as $item)
		{
		?>
		<dl>
			<dt><a href="<?php echo $url = $R->getPageUrl('product/'.$item['ct_id']); ?>">
			<?php
			$thumb = $A->getThumb($item['ct_cover'], 260, 200);
			if (empty($thumb))
			{
			?>
			<img src="<?php echo URL_THEME; ?>images/blank.gif" width="260" height="200" class="nophoto" />
			<?php
			}
			else
			{
			?>
			<img src="<?php echo $thumb; ?>" width="260" height="200" alt="<?php echo $item['ct_title']; ?>" />
			<?php
			}
			?>
			</a></dt>
			<dd class="T">
				<a class="title" href="<?php echo $url ?>"><strong><?php echo str_replace($keyw, '<span>'.$keyw.'</span>', $item['ct_title']); ?></strong></a>
				<div class="view"><?php echo $item['ct_summary']; ?></div>
			</dd>
		</dl>
		<?php
		}
		if (count($items) == 0)
		{
		?>
		<div class="page">暂无记录。</div>
		<?php
		}
		else echo $turnner;
		?>
	</div>
	<div class="clear"></div>
</div>
<?php include 'inc.footer.php'; ?>
<script type="text/javascript" src="<?php echo URL_THEME; ?>images/jquery.min.js"></script>
</body>
</html>
