<?php
/***
 * 名称：用户模块类
 * Alan 2012.03.06
 * www.fcontex.com
*/

//类命名方式必须是FCModule加首字段大写的目录名才会被内核识别
final class FCModuleMember
{
	/***
	 * 模块基本信息
	*/
	public $basic = array
	(
		'for'     => '1.0.1+',
		'name'    => '用户管理',
		'icon'    => 'module.png',
		'desc'    => '用户模块，提供用户管理和权限控制功能。',
		'author'  => 'Alan',
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
		'user.group.select' => '分组查看',
		'user.group.insert' => '分组添加',
		'user.group.update' => '分组编辑',
		'user.group.delete' => '分组删除',
		'user.select' => '用户查看',
		'user.insert' => '用户添加',
		'user.update' => '用户编辑',
		'user.delete' => '用户删除',
	);
	
	public $menus = array
	(
		//单组模块：组名与模块名一致 不配组图标（回落模块图标） 视觉与改造前的扁平菜单一致
		'用户管理' => array
		(
			'items' => array
			(
				'分组查看' => array('url'=>'fc.group.select', 'icon'=>'group.select.png', 'rights'=>'user.group.select'),
				'分组添加' => array('url'=>'fc.group.insert', 'icon'=>'group.insert.png', 'rights'=>'user.group.insert'),
				'用户查看' => array('url'=>'fc.user.select', 'icon'=>'user.select.png', 'rights'=>'user.select'),
				'用户添加' => array('url'=>'fc.user.insert', 'icon'=>'user.insert.png', 'rights'=>'user.insert'),
			)
		)
	);
	
	/***
	 * 模块安装回调函数
	 * 用于完成模块安装时的初始化工作
	*/
	public function install()
	{
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