<!--头部导航-->
<div class="header">
<!--header-->
	<div class="logo"><!--logo--><a href="<?php echo URL_SITE; ?>"><strong><?php echo $SITE['site_name']; ?></strong></a><!--logo--></div>
	<div class="search">
	<!--search-->
		<form method="get" title="内容检索" onsubmit="var key=$('#keyw').val().replace('内容检索','');if(key!='')location.href='<?php echo $R->getPageUrl('content/search/', ''); ?>'+encodeURIComponent(key);return false;">
		<input type="text" class="text" name="keyw" id="keyw" value="<?php echo (empty($keyw) ? '内容检索' : $keyw); ?>" onfocus="if(this.value=='内容检索')this.value='';" onblur="if(this.value=='')this.value='内容检索';" />
		<p><input type="submit" value="Search" class="button" /></p>
		</form>
	<!--search-->
	</div>
	<div class="navi">
	<!--navi-->
		<?php
		$navi = $C->getNavigate();
		$dot = '';
		foreach ($navi as $v)
		{
			echo $dot .= '<a href="'.$v['nv_url'].'"';
			if ($v['nv_target']) echo ' target="_blank"';
			echo '>'.$v['nv_title'].'</a>';
			$dot = '<span></span>';
		}
		?>
	<!--navi-->
	</div>
	<div class="clear"></div>
<!--header-->
</div>
