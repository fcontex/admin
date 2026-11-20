<?php
/***
 * 名称： 互动模块动作处理程序
 * Alan 2012.07
 * www.fcontex.com
*/

include '../../startup.php';

switch ($mode = $A->strGet('mode'))
{
	////前端请求////
	case 'comment.insert':
		$cm_cid		= intval($A->strPost('cm_cid'));
		$cm_ctitle	= strip_tags(trim($A->strPost('cm_ctitle')));
		$cm_toid	= intval($A->strPost('cm_toid'));
		$cm_topid	= intval($A->strPost('cm_topid'));
		$cm_name	= strip_tags(trim($A->strPost('cm_name')));
		$cm_toname	= strip_tags(trim($A->strPost('cm_toname')));
		$cm_email	= strip_tags(trim($A->strPost('cm_email')));
		$cm_url		= strip_tags(trim($A->strPost('cm_url')));
		$cm_content	= strip_tags(trim($A->strPost('cm_content')));
		$cm_ip      = $A->getUserIP();
		//$cm_iparea  = $A->loadLibrary('iparea')->dataMini($cm_ip);
		
		if (!$cm_cid || strlen($cm_ctitle)>100 || strlen($cm_toname)>100)
		{
			echo 'ERR|发生意外，请刷新页面后重试。';
		}
		elseif ($cm_name == '')
		{
			echo 'ERR|请填写称呼。|cm_name';
		}
		elseif (strlen($cm_name) > 36)
		{
			echo 'ERR|称呼长度超出限制。|cm_name';
		}
		elseif (strlen($cm_email) > 100)
		{
			echo 'ERR|邮箱地址长度超出限制。|cm_email';
		}
		elseif (strlen($cm_url) > 100)
		{
			echo 'ERR|网址长度超出限制。|cm_url';
		}
		elseif ($cm_content == '')
		{
			echo 'ERR|请填写内容。|cm_content';
		}
		elseif (strlen($cm_content) > 1000)
		{
			echo 'ERR|内容长度超出限制。|cm_content';
		}
		else
		{
			$array = array
			(
				'cm_cid'	=> $cm_cid,
				'cm_ctitle'	=> $cm_ctitle,
				'cm_toid'	=> $cm_toid,
				'cm_topid'	=> $cm_topid,
				'cm_name'	=> $cm_name,
				'cm_toname'	=> $cm_toname,
				'cm_email'	=> $cm_email,
				'cm_url'	=> str_ireplace('http://', '', $cm_url),
				'cm_content'=> $cm_content,
				'cm_ip'		=> $cm_ip,
				//'cm_iparea'	=> $cm_iparea,
				'cm_check'	=> 1,
				'cm_time'	=> time(),
				'cm_update' => time()
			);
			try
			{
				$D->transaction(function($D) use ($array, $cm_cid, $cm_topid)
				{
					$D->insert('comment', $array);
					$D->update('content', array('ct_talks' => $D->raw('ct_talks + 1')), array('ct_id' => $cm_cid));
					if ($cm_topid) $D->update('comment', array('cm_update' => time()), array('cm_id' => $cm_topid));
				});
			}
			catch (Exception $e)
			{
				echo 'ERR|提交失败，请稍后重试。';
				break;
			}
			echo 'YES|提交成功，更新列表...';
		}
		break;
	
	case 'gbook.insert':
		$gb_toid	= intval($A->strPost('gb_toid'));
		$gb_topid	= intval($A->strPost('gb_topid'));
		$gb_name	= strip_tags(trim($A->strPost('gb_name')));
		$gb_toname	= strip_tags(trim($A->strPost('gb_toname')));
		$gb_email	= strip_tags(trim($A->strPost('gb_email')));
		$gb_url		= strip_tags(trim($A->strPost('gb_url')));
		$gb_content	= strip_tags(trim($A->strPost('gb_content')));
		$gb_ip      = $A->getUserIP();
		//$gb_iparea  = $A->loadLibrary('iparea')->dataMini($gb_ip);
		
		if (strlen($gb_toname) > 100)
		{
			echo 'ERR|发生意外，请刷新页面后重试。';
		}
		elseif ($gb_name == '')
		{
			echo 'ERR|请填写称呼。|gb_name';
		}
		elseif (strlen($gb_name) > 36)
		{
			echo 'ERR|称呼长度超出限制。|gb_name';
		}
		elseif (strlen($gb_email) > 100)
		{
			echo 'ERR|邮箱地址长度超出限制。|gb_email';
		}
		elseif (strlen($gb_url) > 100)
		{
			echo 'ERR|网址长度超出限制。|gb_url';
		}
		elseif ($gb_content == '')
		{
			echo 'ERR|请填写内容。|gb_content';
		}
		elseif (strlen($gb_content) > 1000)
		{
			echo 'ERR|内容长度超出限制。|gb_content';
		}
		else
		{
			$array = array
			(
				'gb_toid'	=> $gb_toid,
				'gb_topid'	=> $gb_topid,
				'gb_name'	=> $gb_name,
				'gb_toname'	=> $gb_toname,
				'gb_email'	=> $gb_email,
				'gb_url'	=> str_ireplace('http://', '', $gb_url),
				'gb_content'=> $gb_content,
				'gb_ip'		=> $gb_ip,
				//'gb_iparea'	=> $gb_iparea,
				'gb_check'	=> 1,
				'gb_time'	=> time(),
				'gb_update' => time()
			);
			try
			{
				$D->transaction(function($D) use ($array, $gb_topid)
				{
					$D->insert('gbook', $array);
					if ($gb_topid) $D->update('gbook', array('gb_update' => time()), array('gb_id' => $gb_topid));
				});
			}
			catch (Exception $e)
			{
				echo 'ERR|提交失败，请稍后重试。';
				break;
			}
			echo 'YES|提交成功，更新列表...';
		}
		break;
		
	////后端请求////
	case 'fc.settings':
		$U->checkRights('message.settings', TRUE);

		//读改写 保留配置文件中其它键
		$data = $A->loadConfig('message.settings');

		$data['comment_disable']	= intval($A->strPost('comment_disable')) ? TRUE : FALSE;
		$data['gbook_disable']		= intval($A->strPost('gbook_disable')) ? TRUE : FALSE;

		$A->saveConfig('message.settings', $data);
		$A->logInsert('更新了模块配置#message');
		echo 'YES';
		break;
	
	case 'fc.gbook.delete':
		$U->checkRights('gbook.delete', TRUE);
		
		$args = $A->strPost('args');
		if (!preg_match('/^[0-9]+?(,[0-9]+?)*$/', $args))
		{
			echo 'ERR|未选中任何项。';
		}
		else
		{
			$D->delete('gbook', 'gb_id in ('.$args.')');
			
			$A->logInsert('删除了留言 #'.$args);
			echo 'YES';
		}
		break;
		
	case 'fc.gbook.check_1':
	case 'fc.gbook.check_0':
		$U->checkRights('gbook.update', TRUE);
		
		$args = $A->strPost('args');
		$check = intval($A->strPost('check'));
		if (!$args) exit('ERR|请选择记录。');
		$ids = explode(',', $args);
		foreach ($ids as $id)
		{
			$D->update('gbook', array('gb_check' => $check), array('gb_id' => $id));
		}
		echo 'YES';
		break;
	
	case 'fc.gbook.update':
		$U->checkRights('gbook.update', TRUE);
		
		$args = intval($A->strPost('args'));
		$array = array
		(
			'gb_content' => trim($A->strPost('gb_content')),
			'gb_check' => intval($A->strPost('gb_check'))
		);
		$D->update('gbook', $array, array('gb_id' => $args));
		
		$A->logInsert('更新了留言 #'.$args);
		echo 'YES';
		break;
		
	case 'fc.comment.delete':
		$U->checkRights('comment.delete', TRUE);
		
		$args = $A->strPost('args');
		if (!preg_match('/^[0-9]+?(,[0-9]+?)*$/', $args))
		{
			echo 'ERR|未选中任何项。';
		}
		else
		{
			$ct_ids = DB::table('comment')->whereIn('cm_id', explode(',', $args))->column('cm_cid');
			$D->delete('comment', 'cm_id in ('.$args.')');
			foreach ($ct_ids as $ct_id)
			{
				$ct_id = intval($ct_id);
				$talks = DB::table('comment')->where('cm_cid = ?', array($ct_id))->count();
				$D->update('content', array('ct_talks' => $talks), array('ct_id' => $ct_id));
			}
			
			$A->logInsert('删除了评论 #'.$args);
			echo 'YES';
		}
		break;
		
	case 'fc.comment.check_1':
	case 'fc.comment.check_0':
		$U->checkRights('comment.update', TRUE);
		
		$args = $A->strPost('args');
		$check = intval($A->strPost('check'));
		if (!$args) exit('ERR|请选择记录。');
		$ids = explode(',', $args);
		foreach ($ids as $id)
		{
			$D->update('comment', array('cm_check' => $check), array('cm_id' => $id));
		}
		echo 'YES';
		break;
		
	case 'fc.comment.update':
		$U->checkRights('comment.update', TRUE);
		
		$args = intval($A->strPost('args'));
		$array = array
		(
			'cm_content' => trim($A->strPost('cm_content')),
			'cm_check' => intval($A->strPost('cm_check'))
		);
		$D->update('comment', $array, array('cm_id' => $args));
		
		$A->logInsert('更新了评论 #'.$args);
		echo 'YES';
		break;

	default:
		echo 'ERR|无效请求。';
		break;
}
?>