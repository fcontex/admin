<?php
/***
 * 名称：主题请求分发程序
 *      可选文件，如果使用了此文件，则所有主题请求由此文件进行分发处理，否则由系统默认方式分发处理。
 * Alan 2013.12
 * www.fcontex.com
*/

if (!defined('SYSTEM_INCLUDE')) exit('Access Denied.');

//加载内容对象（渲染时由content::display注入模板）
$C = FCApplication::loadLibrary('content');

//在后面添加栏目后在这里配置
$p_pid = 2;        //产品父栏目ID
$p_ids = array(5, 6);  //产品子栏目ID列表
$n_pid = 3;        //新闻父栏目ID
$n_ids = array(7, 8);  //新闻子栏目ID列表
$r_pid = 4;        //首页滚动栏目ID

$T->bind('links', $C->getContents(1));
switch ($R->action)
{
	case 'start':
		$R->cacheopen = TRUE;
		
		//大图滚动
		$focuses = DB::table('content')
			->field('ct_cover, ct_title2')
			->where('ct_cid = ?', array($r_pid))
			->where('ct_check = 1')
			->order('ct_inserttime', 'desc')
			->limit(6)
			->rows();
		$T->bind('focuses', $focuses);
		//小图产品
		$products = DB::table('content')
			->field('ct_id, ct_title, ct_title2, ct_cover')
			->where('ct_check = 1')
			->where('ct_fixed = 1')
			->whereIn('ct_cid', $p_ids)
			->order('ct_inserttime', 'desc')
			->limit(4)
			->rows();
		$T->bind('products', $products);
		//滚动新闻
		$contents = DB::table('content')
			->field('ct_id, ct_title')
			->where('ct_check = 1')
			->where('ct_fixed = 1')
			->whereIn('ct_cid', $n_ids)
			->order('ct_inserttime', 'desc')
			->limit(4)
			->rows();
		$T->bind('contents', $contents);
		
		$C->display('start');
		break;
	
	case 'product':
		$R->cacheopen = TRUE;
		
		$id = intval($R->getParam(1,0));
		$cid = intval($R->getParam(1,1));
		$page = intval($R->getParam(1,2));
		
		$T->bind('banner', 'page_product.jpg');
		$T->bind('categories', $C->getCategories($p_pid));
		if ($id > 0)
		{
			$page = $C->getContent($id);
			if (empty($page))
			{
				$R->print404();
			}
			$A->site['site_title'] = $page['ct_title'].' _ '.$A->site['site_title'];
			$A->site['site_description'] = $A->trim($page['ct_summary'], TRUE);
			$A->site['site_keywords'] = $page['ct_title'].$A->site['site_keywords'];
			$T->bind('page', $page);
			$C->display('product.read');
		}
		else
		{
			$pager = FCApplication::sharedPageTurnner();
			$pager->style = 'Simple';
			$pager->page = $page;
			$pager->size = $A->site['site_pagesize'];
			$pager->linker = $R->getPageUrl('product/list-'.$id.'-{p}');
			
			$Q = DB::table('content')
			  ->field('ct_id, ct_username, ct_title, ct_tags, ct_summary, ct_content, ct_inserttime, ct_cover, ct_uid, ct_hits')
			  ->where('ct_check = 1')
			  ->order('ct_inserttime', 'desc');
			if ($cid > 0)
			{
				$Q->where('ct_cid = ?', array($cid));
				$category = $C->getCategory($cid);
			}
			else
			{
				$Q->whereIn('ct_cid', $p_ids);
			}
			$items = $Q->page($pager);
			
			if (empty($category))
			{
				$category['cg_id'] = 0;
				$category['cg_title'] = '主营产品';
			}
			
			$A->site['site_title'] = $category['cg_title'].' _ '.$A->site['site_title'];
			$A->site['site_description'] = $category['cg_title'].'|'.$A->site['site_description'];
			$A->site['site_keywords'] = $category['cg_title'].','.$A->site['site_keywords'];
			$T->bind('turnner', $pager->turnner);
			$T->bind('items', $items);
			$T->bind('category', $category);
			$C->display('product.list');
		}
		break;
	
	case 'news':
		$R->cacheopen = TRUE;
		
		$id = intval($R->getParam(1,0));
		$cid = intval($R->getParam(1,1));
		$page = intval($R->getParam(1,2));
		
		$T->bind('banner', 'page_news.jpg');
		$T->bind('categories', $C->getCategories($n_pid));
		if ($id > 0)
		{
			$page = $C->getContent($id);
			if (empty($page))
			{
				$R->print404();
			}
			$A->site['site_title'] = $page['ct_title'].' _ '.$A->site['site_title'];
			$A->site['site_description'] = $A->trim($page['ct_summary'], TRUE);
			$A->site['site_keywords'] = $page['ct_title'].','.$A->site['site_keywords'];
			$T->bind('page', $page);
			$C->display('news.read');
		}
		else
		{
			$pager = FCApplication::sharedPageTurnner();
			$pager->style = 'Simple';
			$pager->page = $page;
			$pager->size = $A->site['site_pagesize'];
			$pager->linker = $R->getPageUrl('news/list-'.$cid.'-{p}');
			
			$Q = DB::table('content')
			  ->field('ct_id, ct_username, ct_title, ct_tags, ct_summary, ct_content, ct_inserttime, ct_cover, ct_uid, ct_hits')
			  ->where('ct_check = 1')
			  ->order('ct_fixed', 'desc')
			  ->order('ct_inserttime', 'desc');
			if ($cid > 0)
			{
				$Q->where('ct_cid = ?', array($cid));
				$category = $C->getCategory($cid);
			}
			else
			{
				$Q->whereIn('ct_cid', $n_ids);
			}
			$items = $Q->page($pager);
			
			if (empty($category))
			{
				$category['cg_id'] = 0;
				$category['cg_title'] = '新闻中心';
			}
			
			$A->site['site_title'] = $category['cg_title'].' _ '.$A->site['site_title'];
			$A->site['site_description'] = $A->site['site_description'];
			$A->site['site_keywords'] = $category['cg_title'].','.$A->site['site_keywords'];
			$T->bind('turnner', $pager->turnner);
			$T->bind('items', $items);
			$T->bind('category', $category);
			$C->display('news.list');
		}
		break;
	
	case 'search':
		$keyw = urldecode($R->getParam(1));
		$page = intval($R->getParam(2));
		$T->bind('keyw', $keyw);
		
		$pager = FCApplication::sharedPageTurnner();
		$pager->style = 'Simple';
		$pager->page = $page;
		$pager->size = $A->site['site_pagesize'];
		$Q = DB::table('content')
		  ->field('ct_id, ct_username, ct_title, ct_tags, ct_summary, ct_content, ct_inserttime, ct_cover, ct_uid, ct_hits')
		  ->where('ct_check = 1')
		  ->where('(ct_title like ? or ct_tags like ?)', array('%'.$keyw.'%', '%'.$keyw.'%'))
		  ->whereIn('ct_cid', $p_ids)
		  ->order('ct_inserttime', 'desc');
		
		$pager->linker = $R->getPageUrl('search/'.$keyw.'/{p}');
		$A->site['site_title'] = $keyw.' _ 产品搜索 _  '.$A->site['site_title'];
		$A->site['site_description'] = '专业生产'.$keyw.'，提供'.$keyw.'价格信息。'.$A->site['site_description'];
		$A->site['site_keywords'] = $keyw.','.$keyw.'价格,'.$A->site['site_keywords'];
		$T->bind('banner', 'page_product.jpg');
		$T->bind('categories', $C->getCategories($p_pid));
		
		$items = $Q->page($pager);
		$T->bind('turnner', $pager->turnner);
		$T->bind('items', $items);
		$C->display('product.search');
		break;
	
	case 'page':
		$R->cacheopen = TRUE;
		
		$id = intval($R->getParam(1));
		$page = $C->getContent($id);
		if (empty($page))
		{
			$R->print404();
		}
		$A->site['site_title'] = $page['ct_title'].' _ '.$A->site['site_title'];
		$A->site['site_description'] = $A->strLeft($A->trim($page['ct_content'], TRUE), 220, '...');
		$T->bind('page', $page);
		$T->bind('banner', 'page_'.$id.'.jpg');
		$C->display('page');
		break;
	
	default: return TRUE;
}

return FALSE;
?>
