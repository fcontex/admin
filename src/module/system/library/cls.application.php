<?php
/***
 * 名称：应用程序类
 * Alan, 2012.03
 * http://www.fcontex.com/
*/
 
final class FCApplication
{
	//编码模式 UTF-8/GBK
	public $charset = CHARSET;
	
	//页面开始运行时的微秒级时间戳
	private $startTime;
	
	private $tableEnCode = NULL;
	private $tableDeCode = NULL;
	
	//单件模式实例
	private static $instance = NULL;
	
	//用户对象实例
	private static $member = NULL;
	
	//数据对象实例
	private static $database = NULL;
	
	//模板对象实例
	private static $template = NULL;
	
	//路由对象实例
	private static $router = NULL;

	//翻页对象实例
	private static $turnner = NULL;
	
	//免CSRF校验的接口（登录前可调用的写接口）
	private $csrfExcept = array('fc.user.login', 'fc.user.fetchcode', 'fc.user.resetcode');
	
	//系统配置
	public $system;
	
	//站点配置
	public $site;
	
	//构造
	public function __construct()
	{
		//强制工厂单件模式
		if (self::$instance)
		{
			exit('Error: Call FCApplication::sharedApplication() to get a instance.');
		}
		else
		{
			$this->startTime = microtime(TRUE);
		}
		
		if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
		{
			$_POST   = $this->stripslashes($_POST);
			$_GET    = $this->stripslashes($_GET);
			$_COOKIE = $this->stripslashes($_COOKIE);
		}
		
		//可逆加密字典
		$arrEnCode = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=');
		$arrDeCode = str_split('MNOPQRSTUVWXYZlmnopqrstuvwxyz~_-0123456789ABCDEFGHIJKLabcdefghijk');
		$this->tableEnCode = array_combine($arrEnCode, $arrDeCode);
		$this->tableDeCode = array_combine($arrDeCode, $arrEnCode);
		
		//加载全局配置
		$this->system = $this->loadConfig('system.settings');
		$this->site   = $this->loadConfig('system.site');
		
		//登记单件实例 直接 new 也无法绕过单件约束
		self::$instance = $this;
	}
	
	//字符串加密
	public function strEnCode($str)
	{
		$encode = '';
		$str = base64_encode($str);
		$end = strlen($str) - 1;
		$dot = ($end+1) / 2 - 1;
		
		for ($i=$dot,$j=$end; $i>=0; $i--,$j--)
		{
			$encode .= $this->tableEnCode[$str[$i]] . $this->tableEnCode[$str[$j]];
		}
		
		return $encode;
	}
	
	//字符串解密
	public function strDeCode($str)
	{
		$decodeA = '';
		$decodeB = '';
		$end = strlen($str) - 1;
		
		for ($i=$end; $i>0;)
		{
			$decodeB .= $this->tableDeCode[$str[$i--]];
			$decodeA .= $this->tableDeCode[$str[$i--]];
		}
		
		return base64_decode($decodeA.$decodeB);
	}
	
	//单件模式实例化接口
	public static function sharedApplication()
	{
		if (!self::$instance)
		{
			self::$instance = new FCApplication();
		}
		
		return self::$instance;
	}
	
	//共享用户对象实例化接口
	public static function sharedMember()
	{
		if (!self::$member)
		{
			self::$member = self::loadLibrary('member');
		}
		
		return self::$member;
	}
	
	//共享数据对象实例化接口
	public static function sharedDataBase()
	{
		if (!self::$database)
		{
			self::loadLibrary('database', NULL, FALSE);
			$config = self::loadConfig('system.database');
			//配置缺失时不建立连接 由启动文件引导至安装向导
			if (!is_array($config) || empty($config['DB_BASE']))
			{
				return NULL;
			}
			$driver = new DBDriverPdo();
			$driver->charset = isset($config['DB_CHAR']) ? $config['DB_CHAR'] : 'utf8mb4';
			$driver->hold = isset($config['DB_HOLD']) ? $config['DB_HOLD'] : TRUE;
			$driver->connect($config['DB_BASE'], $config['DB_USER'], $config['DB_PASS'], $config['DB_HOST']);
			//连接失败不缓存 由启动文件给出可读提示
			if (!$driver->connected())
			{
				return NULL;
			}
			self::$database = $driver;
		}
		return self::$database;
	}
	
