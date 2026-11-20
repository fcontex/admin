<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $SITE['site_title']; ?></title>
<meta name="generator" content="<?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?>" />
<meta name="viewport" content="width=1024" />
<meta name="description" content="<?php echo $SITE['site_description']; ?>" />
<meta name="keywords" content="<?php echo $SITE['site_keywords']; ?>" />
<link href="<?php echo URL_THEME; ?>images/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php include 'inc.header.php'; ?>
<div class="toper" id="focus_S">
	<div class="focus" id="focus_A">
		<?php
		if (isset($focuses[0]))
		{
		?>
		<a style="background:url(<?php echo $A->getThumb($focuses[0]['ct_cover']); ?>) center no-repeat;" bcolor="#ffffff" href="<?php echo $focuses[0]['ct_title2']; ?>">&nbsp;</a>
		<?php
		}
		?>
	</div>
	<div class="lists" id="focus_B">
		<?php
		$class = ' class="C"';
		$tools = '';
		$index = 0;
		foreach ($focuses as $focus)
		{
			$tools .= '<img src="'.URL_THEME.'images/blank.gif" onclick="imgPlayer.play('.($index++).');"'.$class.' />';
			$class  = '';
		?>
		<a style="background:url(<?php echo $A->getThumb($focus['ct_cover']); ?>) center no-repeat;" bcolor="#ffffff" href="<?php echo $focus['ct_title2']; ?>">&nbsp;</a>
		<?php
		}
		?>
	</div>
	<div class="tools" id="focus_C"><?php echo $tools; ?></div>
</div>
<div class="container rolls">
	<div class="R">
		<a href="#" class="A" onclick="textPlayer.prev();return false;"><img src="<?php echo URL_THEME; ?>images/blank.gif" /></a>
		<a href="#" class="B" onclick="textPlayer.next();return false;"><img src="<?php echo URL_THEME; ?>images/blank.gif" /></a>
	</div>
	<div class="L" id="rolls">
		<?php
		$class = ' class="init"';
		foreach ($contents as $content)
		{
		?>
		<a href="<?php echo $R->getPageUrl('news/'.$content['ct_id']); ?>"<?php echo $class; ?>><?php echo $content['ct_title']; ?></a>
		<?php
			$class = '';
		}
		?>
	</div>
</div>
<div class="container products">
	<div class="backer">
		<?php
		foreach ($products as $item)
		{
		?>
		<dl title="<?php echo $item['ct_title']; ?>">
			<dt>
				<img class="product" src="<?php echo $A->getThumb($item['ct_cover'], 236, 210); ?>" alt="<?php echo $item['ct_title']; ?>" width="236" height="210" />
				<a href="<?php echo $url = $R->getPageUrl('content/product-read-'.$item['ct_id']); ?>"><img src="<?php echo URL_THEME; ?>images/blank.gif" width="240" height="210" /></a>
			</dt>
			<dd><a href="<?php echo $url; ?>"><strong><?php echo $item['ct_title']; ?></strong></a></dd>
			<dd><?php echo $item['ct_title2']; ?></dd>
		</dl>
		<?php
		}
		?>
	</div>
</div>
<?php include 'inc.footer.php'; ?>
<script type="text/javascript" src="<?php echo URL_THEME; ?>images/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo URL_THEME; ?>images/jquery.color.js"></script>
<script type="text/javascript">
$(function()
{
	//滚动图片
	(function($)
	{
		var $$ =
		{
			lists : $('#focus_B a'),
			tools : $('#focus_C img'),
			focus : $('#focus_A'),
			stage : $('#focus_S'),
			index : 0,
			cycle : 5000,
			timer : null
		};
		
		$$.count = $$.lists.size();
		
		$$.play = function(index, undefined)
		{
			if ($$.timer)
			{
				clearInterval($$.timer);
				$$.timer = null;
			}
			$$.timer = setInterval($$.play, $$.cycle);
			
			//$$.lists.stop();
			
			if (index == undefined)
			{
				if (++$$.index >= $$.count) $$.index = 0;
				index = $$.index;
			}
			else $$.index = index;
			
			$$.focus.find('a').fadeOut(1000, function()
			{
				$$.tools.removeClass('C');
				$($$.tools.get(index)).addClass('C');
				
				var dolly = $($$.lists.get(index)).clone();
				dolly.css('display', 'none');
				$$.focus.empty().append(dolly);
				//$$.stage.animate({backgroundColor:dolly.attr('bcolor')}, 800, function(){dolly.fadeIn(1000);});
				dolly.fadeIn(1000);
			});
		};
		
		$$.next = $$.play;
		
		$$.prev = function()
		{
			$$.play(($$.index<=0 ? $$.count : $$.index) - 1);
		};
		
		$$.start = function()
		{
			window.setTimeout(function(){$$.play(1);}, $$.cycle);
		};
		
		window.imgPlayer = $$;
		
	})(jQuery);
	
	//滚动文字
	(function($)
	{
		var $$ =
		{
			lists : $('#rolls a'),
			stage : $('#rolls'),
			index : 0,
			cycle : 5000,
			timer : null
		};
		
		$$.count = $$.lists.size();
		
		$$.play = function(direction, undefined)
		{
			if ($$.timer)
			{
				clearInterval($$.timer);
				$$.timer = null;
			}
			$$.timer = setInterval($$.play, $$.cycle);
			
			$$.lists.stop();
			
			if (direction == undefined)
			{
				direction = 1;
			}
			
			if (direction == 1)
			{
				$($$.lists.get($$.index)).animate({top:'-2em'}, 300, function()
				{
					if (++$$.index >= $$.count) $$.index = 0;
					
					$$.lists.css('top', '-2em');
					var dolly = $($$.lists.get($$.index));
					dolly.css('top', '2em');
					dolly.animate({top:0}, 300);
				});
			}
			else
			{
				$($$.lists.get($$.index)).animate({top:'2em'}, 300, function()
				{
					if (--$$.index < 0) $$.index = $$.count-1;
					
					$$.lists.css('top', '2em');
					var dolly = $($$.lists.get($$.index));
					dolly.css('top', '-2em');
					dolly.animate({top:0}, 300);
				});
			}
		};
		
		$$.next = function()
		{
			$$.play(1);
		};
		
		$$.prev = function()
		{
			$$.play(0);
		};
		
		$$.start = function()
		{
			$$.play(1);
		};
		
		window.textPlayer = $$;
		
	})(jQuery);
	
	imgPlayer.start();
	textPlayer.start();
});
</script>
</body>
</html>
