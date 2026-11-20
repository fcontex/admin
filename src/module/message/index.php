<?php
/***
 * 名称：内容模块
 * Joe 2012.07
 * www.fcontex.com
*/

if (!defined('SYSTEM_INCLUDE')) exit('Access Denied.');

//主题模板（inc.head/inc.side）依赖内容对象（渲染时由content::display注入模板）
$C = FCApplication::loadLibrary('content');

$T->bind('M', $A->loadLibrary('message'));

switch ($this->action)
{
	////前端请求////
	case 'comment':
		$R->cacheopen = TRUE;
		
		$cid = intval($R->getParam(1,1));
		$page = intval($R->getParam(1,2));
		
		if (!($quiet = $A->loadConfig('message.settings', 'comment_disable')))
		{
			$rst = DB::table('content')->field('ct_quiet')->where('ct_id = ?', array($cid))->row();
			if ($rst && $rst['ct_quiet']) $quiet = TRUE;
		}
		$T->bind('quiet', $quiet);
		
		$pager = FCApplication::sharedPageTurnner();
		$pager->page = $page;
		$pager->style = 'Simple';
		$pager->linker = "#{p}\" onclick=\"comment.fetch('".$R->getPageUrl('message/comment-'.$cid.'-{p}')."');return false;";
		$Q = DB::table('comment');
		$comments = $Q->field('*')
		  ->where('cm_check = 1')
		  ->where('cm_topid = 0')
		  ->where('cm_cid = ?', array($cid))
		  ->order('cm_id', 'desc')
		  ->page($pager);
		$T->bind('comments', $comments);
		$T->bind('pager', $pager);
		$T->bind('turnner', $pager->turnner);
		$C->display('comment');
		break;
		
	case 'gbook':
		$R->cacheopen = TRUE;
		
		$page = intval($R->getParam(1,1));
		
		$T->bind('quiet', $A->loadConfig('message.settings', 'gbook_disable'));
		
		$pager = FCApplication::sharedPageTurnner();
		$pager->page = $page;
		$pager->style = 'Simple';
		$pager->linker = $R->getPageUrl('message/gbook-{p}');
		$Q = DB::table('gbook');
		$gbook = $Q->field('*')
		  ->where('gb_check = 1')
		  ->where('gb_topid = 0')
		  ->order('gb_id', 'desc')
		  ->page($pager);
		$T->bind('gbook', $gbook);
		$T->bind('pager', $pager);
		$T->bind('turnner', $pager->turnner);
		$position[0] = array
		(
			'text' => '留言',
			'link' => $R->getPageUrl('message/gbook')
		);
		$T->bind('position', $position);
		$A->site['site_title'] = $position[0]['text'].' _ '.$A->site['site_title'];
		$C->display('gbook');
		break;
	
	default:
		//$R->cacheopen = TRUE;
		$R->header404();
		$C->display('404');
		break;
	
	////后端请求////
	case 'fc.settings':
		$U->checkRights('message.settings');
		$T->show('config.settings');
		break;
	
	case 'fc.gbook.select':
		$U->checkRights('gbook.select');
		$T->show('gbook.select');
		break;
	
	case 'fc.gbook.update':
		$U->checkRights('gbook.update');
		$T->show('gbook.update');
		break;
	
	case 'fc.comment.select':
		$U->checkRights('comment.select');
		$T->show('comment.select');
		break;
	
	case 'fc.comment.update':
		$U->checkRights('comment.update');
		$T->show('comment.update');
		break;
}
?>