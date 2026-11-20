<?php
/***
 * 名称：系统模块动作处理程序
 * Alan 2012.03
 * www.fcontex.com
*/

include '../../startup.php';

switch ($mode = $A->strGet('mode'))
{
	////前端请求////
	
	////后端请求////
	case 'fc.settings':
		$U->checkRights('system.site.update', TRUE);
		
		$data = $A->site;
		
		$data['site_name'] 		= $A->strPost('site_name');
		$data['site_domain'] 		= str_replace('http://', '', $A->strPost('site_domain'));
		$data['site_domainlock']	= intval($A->strPost('site_domainlock')) ? TRUE : FALSE;
		//$data['site_rooturl'] 		= 'http://'.preg_replace(array('/^http:\/\//i', '/\/$/i'), '', $A->strPost('site_rooturl'));
		$data['site_title'] 		= $A->strPost('site_title');
		$data['site_keywords'] 		= $A->strPost('site_keywords');
		$data['site_description'] 	= $A->strPost('site_description');
		$data['site_rewrite'] 		= intval($A->strPost('site_rewrite')) ? TRUE : FALSE;
		$data['site_pagesize'] 		= intval($A->strPost('site_pagesize'));
		$data['site_email'] 		= $A->strPost('site_email');
		$data['site_counter'] 		= $A->strPost('site_counter');
		$data['site_copyright'] 	= $A->strPost('site_copyright');
		$data['site_emailserver'] 	= $A->strPost('site_emailserver');
		$site_emailpassword = $A->strPost('site_emailpassword');
		$data['site_emailpassword'] = $site_emailpassword==$data['site_emailpassword'] ? $site_emailpassword : $A->strEnCode($site_emailpassword);
		
		$A->saveConfig('system.site', $data);
		$A->logInsert('更新了站点配置#system');
		echo 'YES';
		break;
	
	case 'fc.module.insert':
	case 'fc.module.update':
		$U->checkRights('system.modules.insert', TRUE);
		$U->checkRights('system.modules.update', TRUE);
		
		$args = $A->strPost('args');
		$path = PATH_MODULES.$args.'/module.php';
		include $path;
		$class = 'FCModule'.ucfirst($args);
		if (!class_exists($class))
		{
			echo '参数错误 [ '.$args.' ] 。';
		}
		else
		{
			$module = new $class();
			
			//校验菜单引用的权限是否在本模块权限中声明
			//菜单结构：分组 => array('icon'=>可选, 'items'=>array(菜单项=>array('url','icon','rights')))
			$invalid = array();
			foreach ($module->menus as $group => $groupData)
			{
				foreach ((array)$groupData['items'] as $text => $menu)
				{
					if (isset($menu['rights']) && !isset($module->rights[$menu['rights']]))
					{
						$invalid[] = $group.' / '.$text.' 引用的权限 '.$menu['rights'];
					}
				}
			}
			if (!empty($invalid))
			{
				echo 'ERR|菜单权限未声明：'.implode('；', $invalid).'。';
				break;
			}
			
			//缓存模块组
			$modules = $A->loadConfig('system.modules');
			$modules[$args] = $module->basic;
			$modules[$args]['menus'] = $module->menus;
			$modules[$args]['rights'] = $module->rights;
			$A->saveConfig('system.modules', $modules);
			
			//执行安装函数
			$module->install();
			$A->logInsert(($mode=='module.insert' ? '安装' : '更新') . '了模块 #'.$args);
			echo 'YES';
		}
		break;
		
	case 'fc.module.disable':
	case 'fc.module.delete':
		$U->checkRights('system.modules.disable', TRUE);
		$U->checkRights('system.modules.delete', TRUE);
		
		$args = $A->strPost('args');
		$default = isset($A->site['site_defaultmodule']) ? $A->site['site_defaultmodule'] : '';
		if (in_array(strtoupper($args), explode('|', SYSTEM_MODULE)))
		{
			echo 'ERR|核心模块不允许禁用。';
		}
		elseif ($args != '' && $args == $default)
		{
			echo 'ERR|默认模块不允许禁用，请先将其他模块设为默认。';
		}
		else
		{
			$path = PATH_MODULES.$args.'/module.php';
			include $path;
			$class = 'FCModule'.ucfirst($args);
			if (!class_exists($class))
			{
				echo '参数错误 [ '.$args.' ] 。';
			}
			else
			{
				//清理模块组缓存
				$module = new $class();
				$modules = $A->loadConfig('system.modules');
				unset($modules[$args]);
				$module->uninstall();
				$A->saveConfig('system.modules', $modules);

				$A->logInsert(($mode=='module.disable' ? '禁用' : '删除') . '了模块 #'.$args);
				echo 'YES';
			}
		}
		break;
		
	case 'fc.module.order':
		$U->checkRights('system.modules.order', TRUE);
		
		$args = $A->strPost('args');
		$old = $A->loadConfig('system.modules');
		$new = array();
		foreach (explode(',', $args) as $key)
		{
			if (isset($old[$key])) $new[$key] = $old[$key];
		}
		$A->saveConfig('system.modules', $new);

		$A->logInsert('更新了模块排序');

		echo 'YES';
		break;

	//设置默认模块
	case 'fc.module.default':
		$U->checkRights('system.modules.update', TRUE);

		$args = $A->strPost('args');
		$modules = $A->loadConfig('system.modules');
		if (!isset($modules[$args]))
		{
			echo 'ERR|模块 [ '.$args.' ] 未安装。';
		}
		else
		{
			//默认模块名保存到站点配置 路由引擎自动读取
			$data = $A->site;
			$data['site_defaultmodule'] = $args;
			$A->saveConfig('system.site', $data);
			$A->logInsert('设置默认模块 #'.$args);
			echo 'YES';
		}
		break;
	
	case 'fc.config.skin':
		$U->checkRights('system.login', TRUE);

		$args = trim($A->strPost('args'));
		//皮肤名必须是合法目录名且目录真实存在，防止路径注入与脏值入库
		//注意用 PATH_MODULES 定位 system 模块：control 请求路由下 DIR_MODULE 解析为默认模块，PATH_MODULE 不可靠
		if ($args == '' || !preg_match('/^[a-z0-9_-]+$/i', $args) || !is_dir(PATH_MODULES.'system/skins/'.$args))
		{
			echo 'ERR|皮肤设置失败。';
		}
		else
		{
			//个人皮肤：落库当前用户并同步会话 reload 后 URL_SKIN 按新皮肤渲染
			//Cookie 记忆上次显示皮肤 供登录页回落（第三参为绝对过期时间戳 30天后过期 每次登录/切换续写 滚动等效长期有效）
			$D->update('user', array('us_skin' => $args), array('us_id' => intval($_SESSION['userInfo']['us_id'])));
			$_SESSION['userInfo']['us_skin'] = $args;
			$A->setCookie('skin', $args, time() + 2592000);
			$A->logInsert('更换了皮肤 # '.$args);
			echo 'YES';
		}
		break;
	
	case 'fc.logs.delete':
		$U->checkRights('system.logs.delete', TRUE);
		
		$args = $A->strPost('args');
		if (trim($args) == '')
		{
			echo 'ERR|请选择记录。';
		}
		else 
		{
			if ($args == '*')
			{
				$D->delete('logs', '1=1');
			}
			elseif (preg_match('/^[0-9]+(,[0-9]+)*$/', $args))
			{
				$D->delete('logs', 'lg_id in ('.$args.')');
			}
			else
			{
				echo 'ERR|错误的参数。';
				break;
			}
			
			$A->logInsert('清理了日志');
			
			echo 'YES';
		}
		break;
	
	case 'fc.file.update':
		$U->checkRights('system.file.update', TRUE);
		
		$args = $A->strPost('args');
		$at_filename = trim($A->strPost('at_filename'));
		if (!is_numeric($args))
		{
			echo 'ERR|错误的参数[ '.$args.' ]。';
		}
		elseif ($at_filename == '')
		{
			echo 'ERR|请输入文件名。|at_filename_'.$args;
		}
		else
		{
			$updatearr = array
			(
				'at_filename' => $at_filename
			);
			$D->update('attached', $updatearr, array('at_id' => $args));
			$A->logInsert('编辑了附件 #'.$args);
			echo 'YES';
		}
		break;
	
	case 'fc.file.delete':
		$U->checkRights('system.file.delete', TRUE);
		
		$args = $A->strPost('args');
		if (trim($args) == '')
		{
			echo 'ERR|请选择记录。';
		}
		else 
		{
			$arr = explode(',', $args);
			$uploadPath = PATH_STORE.$A->system['uploadDir'].'/';
			$files = array();
			try
			{
				$D->transaction(function($D) use ($arr, $uploadPath, &$files)
				{
					foreach ($arr as $id)
					{
						$id = intval($id);
						if (!$id) continue;
						$rst = DB::table('attached')->field('at_remote, at_dir, at_filenewname')->where('at_id = ?', array($id))->row();
						if (!$rst) continue;
						if (!$rst['at_remote'])
						{
							$files[] = $uploadPath.$rst['at_dir'].$rst['at_filenewname'];
						}
						$D->delete('attached', array('at_id' => $id));
					}
				});
			}
			catch (Exception $e)
			{
				echo 'ERR|删除附件失败。';
				break;
			}
			
			//记录删除成功后清理物理文件 并自底向上清理空目录链（非空目录rmdir必然失败 静默跳过即为并发安全）
			$rootReal = rtrim(str_replace('\\', '/', realpath(PATH_STORE.$A->system['uploadDir'].'/')), '/');
			$dirs = array();
			foreach ($files as $path)
			{
				if (file_exists($path) && unlink($path))
				{
					$dirs[dirname($path)] = TRUE;
				}
			}
			foreach (array_keys($dirs) as $dir)
			{
				while (true)
				{
					$real = str_replace('\\', '/', realpath($dir));
					//上界保护：须位于上传根目录之内（防历史脏at_dir越界）且不等于根目录本身
					if ($real === false || strlen($real) <= strlen($rootReal) || strpos($real.'/', $rootReal.'/') !== 0) break;
					if (!@rmdir($dir)) break;
					$dir = dirname($dir);
				}
			}
			$A->logInsert('删除了附件 # '.$args);
			echo 'YES';
		}
		break;
	
	case 'fc.file.upload':
		$U->checkRights('system.file.upload', TRUE);
		
		$fileDir = $A->strPost('dir');
		//目录名白名单 防止../穿越写
		if (!preg_match('/^[a-z0-9_-]+$/i', $fileDir)) $fileDir = 'content';
		$fileType = $A->strPost('type');
		$suffix = $A->system['uploadSuffix'];
		if (isset($suffix[$fileType]))
		{
			$suffix = $suffix[$fileType];
		}
		else
		{
			if ($fileType == 'all')
			{
				$suffix = implode(',', $suffix);
			}
			else $suffix = $suffix['image'];
		}
		$upload = $A->loadLibrary('fileupload');
		$upload->fileLimitSuffix = $suffix;
		$upload->fileNewDir = $fileDir;
		$data = $upload->upload();
		//上传成功
		if ($data['error'] == '')
		{
			//存入数据库
			$atToken = bin2hex(random_bytes(16));
			$insertarr = array
			(
				'at_token'			=> $atToken,
				'at_filenewname'	=> $data['fileNewName'],
				'at_size'			=> $data['fileSize'],
				'at_suffix'			=> $data['fileSuffix'],
				'at_dir'			=> $data['fileDir'],
				'at_filename'		=> $data['fileName'],
				'at_isimage'		=> $data['isImage'],
				'at_filetype'		=> $data['fileType'],
				'at_uid'			=> $_SESSION['userInfo']['us_id'],
				'at_time'			=> time()
			);
			$data['fileID'] = $D->insert('attached', $insertarr);
			$data['filePipe'] = $R->getPageUrl('system/file/'.$atToken, '', DIR_SITE);
			
			$A->logInsert('上传了附件 # '.$data['fileID'], $_SESSION['userInfo']['us_id']);
		}
		$data['fileUrl'] = DIR_SITE.DIR_STORE.'/'.$A->system['uploadDir'].'/'.$data['fileDir'].$data['fileNewName'];
		$data['fileIcon'] = $A->fileIcon($A->fileSuffix($data['fileName']));
		echo json_encode($data);
		break;
	
	case 'fc.file.remote':
		$U->checkRights('system.file.upload', TRUE);
		
		$image = intval($A->strPost('image'));
		$url = $A->strPost('url');
		$title = $A->strPost('title');
		$atToken = bin2hex(random_bytes(16));
		$insertarr = array
		(
			'at_token'		=> $atToken,
			'at_uid'		=> $_SESSION['userInfo']['us_id'],
			'at_filename'	=> $title,
			'at_isimage'	=> $image,
			'at_remote'		=> 1,
			'at_url'		=> $url,
			'at_title'		=> $title,
			'at_time'		=> time()
		);
		$insertid = $D->insert('attached', $insertarr);
		$A->logInsert('添加了附件 # '.$insertid, $_SESSION['userInfo']['us_id']);
		$data = array
		(
			'fileID' => $insertid,
			'filePipe' => $R->getPageUrl('system/file/'.$atToken, '', DIR_SITE)
		);
		echo 'YES|'.json_encode($data);
		break;
	
	case 'fc.cache.clean':
		$U->checkRights('system.cache', TRUE);
		
		$args = $A->strPost('args');
		$rootReal = rtrim(str_replace('\\', '/', realpath(PATH_CACHE)), '/');
		foreach (explode(',', $args) as $name)
		{
			$real = str_replace('\\', '/', realpath(PATH_CACHE.$name));
			//边界保护：目标必须真实存在且位于缓存根目录之内 防止穿越删除任意文件/目录
			if ($real === false || strpos($real.'/', $rootReal.'/') !== 0) continue;
			if (is_dir($real))
			{
				$A->cleanDirectory($real);
			}
			else
			{
				unlink($real);
			}
		}
		$A->logInsert('清理了缓存 # '.$args);
		echo 'YES';
		break;
	
	case 'fc.cache.time':
		$U->checkRights('system.cache', TRUE);
		
		$args = $A->strPost('args');
		$data = $A->site;
		$data['site_cachetime'] = intval($args);
		$A->saveConfig('system.site', $data);
		
		$A->logInsert('更新了缓存时间 #'.$args);
		echo 'YES';
		break;
		
	default:
		echo 'ERR|无效请求。';
		break;
}
?>