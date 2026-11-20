<?php
/***
 * 名称：用户模块动作处理程序
 * Alan 2012.06
 * www.fcontex.com
*/

include '../../startup.php';

switch ($mode = $A->strGet('mode'))
{
	////前端请求////
		
	////后端请求////
	case 'fc.group.insert':
		$U->checkRights('user.group.insert', TRUE);
		
		$gr_name = trim($A->strPost('gr_name'));
		if ( $gr_name == '' )
		{
			echo 'ERR|请输入组名称。|gr_name';
		}
		else
		{
			if (is_array($rights = $A->strPost('gr_rights')))
			{
				$rights = implode(',', $rights);
			}
			
			$insertarr = array
			(
				'gr_name'	=> $gr_name,
				'gr_desc'	=> $A->strPost('gr_desc'),
				'gr_rights' => $rights,
				'gr_time'	=> time()
			);
			$insertid = $D->insert('group', $insertarr);
			
			$A->logInsert('添加了用户组 #'.$insertid);
			echo 'YES';
		}
		break;
		
	case 'fc.group.update':
		$U->checkRights('user.group.update');
		
		$gr_name = trim($A->strPost('gr_name'));
		
		$args = $A->strPost('args');
		if (!is_numeric($args))
		{
			echo 'ERR|错误的参数[ '.$args.' ]。';
		}
		elseif ( $gr_name == '' )
		{
			echo 'ERR|请输入组名称。|gr_name';
		}
		else
		{
			$rights = '';
			if (is_array($rights = $A->strPost('gr_rights')))
			{
				$rights = implode(',', $rights);
			}
		
			$updatearr = array
			(
				'gr_name'	=> $gr_name,
				'gr_desc'	=> $A->strPost('gr_desc'),
				'gr_rights'	=> $rights
			);
			$D->update('group', $updatearr, array('gr_id' => $args));
			
			$A->logInsert('编辑了用户组 #'.$args);
			echo 'YES';
		}
		break;
		
	case 'fc.group.delete':
		$U->checkRights('user.group.delete', TRUE);
		
		$args = $A->strPost('args');
		if (!preg_match('/^[0-9]+?(,[0-9]+?)*$/', $args))
		{
			echo 'ERR|未选中任何项。';
		}
		else
		{			
			$D->delete('group', 'gr_id in('.$args.')');
			
			$A->logInsert('删除了用户组 #'.$args);
			echo 'YES';
		}
		break;
		
	case 'fc.user.insert':
		$U->checkRights('user.insert', TRUE);
		
		$us_group = intval($A->strPost('us_group'));
		$us_username = trim($A->strPost('us_username'));
		$us_password = trim($A->strPost('us_password'));
		$us_password2 = trim($A->strPost('us_password2'));
		$us_name = trim($A->strPost('us_name'));
		if ( $us_group == 0 )
		{
			echo 'ERR|请选择用户分组。|us_group';
		}
		elseif ( $us_username == '' )
		{
			echo 'ERR|请输入登录帐户。|us_username';
		}
		elseif ( $us_password == '' )
		{
			echo 'ERR|请输入登录密码。|us_password_';
		}
		elseif ( $us_password != $us_password2 )
		{
			echo 'ERR|两次输入密码不一致。|us_password2_';
		}
		elseif ( $us_group == '0' )
		{
			echo 'ERR|请选择所属分组。|us_group';
		}
		elseif ( $us_name == '' )
		{
			echo 'ERR|请输入用户姓名。|us_name';
		}
		else
		{
			$insertarr = array
			(
				'us_group'		=> $us_group,
				'us_username'	=> $us_username,
				'us_password'	=> password::hash($us_password),
				'us_name'		=> $us_name,
				'us_email'		=> $A->strPost('us_email'),
				'us_phone'		=> $A->strPost('us_phone'),
				'us_address'	=> $A->strPost('us_address'),
				'us_face'		=> $A->strPost('us_face'),
				'us_time'		=> time()
			);
			$insertid = $D->insert('user', $insertarr);
			
			$A->logInsert('添加了用户 #'.$insertid);
			echo 'YES';
		}
		break;
		
	case 'fc.user.update':
		$args = $A->strPost('args');
		$uid = isset($_SESSION['userInfo']['us_id']) ? $_SESSION['userInfo']['us_id'] : 0;
		$hasRights = $U->hasRights('user.update');
		if (!$hasRights && $uid != $args)
		{
			exit('ERR|'.ERROR_NO_RIGHTS);
		}
		
		$us_group = trim($A->strPost('us_group'));
		$us_username = trim($A->strPost('us_username'));
		$us_password = trim($A->strPost('us_password'));
		$us_password2 = trim($A->strPost('us_password2'));
		$us_name = trim($A->strPost('us_name'));
		
		if ($us_group == 0 && $hasRights)
		{
			echo 'ERR|请选择用户分组。|us_group';
		}
		elseif ($us_username == '' && $hasRights)
		{
			echo 'ERR|请输入登录帐户。|us_username';
		}
		elseif ($us_password != '' && $us_password != $us_password2)
		{
			echo 'ERR|两次输入密码不一致。|us_password2_';
		}
		elseif ($us_name == '')
		{
			echo 'ERR|请输入用户姓名。|us_name';
		}
		elseif ($U->isSystemUser($args) && !$U->isSystemUser())
		{
			echo 'ERR|没有权限编辑该用户。';
		}
		else
		{
			$updatearr = array
			(
				'us_name'		=> $us_name,
				'us_email'		=> trim($A->strPost('us_email')),
				'us_phone'		=> trim($A->strPost('us_phone')),
				'us_address'	=> $A->strPost('us_address'),
				'us_desc'		=> trim($A->strPost('us_desc')),
				'us_face'		=> $A->strPost('us_face')
			);
			if ($hasRights)
			{
				$updatearr['us_username'] = $us_username;
				$updatearr['us_group'] = $us_group;
			}
			if ($us_password != '') $updatearr['us_password'] = password::hash($us_password);
			$D->update('user', $updatearr, array('us_id' => $args));
			
			if ($args == $_SESSION['userInfo']['us_id'])
			{
				$_SESSION['userInfo']['us_group']	= $us_group;
				$_SESSION['userInfo']['us_name']	= $us_name;
				$_SESSION['userInfo']['us_email']	= trim($A->strPost('us_email'));
				$_SESSION['userInfo']['us_phone']	= trim($A->strPost('us_phone'));
				$_SESSION['userInfo']['us_desc']	= trim($A->strPost('us_desc'));
				$_SESSION['userInfo']['us_face']	= $A->strPost('us_face');
			}
			
			$A->logInsert('编辑了用户 #'.$args);
			echo 'YES';
		}
		break;
		
	case 'fc.user.delete':
		$U->checkRights('user.delete', TRUE);
		
		$args = $A->strPost('args');
		if (!preg_match('/^[0-9]+?(,[0-9]+?)*$/', $args))
		{
			echo 'ERR|未选中任何项。';
		}
		else
		{
			$ids = array();
			foreach (explode(',', $args) as $id)
			{
				if (!$U->isSystemUser($id) && $U->isSystemUser())
				{
					$D->delete('user', 'us_id in('.$args.')');
					$ids[] = $id;
				}
			}
			
			$args = implode(',', $ids);
			$A->logInsert('删除了用户 #'.$args);
			echo 'YES';
		}
		break;
	
	case 'fc.user.login':
		$username = $A->strPost('username');
		$password = $A->strPost('password');
		if (trim($username) == '')
		{
			echo 'ERR|请输入用户名。';
		}
		elseif (trim($password) == '')
		{
			echo 'ERR|请输入密码。';
		}
		else 
		{
			//暂以文件形式验证次数
			$userip = $A->getUserIP();
			$filePath = PATH_STORE.'cache/login/'.md5(($userip ? $userip : 'err')).'.php';
			!file_exists($filePath) && file_put_contents($filePath, 5);
			$hit = file_get_contents($filePath);
			//以文件修改时间判断是否可以重置登录数
			if ($hit < 1)
			{
				$filemtime = filemtime($filePath);
				if (($filemtime+60*15) < time())
				{
					$hit = 5;
					file_put_contents($filePath, $hit);
				}
				else exit('ERR|重复次数过多，请'.intval(15 - (time() - $filemtime) / 60).'分钟后再试。');
			}
			
			$r = DB::table('user')->field('us_id,us_password')->where('us_username = ?', array($username))->row();
			if (!$r || !password::verify($password, $r['us_password']))
			{
				$hit--;
				file_put_contents($filePath, $hit);
				echo 'ERR|用户名或密码不正确，还可以试'.$hit.'次。';
			}
			else 
			{
				//按当前算法升级旧散列
				if (password::needsRehash($r['us_password']))
				{
					$D->update('user', array('us_password' => password::hash($password)), array('us_id' => intval($r['us_id'])));
				}
				
				//防会话固定
				session_regenerate_id(TRUE);
				
				//记住登录：库内仅存令牌散列 Cookie仅存原值
				$token = bin2hex(openssl_random_pseudo_bytes(32));
				$D->update('user', array
				(
					'us_token' => hash('sha256', $token),
					'us_tokenexpire' => time() + 604800
				), array('us_id' => intval($r['us_id'])));
				$A->setCookie('passport', $token);
				
				$rst = DB::table('user')->where('us_id = ?', array(intval($r['us_id'])))->row();
				$_SESSION['userInfo'] = $rst;
				
				$rst = DB::table('group')->where('gr_id = ?', array(intval($rst['us_group'])))->row();
				$_SESSION['userGroup'] = $rst;
				
				if (!$U->hasRights('system.login'))
				{
					exit('ERR|没有后台登录权限。');
				}
				$skin = $U->getSkin();
				//Cookie 记忆上次显示皮肤 供登录页回落（与 fc.config.skin 切换时写入配对 30天滚动续写）
				$A->setCookie('skin', basename(rtrim($skin, '/')), time() + 2592000);
				file_put_contents($filePath, 5);
				
				$A->logInsert('登录了系统 #'.$username);
				echo 'YES|'.$skin.'|'.$_SESSION['userInfo']['us_id'];
			}
		}
		break;
		
	case 'fc.user.loginout':
		if ($U->hasRights('system.login'))
		{
			$username = $_SESSION['userInfo']['us_username'];
			$logoutUid = intval($_SESSION['userInfo']['us_id']);
			$D->update('user', array('us_token' => '', 'us_tokenexpire' => 0), array('us_id' => $logoutUid));
			unset($_SESSION['userInfo']);
			unset($_SESSION['userGroup']);
			$A->setCookie('passport', '');
			
			$A->logInsert('退出了系统 #'.$username, $logoutUid);
		}
		echo 'YES';
		break;
	
	//发送重置密码链接
	case 'fc.user.fetchcode':
		$username = trim($A->strPost('username'));
		$email = trim($A->strPost('email'));
		if ($username == '')
		{
			echo 'ERR|请填写账号。|username';
		}
		elseif (!preg_match("/^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/", $email))
		{
			echo 'ERR|请填写正确的邮箱地址。||email';
		}
		else
		{
			if (DB::table('user')->where('us_username = ? and us_email = ?', array($username, $email))->count() < 1)
			{
				echo 'ERR|账号或邮箱不匹配。';
			}
			else 
			{
				$M = $A->loadLibrary('email');
				$M->sendName = 'Fcontex 官方邮件';
				$M->sendEmail = $A->site['site_email'];
				$M->sendUser = $A->site['site_email'];
				$M->sendHost = $A->site['site_emailserver'];
				$M->sendPass = $A->strDeCode($A->site['site_emailpassword']);
				$body = '您好，这是来自<a href="http://www.fcontex.com" target="_blank">Fcontex</a>一封找回密码邮件。<br /><br />';
				$time = time();
				$code = $A->strEnCode($username.'|'.$email.'|'.$time);
				$url = 'http://'.$A->site['site_domain'].$R->getPageUrl('member/fc.user.resetcode&code='.$code);
				$body .= '点击此链接设置新密码：<a href="'.$url.'" target="_blank">'.$url.'</a>';
				$body .= '<br />15分钟内有效，请尽快修改您的密码。';
				if ($M->mailTo($email, 'Fcontex 系统密码找回服务', $body, $username))
				{
					$D->update('user', array('us_resetcodetime' => $time), array('us_username' => $username));
					echo 'YES';
				}
				else echo 'ERR|'.$M->err;
			}
		}
		break;
	
	//重置密码
	case 'fc.user.resetcode':
		$code = $A->strPost('code');
		$username = trim($A->strPost('username'));
		$password_1 = trim($A->strPost('password_1'));
		$password_2 = trim($A->strPost('password_2'));
		$code = explode('|', $A->strDeCode($code));
		if (count($code) != 3)
		{
			exit('ERR|参数错误。');
		}
		if (trim($code[0]) == '' || trim($code[1]) == '' || trim($code[2]) == '')
		{
			exit('ERR|参数错误。');
		}
		if ($code[0] != $username)
		{
			exit('ERR|用户名不正确。');
		}
		if (DB::table('user')->where('us_resetcodetime = ? and us_username = ? and us_email = ?', array($code[2], $code[0], $code[1]))->count() < 1)
		{
			exit('ERR|获取用户失败。');
		}
		if (((time() - $code[2]) / 60) > 15)
		{
			exit('ERR|链接已经过期。');
		}
		if ($password_1 == '')
		{
			echo 'ERR|请填写新的密码。';
		}
		elseif ($password_2 != $password_1)
		{
			echo 'ERR|两次输入密码不一致。';
		}
		else 
		{
			$D->update('user', array('us_password' => password::hash($password_1), 'us_resetcodetime' => ''), array('us_username' => $username));
			$A->logInsert('重置了密码 #', $username);
			echo 'YES';
		}
		
		break;
	
	default:
		echo 'ERR|无效请求。';
		break;
}
?>