	//数据库可用性守卫：配置缺失时引导至安装向导 配置存在但连接失败时给出提示
	//安装程序自行处理连接错误 不做拦截
	public static function requireDatabase()
	{
		$script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
		$inInstall = strpos($script, '/install/') !== FALSE;
		
		$config = self::loadConfig('system.database');
		if (!is_array($config) || empty($config['DB_BASE']))
		{
			if (!$inInstall)
			{
				header('Location: /install/');
				exit;
			}
			return FALSE;
		}
		
		if (!$inInstall && !self::sharedDataBase())
		{
			self::connectionError();
		}
		return TRUE;
	}
	
	//数据库连接失败提示
	private static function connectionError()
	{
		exit('<div style="margin:80px auto;width:560px;padding:20px 24px;border:1px solid #E3E6EB;border-radius:6px;background:#fff;font:14px/1.8 Arial,\'Microsoft YaHei\';color:#C0392B;">数据库连接失败，请检查 store/config/system.database.php 中的连接参数。</div>');
	}
	
	//查询构造器实例化接口
	//每次返回新实例（复用共享连接）避免链式状态在嵌套调用间相互污染
	public static function query()
	{
		self::loadLibrary('query', NULL, FALSE);
		return new query();
	}
	
	//共享模板对象实例化接口
	public static function sharedTemplate()
	{
		if (!self::$template)
		{
			self::$template = self::loadLibrary('template');
		}
		
		return self::$template;
	}
	
	//共享路由对象实例化接口
	public static function sharedRouter()
	{
		if (!self::$router)
		{
			self::$router = self::loadLibrary('router');
		}
		
		return self::$router;
	}

	//共享翻页对象实例化接口
	public static function sharedPageTurnner()
	{
		if (!self::$turnner)
		{
			self::$turnner = self::loadLibrary('pageturnner');
		}
		
		return self::$turnner;
	}
	
	/***
	 * 系统基础接口
	*/
	
	//返回从页面开始执行到当前的时间差
	public function processTime($point=6)
	{
		return round(microtime(TRUE) - $this->startTime, $point);
	}
	
	//加载配置
	public static function loadConfig($file, $key='', $default='', $reload=FALSE)
	{
		$path = PATH_CONFIG.$file.'.php';
		static $data = array();
		if ($reload && isset($data[$file]))
		{
			unset($data[$file]);
		}
		if (isset($data[$file]))
		{
			if (empty($key)) 
			{
				return $data[$file];
			} 
			elseif (isset($data[$file][$key])) 
			{
				return $data[$file][$key];
			}
			else return $default;
		}
		if (file_exists($path))
		{
			$data[$file] = include $path;
			if (empty($key))
			{
				return $data[$file];
			}
			elseif (isset($data[$file][$key]))
			{
				return $data[$file][$key];
			}
		}
		return $default;
	}
	
	//保存配置
	public static function saveConfig($config, $data)
	{
		$content = "<?php\n/*自动化配置文件*/\nreturn ".var_export($data, TRUE).";\n?>";
		file_put_contents(PATH_CONFIG.$config.'.php', $content);

		//写入后刷新缓存 保证本次请求后续读取到新配置
		self::loadConfig($config, '', '', TRUE);

		return TRUE;
	}

	//重置共享数据对象（更新数据库配置后使用 如安装向导）
	public static function resetDataBase()
	{
		self::$database = NULL;
	}
	
	//静态资源版本号：内核公共脚本与各皮肤样式的最新修改时间 用于刷新浏览器缓存
	public static function assetVersion()
	{
		$time = 0;
		$files = glob(PATH_MODULES.'system/skins/*/style.css');
		if (!is_array($files)) $files = array();
		$files[] = PATH_SCRIPTS.'/lib.system.js';
		foreach ($files as $file)
		{
			if (is_file($file)) $time = max($time, filemtime($file));
		}
		return SYSTEM_VERSION.($time ? '.'.$time : '');
	}

