<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>找回密码 - <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN; ?>style.css?v=<?php echo ASSET_VERSION; ?>" />
<script src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js" type="text/javascript"></script>
<script src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>" type="text/javascript"></script>
<script type="text/javascript">
function fetchCode()
{
	$$.post('<?php echo $R->getCtrlUrl(); ?>?mode=fc.user.fetchcode', $('#form_fetchCode').serialize(), function()
	{
		window.top.$$.dialogs.close(window.top.fetchCodeLayer);
	});
}
</script>
</style>
</head>
<body>
<div class="login_box">
	<h1>找回密码</h1>
    <form onsubmit="fetchCode(); return false;" id="form_fetchCode">
    <table width="100%" border="0" class="table_form">
        <tr>
          <td class="login_user"><span>登录账号</span><input type="text" class="text" name="username" id="username" autocomplete="off" onkeydown="if (this.value == '') $('.login_user span').show(); else $('.login_user span').hide();" onkeyup="if (this.value == '') $('.login_user span').show(); else $('.login_user span').hide();" /></td>
        </tr>
        <tr>
          <td class="login_pass"><span>邮箱地址</span><input type="text" class="text" name="email" id="email" autocomplete="off" onkeydown="if (this.value == '') $('.login_pass span').show(); else $('.login_pass span').hide();" onkeyup="if (this.value == '') $('.login_pass span').show(); else $('.login_pass span').hide();" /></td>
        </tr>
        <tr>
          <td><div id="loginMessage"></div><input type="submit" class="button" value="确定" /><input type="button" class="button cancle" value="取消" onclick="window.top.$$.dialogs.close(window.top.fetchCodeLayer);" /></td>
        </tr>
    </table>
    </form>
    <div class="login_right">
    	<span>忘记邮箱账号？联系管理员吧！</span>
        <a class="button yellow" href="mailto:<?php echo $A->site['site_email']; ?>" onfocus="this.blur();"><?php echo $A->site['site_email']; ?></a>
    </div>
    <div class="clear"></div>
</div>
<script type="text/javascript">$('.table_form span').click(function(){$(this).parent().find('input').focus();});</script>
</body>
</html>
