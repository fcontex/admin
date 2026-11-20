<ul>
	<?php
	//主菜单：模块 > 分组（一个分组 = 一个一级图标） > 菜单项（二级图标）
	//分组无级权限：组内可见菜单项为 0 时 该组连同一级图标一并丢弃（沿用原 $n==0 语义）
	$iconDefault = URL_SKIN.'module.default.png';
	foreach ($A->loadconfig('system.modules') as $name => $module)
	{
		$menus = (array)$module['menus'];
		//运行时副本尚未再生（仍为旧扁平结构 无分组 items 键）时按"单组"对待 保证仍能进入模块管理点"更新"
		$grouped = FALSE;
		foreach ($menus as $g)
		{
			if (isset($g['items'])) { $grouped = TRUE; break; }
		}
		if (!$grouped) $menus = array($module['name'] => array('items' => $menus));
		$groupIndex = 0;
		foreach ($menus as $groupName => $groupData)
		{
			$items = isset($groupData['items']) && is_array($groupData['items']) ? $groupData['items'] : array();
			//组图标缺省回落模块图标（均走 moduleIcon 皮肤覆盖解析）
			$groupIcon = isset($groupData['icon']) && $groupData['icon'] != '' ? $groupData['icon'] : '';
			ob_start();
			$n = 0;
	?>
	<li module="<?php echo $name; ?>" group="<?php echo $groupIndex; ?>" title="<?php echo $groupName; ?>" onmouseover="$(this).addClass('hover');" onmouseout="$(this).removeClass('hover');" onclick="naviClick(this);">
		<img src="<?php echo $groupIcon == '' ? (isset($module['icon']) ? $A->moduleIcon($name, $module['icon']) : $iconDefault) : $A->moduleIcon($name, $groupIcon); ?>" />
		<span class="name"><?php echo $groupName; ?></span>
		<dl>
			<?php
			//菜单项（二级图标）
			foreach ($items as $text => $value)
			{
				if (isset($value['rights']) && !$U->hasRights($value['rights'])) continue;
				if (!empty($value['icon']))
				{
					$icon = $A->moduleIcon($name, $value['icon']);
				}
				else
				{
					$icon = $iconDefault;
				}
			?>
			<dd navigate="<?php echo $name.'/'.$value['url']; ?>" onmouseover="$(this).addClass('hover');" onmouseout="$(this).removeClass('hover');" onclick="menuClick(this);">
				<img src="<?php echo $icon; ?>" />
				<div class="icon"><img src="<?php echo $icon; ?>" /></div>
				<div class="text"><span class="L"><?php echo $text; ?></span><span class="R"></span><span class="T"><?php echo $R->getPageUrl($name.'/'.$value['url']); ?></span></div>
			</dd>
			<?php
				$n++;
			}
			?>
		</dl>
	</li>
	<?php
			$n==0 ? ob_end_clean() : ob_end_flush();
			$groupIndex++;
		}
	}
	?>
</ul>
