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
<div class="container banner"><img src="<?php echo URL_THEME; ?>images/page_404.jpg" alt="<?php echo $SITE['site_title']; ?>" width="980" height="265" /></div>
<div class="container content page404">
	<p>
		<strong>404</strong> : Page Not Found.<br />
		错误：请求的页面已被移动或删除。<a href="<?php echo URL_SITE; ?>">返回首页</a>
	</p>
</div>
<?php include 'inc.footer.php'; ?>
</body>
</html>
