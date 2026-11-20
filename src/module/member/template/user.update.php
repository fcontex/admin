<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>用户编辑 - <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
<meta name="robots" content="nofollow">
<?php if (0) { ?><link rel="stylesheet" type="text/css" href="../../system/skins/default/style.css" /><?php }else{ ?>
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN; ?>style.css?v=<?php echo ASSET_VERSION; ?>" /><?php } ?>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>"></script>
<script src="<?php echo URL_SCRIPTS; ?>jquery.md5.js" type="text/javascript"></script>
<script type="text/javascript">
function user(mode, args)
{
	switch (mode)
	{
		case 'fc.user.update':
			var password_ = $('#us_password_');
			if (password_.val() != '')
			{
				$('#us_password').val($.md5(password_.val()));
			}
			var password2_ = $('#us_password2_');
			if (password2_.val() != '')
			{
				$('#us_password2').val($.md5(password2_.val()));
			}
			$$.post('<?php echo $R->getCtrlUrl(); ?>?mode=' + mode, $('#form_detail').serialize() + '&args=' + args, function()
			{
				if (args == '<?php echo $_SESSION['userInfo']['us_id']; ?>')
				{
					window.top.loginStatus(args);
				}
				<?php if ($U->hasRights('user.update')) { ?>$$.redirect('<?php echo $R->getPageUrl('member/fc.user.select'); ?>');<?php } ?>
			});
			break;
		default:
			$$.alert({text:'无效参数 [ '+mode+' ]。'});
			break;
	}
	
	return false;
}

function headerUploadCallback(param)
{
	$('#thumb_img').html('<a href="'+param.url+'" target="_blank"><img src="'+param.url+'" /></a>');
	$('#us_face').val(param.dir+param.name);
	$$.filesUploadLayer.close();
}

function headerClear()
{
	$('#thumb_img').html('');
	$('#us_face').val('');
}
</script>
</head>
<body>

<form id="form_detail" name="form_detail" method="post" action="###" onsubmit="return user('fc.user.update', <?php echo $args; ?>);">
<table class="table_form" width="100%" border="0" cellspacing="0" cellpadding="0">
	<?php
	if (!is_numeric($args))
	{
	?>
	<tr>
		<th>&nbsp;</th>
		<td>无效参数[ <?php echo $args; ?> ]。</td>
	</tr>
	<?php
	}
	else
	{
		if ($rst = DB::table('user')->field('*')->where('us_id = ?', array($args))->row())
		{
	?>
    <tr>
		<th>所属分组</th>
		<td>
        <select name="us_group"<?php if (!$U->hasRights('user.update')) echo ' disabled="disabled"'; ?>>
        	<option>=选择用户组=</option>
        	<?php
			$items = DB::table('group')->field('gr_id, gr_name')->order('gr_id', 'asc')->rows();
			foreach ($items as $rstG)
			{
			?>
            <option value="<?php echo $rstG['gr_id']; ?>"<?php if ($rst['us_group'] == $rstG['gr_id'])echo ' selected="selected"'; ?>><?php echo $rstG['gr_name']; ?></option>
			<?php
			}
			?>
        </select>
		<span><cite>*</cite> 必填</span>
		</td>
	</tr>
    <tr>
		<th>头像</th>
		<td>
			<div class="thumb">
            	<div class="thumb_img" id="thumb_img"><a target="_blank" href="<?php echo $A->getThumb($rst['us_face']); ?>"><img src="<?php echo $A->getThumb($rst['us_face'], 200, 200); ?>" /></a></div>
                <h1><a href="#" onclick="$$.filesUploadLayer.open({dir:'member',url:'<?php echo $R->getPageUrl('system/fc.files.upload'); ?>',callback:'headerUploadCallback',opener:window});return false;">设置头像</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="headerClear();return false;">清除</a></h1>
            </div>
            <input type="hidden" name="us_face" id="us_face" value="<?php echo $rst['us_face']; ?>">
		</td>
	</tr>
	<tr>
		<th>登录帐户</th>
		<td>
			<input class="text" type="text" name="us_username" id="us_username" autocomplete="off" value="<?php echo $rst['us_username']; ?>"<?php if (!$U->hasRights('user.update')) echo ' disabled="disabled"'; ?> /> <span><cite>*</cite> 必填</span>
			<script type="text/javascript">$(function(){setTimeout(function(){$('#us_username').val('<?php echo $rst['us_username']; ?>');},200)});</script>
		</td>
	</tr>
	<tr>
		<th>登录密码</th>
		<td>
			<input class="text" type="password" id="us_password_" autocomplete="off" />
			<input type="hidden" name="us_password" id="us_password" /> 
			<span>不修改请留空</span>
			<script type="text/javascript">$(function(){setTimeout(function(){$('#us_password').val('');},200)});</script>
		</td>
	</tr>
	<tr>
		<th>确认密码</th>
		<td>
			<input class="text" type="password" id="us_password2_" autocomplete="off" />
			<input type="hidden" name="us_password2" id="us_password2" />
		</td>
	</tr>
	<tr>
		<th>用户姓名</th>
		<td><input class="text" type="text" name="us_name" id="us_name" value="<?php echo $rst['us_name']; ?>" /> <span><cite>*</cite> 必填</span></td>
	</tr>
	<tr>
		<th>邮件地址</th>
		<td><input class="text" type="text" name="us_email" id="us_email" value="<?php echo $rst['us_email']; ?>" /></td>
	</tr>
	<tr>
		<th>电话号码</th>
		<td><input class="text" type="text" name="us_phone" id="us_phone" value="<?php echo $rst['us_phone']; ?>" /></td>
	</tr>
	<tr>
		<th>联系地址</th>
		<td><input class="text" type="text" name="us_address" id="us_address" value="<?php echo $rst['us_address']; ?>" /></td>
	</tr>
	<tr>
		<th>用户描述</th>
		<td><textarea name="gr_desc" id="gr_desc" cols="45" rows="5"><?php echo $rst['us_desc']; ?></textarea></td>
	</tr>
	<tr class="action"><th>&nbsp;</th><td><input type="submit" class="button" value="确认修改"<?php if (!$U->hasRights('user.update')) echo ' disabled="disabled"'; ?> /><input type="button" class="button cancle" value="放弃修改" onclick="<?php if ($U->hasRights('user.select')) { ?>$$.redirect('<?php echo $R->getPageUrl('member/fc.user.select'); ?>');<?php }else{ ?>top.naviShut('fc.user.update');<?php } ?>" /></td></tr>
	<?php
		}
		else
		{
	?>
	<tr>
		<th>&nbsp;</th>
		<td>无效参数[ <?php echo $args; ?> ]。</td>
	</tr>
	<?php
		}
	}
	?>
</table>
</form>

</body>
</html>
