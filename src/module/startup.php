<?php
/***
 * 名称：全局应用程序启动文件
 * Alan, 2012.03
 * http://www.fcontex.com/
*/

//程序错误开关	
ini_set('display_errors', 1);

//输出过滤开关
//ini_set('magic_quotes_runtime', 0);

//全局属性
define('SYSTEM_INCLUDE', 'YES');
define('SYSTEM_NAME', 'FContex Admin');
define('SYSTEM_VERSION', '1.1.8');
//系统模块（不可禁用） content/plugin等内容型模块已可拆卸
define('SYSTEM_MODULE', 'SYSTEM|MEMBER');
define('ERROR_NO_RIGHTS', '没有操作权限。');

//物理根目录
define('PATH_ROOT', dirname(__FILE__).'/../');
//读写存储区
define('DIR_STORE', 'store');
define('PATH_STORE', PATH_ROOT.DIR_STORE.'/');
//配置存储区
define('DIR_CONFIG', 'config');
define('PATH_CONFIG', PATH_STORE.DIR_CONFIG.'/');
//缓存目录
define('DIR_CACHE', 'cache');
define('PATH_CACHE', PATH_STORE.DIR_CACHE.'/');
//模块目录
define('DIR_MODULES', 'module');
define('PATH_MODULES', PATH_ROOT.DIR_MODULES.'/');
//脚本目录
define('DIR_SCRIPTS', 'scripts');
define('PATH_SCRIPTS', PATH_MODULES.'system/'.DIR_SCRIPTS);
//工具目录
define('DIR_TOOLS', 'tools');
define('PATH_TOOLS', PATH_MODULES.'system/'.DIR_TOOLS);

//字符集
define('CHARSET', 'utf-8');
//session_save_path(PATH_CACHE.'session/');
//会话Cookie安全属性（SameSite需PHP7.3+ 低版本该项不生效 由浏览器默认策略兜底）
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ini_set('session.cookie_secure', 1);
session_start();
header('Cache-Control: private');
header('Content-Type: text/html;charset='.CHARSET);
date_default_timezone_set('PRC');

include PATH_MODULES.'system/library/cls.application.php';

//预加载数据库层（DB门面与驱动定义，仅include不建立连接）
FCApplication::loadLibrary('database', NULL, FALSE);
//预加载密码散列封装
FCApplication::loadLibrary('password', NULL, FALSE);

//全局公共对象
$A = FCApplication::sharedApplication();

//CSRF令牌初始化与后台AJAX写请求校验
$A->csrfInit();
$A->csrfCheck();

//数据库可用性守卫（配置缺失跳转安装向导 连接失败给出提示）
$dbReady = FCApplication::requireDatabase();

$U = FCApplication::sharedMember();
$D = $dbReady ? FCApplication::sharedDataBase() : NULL;
$T = FCApplication::sharedTemplate();
$R = FCApplication::sharedRouter();
//内容对象$C由content模块及主题自行加载（FCApplication::loadLibrary('content')）

//网站根目录
define('DIR_SITE', $A->getRootDirectory());
//当前模块
define('DIR_MODULE', $R->module);
define('PATH_MODULE', PATH_ROOT.DIR_MODULES.'/'.DIR_MODULE.'/');
//主题目录及当前主题常量随 cls.content.php 加载（主题是content模块的展示定制能力）

//网站根URL
define('URL_SITE', DIR_SITE);
//皮肤URL（三级回落：用户自选 us_skin → 手工配置 skin_default → 内核兜底 default 不依赖数据库 安装态同样可用）
define('URL_SKIN', $U->getSkin());
//当前皮肤名称（第三方控件按皮肤目录提供多套配色时，用它去匹配同名皮肤）
define('SKIN_NAME', basename(rtrim(URL_SKIN, '/')));
//日期控件皮肤（仅当工具目录下存在同名皮肤时跟随当前皮肤，否则回落 default）
define('DATE_SKIN', is_dir(PATH_TOOLS.'/datepicker/skin/'.SKIN_NAME) ? SKIN_NAME : 'default');
//模块根URL
define('URL_MODULES', URL_SITE.DIR_MODULES.'/');
//当前模块URL
define('URL_MODULE', URL_SITE.DIR_MODULES.'/'.DIR_MODULE.'/');
//脚本URL
define('URL_SCRIPTS', URL_MODULES.'system/'.DIR_SCRIPTS.'/');
//工具URL
define('URL_TOOLS', URL_MODULES.'system/'.DIR_TOOLS.'/');
//静态资源版本号（内核脚本与皮肤样式的最新修改时间）
define('ASSET_VERSION', FCApplication::assetVersion());
?>