	//安装模块数据表（读取模块目录下的install.mysql.sql）
	//建表语句使用CREATE TABLE IF NOT EXISTS 已存在的表自动跳过
	//实现卸载模块保留数据、重新安装不丢数据
	public function installTables($module)
	{
		$config = self::loadConfig('system.database');
		$path = PATH_MODULES.$module.'/install.mysql.sql';
		if (!file_exists($path)) return TRUE;

		//替换表前缀（前缀仅允许字母数字下划线 防止DDL注入）
		$pfix = isset($config['DB_PFIX']) && $config['DB_PFIX'] != '' ? $config['DB_PFIX'] : 'fc_';
		if (!preg_match('/^[A-Za-z0-9_]+$/', $pfix))
		{
			throw new Exception('非法的数据表前缀：'.$pfix);
		}
		$sql = str_replace('fc_', $pfix, file_get_contents($path));

		//逐条执行建表语句（行首锚定 避免误匹配注释中的字样）
		$db = self::sharedDataBase();
		preg_match_all('/^CREATE TABLE[^;]+;/im', $sql, $matches);
		foreach ($matches[0] as $statement)
		{
			$db->exec($statement);
		}

		return TRUE;
	}

	//加载类库
	public static function loadLibrary($library, $module=NULL, $init=TRUE)
	{
		//实例缓存与include记录分开：同一类库可能先只引入、后取实例
		static $class = array();
		static $loaded = array();
		if ($module)
		{
			$path = PATH_MODULES.$module.'/library/cls.'.$library.'.php';
		}
		else
		{
			$path = PATH_MODULES.'system/library/cls.'.$library.'.php';
			
			if (!file_exists($path))
			{
				$path = PATH_MODULES.$library.'/library/cls.'.$library.'.php';
			}
		}
		$key = md5($path);
		$cache = $key.($init ? ':instance' : ':include');
		if (isset($class[$cache]))
		{
			return $class[$cache];
		}
		if (file_exists($path))
		{
			//文件只引入一次 避免重复声明类
			if (!isset($loaded[$key]))
			{
				include $path;
				$loaded[$key] = TRUE;
			}
			$class[$cache] = ($init && class_exists($library)) ? new $library : FALSE;
			return $class[$cache];
		}
		return FALSE;
	}
	
	public function pageError($tips='')
	{
		include PATH_MODULES.'system/template/error.php';
	}
	
	/***
	 * 字符串处理接口
	*/
	
	//获取GET参数值（返回原始值 SQL安全由参数绑定保证）
	public function strGet($key, $default='')
	{
		return isset($_GET[$key]) ? $_GET[$key] : $default;
	}
	
	//获取POST参数值（返回原始值 SQL安全由参数绑定保证）
	public function strPost($key, $default='')
	{
		return isset($_POST[$key]) ? $_POST[$key] : $default;
	}
	
	//递归转义数组字符串
	public function addslashes($str) 
	{
		if (is_array($str)) 
		{
			foreach ($str as $key => $val) 
			{
				$str[$key] = $this->addslashes($val);
			}
		} 
		else 
		{
			$str = addslashes($str);
		}
		return $str;
	}
	
	//递归清除转义
	public function stripslashes($str) 
	{
		if (is_array($str)) 
		{
			foreach ($str as $key => $val) 
			{
				$str[$key] = $this->stripslashes($val);
			}
		} 
		else 
		{
			$str = stripslashes($str);
		}
		return $str;
	}
	
	//按宽度截取字符串(utf-8)
	public function strLeft($str, $width, $ext='')
	{
		if (strtolower($this->charset) == 'gbk')
		{
			return $this->strLeftGbk($str, $width, $ext);
		}
		else
		{
			return $this->strLeftUtf8($str, $width, $ext);
		}
	}
	
