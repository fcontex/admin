<?php
/***
 * 名称：内容模块调度程序
 * Joe 2012.07
 * www.fcontex.com
*/

if (!defined('SYSTEM_INCLUDE')) exit('Access Denied.');

//加载内容对象（含信息类型常量与主题目录常量）
$C = FCApplication::loadLibrary('content');

//主题请求分发文件（可选 存在则由主题接管本模块的前端请求）
//已处理的动作返回FALSE（输出已产生 直接结束） 未处理的动作返回TRUE落入模块默认分发
$dispatcher = PATH_THEME.'dispatcher.php';
if (substr($R->action, 0, 3) != 'fc.' && file_exists($dispatcher))
{
	$continue = include $dispatcher;
	if ($continue !== TRUE)
	{
		return;
	}
}

switch ($R->action)
{
	////前端请求////
	case 'start':
	case 'posts':
	case 'list':
		$R->cacheopen = TRUE;
		
		$cid = intval($R->getParam(1,1));
		$page = intval($R->getParam(1,2));
		
		$pager = FCApplication::sharedPageTurnner();
		$pager->style = 'Simple';
		$pager->page = $page;
		$pager->size = $A->site['site_pagesize'];
		$pager->linker = $R->getPageUrl('content/list-'.$cid.'-{p}');
		
		$Q = DB::table('content')
		  ->field('ct_id, ct_uid, ct_username, ct_title, ct_tags, ct_summary, ct_content, ct_inserttime, ct_cover, ct_hits, ct_talks')
		  ->whereRaw('ct_type > '.CONTENT_PAGE)
		  ->where('ct_check = 1')
		  ->order('ct_fixed', 'desc')
		  ->order('ct_inserttime', 'desc');
		if ($cid != 0) $Q->where('ct_cid = ?', array($cid));
		$items = $Q->page($pager);
		
		if ($cid == 0)
		{
			$A->site['site_title'] = $A->site['site_title'];
			$A->site['site_description'] = $A->site['site_description'];
			$A->site['site_keywords'] = $A->site['site_keywords'];
			
			$T->bind('category', array('id'=>$cid, 'title'=>''));
		}
		else
		{
			$category = $C->getCategory($cid);
			$position[0] = array
			(
				'text' => $category['cg_title'],
				'link' => $R->getPageUrl('content/list-'.$cid.'-'.$page)
			);
			$T->bind('position', $position);
			$A->site['site_title'] = $category['cg_title'].' _ '.$A->site['site_title'];
			$A->site['site_description'] = '查看'.$category['cg_title'].'相关日志。'.$A->site['site_description'];
			$A->site['site_keywords'] = $category['cg_title'].','.$A->site['site_keywords'];
			
			$T->bind('category', array('id'=>$cid, 'title'=>$category['cg_title']));
		}
		$T->bind('pager', $pager);
		$T->bind('turnner', $pager->turnner);
		$T->bind('items', $items);
		$C->display('list');
		break;
	
	case 'read':
	case 'post':
	case 'page':
		$R->cacheopen = TRUE;
		
		$id = intval($R->getParam(1,1));
		$page = $C->getContent($id);
		if (empty($page))
		{
			$R->print404();
		}
		if ($page['ct_type'] == CONTENT_PAGE)
		{
			$position[0] = array
			(
				'text' => $page['ct_title'],
				'link' => $R->getPageUrl('content/page-'.$id)
			);
		}
		else
		{
			$position[0] = array
			(
				'text' => $page['ct_ctitle'],
				'link' => $R->getPageUrl('content/list-'.$page['ct_cid'].'-1')
			);
			$position[1] = array
			(
				'text' => '正文',
				'link' => ''
			);
		}
		$T->bind('position', $position);
		if ($page['ct_seo'])
		{
			$A->site['site_title'] = $page['ct_pagetitle'];
			$A->site['site_description'] = $page['ct_keywords'];
			$A->site['site_keywords'] = $page['ct_description'];
		}
		else
		{
			$A->site['site_title'] = $page['ct_title'].' _ '.$A->site['site_title'];
			$A->site['site_description'] = $page['ct_title'].'，'.$A->trim($page['ct_summary'], TRUE);
			$A->site['site_keywords'] = $page['ct_title'].','.$A->site['site_keywords'];
		}
		$T->bind('page', $page);
		$C->display('read');
		break;
	
	case 'search':
	case 'find':
		$keyw = $R->getParam(2);
		$page = intval($R->getParam(3));
		$T->bind('keyw', $keyw);
		
		$pager = FCApplication::sharedPageTurnner();
		$pager->style = 'Simple';
		$pager->page = $page;
		$pager->size = $A->site['site_pagesize'];
		$pager->linker = $R->getPageUrl('content/search/'.$keyw.'/{p}');
		
		$Q = DB::table('content')
		  ->field('ct_id, ct_uid, ct_username, ct_title, ct_tags, ct_summary, ct_content, ct_inserttime, ct_cover, ct_hits, ct_talks')
		  ->whereRaw('ct_type > '.CONTENT_PAGE)
		  ->where('ct_check = 1')
		  ->where('(ct_title like ? or ct_tags like ?)', array('%'.$keyw.'%', '%'.$keyw.'%'))
		  ->order('ct_inserttime', 'desc');
		$items = $Q->page($pager);
		$T->bind('items', $items);
		$T->bind('pager', $pager);
		$T->bind('turnner', $pager->turnner);
		$position[0] = array
		(
			'text' => '“'.$keyw.'”',
			'link' => ''
		);
		$T->bind('position', $position);
		$A->site['site_title'] = $keyw.' _ 日志搜索 _  '.$A->site['site_title'];
		$A->site['site_description'] = '查看'.$keyw.'相关日志。'.$A->site['site_description'];
		$A->site['site_keywords'] = $keyw.','.$A->site['site_keywords'];
		$C->display('list');
		break;
	
	case 'tags':
	case 'tag':
		$tag = $R->getParam(2);
		$page = intval($R->getParam(3));
		$T->bind('tag', $tag);
		
		$pager = FCApplication::sharedPageTurnner();
		$pager->style = 'Simple';
		$pager->page = $page;
		$pager->size = $A->site['site_pagesize'];
		$pager->linker = $R->getPageUrl('content/tag/'.$tag.'/{p}');
		
		$Q = DB::table('content')
		  ->field('ct_id, ct_username, ct_title, ct_tags, ct_summary, ct_content, ct_inserttime, ct_cover, ct_uid, ct_hits, ct_talks')
		  ->whereRaw('ct_type > '.CONTENT_PAGE)
		  ->where('ct_check = 1')
		  ->where('(ct_tags like ?)', array('%'.$tag.'%'))
		  ->order('ct_inserttime', 'desc');
		$items = $Q->page($pager);
		$T->bind('items', $items);
		$T->bind('pager', $pager);
		$T->bind('turnner', $pager->turnner);
		$position[0] = array
		(
			'text' => '“'.$tag.'”',
			'link' => ''
		);
		$T->bind('position', $position);
		$A->site['site_title'] = $tag.' _ 标签检索 _  '.$A->site['site_title'];
		$A->site['site_description'] = '查看'.$tag.'，相关日志。'.$A->site['site_description'];
		$A->site['site_keywords'] = $tag.','.$A->site['site_keywords'];
		$C->display('list');
		break;
	
	case 'feed':
	case 'rss':
		$R->cacheopen = TRUE;
		
		$contents = $C->getContents(0, 30);
		$item = array();
		$i = 0;
		foreach ($contents as $content)
		{
			$item[$i]['link'] = 'http://'.$A->site['site_domain'].$R->getPageUrl('content/read-'.$content['ct_id']);
			$item[$i]['cover'] = 'http://'.$A->site['site_domain'].$A->getThumb($content['ct_cover']);
			$item[$i]['title'] = $content['ct_title'];
			$item[$i]['author'] = $content['ct_username'];
			$item[$i]['category'] = $content['ct_ctitle'];
			$item[$i]['pubDate'] = date('Y-m-d h:i:s', $content['ct_inserttime']);
			$item[$i]['description'] = $content['ct_summary'];
			
			$i++;
		}
		$channel['link'] = 'http://'.$A->site['site_domain'].$R->getPageUrl('/');
		$channel['title'] = $A->site['site_title'];
		$channel['description'] = $A->site['site_description'];
		$channel['generator'] = SYSTEM_NAME.' '.SYSTEM_VERSION;
		$channel['ttl'] = ceil($A->site['site_cachetime']/60);
		$channel['item'] = $item;
		
		$T->bind('channel', $channel);
		header('Content-Type: text/xml;');
		$C->display('rss');
		break;
	
	case '404': default:
		//$R->cacheopen = TRUE;
		$R->header404();
		$C->display('404');
		break;
	
	////后端请求////
	case 'fc.settings':
		$U->checkRights('content.settings');
		$T->show('theme');
		break;

	case 'fc.category.select':
		$U->checkRights('content.category.select');
		$T->show('category.select');
		break;
	
	case 'fc.category.insert':
		$U->checkRights('content.category.insert');
		$T->show('category.insert');
		break;
	
	case 'fc.category.update':
		$U->checkRights('content.category.update');
		$T->show('category.update');
		break;
	
	case 'fc.content.select':
		$U->checkRights('content.select');
		$T->show('content.select');
		break;
	
	case 'fc.content.insert':
        $U->checkRights('content.insert');
		$T->show('content.insert');
		break;
	
	case 'fc.content.update':
		$U->checkRights('content.update');
		$T->show('content.update');
		break;
	
	case 'fc.page.select':
		$U->checkRights('content.page.select');
		$T->show('page.select');
		break;
	
	case 'fc.page.insert':
		$U->checkRights('content.page.insert');
		$T->show('page.insert');
		break;
	
	case 'fc.page.update':
		$U->checkRights('content.page.update');
		$T->show('page.update');
		break;
	
	case 'fc.tags.select':
		$U->checkRights('content.tags.select');
		$T->show('tags.select');
		break;
	
	case 'fc.navigate.select':
		$U->checkRights('content.navigate.select');
		$T->show('navigate.select');
		break;
}
?>