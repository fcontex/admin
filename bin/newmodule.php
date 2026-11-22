<?php
/***
 * 名称：模块脚手架
 * 用法：php bin/newmodule.php <模块名> [显示名]
 * www.fcontex.com
*/

//仅允许命令行运行
if (PHP_SAPI !== 'cli') exit('该脚本只能在命令行运行。');

if (!isset($argv[1])) usage();

$name = strtolower(trim($argv[1]));
if (!preg_match('/^[a-z][a-z0-9]*$/', $name))
{
	exit('模块名只能是小写字母开头的单词（字母数字），收到：'.$name."\n");
}

$cname = isset($argv[2]) ? trim($argv[2]) : $name;
$Name = ucfirst($name);
$date = date('Y.m');

$root = dirname(__DIR__);
$base = $root.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'module'.DIRECTORY_SEPARATOR.$name;
if (file_exists($base))
{
	exit('目录已存在，为避免覆盖已终止：'.$base."\n");
}

$templates = array
(
	'module.php' => <<<'TPL_MODULE'
<?php
/***
 * 名称：{cname}
 * www.fcontex.com
*/

//类命名方式必须是FCModule加首字母大写的目录名才会被内核识别
final class FCModule{Name}
{
	/***
	 * 模块基本信息
	*/
	public $basic = array
	(
		'for'     => '1.0.1+',
		'name'    => '{cname}',
		'icon'    => 'module.png',
		'desc'    => '{cname}。',
		'author'  => '',
		'contact' => '',
		'version' => '1.0',
		'update'  => '{date}',
		'support' => 'www.fcontex.com',
	);

	/***
	 * 模块权限字段 安装时注册到内核中
	 * 为保证全局唯一请使用模块目录名作前缀
	*/
	public $rights = array
	(
		'{name}.select' => '{cname}查看',
		'{name}.insert' => '{cname}添加',
		'{name}.update' => '{cname}编辑',
		'{name}.delete' => '{cname}删除',
	);

	public $menus = array
	(
		//单组模块：组名与模块名一致 不配组图标（回落模块图标）
		'{cname}' => array
		(
			'items' => array
			(
				'查看{cname}' => array('url'=>'fc.{name}.select', 'icon'=>'module.png', 'rights'=>'{name}.select'),
			)
		),
	);

	/***
	 * 模块安装回调函数
	*/
	public function install()
	{
		global $A;

		//创建模块数据表（已存在则跳过 卸载保留数据）
		$A->installTables('{name}');
	}

	/***
	 * 模块反安装回调函数
	*/
	public function uninstall()
	{
	}
}
?>
TPL_MODULE
,
	'index.php' => <<<'TPL_INDEX'
<?php
/***
 * 名称：{cname}调度程序
 * www.fcontex.com
*/

if (!defined('SYSTEM_INCLUDE')) exit('Access Denied.');

switch ($R->action)
{
	////前端请求////

	////后端请求////
	case 'fc.{name}.select':
		$U->checkRights('{name}.select');
		$T->show('{name}.select');
		break;

	default:
		$R->print404();
}
?>
TPL_INDEX
,
	'control/index.php' => <<<'TPL_CONTROL'
<?php
/***
 * 名称：{cname}动作处理程序
 * www.fcontex.com
*/

include '../../startup.php';

switch ($mode = $A->strGet('mode'))
{
	////前端请求////

	////后端请求////
	case 'fc.{name}.insert':
		$U->checkRights('{name}.insert', TRUE);

		$title = trim($A->strPost('{name}_name'));
		if ($title == '')
		{
			echo 'ERR|请填写名称。|{name}_name';
		}
		else
		{
			$id = $D->insert('{name}', array
			(
				'{name}_name'   => $title,
				'{name}_status' => 1,
				'{name}_time'   => time(),
			));
			$A->logInsert('添加了{cname} #'.$id);
			echo 'YES';
		}
		break;

	case 'fc.{name}.update':
		$U->checkRights('{name}.update');

		$id = intval($A->strPost('args'));
		if (!$id)
		{
			echo 'ERR|参数错误。';
		}
		else
		{
			$title = trim($A->strPost('{name}_name'));
			if ($title == '')
			{
				echo 'ERR|请填写名称。|{name}_name';
			}
			else
			{
				$D->update('{name}', array('{name}_name' => $title), array('{name}_id' => $id));
				$A->logInsert('编辑了{cname} #'.$id);
				echo 'YES';
			}
		}
		break;

	case 'fc.{name}.delete':
		$U->checkRights('{name}.delete', TRUE);

		$args = $A->strPost('args');
		if (!preg_match('/^[0-9]+(,[0-9]+)*$/', $args))
		{
			echo 'ERR|参数错误。';
		}
		else
		{
			$D->delete('{name}', '{name}_id in ('.$args.')');
			$A->logInsert('删除了{cname} #'.$args);
			echo 'YES';
		}
		break;
}
?>
TPL_CONTROL
,
	'template/{name}.select.php' => <<<'TPL_TEMPLATE'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{cname} - <?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
<meta name="robots" content="nofollow">
<link rel="stylesheet" type="text/css" href="<?php echo URL_SKIN; ?>style.css?v=<?php echo ASSET_VERSION; ?>" />
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo URL_SCRIPTS; ?>lib.system.js?v=<?php echo ASSET_VERSION; ?>"></script>
<script type="text/javascript">
function {name}(mode, args)
{
	$$.confirm({text:'确定删除[ #'+args+' ]？', ok:function()
	{
		$$.post('<?php echo $R->getCtrlUrl(); ?>?mode=' + mode, {args:args}, function(){$$.redirect();});
	}});
	return false;
}
</script>
</head>
<body>

<?php
$keyw = trim($A->strGet('keyw'));

$pager = FCApplication::sharedPageTurnner();
$Q = DB::table('{name}');
if ($keyw != '')
{
	$Q->where('{name}_name like ?', array('%'.$keyw.'%'));
}
$Q->order('{name}_id', 'desc');
$items = $Q->page($pager);
?>
<form method="get" action="###" onsubmit="$$.redirect('<?php echo $R->getPageUrl(); ?>&'+$(this).serialize());return false;">
<table class="table_tools" width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<th>
			<input type="text" class="text" name="keyw" id="search_key" size="20" value="<?php echo htmlspecialchars($keyw, ENT_QUOTES, 'UTF-8'); ?>" />
			<input type="submit" value="筛选" class="button" />
			<input type="button" value="重置" class="button" onclick="$$.redirect('<?php echo $R->getPageUrl(); ?>');" />
		</th>
		<td align="right" class="simple"><?php echo $pager->turnner; ?></td>
	</tr>
</table>
</form>
<div class="table_scroll">
<table id="table_list" class="table_list" width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr id="list_caption">
		<th width="10%"> # </th>
		<th width="40%">名称</th>
		<th width="15%">状态</th>
		<th width="20%">时间</th>
		<th width="15%">操作</th>
	</tr>
	<?php
	$n = 0;
	foreach ($items as $rst)
	{
		$n++;
	?>
	<tr>
		<td><?php echo intval($rst['{name}_id']); ?></td>
		<td><?php echo htmlspecialchars($rst['{name}_name'], ENT_QUOTES, 'UTF-8'); ?></td>
		<td><?php if ($rst['{name}_status']) echo '启用'; else echo '<cite class="status_f">停用</cite>'; ?></td>
		<td><?php echo $A->transDate($rst['{name}_time']); ?></td>
		<td><a class="delete" href="javascript:void(0);" onclick="{name}('fc.{name}.delete', <?php echo intval($rst['{name}_id']); ?>);">删除</a></td>
	</tr>
	<?php
	}
	if ($n == 0)
	{
	?>
	<tr>
		<td colspan="5" align="center">暂无记录。</td>
	</tr>
	<?php
	}
	?>
</table>
</div>

<table class="table_tools" width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<th></th>
		<td align="right"><?php echo $pager->turnner; ?></td>
	</tr>
</table>

</body>
</html>
TPL_TEMPLATE
,
	'install.mysql.sql' => <<<'TPL_SQL'
CREATE TABLE IF NOT EXISTS `fc_{name}` (
  `{name}_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `{name}_name` varchar(100) NOT NULL DEFAULT '',
  `{name}_status` tinyint(1) NOT NULL DEFAULT '1',
  `{name}_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`{name}_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
TPL_SQL
);

$search = array('{name}', '{Name}', '{cname}', '{date}');
$replace = array($name, $Name, $cname, $date);
$created = array();

foreach ($templates as $rel => $content)
{
	$content = str_replace($search, $replace, $content);
	$rel = str_replace('{name}', $name, $rel);
	$path = $base.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $rel);

	$dir = dirname($path);
	if (!is_dir($dir)) mkdir($dir, 0755, TRUE);
	file_put_contents($path, $content);
	$created[] = $rel;
}

echo '已生成模块 '.$name.'（'.$cname."）：\n";
foreach ($created as $rel) echo '  '.$rel."\n";
echo "\n下一步：\n";
echo '  1. 按需在 '.$name.'/icons/ 放入 module.png（缺省由内核兜底图标）'."\n";
echo '  2. 登录后台 → 模块管理 → 安装 '.$name."\n";

/***
 * 用法提示
*/
function usage()
{
	exit("用法：php bin/newmodule.php <模块名> [显示名]\n示例：php bin/newmodule.php device 设备管理\n");
}
?>