	private function strLeftUtf8($str, $width, $ext='')
	{
		$ret = '';
		$i = 0; //字节计数器
		$n = 0; //宽度计数器
		
		$len = strlen($str);
		
		while ($i<=$len && $n<$width)
		{
			$asc = ord(substr($str, $i, 1));
			
			//utf-8核心规则：首字节高位连续1的个数
			//表示该字符utf-8编码所需要的字节数
			
			if ($asc >= 224)
			{//1110 0000
				$ret .= substr($str, $i, 3);
				$i += 3; //字节滚动3
				$n += 2; //宽度滚动2
			}
			elseif ($asc >= 192)
			{//1100 0000
				$ret .= substr($str, $i, 2);
				$i += 2; //字节滚动2
				$n += 2; //宽度滚动2
			}
			/*elseif ($asc>=65 && $asc<=90)
			{//大写字母
				$ret .= substr($str, $i, 1);
				$i += 1; //字节滚动1
				$n += 2; //宽度滚动2
			}*/
			else
			{//其它半角
				$ret .= substr($str, $i, 1);
				$i += 1; //字节滚动1
				$n += 1; //宽度滚动1
			}
		}
		
		//超出宽度才加后缀
		if ($n >= $width) $ret .= $ext;
		
		return $ret;
	}
	
	private function strLeftGbk($str, $width, $ext='')
	{
		return $str;
	}
	
	//文件名后辍
	public function fileSuffix($files)
	{
		return strtolower(substr(strrchr($files, '.'), 1));
	}
    
    //是否为图片 读uploadSuffix.image组判定（组内须为GD可处理的栅格格式 svg/avif等加入前须确认缩略图支持）
    public function isImage($fileSuffix)
    {
        $image = isset($this->system['uploadSuffix']['image']) ? $this->system['uploadSuffix']['image'] : '';
        return in_array(strtolower($fileSuffix), explode(',', $image));
    }
	
	//获取缩略图
	public function getThumb($pic, $w=0, $h=0, $back='ffffff', $qual=90)
	{
		if (empty($pic)) return '';
		if (substr($pic, 0, 4) == 'http') return $pic;
		
		//返回原图
		if ($w==0 && $h==0) return URL_SITE.DIR_STORE.'/'.$this->system['uploadDir'].'/'.$pic;
		
		//检查 GD 扩展是否可用
		if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor'))
		{
			// GD 不可用，返回原图
			return URL_SITE.DIR_STORE.'/'.$this->system['uploadDir'].'/'.$pic;
		}
		
		//缩略图目录/路径/名称
		$thumbDir   = 'thumb/';
		$thumbPath  = PATH_CACHE.$thumbDir;
		if (!file_exists($thumbPath)) mkdir($thumbPath);
		$thumbDir  .= $w.'x'.$h.'/';
		$thumbPath  = PATH_CACHE.$thumbDir;
		if (!file_exists($thumbPath)) mkdir($thumbPath);
		$thumbName  = basename($pic);
		
		//缩略图不存在则创建
		if (!file_exists($thumbPath.$thumbName))
		{
			$clsPic = self::loadLibrary('picdeal');
			$clsPic->picPath = PATH_STORE.$this->system['uploadDir'].'/'.$pic;
			$clsPic->picNewPath = $thumbPath.$thumbName;
			if ($w && $h==0)
			{
				list($width, $height, $type, $attr) = getimagesize($clsPic->picPath);
				$h = ceil($w / $width * $height);
			}
			$clsPic->thumb($w, $h, $back, $qual);
		}
		
		//返回缩略图完整URL
		return URL_SITE.DIR_STORE.'/cache/'.$thumbDir.$thumbName;
	}
	
	//根据后辍返回配置文件中的类型
	public function suffixType($fileName='')
	{
		$suffix = $this->fileSuffix($fileName);
		$config = $this->system['uploadSuffix'];
		foreach ($config as $key => $v)
		{
			if (in_array($suffix, explode(',', $v)))
			{
				return $key;
			}	
		}
		return 'file';
	}
	
    //根据后缀返回文件图标
    public function fileIcon($suffix)
	{
		switch ($suffix)
		{
			case 'rar':case 'zip':
			case 'tar':case '7z':case 'gz':case 'tgz':
				$icon = 'file_zip.png';
				break;
			case 'doc':case 'docx':
				$icon = 'file_doc.png';
				break;
			case 'ppt':case 'pptx':
				$icon = 'file_ppt.png';
				break;
			case 'xls':case 'xlsx':
				$icon = 'file_xls.png';
				break;
			case 'bmp':case 'png':case 'gif':case 'webp':
			case 'jpg':case 'jpeg':
				$icon = 'file_bmp.png';
				break;
			case 'mp3':case 'wav':case 'm4a':case 'ogg':case 'flac':
			case 'mp4':case 'webm':case 'mov':
				$icon = 'file_mp3.png';
				break;
			case 'txt':case 'log':case 'md':case 'csv':
				$icon = 'file_txt.png';
				break;
			default:
				$icon = 'file_unknown.png';
				break;
		}
		return URL_SKIN.$icon;
	}

