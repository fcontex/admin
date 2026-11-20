<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>系统控制台 - <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
<meta name="robots" content="nofollow">
<?php if (0) { ?><link rel="stylesheet" type="text/css" href="../skins/default/style.css" /><?php }else{ ?>
<link id="style0" rel="stylesheet" type="text/css" href="<?php echo URL_SKIN; ?>style.css?v=<?php echo ASSET_VERSION; ?>" /><?php } ?>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>jquery.ui.min.js"></script>
<script type="text/javascript">
function naviOpen(target)
{
	$$.target(target);
	
	if (location.href.indexOf('#') == -1) return;
	
	targets = location.href.replace(/^[^#]+#(.+?)$/, '$1');
	target  = targets.split('/');
	
	if (target.length >= 2)
	{
		//一个模块可能对应多个一级图标（分组）：按叶子反查其所属分组再展开面板
		$('#navs dd[navigate="'+targets+'"]').closest('li').removeClass('C').click();
		$('#menus dd[navigate="'+targets+'"]').click();
	}
}

function naviShut(target)
{
	if (target = $$.dialogs.find(target))
	{
		target.close();
	}
}

function naviSwitcher(targetA, targetB)
{
	naviShut(targetA);
	naviShut(targetB);
	naviOpen(targetB);
}

function naviUpdate()
{
	$$.get('<?php echo $R->getPageUrl('system/fc.menus'); ?>', function(text)
	{
		$('#menus').slideUp(100, function(){$('#navs ul').slideUp(100, function()
		{
			$(this).remove();
			$('#navs').append(text).slideDown(100);
			if (!$$.isTouch()) $('#navs li').easyTooltip();
			naviOpen();
			startBuild();
		});});
	}, false);
}

function naviClick(navi)
{
	var _this = $(navi);
		
	if (_this.hasClass('C'))
	{
		$('#menus').slideUp(100); _this.removeClass('C');
	}
	else
	{
		$('#navs li').removeClass(); _this.addClass('C');
		$('#menus .menus').empty().append(_this.find('dl').clone(true));
		//显示之后再定位：隐藏时 jQuery 1.7.2 取不到高度，会把图标排成横排
		$('#menus').slideDown(100, menuPosition);
	}
}

//二级菜单定位
function menuPosition()
{
	var menusHeight = $('#menus').height();
	var menus = $('#menus dd');
	var Top = 0, T = 0, L = 0, Left = 10;
	for (var i = 0; i < menus.length; i++)
	{
		Top = T * 102;
		if (menusHeight < (T+1)*102)
		{
			L++;
			T = 1,Top = 0; Left = L * 120;
		}
		else T++;
		menus.eq(i).css({'margin-top': Top+'px', 'margin-left': Left+'px'});
	}
}

//开始菜单：从 #navs 复用分组与菜单，避免服务端重复渲染
function startBuild()
{
	var rail = '', panes = '', first = '';
	$('#navs > ul > li').each(function()
	{
		var _this = $(this),
		name  = _this.attr('module'),
		group = _this.attr('group'),
		key   = name + '-' + group,
		title = _this.attr('title'),
		icon  = _this.find('> img').attr('src'),
		items = _this.find('dl').html();

		if (!items) return;
		if (first == '') first = key;

		rail  += '<li module="'+name+'" group="'+group+'" data-key="'+key+'"><img src="'+icon+'" /><span>'+title+'</span></li>';
		panes += '<div class="pane" module="'+name+'" group="'+group+'" data-key="'+key+'"><div class="pane-t">'+title+'</div><dl>'+items+'</dl></div>';
	});

	$('#startmenu .rail').html(rail);
	$('#startmenu .panes').html(panes);
	startSelect(first);
}

//切换开始菜单的一级分组（键 = 模块-组序号）
function startSelect(key)
{
	if (!key) return;
	$('#startmenu .rail li').removeClass('C');
	$('#startmenu .panes .pane').removeClass('C');
	$('#startmenu .rail li[data-key="'+key+'"]').addClass('C');
	$('#startmenu .panes .pane[data-key="'+key+'"]').addClass('C');
}

function startOpen()
{
	//模态层存在时不弹出，避免盖住登录等强制窗口
	if ($('#dialog_mask').length) return;
	$('#startmenu').fadeIn(100);
	$('#tasks .start').addClass('C');
}

function startClose()
{
	if ($('#startmenu').is(':hidden')) return;
	$('#startmenu').hide();
	$('#tasks .start').removeClass('C');
}

function startToggle()
{
	$('#startmenu').is(':hidden') ? startOpen() : startClose();
}

//打开菜单弹出层
function menuClick(menu)
{
	var _this = $(menu),
	title = _this.find('.L').text(),
	taskid = 'task_id_'+$$.dialogs.zmax,
	url = _this.find('.T').text(),
	icon = _this.find('.icon').html();
	$$.dialogs.open(
	{
		name   : _this.attr('navigate'),
		title  : title,
		ticon  : icon,
		url    : url,
		create : function(layer)
		{
			$('#tasks .tasks').append($('<li id='+taskid+'>'+title+'</li>').prepend(icon));
			$('#'+taskid).bind('click', function()
			{
				if (layer.content.is(':hidden'))
				{
					layer.show();
				}
				else
				{
					if (layer == $$.dialogs.toper) layer.min();
					else layer.focus();
				}
			});
		},
		focus  : function(layer)
		{
			$('#'+taskid).addClass('C');
			$$.target(_this.attr('navigate'));
		},
		blur   : function(layer)
		{
			$('#'+taskid).removeClass('C');
		},
		min    : function(layer)
		{
			$('#'+taskid).removeClass('C');
		},
		close  : function(layer)
		{
			$('#'+taskid).hide(500, function(){$(this).remove();});
			$$.target('###');
		}
	});
}

//登录层
function loginLayer()
{
	var mobile = $$.isMobile();
	
	$$.dialogs.open
	({
		name: 'login',
		url: '<?php echo $R->getPageUrl('member/fc.user.login'); ?>',
		width: mobile ? 340 : 550,
		height: mobile ? 380 : 240,
		model: true,
		ctrl : [0, 0, 0],
		create : function(layer){window.top.loginLayerObj = layer;}
	});
}

//退出登录
function loginOut()
{
	
	$$.confirm({text:'确定退出登录？', ok:function()
	{
		$$.post('<?php echo $R->getCtrlUrl('member'); ?>?mode=fc.user.loginout', {}, function()
		{
			loginLayer();
			loginStatus();
		});	
	}});
	return false;
}

//找回密码层
function fetchCode()
{
	var mobile = $$.isMobile();
	
	$$.dialogs.open
	({
		name: 'fetchCode',
		url: '<?php echo $R->getPageUrl('member/fc.user.fetchcode'); ?>',
		width: mobile ? 340 : 550,
		height: mobile ? 380 : 240,
		model: true,
		ctrl : [0, 0, 1],
		create : function(layer){window.top.fetchCodeLayer = layer;}
	});
}

//皮肤设置
function startLayer()
{
	$$.dialogs.open
	({
		name: 'start',
		title : '皮肤设置',
		ticon : '',
		url: '<?php echo $R->getPageUrl('system/fc.config.skins'); ?>',
		width: 520,
		height : 500,
		model: true,
		ctrl : [0, 0, 1]
	});
}

//壁纸加载完成后显示
function backShow()
{
	if ($('.backimage').length > 1)
	{
		$$.fullscreen($('.backimage').eq(1));
		$('.backimage').eq(0).remove();
		$('.backimage').eq(1).fadeIn(600);
	}
	else
	{
		$$.fullscreen('.backimage');
		$('.backimage').fadeIn(600);
	}
	
	$('#back_thumb').fadeOut(500);
}
//显示壁纸缩略图，并加载壁纸
function loadBack(skin)
{
	$('#back_thumb div').html('<img src="'+skin+'thumb.jpg" />');
	$('#back_thumb').show();
	$('#back_box').append('<img class="backimage" src="'+skin+'back.jpg" height="100%" style=" display:none;" onload="backShow();" />');
}

//切换皮肤{方案A：直接刷新页面，由服务端按新皮肤重新渲染}
//可顺带解决内联URL_SKIN资源（图标、散图）不跟随皮肤的问题
function skinSwitch(skin)
{
	location.reload();
}

//打开用户编辑窗口
function editUser(uid)
{
	$$.dialogs.open
	(
		{
			name: 'fc.user.update',
			title : '编辑用户',
			ticon : '',
			url: '<?php echo $R->getPageUrl('member/fc.user.update/'); ?>'+uid,
			model: true,
			ctrl : [0,0,1]
		}
	);
}

//加载登录状态
function loginStatus(uid)
{
	uid = uid ? uid : 0;
	$.ajax
	({
		type  : 'get',
		url   : '<?php echo $R->getPageUrl('system/fc.login.status'); ?>',
		cache : false,
		beforeSend : function(xhr){xhr.setRequestHeader('X-CSRF-Token', $$.csrfToken());},
		success : function(text)
		{
			$('.tasks_user').html(text);
			
			//登录状态变化后同步底部入口（首次登录不刷新页面时也能出现）
			$('#startmenu .ft').toggle($('.tasks_user .user').length > 0);
			
			if ($('.tasks_user .user')) $('.tasks_user .user').bind('click', function(){startClose(); editUser(uid);});
		}
	});
}

$(function()
{	
	//菜单提示（触屏没有悬停，不初始化）
	if (!$$.isTouch())
	{
		$('#navs li').easyTooltip();
		$('.tasks_Desktop').easyTooltip({xOffset : -83, yOffset : 50});
	}
	
	//打开锚点层
	naviOpen();
	
	//开始菜单
	startBuild();
	$('#startmenu .rail').delegate('li', 'mouseenter', function(){if (!$$.isTouch()) startSelect($(this).attr('data-key'));});
	$('#startmenu .rail').delegate('li', 'click', function(){startSelect($(this).attr('data-key'));});
	$('#startmenu .panes').delegate('dd', 'click', function(){startClose();});
	$('#startmenu .skin').bind('click', function(){startClose(); startLayer();});
	$('#startmenu .loginout').bind('click', function(){startClose(); loginOut();});
	
	//点菜单外收起；Esc 收起；窗口失焦（含点进窗口 iframe）收起
	$(document).bind('click', function(e)
	{
		if ($(e.target).closest('#startmenu, #tasks .start').length) return;
		startClose();
	});
	$(document).bind('keydown', function(e){if (e.keyCode == 27) startClose();});
	$(window).bind('blur', function(){startClose();});
	
	//提示IE6
	if (!window.XMLHttpRequest)
	{
		$('#Ie6Error').slideDown(600);
	}
	
	//显示桌面
	$('.tasks_Desktop').click(function()
	{
		for (var i = 0; i < $$.dialogs.stack.length; i++)
		{
			$$.dialogs.min($$.dialogs.stack[i]);
		}	
	});
	
	//隐藏主界面loading
	$('#preloader, #preloader_mark').fadeOut(300);
	
	//加载默认壁纸
	loadBack('<?php echo URL_SKIN; ?>');
	
	//判断登录 {false: 显示登录层, true: 绑定用户头像和退出登录事件}
	<?php 
	if (!$U->hasRights('system.login'))
	{
	?>
	loginLayer();
	<?php
	}
	else 
	{
	?>
	$('.tasks_user .user').bind('click', function(){startClose(); editUser(<?php echo $_SESSION['userInfo']['us_id']; ?>);});
	<?php
	}
	?>
	
	//窗口改变大小，二级菜单重新定位
	$(window).resize(function()
	{
		menuPosition();
	});
});
</script>
<style type="text/css">html,body {width:100%; height:100%; overflow:hidden; margin:0; padding:0;}</style>
</head>
<body style="background:#D7F0FF;">

<div id="preloader"></div><div id="preloader_mark"></div>

<div id="container">

	<div id="mobile_mark"></div>
	<div id="back_box"></div>
    <div id="back_thumb"><span></span><div></div></div>
    
	<div id="navs">
		<div class="back"></div>
		<?php include 'menus.php'; ?>
	</div>
	
	<div id="menus"><div class="back"></div><div class="menus"></div></div>
	
	<div id="startmenu">
		<div class="hd">
			<a class="home" href="https://fcontex.com" target="_blank"><span class="icon"><svg viewBox="0 0 44 44" aria-hidden="true"><circle cx="22" cy="22" r="21" fill="#FFFFFF" fill-opacity="0.7" stroke="#FFFFFF" stroke-opacity="0.8" stroke-width="1" /><g class="q"><rect x="11" y="11" width="10" height="10" rx="2" fill="#398DFD" /><rect x="23" y="11" width="10" height="10" rx="2" fill="#F48423" /><rect x="11" y="23" width="10" height="10" rx="2" fill="#4BC48A" /><rect x="23" y="23" width="10" height="10" rx="2" fill="#8C7BFF" /></g></svg></span><?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></a>
			<div class="tasks_user"><?php include 'login.status.php'; ?></div>
		</div>
		<div class="bd">
			<ul class="rail"></ul>
			<div class="panes"></div>
		</div>
		<?php /* 游客也输出，登录后由 loginStatus() 显示，避免首次登录不刷新页面时缺失 */ ?>
		<div class="ft"<?php if (!$U->hasRights('system.login')) echo ' style="display:none;"'; ?>>
			<a href="<?php echo URL_SITE; ?>" target="_blank"><svg viewBox="0 0 16 16" width="14" height="14" aria-hidden="true"><path d="M8 2.2 L14 7.4 H12.2 V13.6 H9.2 V9.6 H6.8 V13.6 H3.8 V7.4 H2 Z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" /></svg>访问前端</a>
			<a href="javascript:void(0);" class="skin"><svg viewBox="0 0 16 16" width="14" height="14" aria-hidden="true"><circle cx="8" cy="8" r="6.4" fill="none" stroke="currentColor" stroke-width="1.4" /><circle cx="5.6" cy="6" r="1.1" fill="currentColor" /><circle cx="9.2" cy="5.2" r="1.1" fill="currentColor" /><circle cx="10.4" cy="8.6" r="1.1" fill="currentColor" /></svg>皮肤设置</a>
			<a href="javascript:void(0);" class="loginout"><svg viewBox="0 0 16 16" width="14" height="14" aria-hidden="true"><path d="M8 2.4 V7.6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" fill="none" /><path d="M4.4 4.8 A4.8 4.8 0 1 0 11.6 4.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" fill="none" /></svg>退出登录</a>
		</div>
	</div>
	
	<div id="tasks">
		<div class="back"></div>
		<div class="start" title="开始" onclick="startToggle();"><svg viewBox="0 0 44 44" aria-hidden="true"><circle cx="22" cy="22" r="21" fill="#FFFFFF" fill-opacity="0.7" stroke="#FFFFFF" stroke-opacity="0.8" stroke-width="1" /><g class="q"><rect x="11" y="11" width="10" height="10" rx="2" fill="#398DFD" /><rect x="23" y="11" width="10" height="10" rx="2" fill="#F48423" /><rect x="11" y="23" width="10" height="10" rx="2" fill="#4BC48A" /><rect x="23" y="23" width="10" height="10" rx="2" fill="#8C7BFF" /></g></svg></div>
		<ul class="tasks"></ul>
        <div class="tasks_right">
        	<div class="tasks_Desktop" title="显示桌面"></div>
        </div>
	</div>
	
	<div id="Ie6Error">您使用的浏览器版本过低，影响网页性能，建议您换用<a href="http://info.msn.com.cn/ie9/" target="_blank">IE9</a>、<a href="http://www.google.cn/chrome/intl/zh-CN/landing_chrome.html" target="_blank">谷歌</a>、或<a href="http://www.firefox.com.cn/download/" target="_blank">火狐</a>浏览器。</div>
</div>

</body>
</html>
