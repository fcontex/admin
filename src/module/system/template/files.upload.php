<?php
//JS脚本回调函数（白名单校验 防止注入到脚本调用位）
$callback = $A->strGet('callback');
if (!preg_match('/^[a-zA-Z0-9_$.]+$/', $callback)) $callback = 'filesUploadNoCallback';
//上传目录（与后端同规则）
$fileDir = $A->strGet('dir');
if (!preg_match('/^[a-z0-9_-]+$/i', $fileDir)) $fileDir = 'content';
//标签文本
$label = htmlspecialchars($A->strGet('label'), ENT_QUOTES, 'UTF-8');
//文件类型
$fileType = $A->strGet('type');
if (!preg_match('/^[a-z]+$/i', $fileType)) $fileType = 'image';
//文件后缀
$suffix = $A->system['uploadSuffix'];
if (isset($suffix[$fileType]))
{
	$suffix = $suffix[$fileType];
}
else
{
	if ($fileType == 'all')
	{
		$suffix = implode(',', $suffix);
	}
	else $suffix = $suffix['image'];
}
$fileExt = $dot = '';
foreach (explode(',', $suffix) as $v)
{
	$fileExt .= $dot . '*.'.$v;
	$dot = ';';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>本地计算机上的文件 - <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
<meta name="robots" content="nofollow">
<?php if (0) { ?><link rel="stylesheet" type="text/css" href="../skins/default/style.css" /><?php }else{ ?>
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN; ?>style.css?v=<?php echo ASSET_VERSION; ?>" /><?php } ?>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>"></script>
<style type="text/css">
.files_upload_zone input[type="file"] {
    display: none;
}
.files_upload_zone .files_upload_button {
    display: inline-block;
    width: 72px;
    height: 25px;
    background: url('<?php echo URL_SKIN; ?>upload.png') no-repeat;
    cursor: pointer;
}
.progress-bar {
    width: 100%;
    height: 20px;
    background-color: #f5f5f5;
    border-radius: 4px;
    overflow: hidden;
    margin: 10px 0;
}
.progress {
    height: 100%;
    background-color: #337ab7;
    width: 0%;
    transition: width 0.3s ease;
}
</style>
<script type="text/javascript">
$(function()
{
	var _continue = $('#continue');
	var fileInput = $('#fileInput');
	var fileQueue = $('#fileQueue');
	var filesList = $('.files_list');
	var uploadUrl = '<?php echo $R->getCtrlUrl(); ?>?mode=fc.file.upload';
	var uploadData = {'type':'<?php echo $fileType; ?>', 'dir':'<?php echo $fileDir; ?>'};
	var maxFileSize = <?php echo $A->system['uploadSize'] * 1024 * 1024; ?>;
	
	//HTML/属性转义（原始文件名等用户可控内容）
	function escHtml(s)
	{
		return String(s).replace(/[&<>"']/g, function(c) {
			return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
		});
	}
	
	//拼接回调参数（JS字面量经JSON.stringify+HTML转义 防注入）
	function fileArgs(obj, image)
	{
		return '$$.filesUploadLayer.opener.<?php echo $callback; ?>({id:'+obj.fileID+',image:'+image+',http:false'
			+',name:'+escHtml(JSON.stringify(obj.fileNewName))
			+',title:'+escHtml(JSON.stringify(obj.fileName))
			+',dir:'+escHtml(JSON.stringify(obj.fileDir))
			+',url:'+escHtml(JSON.stringify(obj.fileUrl))
			+',pipe:'+escHtml(JSON.stringify(obj.filePipe))
			+',size:'+obj.fileSize+'});return false;';
	}
	
	// 创建上传按钮
	var uploadButton = $('<div class="files_upload_button"></div>');
	fileInput.after(uploadButton);
	
	// 点击按钮触发文件选择
	uploadButton.click(function() {
		fileInput.click();
	});
	
	// 监听文件选择
	fileInput.on('change', function(e) {
		var files = e.target.files;
		if (files.length === 0) return;
		
		_continue.html('文件上传中...');
		$('.files_upload_box').animate({height:'30px'}, 300);
		
		var uploadedCount = 0;
		var errorCount = 0;
		
		// 处理每个文件
		for (var i = 0; i < files.length; i++) {
			uploadFile(files[i], function(success) {
				if (success) {
					uploadedCount++;
				} else {
					errorCount++;
				}
				
				// 所有文件处理完成
				if (uploadedCount + errorCount === files.length) {
					_continue.html('上传完成：成功'+uploadedCount+'个，失败'+errorCount+'个。&nbsp;&nbsp;点击继续上传');
				}
			});
		}
	});
	
	// 上传单个文件
	function uploadFile(file, callback) {
		// 检查文件大小
		if (file.size > maxFileSize) {
			var errorMsg = '文件大小超过限制：' + file.name;
			addFileItem(errorMsg, false);
			callback(false);
			return;
		}
		
		// 创建 FormData
		var formData = new FormData();
		formData.append('fileInput', file);
		
		// 添加其他参数
		for (var key in uploadData) {
			formData.append(key, uploadData[key]);
		}
		
		// 创建 XMLHttpRequest
		var xhr = new XMLHttpRequest();
		
		// 显示上传进度
		var progressBar = $('<div class="progress-bar"><div class="progress"></div></div>');
		var progress = progressBar.find('.progress');
		fileQueue.append(progressBar);
		
		xhr.upload.addEventListener('progress', function(e) {
			if (e.lengthComputable) {
				var percent = Math.round((e.loaded / e.total) * 100);
				progress.css('width', percent + '%');
			}
		});
		
		// 处理上传完成
		xhr.addEventListener('load', function() {
			progressBar.remove();
			
			if (xhr.status === 200) {
				var response = xhr.responseText;
				
				if (response[0] != '{') {
					$$.alert({text:response.replace('ERR|', '')});
					addFileItem('上传失败：' + file.name, false);
					callback(false);
					return;
				}
				
				try {
					var obj = JSON.parse(response);
					if (obj.error == '') {
						var str = '';
						if (obj.isImage) {
							str += '<li><span><a href="#" onclick="'+fileArgs(obj, true)+'"><?php echo $label; ?></a></span><img src="'+escHtml(obj.fileUrl)+'" />'+escHtml(obj.fileName)+'&nbsp; |&nbsp; '+(obj.fileSize/1024).toFixed(2)+'KB</li>';
						}
						else {
							str += '<li><span><a href="#" onclick="'+fileArgs(obj, false)+'"><?php echo $label; ?></a></span><img src="'+escHtml(obj.fileIcon)+'" />'+escHtml(obj.fileName)+'</li>';
						}
						filesList.append(str);
						callback(true);
					}
					else {
						addFileItem(obj.error + ' - ' + file.name, false);
						callback(false);
					}
				} catch (e) {
					addFileItem('上传失败：' + file.name, false);
					callback(false);
				}
			} else {
				addFileItem('上传失败：' + file.name, false);
				callback(false);
			}
		});
		
		// 处理上传错误
		xhr.addEventListener('error', function() {
			progressBar.remove();
			addFileItem('上传失败：' + file.name, false);
			callback(false);
		});
		
		// 发送请求
		xhr.open('POST', uploadUrl);
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		xhr.setRequestHeader('X-CSRF-Token', $$.csrfToken());
		xhr.send(formData);
	}
	
	// 添加文件项目到列表
	function addFileItem(text, success) {
		var icon = success ? '' : '<img class="files_stop" src="<?php echo URL_SKIN; ?>icon_stop.png" />';
		filesList.append('<li>' + icon + escHtml(text) + '</li>');
	}
	
	// 继续上传按钮
	_continue.click(function()
	{
		$(this).html('');
		$('.files_upload_box').animate({height:'200px'}, 300);
		// 重置文件输入
		fileInput.val('');
		$(this).blur();
		return false;
	});
});
</script>  
</head>
<body style="padding:0;">
<div class="tabs">
	<div></div>
	<a href="<?php echo $R->getPageUrl('system/fc.files.upload'); ?>&callback=<?php echo $callback; ?>&type=<?php echo $fileType; ?>&dir=<?php echo $fileDir; ?>&label=<?php echo $label; ?>" class="C">本地</a>
	<a href="<?php echo $R->getPageUrl('system/fc.files.server'); ?>&callback=<?php echo $callback; ?>&type=<?php echo $fileType; ?>&dir=<?php echo $fileDir; ?>&label=<?php echo $label; ?>">已上传</a>
	<!--<a href="<?php echo $R->getPageUrl('system/fc.files.url'); ?>&callback=<?php echo $callback; ?>&type=<?php echo $fileType; ?>&dir=<?php echo $fileDir; ?>&label=<?php echo $label; ?>">网络</a>-->
</div>
<div class="files_upload_main">
    <h1>本地计算机上的文件</h1>
    <div class="files_upload_box">
    	<div class="files_upload_zone">
        	<p>
            	最多可选择20个文件同时上传<br />
            	单个文件大小限制：<?php echo $A->system['uploadSize']; ?>MB。<br />
				允许文件类型：<?php echo str_replace(',', ', ', $suffix); ?>
            </p>
    		<input type="file" name="fileInput" id="fileInput" multiple="multiple" />
        </div>
        <a href="#" id="continue"></a>
    </div>
    <div id="fileQueue"></div>
    <ol class="files_list"></ol>
</div>
</body>
</html>