	//根据当前皮肤返回模块图标URL{1:模块icons/<皮肤>/稀疏覆盖 → 2:模块icons/基线 → 3:皮肤兜底图}
	//模块icons/根目录是default皮肤的全套+所有皮肤的总兜底 必须永远完整
	//icons/<皮肤>/子目录只放与根目录不同的图标 可以缺 缺的自动回落根目录
	public function moduleIcon($module, $file)
	{
		//皮肤名白名单 防止路径注入（配置值可手工编辑 可能含../等脏值）
		$skin = basename(rtrim(URL_SKIN, '/'));
		if (!preg_match('/^[a-z0-9_-]+$/i', $skin)) $skin = '';
		//每模块+皮肤只扫一次盘 结果缓存进静态数组 避免逐图标file_exists的磁盘IO
		static $dirs = array();
		$key = $module.'@'.$skin;
		if (!array_key_exists($key, $dirs))
		{
			$files = array();
			if ($skin != '')
			{
				$list = @scandir(PATH_MODULES.$module.'/icons/'.$skin.'/');
				if (is_array($list))
				{
					//空目录容错：过滤.和..后视为"无覆盖" 不误判成"已覆盖但图片全丢"
					$list = array_diff($list, array('.', '..'));
					foreach ($list as $name) $files[$name] = PATH_MODULES.$module.'/icons/'.$skin.'/'.$name;
				}
			}
			$dirs[$key] = $files;
		}
		//命中皮肤覆盖目录
		if (isset($dirs[$key][$file]))
		{
			return URL_MODULES.$module.'/icons/'.$skin.'/'.$file.'?v='.filemtime($dirs[$key][$file]);
		}
		//回落模块基线目录
		if (is_file(PATH_MODULES.$module.'/icons/'.$file))
		{
			return URL_MODULES.$module.'/icons/'.$file.'?v='.filemtime(PATH_MODULES.$module.'/icons/'.$file);
		}
		//全局兜底
		return URL_SKIN.'module.default.png';
	}

	//枚举皮肤{目录名 => skin.php描述} 识别方式为目录里是否存在skin.php
	//供皮肤列表页、图标补齐检查等复用 取代散落在模板里的opendir逻辑
	public function listSkins()
	{
		static $skins = NULL;
		if (is_array($skins)) return $skins;
		$skins = array();
		$root = PATH_MODULES.'system/skins/';
		$handle = @opendir($root);
		if ($handle)
		{
			while (($name = readdir($handle)) !== false)
			{
				if (!is_file($root.$name.'/skin.php')) continue;
				$skins[$name] = include $root.$name.'/skin.php';
			}
			closedir($handle);
		}
		ksort($skins);
		return $skins;
	}
	
