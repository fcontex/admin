/***
 * 名称：全局Javascript运行库
 * 依赖：jQeury
 * Alan, 2012.05
 * http://www.fcontex.com/
*/

(function($)
{
	var $$ = {};
	
	//当前是否移动布局：读 style.css 里的 #mobile_mark，断点只维护样式表一处
	$$.isMobile = function(){ return $('#mobile_mark').css('display') == 'block'; };
	
	//当前是否为无悬停能力的触屏（宽屏平板同样成立）
	$$.isTouch = function(){ return !!(window.matchMedia && window.matchMedia('(hover: none)').matches); };
	
	if (top.$$)
	{
		$$.dialogs = top.$$.dialogs;
	}
	else
	{
		var dialogs = $$.dialogs =
		{
			names:[],		//已创建的层对象标识组
			zmax:999,		//当前焦点层对象的z序值
			toper:null,		//当前焦点层对象
			stack:[],		//已创建的层对象组
			width:960,		//默认宽度
			height:560,		//默认高度
			limit:8			//允许创建的层对象数量
		};
		
		//创建弹出层
		dialogs.open = function(options)
		{
			/**
			 * 设置：
			 * name  : 名称 - 对象唯一标识 
			 * model : 模态
			 * title : 标题
			 * url   : 加载页面
			 * text  : 显示文本 - 不指定url时使用
			 * icon  : 图标类型 TIP/ERR
			 * width : 自定义宽度
			 * height: 自定义高度
			 * ctrl	 ：最小化，最大化，关闭按钮是否启用[1, 1, 1]顺序传参
			 *
			 * 事件：
			 * create : 创建
			 * show   : 显示
			 * focus  : 聚焦
			 * min    : 最小化
			 * max    : 最大化
			 * close  : 关闭
			 * ok:    : 确认
			 * cancle : 取消
			 *
			 * 事件触发顺序：
			 * 创建 - 显示 - 显示后触发聚焦
			 * 拖动开始触发聚焦 - 拖动中 - 拖动结束
			 * 最小化前触发失焦 - 另一个层显示
			 * 关闭前触发失焦 - 关闭
			 *
			 * 返回创建的弹出层对象：
			 * {
			 * content:创建的jQuery对象
			 * show: 显示方法
			 * resize: 框架刷新方法
			 * focus: 聚焦方法
			 * blur: 失焦方法
			 * min: 最小化方法
			 * max: 最大化方法
			 * close: 关闭方法
			 * }
			 */
			 
			if (typeof options == 'string')
			{
				options = {url:options};
			}
			
			//内部数据缓存对象
			var cache =
			{
				name  : options.name || '',
				model : options.model || false,
				title : options.title || '',
				ticon : options.ticon || '',
				url   : options.url || '',
				text  : options.text || '',
				icon  : options.icon || 'TIP',
				width : options.width || dialogs.width,
				height: options.height || dialogs.height,
				ctrl  : options.ctrl || [1, 1, 1]
			};
			
			//检查是否已创建
			if ($.inArray(cache.name, dialogs.names) != -1)
			{
				var layer = dialogs.find(cache.name);
				if (layer) layer.show();
				return layer;
			}
			else if (cache.name)
			{
				dialogs.names.push(cache.name);
			}
			
			//开始创建新层
			cache.layerid = '_layer_'+(dialogs.zmax)+'_';
			cache.frameid = '_frame_'+(dialogs.zmax)+'_';
			
			if (cache.url)
			{
				cache.content = '<div class="dialog_loading" style="height:'+(cache.height-30)+'px;"></div><iframe onload="$(\'#'+cache.layerid+' .dialog_loading\').hide();" id="'+cache.frameid+'" width="100%" height="100%" scrolling="auto" frameborder="0" src="'+cache.url+'"></iframe>';
				cache.control = '<div class="ctrl">';
				cache.control += cache.ctrl[0] ? '<span class="min"></span>' : '';
				cache.control += cache.ctrl[1] ? '<span class="max"></span>' : '';
				cache.control += cache.ctrl[2] ? '<span class="close"></span>' : '';
				cache.control += '</div>';
				//宽高改为open可传参数||默认
				//cache.width = dialogs.width;
				//cache.height = dialogs.height;
				
				//弹出层数量限制
				if (dialogs.stack.length >= dialogs.limit)
				{
					dialogs.stack[0].close();
				}
			}
			else
			{
				cache.content = '<div class="html"><span class="'+cache.icon+'"></span><p>'+cache.text+'</p><div><input class="button ok" type="button" value="确认" />';
				if (typeof options.cancle == 'function')
				{
					cache.content += '<input class="button cancle" type="button" value="取消" />';
				}
				cache.content +='</div></div>';
				cache.control = '<div class="ctrl"></span><span class="close"></span></div>';
				cache.width = options.width || 340;
				cache.height = options.height || 170;
			}
			
			//移动布局：功能窗口直接以最大化尺寸创建，显示时不再有尺寸跳变
			//几何值与 dialogs.max 的缓存一致：左 9、上 9、宽减 18、高减 18（底部再扣任务栏 30），外框外撑 4px 后四边各露 5px
			var isFill = false;
			if ($$.isMobile())
			{
				isFill = !!(cache.url && !(options.width || options.height));
				
				if (isFill)
				{
					cache.width  = $(window).width() - 18;
					cache.height = $(window).height() - 18 - 30;
				}
				else
				{
					var bar    = $('#tasks').outerHeight() || 0,
						cards  = Math.max(0, ($('#tasks .tasks').outerHeight() || 0) - bar);
					
					cache.width  = Math.min(cache.width,  $(window).width() - 32);
					cache.height = Math.min(cache.height, $(window).height() - bar - cards);
				}
			}
			
			var hB = $('body').height();
			var hW = $(window).height();
			cache.layer = (cache.model ? '<div id="dialog_mask" style="z-index:'+(dialogs.zmax++)+';height:'+(hB>hW ? hB :hW)+'px;"></div>' : '') +
							'<div class="dialog" id="'+cache.layerid+'" style="z-index:'+dialogs.zmax+';width:'+cache.width+'px;height:'+cache.height+'px;">'+
							'<div class="back"></div><div class="cover"></div>'+
							cache.control+
							(cache.title ? '<div class="title"><h1>'+cache.ticon+cache.title+'</h1></div>' : '')+'<div class="panel">'+cache.content+'</div></div>';
			
			//层对象layer创建
			var container = $('body'), layer = {content: container.append(cache.layer).find('#'+cache.layerid)};
			//最大化的窗口贴左上留 9px；手机端窗口是 fixed 定位，居中也不叠加滚动量
			var posLeft, posTop;
			if (isFill)
			{
				posLeft = 9;
				posTop  = 9;
			}
			else if ($$.isMobile())
			{
				posLeft = ($(window).width() - layer.content.width())/2;
				posTop  = ($(window).height() - layer.content.height())/2;
			}
			else
			{
				posLeft = container.width()/2 - layer.content.width()/2;
				posTop  = $(window).scrollTop() + $(window).height()/2 - layer.content.height()/2;
			}
			layer.content.css('left', (posLeft<0 ? 0 : posLeft) + 'px').css('top', (posTop<0 ? 0 : posTop) + 'px');
			
			//已按最大化尺寸创建，预置缓存，最大化按钮怎么点都不会改变窗口尺寸
			if (isFill)
			{
				layer.lastOffset = {left:posLeft, top:posTop};
				layer.lastWidth  = cache.width;
				layer.lastHeight = cache.height;
				layer.dragStatus = false;
			}

			//对象压栈
			dialogs.stack.push(layer);
			layer.name = cache.name;
			
			//方法和事件绑定
			layer.show = function(){if(typeof options.show == 'function'){options.show(layer);};dialogs.show(layer);};
			layer.resize = function(){if(typeof options.resize == 'function'){options.resize(layer);};dialogs.resize(layer);};
			layer.focus = function(){if(typeof options.focus == 'function'){options.focus(layer);};dialogs.focus(layer);};
			layer.blur = function(){if(typeof options.blur == 'function'){options.blur(layer);};dialogs.blur(layer);};
			if (cache.url)
			{
				layer.min = function(){if(typeof options.min == 'function'){options.min(layer);};dialogs.min(layer);};
				layer.max = function(){if(typeof options.max == 'function'){options.max(layer);};dialogs.max(layer);};
			}
			layer.close = function(){if(typeof options.close == 'function'){options.close(layer);};dialogs.close(layer);};
			
			//动作绑定
			layer.content.find('.cover').bind('click', function(){layer.focus();return false;});
			layer.content.find('.min').bind('click', function(){layer.min();return false;});
			layer.content.find('.max').bind('click', function(){layer.max();return false;});
			layer.content.find('.close').bind('click', function(){layer.close();return false;});
			if (cache.url && cache.ctrl[1])
			{
				layer.content.find('.title').bind('dblclick', function(){layer.max();return false;}).disableSelection();
			}
			else
			{
				layer.content.find('.title').bind('click', function(){layer.content.find('input.ok').focus();return false;}).disableSelection();
			}
			
			layer.content.find('input.ok').bind('click', function(){if(typeof options.ok == 'function'){options.ok(layer);};});
			layer.content.find('input.cancle').bind('click', function(){if(typeof options.cancle == 'function'){options.cancle(layer);};});
			
			//拖动支持
			layer.content.draggable
			({
				scroll : false,
				handle : layer.content.find('.title,.cover'),
				cancel : '.dialog .title div',
				start  : function(){layer.focus();layer.content.find('.cover').show();},
				stop   : function(){layer.content.find('.cover').hide(); layer.content.find('input.ok').focus();}
			});
			
			//触屏没有悬停与精确拖动的语义，关闭拖动
			if ($$.isTouch()) layer.content.draggable('option', 'disabled', true);
			
			//刷新框架尺寸
			layer.resize();
			
			//触发创建完成事件
			if(typeof options.create == 'function'){options.create(layer);};
			
			//创建完成自动显示
			layer.show();
			
			return cache;
		};
		
		dialogs.find = function(name)
		{
			if (name) for (var i=0; i<dialogs.stack.length; i++)
			{
				if (dialogs.stack[i].name == name) return dialogs.stack[i];
			}
			
			return null;
		};
		
		dialogs.show = function(layer)
		{
			layer.content.show();
			
			//显示时自动聚焦
			layer.focus();
			
			return layer;
		};
		
		dialogs.resize = function(layer)
		{
			var panel = layer.content.find('.panel'),
			padding = panel.innerHeight() - panel.height();
			
			layer.content.find('.panel').height(layer.content.height() - layer.content.find('.title').height() - padding);
			
			return layer;
		};
		
		dialogs.focus = function(layer)
		{
			if (dialogs.toper == layer)
			{
				return layer;
			}
			
			if (dialogs.toper)
			{
				dialogs.toper.blur();
			}
			
			layer.content.css('z-index', ++dialogs.zmax);
			layer.content.find('.cover').hide();
			layer.content.find('input.ok').focus();
			
			dialogs.toper = layer;
			
			return layer;
		};
		
		dialogs.blur = function(layer)
		{
			dialogs.toper = null;
			
			layer.content.find('.cover').show();
			
			return layer;
		};
		
		dialogs.min = function(layer)
		{
			//最小化前自动失焦
			layer.blur();
			
			layer.content.hide();
			
			//最小化后自动聚焦新层
			dialogs.autofocus();
			
			return layer;
		};
		
		dialogs.max = function(layer)
		{
			//第一次最大化前初始化缓存
			if (!layer.lastOffset)
			{
				var container = $('body');
				
				//手机端窗口是 fixed 定位，坐标直接用视口值
				if ($$.isMobile())
				{
					layer.lastOffset = {left:9, top:9};
					layer.lastWidth = $(window).width() - 18;
					layer.lastHeight = $(window).height() - 18 - 30;
				}
				else
				{
					layer.lastOffset = container.offset();
					layer.lastOffset.left += 8;
					layer.lastOffset.top += 5;
					layer.lastWidth = container.width()-16;
					layer.lastHeight = $(window).height()-13-30;
				}
				
				layer.dragStatus = false;
			}
			
			//先暂存当前的状态值
			var offset = layer.content.offset();
			var width = layer.content.width();
			var height = layer.content.height();
			var status = !layer.content.draggable("option", "disabled");
			
			//应用已缓存的上次状态值
			layer.content.css({left:layer.lastOffset.left, top:layer.lastOffset.top});
			layer.content.width(layer.lastWidth);
			layer.content.height(layer.lastHeight);
			layer.content.draggable("option", "disabled", !layer.dragStatus);
			
			//用暂存的状态值更新缓存
			layer.lastOffset = offset;
			layer.lastWidth = width;
			layer.lastHeight = height;
			layer.dragStatus = status;
			
			//刷新框架尺寸
			layer.resize();
			
			return layer;
		};
		
		dialogs.close = function(layer)
		{
			var names = [];
			for (var i=0; i<dialogs.names.length; i++)
			{
				if (dialogs.names[i] != layer.name) names.push(dialogs.names[i]);
			}
			dialogs.names = names;
			
			var stack = [];
			for (var i=0; i<dialogs.stack.length; i++)
			{
				if (dialogs.stack[i] != layer) stack.push(dialogs.stack[i]);
			}
			dialogs.stack = stack;
			
			//关闭前自动失焦
			layer.blur();
			
			$('#dialog_mask').remove();
			layer.content.remove();
			
			//关闭后自动聚焦新层
			dialogs.autofocus();
			
			return layer;
		};
		
		dialogs.autofocus = function()
		{
			var layer = null;
			
			for (var i=0,z=0; i<dialogs.stack.length; i++)
			{
				if (dialogs.stack[i].content.css('display') == 'none')
				{
					continue;
				}
				
				var zindex = dialogs.stack[i].content.css('z-index');
				if (zindex > z)
				{
					z = zindex;
					layer = dialogs.stack[i];
				}
			}
			
			if (layer) layer.focus();
		};
	}
	
	/***
	 * Tip Tools
	*/
	
	$$.alert = function(args)
	{//(text, ok, icon, title)
		$$.dialogs.open
		({
		 	name:'alert',
			text:args.text,
			icon:args.icon,
			ok:function(layer)
			{
				if(typeof args.ok == 'function') args.ok(layer);
				layer.close();
			},
			title:args.title||'系统提示',
			model:true
		});
	};
	
	$$.confirm = function(args)
	{//(text, ok, icon, title)
		$$.dialogs.open
		({
			name:'confirm',
			text:args.text,
			icon:args.icon,
			ok:function(layer)
			{
				if(typeof args.ok == 'function') args.ok(layer);
				layer.close();
			},
			cancle:function(layer)
			{
				if(typeof args.cancle == 'function') args.cancle(layer);
				layer.close();
			},
			title:args.title||'系统提示',
			model:true
		});
	};
	
	$$.loading = top.$$ ? top.$$.loading : function(options)
	{
		if (top.loadingTimer)
		{
			top.clearTimeout(top.loadingTimer);
			top.loadingTimer = 0;
		}
		
		if (typeof options == 'string')
		{
			options = {icon:true,text:options};
		}
		
		var cache =
		{
			icon: options.icon===false ? false : true,
			text: options.text || ''
		};
		
		var layer = $('body').find('#loading');
		if (!layer.get(0))
		{
			layer = $('body').append('<div id="loading"><div></div><span>'+options.text+'</span></div>').find('#loading');
		}
		
		if (cache.icon)
		{
			layer.find('span').css('background-position', 'left center').css('padding-left', '16px');
		}
		else
		{
			layer.find('span').css('background-position', '-30px 0').css('padding-left','6px');
		}
		layer.find('span').html(cache.text);
		layer.show();
		
		if (typeof(options.hide) == 'number')
		{
			top.loadingTimer = top.setTimeout(function()
			{
				layer.hide();
				top.loadingTimer = 0;
			},
			options.hide*1000);
		}
	};
	
	/***
	 * Ajax Tools
	*/
	
	//读取CSRF令牌（由服务端下发的可读Cookie 请求时回传请求头）
	$$.csrfToken = function()
	{
		var m = document.cookie.match(/(?:^|;\s*)csrf_token=([^;]*)/);
		return m ? decodeURIComponent(m[1]) : '';
	};
	
	$$.post = function(url, data, callback, loading)
	{
		loading = loading === false ? false : true;
		
		if (loading) $$.loading('执行中...');
		
		$.ajax
		({
			type  : 'post',
			url   : url,
			cache : false,
			data  : data,
			beforeSend : function(xhr){xhr.setRequestHeader('X-CSRF-Token', $$.csrfToken());},
			success : function(data, textStatus)
			{
				var a = data ? data.split('|') : ['无效的服务器响应。'];
				switch (a[0])
				{
				case 'YES':
					if (loading) $$.loading({text:'执行成功。', icon:false, hide:3});
					if(typeof(callback) == 'function') callback(a[1]);
					break;
				case 'WAR':
				case 'ERR':
					if (loading) $$.loading({hide:0});
					if (a[1]) $$.alert({text:a[1], ok:function(){if (a[2]) $('#'+a[2]).focus();}, icon:a[0], title:'错误提示'});
					break;
				default:
					if (loading) $$.loading({hide:0});
					if(typeof(callback) == 'function') callback(data);
					//alert(data);
					break;
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown)
			{
				if (loading) $$.loading({hide:0});
				alert(XMLHttpRequest.responseText);
			}
			
		});
	};
	
	$$.get = function(url, callback, loading)
	{
		loading = loading === false ? false : true;
		
		if (loading) $$.loading('执行中...');
		
		$.ajax
		({
			type  : 'get',
			url   : url,
			cache : false,
			beforeSend : function(xhr){xhr.setRequestHeader('X-CSRF-Token', $$.csrfToken());},
			success : function(data, textStatus)
			{
				var a = data ? data.split('|') : ['无效的服务器响应。'];
				switch (a[0])
				{
				case 'YES':
					if (loading) $$.loading({text:'执行成功。', icon:false, hide:3});
					if(typeof(callback) == 'function') callback(a[1]);
					break;
				case 'ERR':
				case 'WAR':
					if (loading) $$.loading({hide:0});
					if (a[1]) $$.alert({text:a[1], ok:function(){if (a[2]) $('#'+a[2]).focus();}, icon:a[0], title:'错误提示'});
					break;
				default:
					if (loading) $$.loading({hide:0});
					if(typeof(callback) == 'function') callback(data);
					//alert(data);
					break;
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown)
			{
				if (loading) $$.loading({hide:0});
				alert(XMLHttpRequest.responseText);
			}
		});
	};
	
	/***
	 * Other Tools
	*/
	
	$$.test = function(string, pattern, flag)
	{
		if (!flag) flag = 'i';
		var reg = new RegExp(pattern, flag) ;
		return reg.test(string);
	}
	
	$$.redirect = function(url)
	{
		location.href = url ? url : location.href;
	};
	
	$$.target = function(target)
	{
		if (target)
		{
			//location.href = location.href.replace(/#.*$/, '') + '#' + target;
			location.hash = target;
		}
		else
		{
			return location.hash;//location.href.replace(/^[^#]+#(.*)$/, '$1');
		}
	};
	
	$$.fullscreen = function(selector)
	{
		var img = $(selector),  win = $(window),
		imgW = img.width(), imgH = img.height();
		
		var resize = function()
		{
			var winW = win.width(), winH = win.height();
			var W, H;
			
			if (imgW/imgH < winW/winH)
			{
				img.width(W = winW);
				img.height(H = winW/imgW*imgH);
			}
			else
			{
				img.width(W = imgW/imgH*winH);
				img.height(H = winH);
			}
			
			img.css('left', winW-W+'px').css('top', winH-H+'px');
		};
		
		resize(); img.show();
		
		win.bind('resize', resize);
	};
	
	$$.selectval = function(selector)
	{
		$val = '', dot = '';
		$(selector).each(function(){$val += dot + $(this).val(); dot = ',';});
		return $val;
	};
	
	$$.filesUploadLayer = top.$$ ? top.$$.filesUploadLayer :
	{
		open: function(param)
		{
			var callback = param.callback || '';
			var type = param.type || 'image';
			var dir  = param.dir || 'content';
			var label = typeof param.label=='string' ? param.label : '使用';
			var closeCallback = typeof param.close=='function' ? param.close : null;
			$$.dialogs.open
			({
				name: param.name || 'filesUploadLayer',
				title: param.title || '添加文件',
				url: param.url+'&callback='+callback+'&type='+type+'&dir='+dir+'&label='+label,
				width: param.width || 550,
				height: 450,
				model: true,
				ctrl: [0, 0, 1],
				close: closeCallback
			});
			
			this.editor = param.editor;
			this.opener = param.opener || top;
		},
		close: function()
		{
			$$.dialogs.find('filesUploadLayer').close();
		},
		callbackEditor: function(param)
		{
			var str;
			if (param.image)
			{
				str = '<img' + (param.title ? ' atl="'+param.title+'"' : '') + (param.align ? ' align="'+obj.align+'"' : '') + ' src="'+param.url+'" />';
			}
			else 
			{
				str = '<a key="'+param.id+'" class="fcattached" target="_blank" href="'+param.pipe+'" rel="nofollow">' + param.title + '<span></span></a>';	
			}
			this.editor.insertHtml(str);
			this.close();
		}
	};
	
	$$.editor = function(options)
	{
		/***
		 * target:	目标选择器（textarea，提交前调用 sync() 回写 HTML）
		 * mode:	调用模式[1=全部, 2=简洁]，默认 1
		 * items:	mode=3 时自定义工具栏（Quill 下保留参数，暂不生效）
		 * css:		兼容旧参数（Quill 直接编辑页面 DOM，不再注入主题样式）
		 * url:		文件上传页面路径
		*/
		if (typeof options == 'string') options = {target:options};
		if (!options) options = {};

		var target = $(options.target);
		if (!target.length) return null;

		var cache = {mode: options.mode || 1, url: options.url};
		var full = cache.mode != 2;

		//外壳与工具栏
		var wrap = $('<div class="fc-quill"></div>');
		var bar  = $('<div></div>');
		var box  = $('<div></div>');
		var html = '<span class="ql-formats"><select class="ql-header">'
			+ '<option value="1"></option><option value="2"></option><option value="3"></option><option selected></option>'
			+ '</select></span>'
			+ '<span class="ql-formats"><button class="ql-bold" type="button"></button>'
			+ '<button class="ql-italic" type="button"></button>'
			+ '<button class="ql-underline" type="button"></button>'
			+ '<button class="ql-strike" type="button"></button></span>';
		if (full)
		{
			html += '<span class="ql-formats"><button class="ql-list" value="ordered" type="button"></button>'
				+ '<button class="ql-list" value="bullet" type="button"></button>'
				+ '<button class="ql-indent" value="-1" type="button"></button>'
				+ '<button class="ql-indent" value="+1" type="button"></button></span>'
				+ '<span class="ql-formats"><select class="ql-align">'
				+ '<option selected></option><option value="center"></option><option value="right"></option><option value="justify"></option>'
				+ '</select></span>';
		}
		html += '<span class="ql-formats"><select class="ql-color"></select><select class="ql-background"></select></span>'
			+ '<span class="ql-formats"><button class="ql-link" type="button"></button>';
		if (full)
		{
			html += '<button class="ql-blockquote" type="button"></button>'
				+ '<button class="ql-code-block" type="button"></button>'
				+ '<button class="ql-image" type="button"></button>'
				+ '<button class="fc-attachment" title="插入附件" aria-label="插入附件" type="button"><svg viewBox="0 0 18 18"><path class="ql-stroke" d="M10 2H6.5A1.5 1.5 0 0 0 5 3.5v11A1.5 1.5 0 0 0 6.5 16h6a1.5 1.5 0 0 0 1.5-1.5V6l-4-4z"/><path class="ql-stroke" d="M10 2v4h4"/></svg></button>';
		}
		html += '<button class="ql-clean" type="button"></button></span>'
			+ '<span class="ql-formats"><button class="fc-src" title="HTML 源码" aria-label="HTML 源码" type="button"><svg viewBox="0 0 18 18"><path class="ql-stroke" d="M7 6.5L3.5 10 7 13.5M11 6.5L14.5 10 11 13.5"/></svg></button></span>';
		bar.html(html);

		wrap.append(bar).append(box);
		target.after(wrap).hide();
		if (!full) wrap.addClass('fc-quill-s');

		var quill = new Quill(box[0], {theme:'snow', modules:{toolbar:{container:bar[0]}}, placeholder:''});

		var init = target.val();
		if (init) quill.clipboard.dangerouslyPasteHTML(init);

		var source = $('<textarea class="fc-quill-source"></textarea>');
		box.append(source);
		var srcOn = false;

		//工具栏悬停提示（Quill 默认不提供 title）
		var tips = {'bold':'加粗','italic':'斜体','underline':'下划线','strike':'删除线','list':'列表','indent':'缩进','blockquote':'引用','code-block':'代码块','link':'链接','image':'插入图片','clean':'清除格式','header':'标题','align':'对齐','color':'文字颜色','background':'背景颜色'};
		var tipKey = function(cls)
		{
			cls = cls.split(/\s+/);
			for (var i = 0; i < cls.length; i++)
			{
				var k = cls[i].replace(/^ql-/, '');
				if (tips[k]) return tips[k];
			}
			return '';
		};
		bar.find('button').each(function()
		{
			if (!this.title) this.title = tipKey(this.className);
		});
		bar.find('.ql-picker').each(function()
		{
			var tip = tipKey(this.className.replace(/ql-(expanded|picker)\s*/g, ''));
			if (tip) $(this).find('.ql-picker-label').attr('title', tip);
		});

		var Delta = Quill.import('delta');
		var api =
		{
			quill: quill,
			//在光标处插入HTML片段（弹层打开前记录的位置），保留已有正文
			insertHtml: function(str)
			{
				if (!str) return;
				var index = typeof api.__insertIndex == 'number' ? api.__insertIndex : quill.getLength();
				//当前 quill 版本的 convert 取 {html:...}，旧版取字符串，两者都兼容
				var delta = quill.clipboard.convert({html: str});
				if (!delta || !delta.ops || !delta.ops.length) delta = quill.clipboard.convert(str);
				quill.focus();
				quill.updateContents(new Delta().retain(index).concat(delta), 'user');
				quill.setSelection(index + delta.length(), 0);
				api.__insertIndex = index + delta.length();
			},
			html: function()
			{
				return srcOn ? source.val() : quill.root.innerHTML;
			},
			setHtml: function(str)
			{
				quill.setContents([]);
				if (str) quill.clipboard.dangerouslyPasteHTML(str);
			},
			sync: function()
			{
				target.val(api.html());
			},
			focus: function()
			{
				quill.focus();
			}
		};

		//插入图片与附件：复用后台上传弹窗
		//弹层会抢走焦点导致 getSelection 失效，需在打开前记录插入位置
		var rememberIndex = function()
		{
			var range = quill.getSelection(true);
			api.__insertIndex = range ? range.index : quill.getLength();
		};
		quill.getModule('toolbar').addHandler('image', function()
		{
			rememberIndex();
			$$.filesUploadLayer.open({url:cache.url, type:'image', callback:'$$.filesUploadLayer.callbackEditor', editor:api});
		});
		bar.on('click', '.fc-attachment', function()
		{
			rememberIndex();
			$$.filesUploadLayer.open({url:cache.url, type:'file', callback:'$$.filesUploadLayer.callbackEditor', editor:api});
		});

		//HTML 源码切换
		bar.on('click', '.fc-src', function()
		{
			var btn = $(this);
			if (!srcOn)
			{
				source.val(quill.root.innerHTML).show();
				quill.root.style.display = 'none';
			}
			else
			{
				api.setHtml(source.val());
				source.hide();
				quill.root.style.display = '';
			}
			srcOn = !srcOn;
			btn.toggleClass('ql-active', srcOn);
		});

		return api;
	};
	
	$$.tooltip = function(selector)
	{
		//将后面的Easy Tooltop封装到这里来
	};
	
	window.$$ = $$;
	
})(jQuery);

/**
 * Easy Tooltip 1.0 - jQuery plugin
 * written by Alen Grakalic	
 * http://cssglobe.com/post/4380/easy-tooltip--jquery-plugin
 *
 * Copyright (c) 2009 Alen Grakalic (http://cssglobe.com)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * Edit by Alan
 * http://www.fcontex.com
 *
 * Built for jQuery library
 * http://jquery.com
 **/
 
(function($){$.fn.easyTooltip = function(options)
{
	var defaults =
	{	
		xOffset: 20,		
		yOffset: 20,
		tooltipId: "easyTooltip",
		clickRemove: false,
		content: "",
		useElement: ""
	};
	
	var options = $.extend(defaults, options); 
	var content;
	
	this.each(function()
	{
		var title = $(this).attr("title");
		$(this).hover(function(e)
		{
			content = (options.content != "") ? options.content : title;
			content = (options.useElement != "") ? $("#" + options.useElement).html() : content;
			$(this).attr("title","");
			if (content != "" && content != undefined)
			{
				$("body").append("<div id='"+ options.tooltipId +"'>"+ content +"</div>");
				$("#" + options.tooltipId)
					.css("position","absolute")
					.css("top",(e.pageY - options.yOffset) + "px")
					.css("left",(e.pageX + options.xOffset) + "px")
					.css("display","none")
					.fadeIn("fast");
			}
		}, function()
		{
			$("#" + options.tooltipId).remove();
			$(this).attr("title",title);
		});
		$(this).mousemove(function(e)
		{
			$("#" + options.tooltipId)
				.css("top",(e.pageY - options.yOffset) + "px")
				.css("left",(e.pageX + options.xOffset) + "px");
		});
		if(options.clickRemove)
		{
			$(this).mousedown(function(e)
			{
				$("#" + options.tooltipId).remove();
				$(this).attr("title",title);
			});
		}
	});
	
}})(jQuery);
