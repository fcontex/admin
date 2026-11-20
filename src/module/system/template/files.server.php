<?php
//JS脚本回调函数（白名单校验 防止注入到脚本调用位）
$callback = $A->strGet('callback');
if (!preg_match('/^[a-zA-Z0-9_$.]+$/', $callback)) $callback = 'filesUploadNoCallback';
//上传目录
$fileDir = $A->strGet('dir');
//标签文本
$label = $A->strGet('label');
//文件类型
$fileType = $A->strGet('type');
$show = $A->strGet('show');
$keyw = $A->strGet('keyw');
//本页地址（页面路由，非控制器地址）
$pageUrl = $R->getPageUrl('system/fc.files.server');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>已上传到服务器的文件 - <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
<meta name="robots" content="nofollow">
<?php if (0) { ?><link rel="stylesheet" type="text/css" href="../skins/default/style.css" /><?php }else{ ?>
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN; ?>style.css?v=<?php echo ASSET_VERSION; ?>" /><?php } ?>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>"></script>
</head>
<body style="padding:0;">
<div class="tabs">
	<div></div>
	<a href="<?php echo $R->getPageUrl('system/fc.files.upload'); ?>&callback=<?php echo htmlspecialchars($callback, ENT_QUOTES, 'UTF-8'); ?>&type=<?php echo htmlspecialchars($fileType, ENT_QUOTES, 'UTF-8'); ?>&dir=<?php echo htmlspecialchars($fileDir, ENT_QUOTES, 'UTF-8'); ?>&label=<?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>">本地</a>
	<a href="<?php echo $R->getPageUrl('system/fc.files.server'); ?>&callback=<?php echo htmlspecialchars($callback, ENT_QUOTES, 'UTF-8'); ?>&type=<?php echo htmlspecialchars($fileType, ENT_QUOTES, 'UTF-8'); ?>&dir=<?php echo htmlspecialchars($fileDir, ENT_QUOTES, 'UTF-8'); ?>&label=<?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>" class="C">已上传</a>
	<!--<a href="<?php echo $R->getPageUrl('system/fc.files.url'); ?>&callback=<?php echo htmlspecialchars($callback, ENT_QUOTES, 'UTF-8'); ?>&type=<?php echo htmlspecialchars($fileType, ENT_QUOTES, 'UTF-8'); ?>&dir=<?php echo htmlspecialchars($fileDir, ENT_QUOTES, 'UTF-8'); ?>&label=<?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>">网络</a>-->
</div>
<div class="files_upload_main">
	<div class="files_search"><input name="keyw" id="keyw" type="text" value="<?php echo htmlspecialchars($keyw, ENT_QUOTES, 'UTF-8'); ?>" class="text" /> <input type="button" class="button" value="搜索" onclick="$$.redirect('<?php echo $pageUrl; ?>&type=<?php echo htmlspecialchars($fileType, ENT_QUOTES, 'UTF-8'); ?>&show=<?php echo htmlspecialchars($show, ENT_QUOTES, 'UTF-8'); ?>&keyw='+$('#keyw').val());" /></div>
	<h1>已上传到服务器的文件</h1>
	<div class="files_server_main" style="clear:both;">
		<?php
		$pager = FCApplication::sharedPageTurnner();
		$pager->style = 'Simple';
		$Q = DB::table('attached')
		  ->field(array('at_id', 'at_token', 'at_dir', 'at_filename', 'at_filenewname', 'at_isimage', 'at_size', 'at_remote', 'at_url'));
		if ($show == 'image') $Q->where('at_isimage = 1');
		if (trim($keyw) != '')
		{
			$Q->where('(at_filename like ? or at_filenewname like ?)', array('%'.$keyw.'%', '%'.$keyw.'%'));
		}
		$Q->order('at_id', 'desc');
		$items = $Q->page($pager);
		?>
		<div class="file_page"><?php echo $pager->turnner; ?></div>
		<h2 class="files_server_title">
			<a href="<?php echo $pageUrl; ?>&type=<?php echo htmlspecialchars($fileType, ENT_QUOTES, 'UTF-8'); ?>&show=all"<?php if ($show == 'all' || trim($show) == '') echo ' class="current"'; ?>>所有文件</a>&nbsp;&nbsp;|&nbsp;&nbsp;
			<a href="<?php echo $pageUrl; ?>&type=<?php echo htmlspecialchars($fileType, ENT_QUOTES, 'UTF-8'); ?>&show=image"<?php if ($show == 'image') echo ' class="current"'; ?>>图像 (<?php echo DB::table('attached')->where('at_isimage = 1')->count(); ?>)</a>
		</h2>
		<div style="clear:both;"></div>
		<ul>
		<?php
		$n = 0;
		foreach ($items as $r)
		{
			//用户可控字段统一转义：JS字面量用 json_encode+htmlspecialchars，HTML文本用 htmlspecialchars
			$jsName   = htmlspecialchars(json_encode($r['at_filenewname']), ENT_QUOTES, 'UTF-8');
			$jsTitle  = htmlspecialchars(json_encode($r['at_filename']), ENT_QUOTES, 'UTF-8');
			$jsDir    = htmlspecialchars(json_encode($r['at_dir']), ENT_QUOTES, 'UTF-8');
			$jsUrl    = htmlspecialchars(json_encode(DIR_SITE.DIR_STORE.'/'.$A->system['uploadDir'].'/'.$r['at_dir'].$r['at_filenewname']), ENT_QUOTES, 'UTF-8');
			$jsLink   = htmlspecialchars(json_encode($r['at_url']), ENT_QUOTES, 'UTF-8');
			$jsPipe   = htmlspecialchars(json_encode($R->getPageUrl('system/file/'.$r['at_token'], '', DIR_SITE)), ENT_QUOTES, 'UTF-8');
			$txtTitle = htmlspecialchars($r['at_filename'], ENT_QUOTES, 'UTF-8');
			$thumbUrl  = htmlspecialchars($A->getThumb($r['at_dir'].$r['at_filenewname']), ENT_QUOTES, 'UTF-8');
			$thumbSrc  = htmlspecialchars($r['at_isimage'] ? $A->getThumb($r['at_dir'].$r['at_filenewname'], 200, 200) : $A->fileIcon($A->fileSuffix($r['at_filenewname'])), ENT_QUOTES, 'UTF-8');
			if ($r['at_remote'])
			{
			?>
			<li>
				<span><a href="#" onclick="$$.filesUploadLayer.opener.<?php echo $callback; ?>({id:<?php echo $r['at_id']; ?>,image:<?php echo $r['at_isimage'] ? 'true' : 'false'; ?>,http:false,name:<?php echo $jsName; ?>,title:<?php echo $jsTitle; ?>,dir:<?php echo $jsDir; ?>,url:<?php echo $jsLink; ?>,pipe:<?php echo $jsPipe; ?>,size:<?php echo $r['at_size']; ?>});return false;"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></a></span>
				<a target="_blank" href="<?php echo htmlspecialchars($r['at_url'], ENT_QUOTES, 'UTF-8'); ?>"><img src="<?php echo $A->fileIcon($A->fileSuffix($r['at_filenewname'])); ?>" height="40" alt="<?php echo $txtTitle; ?>" title="<?php echo $txtTitle; ?>" /></a> 
				<strong><?php echo $txtTitle; ?></strong>&nbsp; |&nbsp; 远程文件
			</li>
			<?php
			}
			else
			{
			?>
			<li>
				<span><a href="#" onclick="$$.filesUploadLayer.opener.<?php echo $callback; ?>({id:<?php echo $r['at_id']; ?>,image:<?php echo $r['at_isimage'] ? 'true' : 'false'; ?>,http:false,name:<?php echo $jsName; ?>,title:<?php echo $jsTitle; ?>,dir:<?php echo $jsDir; ?>,url:<?php echo $jsUrl; ?>,pipe:<?php echo $jsPipe; ?>,size:<?php echo $r['at_size']; ?>});return false;"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></a></span>
				<a target="_blank" href="<?php echo $thumbUrl; ?>"><img src="<?php echo $thumbSrc; ?>" height="40" alt="<?php echo $txtTitle; ?>" title="<?php echo $txtTitle; ?>" /></a> 
				<strong><?php echo $txtTitle; ?></strong>&nbsp; |&nbsp; <?php echo round($r['at_size']/1024, 2).'KB'; ?>
			</li>
			<?php
			}
			$n++;
		}
		if ($n == 0) echo '<li>&nbsp; &nbsp; 暂无记录。</li>';
		?>
		</ul>
	</div>
</div>
</body>
</html>
