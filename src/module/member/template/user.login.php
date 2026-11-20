<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>系统登录</title>
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN; ?>style.css?v=<?php echo ASSET_VERSION; ?>" />
<script src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js" type="text/javascript"></script>
<script src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>" type="text/javascript"></script>
<script src="<?php echo URL_SCRIPTS; ?>jquery.md5.js" type="text/javascript"></script>
<script type="text/javascript">
function login()
{
	var message   = $('#loginMessage');
	var username  = $('#username');
	var password_ = $('#password_');
	var subbutton = $('.table_form .button');
	if (username.val() == '')
	{
		username.focus();
		message.html('请输入登录帐户。');
		message.fadeIn(100);
	}
	else if (password_.val() == '')
	{
		password_.focus();
		message.html('请输入登录密码。');
		message.fadeIn(100);
	}
	else 
	{
		if (password_.val() != '')
		{
			$('#password').val($.md5(password_.val()));
		}
		$.ajax
		({
			type    : 'post',
			url     : '<?php echo $R->getCtrlUrl(); ?>?mode=fc.user.login',
			cache   : false,
			data    : $('#form_login').serialize(),
			beforeSend : function(XMLHttpRequest)
			{
				subbutton.addClass('loginLoad');
				subbutton.attr({'value': '', 'disabled' : 'disabled'});
			},
			success : function(data, textStatus)
			{
				var arr = data.split('|');
				if (arr[0] == 'YES')
				{
					message.html('验证通过，加载控制台...');
					message.fadeIn(100);
					window.setTimeout(function()
					{
						var layerWidth = window.top.loginLayerObj.content.width();
						window.top.loginLayerObj.content.animate({left: -(layerWidth+100)+'px'}, 500, '', function()
						{
							// 更新为用户皮肤 
							if (arr[1] != '<?php echo URL_SKIN; ?>' && arr[1])
							{
								window.top.skinSwitch(arr[1]);
							}
							// 更新用户状态信息
							window.top.loginStatus(arr[2]);
							//更新全部菜单项
							window.top.naviUpdate();
							// 关闭遮罩， 移除登录层
							window.top.$('#dialog_mask').remove();
							$$.dialogs.close(window.top.loginLayerObj);
					});}, 1000);
				}
				else
				{
					message.html(arr[1]);
					message.fadeIn(100);
					ready();
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown)
			{
				message.html('未知错误，请重试。');
				ready();
			}
			
		});
	}
	
	function ready()
	{
		subbutton.removeClass('loginLoad');
		subbutton.attr({'value' : '登录'});
		subbutton.removeAttr('disabled');
	}
}

function tipTool(selector)
{
	var td = $(selector);
	if (td.find('input').val() == '')
	{
		td.find('span').show();
	}
	else
	{
		td.find('span').hide();;
	}
}

$(function()
{
	setTimeout(function()
	{
		tipTool('#form_login .login_user');
		tipTool('#form_login .login_pass');
	}, 200);
	
	$('#username').focus();
});
</script>
</style>
</head>
<body>
<div class="login_box">
	<h1><?php echo SYSTEM_NAME; ?> 登录</h1>
	<form onsubmit="login(); return false;" id="form_login">
	<table width="100%" border="0" class="table_form">
		<tr>
			<td class="login_user"><span>账号</span><input type="text" class="text" name="username" id="username" autocomplete="off" onkeyup="tipTool('#form_login .login_user');" /></td>
		</tr>
		<tr>
			<td class="login_pass">
				<span>密码</span><input type="password" class="text" id="password_" onkeyup="tipTool('#form_login .login_pass');" />
				<input type="hidden" name="password" id="password" />
			</td>
		</tr>
		<tr>
		  <td><div id="loginMessage"></div><input type="submit" class="button" value="登录" /><a href="#" onclick="window.top.fetchCode(); return false;">忘记密码？</a>&nbsp;&nbsp;<a href="../../../" target="_blank">返回首页</a></td>
		</tr>
	</table>
	</form>
    <div class="login_right">
    	<span>获取 <?php echo SYSTEM_NAME; ?> 使用帮助以及二次开发资源：</span>
        <a class="login_link" href="http://www.fcontex.com/" target="_blank" id="go">查找在线资源</a>
    </div>
    <div class="clear"></div>
</div>
<script type="text/javascript">$('.table_form span').click(function(){$(this).parent().find('input').focus();});</script>
</body>
</html>
