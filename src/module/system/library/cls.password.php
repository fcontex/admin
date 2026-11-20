<?php
/***
 * 名称：密码散列封装
 * 说明：服务端统一以 password_hash 存储（PHP 5.5+）
 *       前端沿用 md5 提交，故散列对象是 md5 后的口令串
*/

final class password
{
	//生成散列
	public static function hash($plain)
	{
		return password_hash($plain, PASSWORD_DEFAULT);
	}
	
	//校验散列
	public static function verify($plain, $hash)
	{
		return $hash != '' && password_verify($plain, $hash);
	}
	
	//是否需要按当前算法重新散列
	public static function needsRehash($hash)
	{
		return password_needs_rehash($hash, PASSWORD_DEFAULT);
	}
}
?>
