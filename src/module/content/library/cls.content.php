<?php
/***
 * 名称：内容模块类
 * Joe 2012.11
 * www.fcontex.com
 * 读取一律通过查询构造器，本类不对外暴露SQL片段
*/

//内容信息类型（原定义于startup 随内容模块可拆卸归属模块自身）
if (!defined('CONTENT_PAGE'))
{
	define('CONTENT_PAGE', 0);
	define('CONTENT_MISC', 1);
	define('CONTENT_TEXT', 2);
	define('CONTENT_PHOTO', 3);
	define('CONTENT_MUSIC', 4);
	define('CONTENT_VIDEO', 5);
}

//主题目录（原定义于startup 主题是content模块的展示定制能力 随模块可拆卸）
//当前主题取模块配置content.settings.php的theme 换主题入口见content模块fc.settings
if (!defined('DIR_THEMES'))
{
	$_content = FCApplication::sharedApplication()->loadConfig('content.settings');
	define('DIR_THEMES', 'themes');
	define('PATH_THEMES', PATH_MODULES.'content/'.DIR_THEMES.'/');
	define('DIR_THEME', isset($_content['theme']) && $_content['theme'] ? $_content['theme'] : 'default');
	define('PATH_THEME', PATH_THEMES.DIR_THEME.'/');
	define('URL_THEMES', URL_MODULES.'content/'.DIR_THEMES);
	define('URL_THEME', URL_THEMES.'/'.DIR_THEME.'/');
	unset($_content);
}

final class content
{
	public $app;
	
	public function __construct()
	{
		$this->app = FCApplication::sharedApplication();
	}
	
	//调用内容列表（不含单页）
	public function getContents($cid = 0, $limit = 0)
	{
		$Q = DB::table('content')
		  ->where('ct_check = 1')
		  ->where('ct_type > ?', array(CONTENT_PAGE));
		if ($cid > 0) $Q->where('ct_cid = ?', array(intval($cid)));
		$Q->order('ct_fixed', 'desc')->order('ct_inserttime', 'desc');
		if ($limit > 0) $Q->limit(intval($limit));
		return $Q->rows();
	}
	
	//调用单篇内容
	public function getContent($id)
	{
		return DB::table('content')->where('ct_id = ?', array(intval($id)))->row();
	}
	
	//调用指定日志标签
	public function getContentTags($id)
	{
		return DB::table('tags')->where('tg_cid like ?', array('%,'.intval($id).',%'))->rows();
	}
	
	//调用标签
	public function getTags($limit = 0)
	{
		$Q = DB::table('tags')->order('tg_id', 'desc');
		if ($limit > 0) $Q->limit(intval($limit));
		return $Q->rows();
	}
	
	//调用分类
	public function getCategories($pid = -1)
	{
		$Q = DB::table('category')->where('cg_show = 1');
		if ($pid != -1) $Q->where('cg_pid = ?', array(intval($pid)));
		$Q->order('cg_order', 'asc')->order('cg_id', 'asc');
		return $Q->rows();
	}
	
	//调用单条分类信息
	public function getCategory($id)
	{
		return DB::table('category')->where('cg_id = ?', array(intval($id)))->row();
	}
	
	//获取指定用户信息
	public function getUser($uid, $find = 'us_name, us_username, us_id')
	{
		return DB::table('user')->field($find)->where('us_id = ?', array(intval($uid)))->row();
	}
	
	//获取导航
	public function getNavigate()
	{
		return DB::table('navigate')->where('nv_check = 1')->order('nv_order', 'asc')->rows();
	}

	//以当前主题渲染模板（模板根指向主题目录 同时向模板注入内容对象$C）
	public function display($file)
	{
		$T = FCApplication::sharedTemplate();
		$T->root = PATH_THEME;
		$T->bind('C', $this);
		$T->show($file);
	}
}
?>
