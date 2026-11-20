<?php //保持utf-8无BOM文件编码 ?>
<div class="foot">
<!--footer-->
	<div><p>
		<strong>
			<a href="http://www.fcontex.com/" target="_blank">Powered By <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></a>&nbsp; &nbsp; 
			<?php echo $SITE['site_copyright']; ?>
		</strong>
		<a href="<?php echo $R->getPageUrl('system'); ?>" rel="nofollow">Login</a>&nbsp;|&nbsp;
		<a href="<?php echo $R->getPageUrl('content/rss', ''); ?>" target="_blank">Rss</a>&nbsp;|&nbsp;
		<!--<a href="/sitemap.xml" target="_blank">Sitemap</a>&nbsp;|&nbsp;-->
		<span style="font-style:normal;" id="baidu"><?php echo $SITE['site_counter']; ?></span>
		<script type="text/javascript">$(function(){$('#baidu a').html('Baidu');});</script>
	</p></div>
<!--footer-->
</div>
