<?php
/***
 * 名称：系统模块调度程序
 * Alan 2012.03
 * www.fcontex.com
*/

if (!defined('SYSTEM_INCLUDE')) exit('Access Denied.');

switch ($R->action)
{
	////前端请求////
	case 'start':
		//起始页（system为默认模块时作为站点首页 未登录时由桌面自行弹出登录层）
		//显式指定system模板目录 避免PATH_MODULE常量在嵌套分发时指向其他模块
		$T->root = PATH_MODULES.'system/template/';
		$T->show('start');
		break;

	case '404':
		$R->header404();
		//显式指定system模板目录 使用system模块自己的404模板
		$T->root = PATH_MODULES.'system/template/';
		$T->show('404');
		break;

	case 'file':
		//凭随机 token 下载（不可枚举）
		$token = strtolower($R->getParam(2));
		if (!preg_match('/^[a-f0-9]{32}$/', $token))
		{
			$R->print404();
		}
		
		$rst = DB::table('attached')->field('at_id, at_filename, at_filenewname, at_dir, at_remote, at_url')->where('at_token = ?', array($token))->row();
		if (empty($rst))
		{
			$R->print404();
		}
		$id = intval($rst['at_id']);
		//下载计数（2秒节流 分块续传不会刷爆）
		if (!isset($_SESSION['file_'.$id]) || time()-intval($_SESSION['file_'.$id])>2)
		{
			$_SESSION['file_'.$id] = time();
			$D->update('attached', array('at_hits' => $D->raw('at_hits + 1')), array('at_id' => $id));
		}
		
		//远程附件：不落盘 直接跳转（须先于文件系统检查 远程记录的at_dir/at_filenewname为空会误判）
		if ($rst['at_remote'])
		{
			header('Location: '.$rst['at_url']);
			exit;
		}
		
		$fname = PATH_STORE.$A->system['uploadDir'].'/'.$rst['at_dir'].$rst['at_filenewname'];
		if (!is_file($fname))
		{
			$R->print404();
		}
		//不再按当前 uploadSize 拦截：上传时已按当时限额校验 调小限额不应让存量附件失联
		$fsize = filesize($fname);
		
		//断点续传（仅单段Range；无Range/多段/畸形一律走200整文件）
		$start = 0;
		$end = $fsize - 1;
		$partial = FALSE;
		if (isset($_SERVER['HTTP_RANGE']) && preg_match('/^bytes=(\d*)-(\d*)$/', trim($_SERVER['HTTP_RANGE']), $m) && ($m[1] !== '' || $m[2] !== ''))
		{
			if ($m[1] === '')
			{
				//bytes=-N 后缀段（播放器常用）
				$len = intval($m[2]);
				if ($len > 0)
				{
					$start = max(0, $fsize - $len);
					$partial = TRUE;
				}
			}
			else
			{
				$start = intval($m[1]);
				$end = ($m[2] === '') ? $fsize - 1 : min(intval($m[2]), $fsize - 1);
				$partial = TRUE;
			}
			if ($partial && ($start > $end || $start >= $fsize))
			{
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				header('Content-Range: bytes */'.$fsize);
				exit;
			}
		}
		
		//文件名加固：去换行与双引号 中文走RFC 5987 ASCII兜底
		$filename = str_replace(array("\r", "\n", '"'), '', $rst['at_filename']);
		$filenameAscii = preg_replace('/[^\x20-\x7e]/', '_', $filename);
		if ($filenameAscii == '') $filenameAscii = 'download';
		
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.$filenameAscii.'"; filename*=UTF-8\'\''.rawurlencode($filename));
		header('Accept-Ranges: bytes');
		if ($partial)
		{
			header('HTTP/1.1 206 Partial Content');
			header('Content-Range: bytes '.$start.'-'.$end.'/'.$fsize);
			header('Content-Length: '.($end - $start + 1));
		}
		else
		{
			header('Content-Length: '.$fsize);
		}
		
		//关闭路由层缓冲逐块输出 缓冲区关闭后必须exit 否则路由收尾会告警污染二进制
		ignore_user_abort(TRUE);
		set_time_limit(0);
		while (ob_get_level() > 0) ob_end_clean();
		$fp = fopen($fname, 'rb');
		fseek($fp, $start);
		$remaining = $end - $start + 1;
		while ($remaining > 0 && !feof($fp))
		{
			$chunk = fread($fp, min(65536, $remaining));
			if ($chunk === FALSE || $chunk === '') break;
			echo $chunk;
			$remaining -= strlen($chunk);
			flush();
		}
		fclose($fp);
		exit;
	
	////后端请求////
	case 'fc.settings':
		$U->checkRights('system.site.select');
		$T->show('config.site');
		break;
	
	case 'fc.menus':
		$U->checkRights('system.login');
		$T->show('menus');
		break;
	
	case 'fc.logs':
		$U->checkRights('system.logs.select');
		$T->show('logs');
		break;
	
	case 'fc.cache':
		$U->checkRights('system.cache');
		$T->show('cache');
		break;
	
	case 'fc.modules':
		$U->checkRights('system.modules.select');
		$T->show('modules');
		break;
	
	case 'fc.files.upload':
		$U->checkRights('system.file.upload');
		$T->show('files.upload');
		break;
	
	case 'fc.files.url':
		$U->checkRights('system.file.upload');
		$T->show('files.url');
		break;
	
	case 'fc.files.server':
		$U->checkRights('system.file.upload');
		$T->show('files.server');
		break;
	
	case 'fc.files':
		$U->checkRights('system.file.select');
		$T->show('files.select');
		break;
	
	case 'fc.config.skins':
		$U->checkRights('system.login');
		$T->show('config.skins');
		break;

	case 'fc.login.status';
		$T->show('login.status');
		break;
	
	default:
		$T->show('start');
		break;
}
?>