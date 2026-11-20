<?php
/***
 * 名称：用户模块
 * Alan 2012.03
 * www.fcontex.com
*/

if (!defined('SYSTEM_INCLUDE')) exit('Access Denied.');

switch ($R->action)
{
	////前端请求////
	
	////后端请求////
	case 'fc.group.select':
		$U->checkRights('user.group.select');
		$T->show('group.select');
		break;
	
	case 'fc.group.insert':
		$U->checkRights('user.group.insert');
		$T->show('group.insert');
		break;
	
	case 'fc.group.update':
		$U->checkRights('user.group.update');
		$T->show('group.update');
		break;
	
	case 'fc.user.select':
		$U->checkRights('user.select');
		$T->show('user.select');
		break;
	
	case 'fc.user.insert':
		$U->checkRights('user.insert');
		$T->show('user.insert');
		break;
	
	case 'fc.user.update':
		$args = intval($R->getParam(2));
		$T->bind('args', $args);
		if (!$U->hasRights('user.update'))
		{
			$uid = isset($_SESSION['userInfo']) ? $_SESSION['userInfo']['us_id'] : 0;
			if ($uid != $args)
			{
				$A->pageError();
				exit();
			}
		}
		$T->show('user.update');
		break;
	
	case 'fc.user.login':
		$T->show('user.login');
		break;
	
	case 'fc.user.fetchcode':
		$T->show('user.fetchcode');
		break;
	
	case 'fc.user.resetcode':
		$code = $A->strGet('code');
		$code = explode('|', $A->strDeCode($code));
		if (count($code) != 3)
		{
			$A->pageError('参数错误。');
		}
		if (trim($code[0]) == '' || trim($code[1]) == '' || trim($code[2]) == '')
		{
			exit('ERR|参数错误。');
		}
		if (DB::table('user')->where('us_resetcodetime = ? and us_username = ? and us_email = ?', array($code[2], $code[0], $code[1]))->count() < 1)
		{
			$A->pageError('错误：没有找到用户，可能链接已经被使用。');
		}
		if (((time() - $code[2]) / 60) > 15)
		{
			$A->pageError('错误：链接已经过期，请重新找回密码以获取新的地址。');
		}
		$T->show('user.resetcode');
		break;
	
	default:
		$A->pageError('无效请求。');
		break;
}
?>