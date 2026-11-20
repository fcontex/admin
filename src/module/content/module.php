<?php
/***
 * 名称：内容模块类
 * Alan 2012.03.06
 * www.fcontex.com
*/

//类命名方式必须是FCModule加首字段大写的目录名才会被内核识别
final class FCModuleContent
{
	/***
	 * 模块基本信息
	*/
	public $basic = array
	(
		'for'     => '1.0.1+',
		'name'    => '信息管理',
		'icon'    => 'module.png',
		'desc'    => '内容模块，提供文章栏目分类及内容管理功能。',
		'author'  => 'alan',
		'contact' => 'alan@fcontex.com',
		'version' => '1.0',
		'update'  => '2012.05',
		'support' => 'www.fcontex.com',
	);
	
	/***
	 * 模块权限字段 安装时注册到内核中
	 * 为保证全局唯一请使用模块目录名加下划线作前缀
	*/
	public $rights = array
	(
		'content.settings' => '参数配置',
		'content.category.select' => '栏目查看',
		'content.category.insert' => '栏目添加',
		'content.category.update' => '栏目编辑',
		'content.category.order'  => '栏目排序',
		'content.category.delete' => '栏目删除',
		'content.select' => '信息查看',
		'content.insert' => '信息添加',
		'content.update' => '信息编辑',
		'content.delete' => '信息删除',
		'content.page.select' => '页面查看',
		'content.page.insert' => '页面添加',
		'content.page.update' => '页面编辑',
		'content.page.delete' => '页面删除',
		'content.tags.select' => '标签查看',
		'content.tags.update' => '标签编辑',
		'content.tags.delete' => '标签删除',
		'content.navigate.select'  => '导航查看',
		'content.navigate.insert'  => '导航添加',
		'content.navigate.update'  => '导航修改',
		'content.navigate.delete'  => '导航删除'
	);
	
	public $menus = array
	(
		//单组模块：组名与模块名一致 不配组图标（回落模块图标） 视觉与改造前的扁平菜单一致
		'信息管理' => array
		(
			'items' => array
			(
				'参数配置' => array('url'=>'fc.settings', 'icon'=>'settings.png', 'rights'=>'content.settings'),
				'查看栏目' => array('url'=>'fc.category.select', 'icon'=>'category.select.png', 'rights'=>'content.category.select'),
				'添加栏目' => array('url'=>'fc.category.insert', 'icon'=>'category.insert.png', 'rights'=>'content.category.insert'),
				'查看信息' => array('url'=>'fc.content.select', 'icon'=>'content.select.png', 'rights'=>'content.select'),
				'添加信息' => array('url'=>'fc.content.insert', 'icon'=>'content.insert.png', 'rights'=>'content.insert'),
				'查看页面' => array('url'=>'fc.page.select', 'icon'=>'page.select.png', 'rights'=>'content.page.select'),
				'添加页面' => array('url'=>'fc.page.insert', 'icon'=>'page.insert.png', 'rights'=>'content.page.insert'),
				'标签管理' => array('url'=>'fc.tags.select', 'icon'=>'tags.select.png', 'rights'=>'content.tags.select'),
				'导航管理' => array('url'=>'fc.navigate.select', 'icon'=>'navigate.select.png', 'rights'=>'content.navigate.select')
			)
		)
	);
	
	/***
	 * 模块安装回调函数
	 * 用于完成模块安装时的初始化工作
	*/
	public function install()
	{
		global $A;

		//创建模块数据表（已存在则跳过 卸载保留数据）
		$A->installTables('content');
	}
	
	/***
	 * 模块反安装回调函数
	 * 用于完成模块卸载时的清理工作
	*/
	public function uninstall()
	{
	}
}
?>