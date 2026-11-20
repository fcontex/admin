<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $SITE['site_title']; ?></title>
<meta name="generator" content="<?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?>" />
<meta name="viewport" content="width=1136" />
<meta name="description" content="<?php echo $SITE['site_description']; ?>" />
<meta name="keywords" content="<?php echo $SITE['site_keywords']; ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo URL_THEME; ?>images/style.css" />
<script type="text/javascript" src="<?php echo URL_THEME; ?>images/jquery.js"></script>
<!--scrolltop_js--><script type="text/javascript" src="<?php echo URL_THEME; ?>images/scrolltop.js"></script><!--scrolltop_js-->
<script type="text/javascript">
<!--gbook_js-->
function gbook()
{
	$('#comment_info').removeClass().html('<img src="<?php echo URL_THEME; ?>images/loading.gif" />').show();
	$.ajax
	({
		type    : 'post',
		url     : '<?php echo $R->getCtrlUrl('message'); ?>?mode=gbook.insert',
		cache   : false,
		data    : $('#form_comment').serialize(),
		success : function(data, textStatus)
		{
			var a = data ? data.split('|') : ['无效的服务器响应。'];
			if (a[0] == 'YES')
			{
				$('#comment_info').addClass('comm_yes').html(a[1]);
				setTimeout(function()
				{
					$('#comment_info').removeClass().html('');
					$('#form_comment').get(0).reset();
					$('#cancel').click();
					location.href = location.href;
				},
				800);
			}
			else if (a[0] == 'ERR')
			{
				$('#comment_info').addClass('comm_err').html(a[1]);
				if (a[2]) $('#'+a[2]).focus();
			}
			else
			{
				$('#comment_info').html(data);
			}
		},
		error  : function(XMLHttpRequest, textStatus, errorThrown)
		{
			$('#comment_info').addClass('comm_err').html('超求超时。');
		}
	});
}
<!--gbook_js-->

$(function()
{
	<!--scrolltop_js-->
	scrolltop.init();
	<!--scrolltop_js-->
	
	<!--gbook_js-->
	$('.comment_list_button input').click(function()
	{
		$('#comment_info').removeClass().html('');
		var form = $('#form_comment').clone(true);
		$('#form_comment').remove();
		$(this).parent().after(form);
		$('#gb_topid').val($(this).attr('topid'));
		$('#gb_toid').val($(this).attr('toid'));
		$('#gb_toname').val($(this).attr('toname'));
		$('#cancel').css('display', 'inline').bind('click', function()
		{
			$('#comment_info').removeClass().html('');
			$('#form_comment').remove();
			$('#comment_form_box').after(form);
			$('#gb_topid').val('0');
			$('#gb_toid').val('0');
			$(this).css('display', 'none');
		});
	});
	<!--gbook_js-->
});
</script>
</head>
<body>

	<div class="toper"></div>
	
	<div class="container">
	<!--container-->
	
		<?php include 'inc.head.php'; ?>
		
		<?php
		if ($position)
		{
		?>
		<div class="center position">
		<!--position-->
			<a href="<?php echo URL_SITE; ?>">首页</a>
			<?php
			foreach ($position as $pos)
			{
				if ($pos['link'])
				{
			?>
			<a href="<?php echo $pos['link']; ?>"><?php echo $pos['text']; ?></a>
			<?php
				}
				else echo $pos['text'];
			}
			?>
		<!--position-->
		</div>
		<?php
		}
		?>
		
		<div class="center">
		<!--center-->
		
			<div class="left">
			<!--left-->
			
				<div class="comment_list">
				<!--gbook-->
				
					<div id="comment_form_box">
					<!--gbook_form-->
					<?php
					if ($quiet)
					{
						echo '留言功能已关闭。';
					}
					else
					{
					?>
						<form onsubmit="gbook(); return false;" id="form_comment">
						<input type="hidden" name="gb_toid" id="gb_toid" value="0" />
						<input type="hidden" name="gb_topid" id="gb_topid" value="0" />
						<input type="hidden" name="gb_toname" id="gb_toname" value="" />
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tr>
								<td><input type="text" class="text" name="gb_name" id="gb_name" /><label>*称呼</label></td>
							</tr>
							<tr>
								<td><input type="text" class="text" name="gb_email" id="gb_email" /><label>邮箱</label></td>
							</tr>
							<tr>
								<td><input type="text" class="text" name="gb_url" id="gb_url" /><label>网站</label></td>
							</tr>
							<tr>
								<td><textarea class="text" name="gb_content" id="gb_content"></textarea><label>*内容</label></td>
							</tr>
							<tr>
								<td class="submit"><input type="submit" value="提交" /> <input type="button" value="取消" id="cancel" /><label id="comment_info" style="display:none;"></label></td>
							</tr>
						</table>
						</form>
					<?php
					}
					?>
					<!--gbook_form-->
					</div>
					
					<p class="delimiter"></p>
					
					<div id="comment_box">
					<!--gbook_list-->
						<?php
						foreach ($gbook as $rst)
						{
						?>
						<div class="comment_list_1">
							<span class="comment_list_face"><img src="http://www.gravatar.com/avatar/<?php echo md5($rst['gb_email']); ?>?s=40&r=X" /></span>
							<h2 class="comment_list_name"><a target="_blank" href="<?php if ($rst['gb_url']) echo 'http://'.$rst['gb_url']; else echo 'javascript:void(0);'; ?>"><?php echo $rst['gb_name']; ?></a></h2>
								<span class="comment_list_time"><?php echo date('Y-m-d H:i', $rst['gb_time']); ?></span>
								<div class="comment_list_content"><?php echo $rst['gb_content']; ?></div>
								<div class="comment_list_button"><input type="button" value="回复" topid="<?php echo $rst['gb_id']; ?>" toid="<?php echo $rst['gb_id']; ?>" toname="<?php echo $rst['gb_name']; ?>" /></div>
							<?php
							foreach ($M->getGbook($rst['gb_id']) as $rst2)
							{
							?>
							<div class="comment_list_2">
								<span class="comment_list_face"><img src="http://www.gravatar.com/avatar/<?php echo md5($rst2['gb_email']); ?>?s=40&r=X" /></span>
								<h2 class="comment_list_name"><a target="_blank" href="<?php if ($rst2['gb_url']) echo 'http://'.$rst2['gb_url']; else echo 'javascript:void(0);'; ?>"><?php echo $rst2['gb_name']; ?></a> @<?php echo $rst2['gb_toname']; ?></h2>
								<span class="comment_list_time"><?php echo date('Y-m-d H:i', $rst2['gb_time']); ?></span>
								<div class="comment_list_content"><?php echo $rst2['gb_content']; ?></div>
								<div class="comment_list_button"><input type="button" value="回复" topid="<?php echo $rst['gb_id']; ?>" toid="<?php echo $rst2['gb_id']; ?>" toname="<?php echo $rst2['gb_name']; ?>" /></div>
							</div>
							<?php
							}
							?>
						</div>
						<?php
						}
						?>
						<?php echo $turnner; ?>
					<!--gbook_list-->
					</div>
				
				<!--gbook-->
				</div>
			
			<!--left-->
			</div>
			
			<?php include 'inc.side.php'; ?>
		
		<!--center-->
		</div>
		
		<div class="clear"></div>
		
		<?php include 'inc.foot.php'; ?>
	
	<!--container-->
	</div>
	
</body>
</html>
