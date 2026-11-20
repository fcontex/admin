<?php //保持utf-8无BOM文件编码 ?>
<div class="side">
<!--side-->
	<div class="logo"><!--logo--><a href="<?php echo URL_SITE; ?>"><strong><?php echo $SITE['site_name']; ?></strong></a><!--logo--></div>
	<div class="widget">
		<ul class="category">
		<!--category-->
			<?php
			foreach ($C->getCategories(0) as $cateP)
			{
			?>
			<li class="P"><a href="<?php if ($cateP['cg_type'] == 1){echo $cateP['cg_url'];}else{echo $R->getPageUrl('content/list-'.$cateP['cg_id']);}?>" title="<?php echo $cateP['cg_title']; ?>"<?php if ($cateP['cg_target'] == '_blank' && $cateP['cg_type'] == 1){echo ' target="_blank"';} ?>><?php echo $cateP['cg_title']; ?></a></li>
			<?php
			foreach ($C->getCategories($cateP['cg_id']) as $cate)
			{
			?>
			<li><a href="<?php if ($cate['cg_type'] == 1){echo $cate['cg_url'];}else{echo $R->getPageUrl('content/list-'.$cate['cg_id']);}?>" title="<?php echo $cate['cg_title']; ?>"<?php if ($cate['cg_target'] == '_blank' && $cate['cg_type'] == 1){echo ' target="_blank"';} ?>><?php echo $cate['cg_title']; ?></a></li>
			<?php
			}
			}
			?>
		<!--category-->
		</ul>
	</div>
	<div class="widget">
		<h2>Tags</h2>
		<div class="tags">
		<!--tags-->
			<?php
			foreach ($C->getTags() as $tag)
			{
			?>
			<p><a href="<?php echo $R->getPageUrl('content/tag/'.$tag['tg_title'], ''); ?>" title="<?php echo $tag['tg_title']; ?>" style="<?php echo $tag['tg_color'] ? 'color:'.$tag['tg_color'] : ''; ?>"><?php echo $tag['tg_title']; ?></a></p>
			<?php
			}
			?>
			<div class="clear"></div>
		<!--tags-->
		</div>
	</div>
<!--side-->
</div>
