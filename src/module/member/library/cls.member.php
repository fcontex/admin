<?php
/***
 * 名称：用户类
 * Alan, 2012.06
 * http://www.fcontex.com/
*/
 
final class member
{
	private $app = NULL;
	
	public function __construct()
	{
		$this->app = FCApplication::sharedApplication();
	}
	
	//读取COOKIE通行证（随机令牌）来创建用户会话
	public function session($passport=NULL)
	{
		if (!$passport && !($passport = $this->app->getCookie('passport')))
		{
			return false;
		}
		
		//令牌为32字节随机值的十六进制串
		if (!is_string($passport) || strlen($passport) != 64)
		{
			return false;
		}
		
		$rstU = DB::table('user')
			->where('us_token = ? and us_tokenexpire > ?', array(hash('sha256', $passport), time()))
			->row();
		if (!$rstU)
		{
			return false;
		}
		//exit(var_export($rstU, TRUE));
		
		$rstG = DB::table('group')->where('gr_id = ?', array(intval($rstU['us_group'])))->row();
		if (!$rstG)
		{
			return false;
		}
		//exit(var_export($rstG, TRUE));
		
		$_SESSION['userInfo'] = $rstU;
		$_SESSION['userGroup'] = $rstG;
		
		//exit(var_export($_SESSION, TRUE));
		
		return TRUE;
	}
	
	//用户权限检查
	public function hasRights($rights)
	{
		if (!isset($_SESSION['userInfo']))
		{
			$this->session();
		}
		if ($this->isSystemUser())
		{
			return TRUE;	
		}
		if (isset($_SESSION['userGroup']['gr_rights']))
		{
			return in_array($rights, explode(',', $_SESSION['userGroup']['gr_rights']));
		}
		else return false;
	}
	
	public function checkRights($rights, $control=FALSE)
	{
		if ($this->hasRights($rights))
		{
			return;
		}
		if ($control)
		{
			echo 'ERR|'.ERROR_NO_RIGHTS;
		}
		else
		{
			$this->app->pageError($rights);
		}
		exit();
	}
	
	public function checkRightsRedicrect($rights, $url='')
	{
		if ($this->hasRights($rights))
		{
			return;
		}
		if ($url != '')
		{
			header('Location: '.$url);
		}
		exit();
	}
	
	//获取皮肤（四级回落：登录用户自选 us_skin → Cookie 上次显示记忆 → 出厂基准 skin_default → 内核兜底 default）
	public function getSkin()
	{
		$dir = DIR_SITE.DIR_MODULES.'/system/skins/';
		//格式先过白名单：仅 is_dir 会被 ../ 类值穿透（skins/../ 即 system/ 真实存在，不会回落）
		$skin = isset($_SESSION['userInfo']['us_skin']) ? $_SESSION['userInfo']['us_skin'] : '';
		if ($skin && preg_match('/^[a-z0-9_-]+$/i', $skin) && is_dir(PATH_MODULES.'system/skins/'.$skin)) return $dir.$skin.'/';
		$skin = $this->app->getCookie('skin');
		if ($skin && preg_match('/^[a-z0-9_-]+$/i', $skin) && is_dir(PATH_MODULES.'system/skins/'.$skin)) return $dir.$skin.'/';
		$skin = isset($this->app->system['skin_default']) ? $this->app->system['skin_default'] : '';
		if ($skin && preg_match('/^[a-z0-9_-]+$/i', $skin) && is_dir(PATH_MODULES.'system/skins/'.$skin)) return $dir.$skin.'/';
		return $dir.'default/';
	}
	
	//获取用户头像
	public function getFace($files='')
	{
		$name = substr(strrchr($files, '/'), 1);
		if ($files == '' || !file_exists(PATH_STORE.$this->app->system['uploadDir'].'/'.str_replace($name, '', $files).$name))
		{
			return URL_SKIN.'error_no_rights.png';
		}
		else return DIR_SITE.DIR_STORE.'/'.$this->app->system['uploadDir'].'/'.str_replace($name, '', $files).$name;
	}
	
	//是否为系统管理员
	public function isSystemUser($uid=0)
	{
		if (!isset($_SESSION['userInfo']['us_id']))
		{
			return false;
		}
		$uid = $uid ? $uid : $_SESSION['userInfo']['us_id'];
		return in_array($uid, explode(',', $this->app->system['system_user']));
	}
}
?>