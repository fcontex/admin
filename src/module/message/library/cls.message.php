<?php
/***
 * 名称：消息模块类
 * Joe 2012.11
 * www.fcontex.com
*/

final class message 
{
	public $app;
	
	public function __construct()
	{
		$this->app = FCApplication::sharedApplication();
	}
	
	//调用二级评论
	public function getComments($topid)
	{
		return DB::table('comment')->where('cm_topid = ?', array(intval($topid)))->order('cm_id', 'asc')->rows();
	}
	
	//调用二级留言
	public function getGbook($topid)
	{
		return DB::table('gbook')->where('gb_topid = ?', array(intval($topid)))->order('gb_id', 'asc')->rows();
	}
}
?>
