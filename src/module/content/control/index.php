<?php
/***
 * 名称：内容模块动作处理程序
 * Alan 2012.07
 * www.fcontex.com
*/

include '../../startup.php';

$C = $A->loadLibrary('content');

switch ($mode = $A->strGet('mode'))
{
	////前端请求////
	case 'count':
		$id = intval($A->strGet('id'));
		$atts = $A->strGet('atts');
		$json = '{';
		if ($id)
		{
			$D->update('content', array('ct_hits' => $D->raw('ct_hits + 1')), array('ct_id' => $id));
			$rst = DB::table('content')->field('ct_hits, ct_talks')->where('ct_id = ?', array($id))->row();
			$json .= 'hits:'.intval($rst['ct_hits']).',talks:'.intval($rst['ct_talks']);
		}
		if (preg_match('/^[0-9]+?(,[0-9]+?)*$/', $atts))
		{
			$str = ',atts:['; $dot = '';
			foreach (explode(',', $atts) as $att)
			{
				$at_hits = 0;
				if ($rst = DB::table('attached')->field('at_hits')->where('at_id = ?', array(intval($att)))->row())
				{
					$at_hits = intval($rst['at_hits']);
				}
				$str .= $dot.$at_hits;
				$dot = ',';
			}
			$json .= $str.']';
		}
		echo $json.'}';
		break;
	
	////后端请求////
	case 'fc.settings':
		$U->checkRights('content.settings', TRUE);

		$args = trim($A->strPost('args'));
		if ($args == '' || !is_dir(PATH_THEMES.$args))
		{
			echo 'ERR|主题设置失败。';
		}
		else
		{
			//单字段配置文件 整写即可 无需读改写
			$A->saveConfig('content.settings', array('theme' => $args));
			$A->logInsert('更换了主题 # '.$args);
			echo 'YES';
		}
		break;

	case 'fc.category.insert':
		$U->checkRights('content.category.insert', TRUE);
		
		$cg_title = trim($A->strPost('cg_title'));
		if ( $cg_title == '' )
		{
			echo 'ERR|请输入栏目名称。|cg_title';
		}
		else
		{
			$insertarr = array
			(
				'cg_pid'	=> intval($A->strPost('cg_pid')),
				'cg_title'	=> $cg_title,
				'cg_type'	=> intval($A->strPost('cg_type')),
				'cg_url'	=> $A->strPost('cg_url'),
				'cg_target'	=> $A->strPost('cg_target'),
				'cg_desc'	=> $A->strPost('cg_desc'),
				'cg_show'	=> 1,
				'cg_time'	=> time()
			);
			$insertid = $D->insert('category', $insertarr);
			
			$A->logInsert('添加了栏目 #'.$insertid);
			echo 'YES';
			
		}
		break;
	
	case 'fc.category.update':
		$U->checkRights('content.category.update', TRUE);
		
		$args = $A->strPost('args');
		$cg_title = trim($A->strPost('cg_title'));
		if (!is_numeric($args))
		{
			echo 'ERR|错误的参数[ '.$args.' ]。';
		}
		elseif ( $cg_title == '' )
		{
			echo 'ERR|请输入栏目名称。|cg_title';
		}
		else
		{
			$updatearr = array
			(
				'cg_pid'	=> intval($A->strPost('cg_pid')),
				'cg_title'	=> $cg_title,
				'cg_type'	=> intval($A->strPost('cg_type')),
				'cg_url'	=> $A->strPost('cg_url'),
				'cg_target'	=> $A->strPost('cg_target'),
				'cg_desc'	=> $A->strPost('cg_desc')
			);
			$D->update('category', $updatearr, array('cg_id' => $args));
			
			$A->logInsert('编辑了栏目 #'.$args);
			echo 'YES';
			
		}
		break;
	
	case 'fc.category.delete':
		$U->checkRights('content.category.delete', TRUE);
		
		$args = $A->strPost('args');
		if (!preg_match('/^[0-9]+?(,[0-9]+?)*$/', $args))
		{
			echo 'ERR|未选中任何项。';
		}
		else
		{
			$D->delete('category', 'cg_id in ('.$args.')');
			
			$A->logInsert('删除了栏目 #'.$args);
			echo 'YES';
		}
		break;
	
	case 'fc.category.show':
		$U->checkRights('content.category.update', TRUE);
		
		$args = $A->strPost('args');
		$show = intval($A->strPost('show')) ? 1 : 0;
		$D->update('category', array('cg_show' => $show), array('cg_id' => $args));
		
		echo 'YES';
		break;
	
	case 'fc.category.order':
		$U->checkRights('content.category.order', TRUE);
		
		$args = $A->strPost('args');
		if (!preg_match('/^[0-9]+?(,[0-9]+?)*$/', $args))
		{
			echo 'ERR|未选中任何项。';
		}
		else
		{
			
			foreach (explode(',', $args) as $key => $value)
			{
				$D->update('category', array('cg_order' => intval($key)), array('cg_id' => intval($value)));
			}
			$A->logInsert('更新了栏目排序');
			echo 'YES';
		}
		break;
		
	case 'fc.content.insert':
		$U->checkRights('content.insert', TRUE);
		
		$ct_type 			= intval($A->strPost('ct_type'));
		$ct_title			= trim($A->strPost('ct_title'));
		$ct_title2			= trim($A->strPost('ct_title2'));
		$ct_title3			= trim($A->strPost('ct_title3'));
		$ct_cid				= intval($A->strPost('ct_cid'));
		$ct_ctitle			= trim($A->strPost('ct_ctitle'));
		$ct_summary			= trim($A->strPost('ct_summary'));
		$ct_content			= trim($A->strPost('ct_content'));
		$ct_cover			= trim($A->strPost('ct_cover'));
		$ct_pictures		= trim($A->strPost('ct_pictures'));
		$ct_check			= $A->strPost('ct_check');
		$ct_fixed			= $A->strPost('ct_fixed');
		$ct_quiet			= $A->strPost('ct_quiet');
		$ct_tags			= str_replace('，', ',', trim($A->strPost('ct_tags')));
		$ct_inserttime		= intval(strtotime($A->strPost('ct_inserttime')));
		if ($ct_title == '')
		{
			echo 'ERR|请输入标题。|ct_title';
		}
		/*elseif ( $ct_cid == '0' )
		{
			echo 'ERR|请选择栏目。|ct_cid';
		}
		elseif ($ct_content == '')
		{
			echo 'ERR|请输入内容。|ct_content';
		}*/
		else
		{
			$insertarr = array
			(
				'ct_type'			=> ($ct_type ? $ct_type : 0),
				'ct_title'			=> $ct_title,
				'ct_title2'			=> $ct_title2,
				'ct_title3'			=> $ct_title3,
				'ct_cid'			=> $ct_cid,
				'ct_ctitle'			=> $ct_ctitle,
				'ct_tags'			=> $ct_tags,
				'ct_summary'		=> $A->strPost('ct_summary_auto') ? $A->strLeft(strip_tags($ct_content), 200) : $ct_summary,
				'ct_content'		=> $ct_content,
				'ct_cover'			=> $ct_cover,
				'ct_pictures'		=> $ct_pictures,
				'ct_inserttime'		=> $ct_inserttime,
				'ct_updatetime'		=> time(),
				'ct_uid'			=> $_SESSION['userInfo']['us_id'],
				'ct_username'		=> $_SESSION['userInfo']['us_username'],
				'ct_quiet'			=> ($ct_quiet ? 0 : 1),
				'ct_fixed'			=> ($ct_fixed ? 1 : 0),
				'ct_check'			=> ($ct_check ? 0 : 1)
			);
			if ($A->strPost('ct_seo'))
			{
				$insertarr['ct_seo'] 			= 1;
				$insertarr['ct_pagetitle']		= trim($A->strPost('ct_pagetitle'));
				$insertarr['ct_keywords']		= trim($A->strPost('ct_keywords'));
				$insertarr['ct_description']	= trim($A->strPost('ct_description'));
			}
			try
			{
				$insertid = $D->transaction(function($D) use ($insertarr, $ct_tags, $ct_cid)
				{
					$insertid = $D->insert('content', $insertarr);
					
					//处理标签
					if ($ct_tags != '')
					{
						$tags = explode(',', $ct_tags);
						foreach ($tags as $tag)
						{
							$tag = trim($tag);
							if (!DB::table('tags')->where('tg_title = ?', array($tag))->exists())
							{
								$D->insert('tags', array('tg_title' => $tag));
							}
						}
					}
					
					//栏目文章统计
					if ($ct_cid > 0)
					{
						$D->exec('update T[category] set cg_count = (select count(*) from T[content] where ct_cid=?) where cg_id=?', array($ct_cid, $ct_cid));
					}
					
					return $insertid;
				});
			}
			catch (Exception $e)
			{
				echo 'ERR|保存失败，请重试。';
				break;
			}
			
			$A->logInsert('添加了信息 #'.$insertid);
			echo 'YES';
			
		}
		break;
		
	case 'fc.content.update':
		$U->checkRights('content.update', TRUE);
		
		$ct_title		= trim($A->strPost('ct_title'));
		$ct_title2		= trim($A->strPost('ct_title2'));
		$ct_title3		= trim($A->strPost('ct_title3'));
		$ct_cid			= intval($A->strPost('ct_cid'));
		$ct_ctitle		= trim($A->strPost('ct_ctitle'));
		$ct_summary		= trim($A->strPost('ct_summary'));
		$ct_content		= trim($A->strPost('ct_content'));
		$ct_cover		= trim($A->strPost('ct_cover'));
		$ct_pictures	= trim($A->strPost('ct_pictures'));
		$ct_check		= $A->strPost('ct_check');
		$ct_fixed		= $A->strPost('ct_fixed');
		$ct_quiet		= $A->strPost('ct_quiet');
		$ct_tags		= str_replace('，', ',', trim($A->strPost('ct_tags')));
		$ct_inserttime	= intval(strtotime($A->strPost('ct_inserttime')));
		
		$args = $A->strPost('args');
		if (!is_numeric($args))
		{
			echo 'ERR|错误的参数[ '.$args.' ]。';
		}
		elseif ($ct_title == '')
		{
			echo 'ERR|请输入标题。|ct_title';
		}
		/*elseif ( $ct_cid == '0' )
		{
			echo 'ERR|请选择栏目。|ct_cid';
		}
		elseif ($ct_content == '')
		{
			echo 'ERR|请输入内容。|ct_content';
		}*/
		else
		{
			$updatearr = array
			(
				'ct_title' 			=> $ct_title,
				'ct_title2' 		=> $ct_title2,
				'ct_title3' 		=> $ct_title3,
				'ct_cid' 			=> $ct_cid,
				'ct_ctitle' 		=> $ct_ctitle,
				'ct_tags'			=> $ct_tags,
				'ct_summary'		=> $A->strPost('ct_summary_auto') ? $A->strLeft(strip_tags($ct_content), 200) : $ct_summary,
				'ct_content' 		=> $ct_content,
				'ct_cover'			=> $ct_cover,
				'ct_pictures'		=> $ct_pictures,
				'ct_inserttime'		=> $ct_inserttime,
				'ct_updatetime' 	=> time(),
				'ct_uid'			=> $_SESSION['userInfo']['us_id'],
				'ct_username'		=> $_SESSION['userInfo']['us_username'],
				'ct_quiet'			=> ($ct_quiet ? 0 : 1),
				'ct_fixed'			=> ($ct_fixed ? 1 : 0),
				'ct_check'			=> ($ct_check ? 0 : 1)
			);
			$updatearr['ct_seo'] 			= $A->strPost('ct_seo') ? 1 : 0;
			$updatearr['ct_pagetitle']		= trim($A->strPost('ct_pagetitle'));
			$updatearr['ct_keywords']		= trim($A->strPost('ct_keywords'));
			$updatearr['ct_description']	= trim($A->strPost('ct_description'));
			
			try
			{
				$D->transaction(function($D) use ($updatearr, $args, $ct_tags, $ct_cid)
				{
					$D->update('content', $updatearr, array('ct_id' => $args));
					
					//处理标签
					if ($ct_tags != '')
					{
						$tags = explode(',', $ct_tags);
						foreach ($tags as $tag)
						{
							$tag = trim($tag);
							if (!DB::table('tags')->where('tg_title = ?', array($tag))->exists())
							{
								$D->insert('tags', array('tg_title' => $tag));
							}
						}
					}
					
					//栏目文章统计
					if ($ct_cid > 0)
					{
						$D->exec('update T[category] set cg_count = (select count(*) from T[content] where ct_cid=?) where cg_id=?', array($ct_cid, $ct_cid));
					}
				});
			}
			catch (Exception $e)
			{
				echo 'ERR|保存失败，请重试。';
				break;
			}
			
			$A->logInsert('编辑了信息 #'.$args);
			echo 'YES';
			
		}
		break;
		
	case 'fc.content.delete':
		$U->checkRights('content.delete', TRUE);
		
		$args = $A->strPost('args');
		if (!preg_match('/^[0-9]+?(,[0-9]+?)*$/', $args))
		{
			echo 'ERR|未选中任何项。';
		}
		else
		{
			$D->delete('content', 'ct_id in ('.$args.')');
			
			$A->logInsert('删除了信息 #'.$args);
			echo 'YES';
		}
		break;
		
	case 'fc.content.check':
		$save = $A->strPost('save');
		$args = $A->strPost('args');
		$updatearr = array();
		if     ($save == 'fc.content.check_1') $updatearr['ct_check'] = 1;
		elseif ($save == 'fc.content.check_0') $updatearr['ct_check'] = 0;
		elseif ($save == 'fc.content.fixed_1') $updatearr['ct_fixed'] = 1;
		elseif ($save == 'fc.content.fixed_0') $updatearr['ct_fixed'] = 0;
		elseif ($save == 'fc.content.refresh') $updatearr['ct_updatetime'] = time();
		else
		{
			exit('ERR|请选择操作。');	
		}
		foreach (explode(',', $args) as $id)
		{
			$D->update('content', $updatearr, array('ct_id' => intval($id)));	
		}
		echo 'YES';
		break;
	
	case 'fc.tags.delete':
		$U->checkRights('content.tags.delete', TRUE);
		
		$args = $A->strPost('args');
		if (!preg_match('/^[0-9]+?(,[0-9]+?)*$/', $args))
		{
			echo 'ERR|未选中任何项。';
		}
		else
		{
			$D->delete('tags', 'tg_id in ('.$args.')');
			
			$A->logInsert('删除了标签 #'.$args);
			echo 'YES';
		}
		break;
	
	case 'fc.tags.update':
		$U->checkRights('content.tags.update', TRUE);
		
		$ids = $A->strPost('tg_id');
		if (!is_array($ids))
		{
			echo 'ERR|没有标签被修改。';
		}
		else 
		{
			$title = $A->strPost('tg_title');
			$color = $A->strPost('tg_color');
			foreach ($ids as $key => $id)
			{
				$title[$key] = isset($title[$key]) ? $title[$key] : '';
				$color[$key] = isset($color[$key]) ? $color[$key] : '';
				$D->update('tags', array('tg_title' => trim($title[$key]), 'tg_color' => trim($color[$key])), array('tg_id' => $id));
			}
			
			$ids = implode(',', $ids);
			$A->logInsert('修改了标签 #'.$ids);
			echo 'YES';
		}
		break;
		
	case 'fc.navigate.insert':
		$U->checkRights('content.navigate.insert', TRUE);
		
		$title = trim($A->strPost('nv_title'));
		if ($title == '')
		{
			exit('ERR|请填写标题。|nv_title');
		}
		$type = $A->strPost('nv_type');
		$target = $A->strPost('nv_target') ? 1 : 0;
		$check = $A->strPost('nv_check') ? 1 : 0;
		$insertarr = array('nv_title' => $title, 'nv_target' => $target, 'nv_check' => $check);
		if ($type == 1)
		{
			//自定义链接
			$url = trim($A->strPost('nv_url'));
			if ($url == '') exit('ERR|请填写链接地址。|nv_url');
			$insertarr['nv_url'] = $url;	
		}
		elseif ($type == 2)
		{
			//栏目链接
			$category = intval($A->strPost('nv_category'));
			$insertarr['nv_url'] = $R->getPageUrl('content/list-'.$category);
		}
		elseif ($type == 3)
		{
			//页面链接
			$page = intval($A->strPost('nv_page'));
			$insertarr['nv_url'] = $R->getPageUrl('content/page-'.intval($page));
		}
		else
		exit('ERR|没有链接选择类型。');
		
		//查询最大排序值
		$rst = DB::table('navigate')->fieldRaw('max(nv_order) as num')->row();
		$insertarr['nv_order'] = intval($rst['num']) + 1;
		$insertid = $D->insert('navigate', $insertarr);
		
		$A->logInsert('添加了导航 #'.$insertid);
		echo 'YES';
		break;
		
	case 'fc.navigate.check':
		$U->checkRights('content.navigate.update', TRUE);
		
		$args = intval($A->strPost('args'));
		$check = intval($A->strPost('check')) ? 1 : 0;
		$D->update('navigate', array('nv_check' => $check), array('nv_id' => $args));
		
		echo 'YES';
		break;
	
	case 'fc.navigate.target':
		$U->checkRights('content.navigate.update', TRUE);
		
		$args = intval($A->strPost('args'));
		$target = intval($A->strPost('target')) ? 1 : 0;
		$D->update('navigate', array('nv_target' => $target), array('nv_id' => $args));
		
		echo 'YES';
		break;
		
	case 'fc.navigate.update':
		$U->checkRights('content.navigate.update', TRUE);
		
		$ids = $A->strPost('ids');
		$nv_order = $A->strPost('nv_order');
		$nv_title = $A->strPost('nv_title');
		$nv_url	  = $A->strPost('nv_url');
		if (!is_array($ids))
		{
			exit('ERR|没有选中记录。');
		}
		foreach ($ids as $key => $id)
		{
			$D->update('navigate', array('nv_title' => $nv_title[$key], 'nv_order' => $nv_order[$key], 'nv_url' => $nv_url[$key]), array('nv_id' => $id));
		}
		
		$ids = implode(',', $ids);
		$A->logInsert('编辑了导航 #'.$ids);
		echo 'YES';
		break;
		
	case 'fc.navigate.delete':
		$U->checkRights('content.navigate.delete', TRUE);
		
		$args = intval($A->strPost('args'));
		$D->delete('navigate', 'nv_id = '.$args);
		
		$A->logInsert('删除了导航 #'.$args);
		echo 'YES';
		break;
		
	default:
		echo 'ERR|无效请求。';
		break;
}
?>