<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>错误信息 - <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN; ?>style.css?v=<?php echo ASSET_VERSION; ?>" />
</head>
<body>
	<div class="page_error"><?php if ($tips == ''){ ?><?php echo ERROR_NO_RIGHTS; } else { echo '#'.$tips.'#'.ERROR_NO_RIGHTS; } ?> <a href="#" onclick="location.reload(); return false;">刷新</a></div>
</body>
</html>
