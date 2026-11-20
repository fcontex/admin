<div class="header">
	<div class="container search">
		<!--<div class="lang"><a href="/global">English</a>
			<input type="text" readonly="readonly" value="English" onclick="$('#lang').slideToggle(100);" onblur="setTimeout(function(){$('#lang').slideUp(100);},500);" />
			<ul id="lang">
				<li><a href="<?php echo URL_SITE; ?>global">English</a></li>
				<li><a href="<?php echo URL_SITE; ?>">简体中文</a></li>
			</ul>
		</div>-->
		<form method="get" title="产品搜索" onsubmit="var key=$('#keyw').val().replace('产品搜索','');if(key!='')location.href='<?php echo $R->getPageUrl('search/', ''); ?>'+encodeURIComponent(key);return false;">
			<input type="text" class="keyw" id="keyw" value="<?php echo (empty($keyw) ? '产品搜索' : $keyw); ?>" onfocus="if(this.value=='产品搜索')this.value='';" onblur="if(this.value=='')this.value='产品搜索';" />
			<input type="submit" class="subm" value="&nbsp;" />
		</form>
		<div class="anchor">
			快速搜索：
			<?php
			$keys = explode(',', '手机,平板,安卓,互联网');
			foreach ($keys as $k)
			{
			?>
			<a href="<?php echo $R->getPageUrl('search/'.$k, ''); ?>"><?php echo $k; ?></a> &nbsp;
			<?php
			}
			?>
		</div>
	</div>
	<div class="container navi">
		<div class="L"><a href="<?php echo URL_SITE; ?>"><img src="<?php echo URL_THEME; ?>images/blank.gif" /></a></div>
		<div class="C">
			<ul>
				<?php
                $navis = $C->getNavigate();
				foreach ($navis as $navi)
				{
				?>
				<li><a href="<?php echo $navi['nv_url']; ?>"<?php if ($navi['nv_target']) echo ' target="_blank"'; ?>><?php echo $navi['nv_title']; ?></a></li>
				<?php
				}
				?>
			</ul>
		</div>
		<div class="R">&nbsp;</div>
	</div>
</div>
<div style="height:0px;overflow:hidden;"></div>