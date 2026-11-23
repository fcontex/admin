<?php
/***
 * 名称：登录失败限流
 * 说明：按 IP 与用户名双维度统计失败次数，文件存储并以 flock 原子更新；IPv4 / IPv6 全量地址均参与计数。
 * Alan 2026.11
 * www.fcontex.com
*/

if (!defined('PATH_STORE')) exit('Access Denied.');

class loginlimit
{
	//计数文件头，避免文件被直接访问时输出内容
	const GUARD = '<?php exit; ?>';
	
	//组装计数文件路径：IP 维度 + 账号维度（账号不区分大小写）
	private static function paths($ip, $username)
	{
		$dir = PATH_STORE.'cache/login/';
		$paths = array();
		$ip = trim((string)$ip);
		if ($ip !== '') $paths[] = $dir.'ip_'.md5($ip).'.php';
		$username = strtolower(trim((string)$username));
		if ($username !== '') $paths[] = $dir.'us_'.md5($username).'.php';
		return $paths;
	}
	
	//取阈值，非法值回落默认
	private static function conf($config, $key, $default)
	{
		$value = isset($config[$key]) ? intval($config[$key]) : 0;
		return $value > 0 ? $value : $default;
	}
	
	//加锁读取—判断—写回，返回 array('hits', 'left', 'locked')
	private static function update($path, $isFail, $config)
	{
		$attempts = self::conf($config, 'loginAttempts', 5);
		$window = self::conf($config, 'loginWindow', 15) * 60;
		$lock = self::conf($config, 'loginLock', 15) * 60;
		
		$dir = dirname($path);
		if (!is_dir($dir)) @mkdir($dir, 0755, TRUE);
		
		$existed = is_file($path);
		$fp = @fopen($path, 'c+');
		if ($fp === FALSE)
		{
			//无法落盘时不阻断登录，避免因目录权限问题锁死整站
			return array('hits' => 0, 'left' => $attempts, 'locked' => 0);
		}
		
		flock($fp, LOCK_EX);
		$raw = trim(substr((string)stream_get_contents($fp), strlen(self::GUARD)));
		$hits = 0;
		$first = 0;
		$locked = 0;
		if ($raw !== '')
		{
			$part = explode('|', $raw);
			$hits = intval(isset($part[0]) ? $part[0] : 0);
			$first = intval(isset($part[1]) ? $part[1] : 0);
			$locked = intval(isset($part[2]) ? $part[2] : 0);
		}
		
		$now = time();
		//锁定期满或统计窗口过期，计数归零
		if ($locked > 0 && $locked <= $now)
		{
			$hits = 0;
			$first = 0;
			$locked = 0;
		}
		elseif ($locked === 0 && $first > 0 && ($first + $window) <= $now)
		{
			$hits = 0;
			$first = 0;
		}
		
		if ($isFail && $locked === 0)
		{
			if ($hits === 0) $first = $now;
			$hits++;
			if ($hits >= $attempts) $locked = $now + $lock;
		}
		
		rewind($fp);
		ftruncate($fp, 0);
		fwrite($fp, self::GUARD.$hits.'|'.$first.'|'.$locked);
		fflush($fp);
		flock($fp, LOCK_UN);
		fclose($fp);
		
		//计数已归零的文件直接清掉，避免缓存目录堆积
		if ($existed && $hits === 0 && $locked === 0) @unlink($path);
		
		$left = $attempts - $hits;
		return array('hits' => $hits, 'left' => ($left > 0 ? $left : 0), 'locked' => $locked);
	}
	
	//是否处于锁定期，返回剩余分钟数（0 表示未锁定）
	public static function locked($ip, $username, $config = array())
	{
		$minutes = 0;
		foreach (self::paths($ip, $username) as $path)
		{
			$info = self::update($path, FALSE, $config);
			if ($info['locked'] > 0)
			{
				$left = intval(ceil(($info['locked'] - time()) / 60));
				if ($left > $minutes) $minutes = $left;
			}
		}
		return $minutes;
	}
	
	//登记一次失败，返回剩余可试次数（0 表示本次已触发锁定）
	public static function fail($ip, $username, $config = array())
	{
		$left = self::conf($config, 'loginAttempts', 5);
		foreach (self::paths($ip, $username) as $path)
		{
			$info = self::update($path, TRUE, $config);
			if ($info['left'] < $left) $left = $info['left'];
		}
		return $left;
	}
	
	//登录成功后清空计数
	public static function clear($ip, $username)
	{
		foreach (self::paths($ip, $username) as $path)
		{
			if (is_file($path)) @unlink($path);
		}
	}
}
?>