	//返回用户IP
	public function getUserIP()
	{
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown'))
		{
			$ip = getenv('HTTP_CLIENT_IP');
		} 
		elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) 
		{
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		} 
		elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) 
		{
			$ip = getenv('REMOTE_ADDR');
		} 
		elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) 
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
	}
	
	//cookie设置
	public function setCookie($var, $value, $life=0, $httponly=TRUE) 
	{
		setcookie($this->system['cookie_pre'].$var, $value, $life?$life:$this->system['cookie_ttl'], $this->system['cookie_path'], $this->system['cookie_domain'], $_SERVER['SERVER_PORT']==443?1:0, $httponly);
	}
	
	//cookie获取
	public function getCookie($var) 
	{
		return isset($_COOKIE[$this->system['cookie_pre'].$var]) ? $_COOKIE[$this->system['cookie_pre'].$var] : '';
	}
	
	//CSRF令牌初始化：令牌保存在会话中（不轮换 避免多窗口并发互踢）并下发同名可读Cookie供前端回传
	public function csrfInit()
	{
		if (empty($_SESSION['csrf_token']))
		{
			$_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(16));
		}
		setcookie('csrf_token', $_SESSION['csrf_token'], 0, '/');
	}
	
	//CSRF校验：仅校验后台AJAX动作 页面导航类请求与未登录场景所需接口放行
	public function csrfCheck()
	{
		$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
		$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
		if (!$isAjax || strpos($mode, 'fc.') !== 0 || in_array($mode, $this->csrfExcept))
		{
			return;
		}
		$csrf = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '';
		if ($csrf === '' || hash('sha256', $csrf) !== hash('sha256', $_SESSION['csrf_token']))
		{
			echo 'ERR|会话校验失败，请刷新页面后重试。';
			exit;
		}
	}
	
	//返回根目录
	public function getRootDirectory()
	{
		/*//获取本文件路径
		$path = str_replace('\\', '/', dirname(__FILE__));
		//获取虚拟根路径
		$root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
		//从完整路径中替换掉根目录路径
		$path = str_ireplace($root, '', $path);
		//替换掉本文件目录
		return str_replace(DIR_MODULES.'/system/library', '', $path[0]=='/' ? $path : '/'.$path);*/
		return '/';
	}
	
	//清空目录 危险函数请勿滥用
	public function cleanDirectory($dir, $del=FALSE)
	{
		if(!is_dir($dir)) return FALSE;
		$res = TRUE;
		
		$handle = opendir($dir);
		while (($name=readdir($handle)) !== FALSE)
		{
			if ($name == "." || $name == "..") continue;
			
			$name = $dir.'/'.$name;
			if (is_dir($name))
			{
				if (!$this->cleanDirectory($name, TRUE))
				{
					$res = FALSE;
				}
			}
			elseif (is_file($name))
			{
				if (!@unlink($name))
				{
					$res = FALSE;
				}
			}
		}
		closedir($handle);
		if ($del && !@rmdir($dir))
		{
			$res = FALSE;
		}
		
		return $res;
	}
	
	//清除连续空白及HTML标签
	public function trim($str, $strip=FALSE)
	{
		return preg_replace('/[\s]+/i', '', $strip?strip_tags($str):$str);
	}
	
	/**
	* 转换字节数为其他单位
	* @param	string	$filesize	字节大小
	* @return	string	返回大小
	*/
	public function transSize($filesize)
	{
		if ($filesize >= 1073741824)
		{
			$filesize = round($filesize / 1073741824 * 100) / 100 .' GB';
		} 
		elseif ($filesize >= 1048576)
		{
			$filesize = round($filesize / 1048576 * 100) / 100 .' MB';
		}
		elseif ($filesize >= 1024)
		{
			$filesize = round($filesize / 1024 * 100) / 100 . ' KB';
		}
		else
		{
			$filesize = $filesize.' Bytes';
		}
		return $filesize;
	}
	
	//格式化时间为天， 超过3天显示日期
	public function transDate($date, $format='Y-m-d H:i:s')
	{
		if ($date >= strtotime(date("Y-m-d")))
		{
			return '今天 '.date('(H:i)', $date);
		}
		elseif ($date >= strtotime('-2 day'))
		{
			return '昨天 '.date('(H:i)', $date);
		}
		elseif ($date >= strtotime('-3 day'))
		{
			return '前天 '.date('(H:i)', $date);
		}
		else 
		{
			return date($format, $date);	
		}
	}
	
	//添加操作日志
	public function logInsert($event, $uid=0)
	{
		if ($uid==0 && isset($_SESSION['userInfo']))
		{
			$uid  =  $_SESSION['userInfo']['us_id'];
		}
		$time = time();
		$ip   = $this->getUserIP();
		//$iparea = $this->loadLibrary('iparea');
		//$area = $iparea->dataMini($ip);
		$insertarr = array
		(
			'lg_uid'	=> $uid,
			'lg_time'	=> $time,
			'lg_event'	=> $event,
			'lg_ip'		=> $ip
		);
		//if ($area && $area != 'Unknown') $insertarr['lg_iparea'] = $area;
		self::$database->insert('logs', $insertarr);
	}
}
?>