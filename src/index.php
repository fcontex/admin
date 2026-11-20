<?php
/***
 * 名称：系统入口
 * Alan 2012.03.06
 * www.fcontex.com
*/
include 'module/startup.php';

if ($A->site['site_domainlock'] && $_SERVER['HTTP_HOST']!=$A->site['site_domain'])
{
	header('HTTP/1.1 301 Moved Permanently');
	header("location: http://".$A->site['site_domain'].$R->getPageUrl('*'));
}
else $R->dispatch();
?>