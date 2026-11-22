<?php
include '../module/startup.php';

$mode = $A->strGet('m');
if (!empty($mode))
{
	//准备数据
	$info = array
	(
		'db_base' => $A->strPost('db_base'),
		'db_host' => $A->strPost('db_host'),
		'db_user' => $A->strPost('db_user'),
		'db_pass' => $A->strPost('db_pass'),
		'db_pfix' => $A->strPost('db_pfix'),
		'us_name' => $A->strPost('us_name'),
		'us_pass' => $A->strPost('us_pass'),
	);
	if (!preg_match('/^[A-Za-z0-9_]+$/', $info['db_pfix']))
	{
		exit('ERR|安装失败：数据表前缀只能包含字母、数字和下划线。');
	}
	$sql = str_replace('fc_', $info['db_pfix'], file_get_contents('mysql.sql'));
	preg_match_all('/CREATE TABLE[^;]+?;/', $sql, $creates);
	$creates = $creates[0];
	preg_match_all('/INSERT INTO[\s\S]+?\);/', $sql, $inserts);
	$inserts = $inserts[0];
	
	//连接数据库
	$db = new DBDriverPdo();
	$db->charset = 'utf8mb4';
	$db->connect($info['db_base'], $info['db_user'], $info['db_pass'], $info['db_host']);
	if (!$db->connected())
	{
		exit('ERR|安装失败：无法完成数据库初始化，请确认连接信息是否填写正确。');
	}
	else
	{
		//执行SQL语句
		$sqls = array_merge($creates, $inserts);
		try
		{
			foreach ($sqls as $sql)
			{
				$db->exec($sql);
			}
		}
		catch (PDOException $e)
		{
			exit('ERR|安装失败：执行语句出错：<br />'.htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8').'<br /><br />出错语句：<br />'.$sql.'<br /><br />请确认：<br />1、数据库连接信息是否正确；<br />2、数据库['.$info['db_base'].']是否已创建，字符集是否为 utf8mb4；<br />3、该库中是否已存在同名数据表（可更换表前缀重试）。');
		}
		
		//更新配置
		$config = $A->loadConfig('system.database');
		$config['DB_BASE'] = $info['db_base'];
		$config['DB_HOST'] = $info['db_host'];
		$config['DB_USER'] = $info['db_user'];
		$config['DB_PASS'] = $info['db_pass'];
		$config['DB_CHAR'] = 'utf8mb4';
		$config['DB_HOLD'] = TRUE;
		$config['DB_PFIX'] = $info['db_pfix'];
		$A->saveConfig('system.database', $config);
		
		//更新管理员（前端提交的是 md5 后的口令串 服务端再做散列存储）
		$db->update($info['db_pfix'].'user', array
		(
			'us_username' => $info['us_name'],
			'us_password' => password::hash(md5($info['us_pass']))
		), array('us_id' => 1));

		//重置共享数据对象以使用新的数据库配置
		FCApplication::resetDataBase();

		//执行模块安装回调（创建各模块数据表）
		$handle = opendir(PATH_MODULES);
		while (($name = readdir($handle)) !== false)
		{
			$file = PATH_MODULES.$name.'/module.php';
			if (!is_file($file)) continue;
			include $file;
			$class = 'FCModule'.ucfirst($name);
			if (class_exists($class) && method_exists($class, 'install'))
			{
				$module = new $class();
				$module->install();
			}
		}
		closedir($handle);
		
		//清理文件
		@$A->cleanDirectory('../install', TRUE);
		echo('YES|安装成功，<span style="color:#f00;">请检查并确认[install]目录已被删除。</span>。');
	}
}
else
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?> 安装程序</title>
<link rel="stylesheet" type="text/css" href="images/style.css" />
<script type="text/javascript" src="images/jquery.js"></script>
<script type="text/javascript" src="images/jquery.fullscreenr.js"></script>
<script type="text/javascript">
function backResize()
{
	var backIMG = $('#backimg');
	$.fn.fullscreenr({width:backIMG.width(), height:backIMG.height(), bgID:'#backimg'});
	backIMG.fadeIn(1000);
}
function install()
{
	$('#tips_2').html('');
		
	if ($.trim($('#db_base').val()) == '')
	{
		$('#tips_2').html('错误：请输入数据库名称。');
		$('#db_base').focus();
		return;
	}
	else if ($.trim($('#db_host').val()) == '')
	{
		$('#tips_2').html('错误：请输入数据库地址。');
		$('#db_host').focus();
		return;
	}
	else if ($.trim($('#db_user').val()) == '')
	{
		$('#tips_2').html('错误：请输入数据库用户名。');
		$('#db_user').focus();
		return;
	}
	else if ($.trim($('#db_pass').val()) == '')
	{
		$('#tips_2').html('错误：请输入数据库密码。');
		$('#db_pass').focus();
		return;
	}
	else if ($.trim($('#db_pfix').val()) == '')
	{
		$('#tips_2').html('错误：请输入数据表前缀。');
		$('#db_pfix').focus();
		return;
	}
	else if ($.trim($('#us_name').val()) == '')
	{
		$('#tips_2').html('错误：请输入管理员帐户。');
		$('#us_name').focus();
		return;
	}
	else if ($.trim($('#us_pass').val()) == '')
	{
		$('#tips_2').html('错误：请输入管理员密码。');
		$('#us_pass').focus();
		return;
	}
	else if ($.trim($('#us_pass_2').val()) == '')
	{
		$('#tips_2').html('错误：请确认管理员密码。');
		$('#us_pass_2').focus();
		return;
	}
	else if ($('#us_pass_2').val() != $('#us_pass').val())
	{
		$('#tips_2').html('错误：密码输入不一致。');
		$('#us_pass_2').select();
		return;
	}
		
	$.ajax
	({
		type       : 'post',
		url        : '?m=GO',
		cache      : false,
		data       : $('#form_install').serialize(),
		beforeSend : function(XMLHttpRequest)
		{
			$('#tips_3').html('安装中请稍候...');
			$('#step_2').slideUp(300);
			$('#step_3').slideDown(300);
		},
		success    : function(data, textStatus)
		{
			var a = data ? data.split('|') : ['无效的服务器响应。'];
			if (a[0] == 'YES')
			{
				$('#tips_3').html(a[1]);
				$('#button_3_1').show(100);
			}
			else if (a[0] == 'ERR')
			{
				$('#tips_3').html(a[1]);
				$('#button_3_0').show(100);
			}
			else
			{
				$('#tips_3').html(data);
				$('#button_3_0').show(100);
			}
		},
		error     : function(XMLHttpRequest, textStatus, errorThrown)
		{
			$('#tips_3').html('请求超时。');
			$('#button_3_0').show(100);
		}
	});
}
$(function()
{
	window.setTimeout(backResize, 500);
	
	$('#button_1').click(function()
	{
		$('#step_1').slideUp(300);
		$('#step_2').slideDown(300);
	});
	
	$('#button_3_0').click(function()
	{
		$('#tips_2').html('');
		$('#button_3_0').hide();
		$('#step_3').slideUp(300);
		$('#step_2').slideDown(300);
	});
});
</script>
</head>

<body>
	<div class="backimgMask"></div>
	<img id="backimg" src="images/back.jpg" />
	<div class="toper w_700 filter_8">
		<h1>欢迎进入 <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?> 安装程序</h1>
		<p>- We create awesome stuff -</p> 
	</div>
	<div class="content w_700 filter_8">
	
		<div id="step_1" class="steps" style="display:block;">
			<h2>阅读发行协议</h2>
			FContex Admin 采用 MIT 许可协议（MIT License）发布，版权归 Alan 所有。<br />
			协议全文请见：<br />
			<a href="https://github.com/fcontex/admin/blob/master/LICENSE" target="_blank">https://github.com/fcontex/admin/blob/master/LICENSE</a><br /><br />
			<strong>MIT License</strong><br />
			您可以自由：<br />
				使用、复制、修改、合并、发布、分发、再许可和/或销售本软件的副本，包括商业用途；<br />
			惟须遵守下列条件：<br />
				保留版权声明 — 在所有副本或软件的实质性部分中，须保留上述版权声明与本许可声明。<br />
			免责声明 — 本软件按“现状”提供，不附带任何明示或暗示的担保，包括但不限于适销性、特定用途适用性及非侵权性的担保；作者或版权持有人不对任何索赔、损害或其他责任负责。<br /><br />
			Copyright (c) 2012-2026 Alan
			<div class="button"><a class="button" id="button_1">我已阅读并同意上述许可条款</a></div>
			<div class="clear"></div>
		</div>
		
		<div id="step_2" class="steps">
			<form id="form_install" method="post" action="?" onsubmit="install('s');return false;">
				<h2>MySQL数据库信息</h2>
				<dl>
					<dt>数据库名称：</dt>
					<dd style="height:80px;"><input type="text" class="text" name="db_base" id="db_base" /><br /><span id="db_base_tips">请先手动创建该名称的MySQL数据库，创建时：<br />[字 符 集] 选择“utf8mb4 -- UTF-8 Unicode”，<br />[排序规格] 选择“utf8mb4_general_ci”。</span></dd>
					<dt>数据库地址：</dt>
					<dd><input type="text" class="text" name="db_host" id="db_host" value="127.0.0.1" /></dd>
					<dt>数据库用户名：</dt>
					<dd><input type="text" class="text" name="db_user" id="db_user" value="root" /></dd>
					<dt>数据库密码：</dt>
					<dd><input type="text" class="text" name="db_pass" id="db_pass" /></dd>
					<dt>数据库表前辍：</dt>
					<dd><input type="text" class="text" name="db_pfix" id="db_pfix" value="fc_" /></dd>
				</dl>
				<div class="clear"></div>
				<h2>网站管理员信息</h2>
				<dl>
					<dt>管理员账号：</dt>
					<dd><input type="text" class="text" name="us_name" id="us_name" value="admin" /></dd>
					<dt>管理员密码：</dt>
					<dd><input type="password" class="text" name="us_pass" id="us_pass" /></dd>
					<dt>确认密码：</dt>
					<dd><input type="password" class="text" id="us_pass_2" /></dd>
				</dl>
				<div class="clear"></div>
				<div class="button"><a class="button" id="button_2" onclick="install('s');">确认安装</a></div>
				<div class="clear"></div>
				<div style="display:none;"><input type="submit" /></div>
				<div id="tips_2"></div>
			</form>
		</div>
		
		<div id="step_3" class="steps">
			<div id="tips_3"></div>
			<div class="button">
				<a class="button" id="button_3_0" style="display:none;">返回</a>
				<a class="button" id="button_3_1" onclick="location.href='../?system.html';" style="display:none;">完成</a>
			</div>
			<div class="clear"></div>
		</div>
		
	</div>

</body>
</html>
<?php
}
?>