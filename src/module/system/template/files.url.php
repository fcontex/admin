<?php
//JS脚本回调函数
$callback = $A->strGet('callback');
//上传目录
$fileDir = $A->strGet('dir');
//标签文本
$label = $A->strGet('label');
//文件类型
$fileType = $A->strGet('type');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>网络远程文件 - <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
<meta name="robots" content="nofollow">
<?php if (0) { ?><link rel="stylesheet" type="text/css" href="../skins/default/style.css" /><?php }else{ ?>
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN; ?>style.css?v=<?php echo ASSET_VERSION; ?>" /><?php } ?> 
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>"></script>

</head>
<body style="padding:0;">
<div class="tabs">
	<div></div>
	<a href="<?php echo $R->getPageUrl('system/fc.files.upload'); ?>&callback=<?php echo $callback; ?>&type=<?php echo $fileType; ?>&dir=<?php echo $fileDir; ?>&label=<?php echo $label; ?>">本地</a>
	<a href="<?php echo $R->getPageUrl('system/fc.files.server'); ?>&callback=<?php echo $callback; ?>&type=<?php echo $fileType; ?>&dir=<?php echo $fileDir; ?>&label=<?php echo $label; ?>">已上传</a>
	<a href="<?php echo $R->getPageUrl('system/fc.files.url'); ?>&callback=<?php echo $callback; ?>&type=<?php echo $fileType; ?>&dir=<?php echo $fileDir; ?>&label=<?php echo $label; ?>" class="C">网络</a>
</div>
<div class="files_upload_main">
    <h1>网络远程文件</h1>
    <div class="files_url_main">
    	<h2 class="files_url_title" id="files_url_title" style="display:none;">
        	<input type="radio" name="file_type" id="file_1" value="image" checked="checked" class="checkbox" /> <label for="file_1">图像</label>
            <input type="radio" name="file_type" id="file_2" value="file" class="checkbox" /> <label for="file_2">文件</label>
            <input type="hidden" id="fileType" />
            <div style="clear:both;"></div>
        </h2>
        <form id="form_detail_1" onsubmit="formSubmit(this); return false;">
        <input type="hidden" name="image" value="1" />
       	  <table class="table_form" id="table_form" width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <th>URL</th>
                    <td><input class="text" type="text" name="url" id="url_1" pattern="^http:\/\/.+\..+$" /> <span><cite>*</cite></span></td>
                </tr>
                <tr style="display:none;">
                    <th>标题</th>
                    <td><input class="text" type="text" name="title" id="title_1" /></td>
                </tr>
                <tr style="display:none;">
                    <th>对齐</th>
                    <td>
                    	<input type="radio" class="checkbox" name="align" id="align_1_0" value="" checked="checked" /> <label for="align_1_0">无</label>&nbsp;&nbsp;&nbsp;
                    	<input type="radio" class="checkbox" name="align" id="align_1_1" value="left" /> <label for="align_1_1">左</label>&nbsp;&nbsp;&nbsp;
                        <input type="radio" class="checkbox" name="align" id="align_1_2" value="center" /> <label for="align_1_2">中</label>&nbsp;&nbsp;&nbsp;
                        <input type="radio" class="checkbox" name="align" id="align_1_3" value="right" /> <label for="align_1_3">右</label>&nbsp;&nbsp;&nbsp;
                    </td>
                </tr>
                <tr class="action"><th>&nbsp;</th><td><input type="submit" class="button" value="确定" /></td></tr>
            </table>
        </form>
        <form id="form_detail_2" style="display:none;" onsubmit="formSubmit(this); return false;">
        <input type="hidden" name="image" value="0" />
       	  <table class="table_form" width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <th>URL</th>
                    <td><input class="text" type="text" name="url" id="url_2" pattern="^http:\/\/.+\..+$" /> <span><cite>*</cite></span></td>
                </tr>
                <tr>
                    <th>名称</th>
                    <td><input class="text" type="text" name="title" id="title_2" pattern="^.+$" /> <span><cite>*</cite></span></td>
                </tr>
                <tr class="action"><th>&nbsp;</th><td><input type="submit" class="button" value="确定" /></td></tr>
            </table>
        </form>
  </div>
</div>
<script type="text/javascript">
if ($$.filesUploadLayer.editor)
{
	$('#table_form tr:hidden,#files_url_title').show();
}
$('#files_url_title input').click(function()
{
	var index = $('#files_url_title input').index(this);
	$('#files_url_title input').removeAttr('checked');
	$(this).attr('checked', 'checked');
	$('form').hide();
	$('form').eq(index).show(300);
});
var checkpass = true;
$('input[pattern]').blur(function()
{
	var tipY  = '<img src="<?php echo URL_SKIN; ?>icon_yes.gif" />';
	var tipN = '<img src="<?php echo URL_SKIN; ?>icon_no.gif" />';
	var _this = $(this);
	var span = $(this).parent().find('span');
	if ($$.test($(this).val(), $(this).attr('pattern')))
	{
		span.html(tipY);
	}
	else
	{
		span.html(tipN);
		checkpass = false;
	}
});
if ('<?php echo $fileType; ?>' == 'file')
{
	$('#file_2').click();
}

function formSubmit(form)
{
	checkpass = true;
	$(form).find('input[pattern]').blur();
	if (!checkpass) return;
	
	$$.post('<?php echo $R->getCtrlUrl(); ?>?mode=file.remote', $(form).serialize(), function(text)
	{
		var obj = JSON.parse(text);
		
		var url =$(form).find('input[name=url]').val();
		$$.filesUploadLayer.opener.<?php echo $callback; ?>
		({
			id: obj.fileID,																//文件ID
			image: parseInt($(form).find('input[name=image]').val()) ? true : false,	//是否为图片
			http: true,																	//是否为远程文件
			name: url.substring(url.lastIndexOf('/')+1),								//真实文件名
			title: $(form).find('input[name=title]').val(),								//显示名称
			dir: '',																	//所在目录
			url: url,																	//真实URL
			pipe: obj.filePipe,															//下载通道
			size: 0,																	//文件大小
			align: $(form).find('input[name=align]:checked').val()						//对齐方式 插入远程图片专用
		});
	});
}
</script>
</body>
</html>
