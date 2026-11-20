<?php //保持utf-8无BOM文件编码 ?>
<div class="head">
<!--head-->
	<div class="search">
	<!--search-->
		<form method="get" title="内容检索" onsubmit="var key=$('#keyw').val().replace('内容检索','');if(key!='')location.href='<?php echo $R->getPageUrl('content/search/', ''); ?>'+encodeURIComponent(key);return false;">
		<input type="text" class="text" name="keyw" id="keyw" value="<?php echo (empty($keyw) ? '内容检索' : $keyw); ?>" onfocus="if(this.value=='内容检索')this.value='';" onblur="if(this.value=='')this.value='内容检索';" />
		<p><input type="submit" value="Search" class="button" /></p>
		</form>
	<!--search-->
	</div>
	<div class="navigate">
	<!--navi-->
		<?php
		$navigate = $C->getNavigate();
		$n = 0;
		foreach ($navigate as $navi)
		{
			if ($n++ > 0) echo '<span></span>';
		?>
		<a href="<?php echo $navi['nv_url']; ?>"<?php if ($navi['nv_target']) echo ' target="_blank"'; ?>><?php echo $navi['nv_title']; ?></a>
		<?php
		}
		?>
	<!--navi-->
	</div>
	<div class="clear"></div>
<!--head-->
</div>
