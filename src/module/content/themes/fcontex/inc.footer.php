<div class="container footer">
	<div class="R"><a href="http://www.fcontex.com/" target="_blank">Powered by <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></a>&nbsp; &nbsp; <?php echo $SITE['site_copyright']; ?></div>
	<?php
	foreach ($links as $link)
	{
	?>
	<a href="<?php echo $R->getPageUrl('page/'.$link['ct_id']); ?>"><?php echo $link['ct_title']; ?></a>&nbsp;|&nbsp;
	<?php
	}
	?>
	<!--<a href="<?php echo $R->getPageUrl('rss', ''); ?>" target="_blank">Rss</a>&nbsp;|&nbsp;-->
	<!--<a href="/sitemap.xml" target="_blank">Sitemap</a>&nbsp;|&nbsp;-->
	<a href="<?php echo $R->getPageUrl('system', ''); ?>" rel="nofollow">Login</a>&nbsp;|&nbsp;
	<?php echo $SITE['site_counter']; ?>
</div>