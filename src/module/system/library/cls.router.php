<?php
/***
 * 名称：URL路由分发类
 * Joe, Alan, 2012.11
 * http://www.fcontex.com/
*/
 
class router
{
	//URL Query
	public $query;
	
	private $app;
	
	//路由
	public $route;
	
	//参数
	private $params;
	
	//缓存
	public  $cacheopen;
	private $cachetime;
	
	//模块
	public $module;
	
	//动作
	public $action;

	//404控制
	private $in404;
	
	public function __construct()
	{
		$this->app = FCApplication::sharedApplication();
		
		$this->cacheopen = FALSE;
		$this->cachetime = $this->app->site['site_cachetime'];
		$this->query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
		$this->in404 = FALSE;
		
		$this->parse();
	}
	
	/***
	 * 请求解析
	*/
	public function parse($query='')
	{
		global $A;
		
		if (!$query)
		{
			$query = $this->query;
		}
		else
		{
			$this->query = $query;
		}
		
		$this->route = preg_replace(array('/&.+$/i', '/%26.+$/i', '/\.html$/i'), '', $this->query);  //%26=&
		
		$this->params = explode('/', $this->route);
		$this->module = $this->getParam(0);
		$this->action = $this->getParam(1,0);
		
		//默认模块（站点配置中可设置 用于处理空URL及短URL）
		$default = $this->getDefaultModule();
		if (empty($this->module)) $this->module = $default;
		if (empty($this->action)) $this->action = 'start';
		$modules = $A->loadConfig('system.modules');
		if (!isset($modules[$this->module]))
		{
			$this->action = $this->module;
			$this->module = $default;
		}
	}

	/***
	 * 返回默认模块
	 * 站点配置中未设置或模块未安装时回退为system
	*/
	public function getDefaultModule()
	{
		global $A;

		$default = isset($A->site['site_defaultmodule']) ? trim($A->site['site_defaultmodule']) : '';
		$modules = $A->loadConfig('system.modules');
		if ($default == '' || !isset($modules[$default])) $default = 'system';

		return $default;
	}
	
	/***
	 * 请求分发
	*/
	public function dispatch($query='')
	{
		$this->parse($query);
		
		//缓存控制
		$cache_path  = PATH_CACHE.'output/';
		if (!file_exists($cache_path)) mkdir($cache_path);
		$cache_path .= md5($this->query);
		if (!file_exists($cache_path) || ($this->cachetime>0 && time()-filemtime($cache_path)>=$this->cachetime))
		{
			//全局公共对象
			global $A, $U, $D, $T, $R;

			$output = '';

			//模块请求分发文件（路由只负责分发到模块 主题分发由content模块自行处理）
			$dispatcher = PATH_MODULES.$this->module.'/index.php';

			if (!file_exists($dispatcher))
			{
				$this->print404();
			}
			else
			{
				//得到输出缓冲
				ob_start();
				include $dispatcher;
				$output = ob_get_contents();
				ob_end_clean();
			}
			
			if (!empty($output))
			{
				//写入文件缓存
				if ($this->cacheopen)
				{
					file_put_contents($cache_path, $output);
				}
				$this->cacheopen = FALSE;
			}
		}
		else $output = file_get_contents($cache_path);
		
		echo($output);
	}

	//获取URL参数值
	public function getParam($m=0, $n=-1)
	{
		if (!isset($this->params[$m])) return '';
		$param = $this->params[$m];
		if ($n === -1) return $param;
		
		$param = explode('-', $param);
		return isset($param[$n]) ? $param[$n] : '';
	}
	
	public function getUrlPrefix()
	{
		return $this->app->site['site_rewrite'] ? '' : '?';
	}
	
	//返回URL路径
	public function getPageUrl($data='', $ext='', $prefix=URL_SITE)
	{
		if ($data == '/') return $prefix;
		if ($data == '*') return $prefix.$this->getUrlPrefix().$this->query;
		if ($data == '') return $prefix.$this->getUrlPrefix().$this->route;
		return $prefix.$this->getUrlPrefix().$data.$ext;
	}
	
	//返回请求处理路径
	public function getCtrlUrl($module='', $args='')
	{
		return URL_MODULES.($module ? $module : $this->module).'/control/'.($args ? '?'.$args : '');
	}
	
	//输出404头信息
	public function header404()
	{
		static $header = FALSE;
		
		if ($header) return;
		header('HTTP/1.1 404 Not Found');
		$header = TRUE;
	}
	
	//输出404错误
	public function print404()
	{
		if (!$this->in404)
		{
			$this->in404 = TRUE;
			$this->header404();
			$this->dispatch($this->getDefaultModule().'/404');
			exit();
		}
		else
		//404模板文件不存在
		{
			$this->header404();
			exit('<strong style="font-size:24px;font-variant:small-caps;">Error 404: Page Not Found.</strong>');
		}
	}
}
?>