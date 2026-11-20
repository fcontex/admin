<?php
/***
 * 名称：模板类
 * Joe, 2012.03
 * http://www.fcontex.com/
 */

final class template
{
	//模板文件目录
	public $root;

	//模板文件后缀
	public $ext = '.php';

	public $contents;

	private $vars = array();

	public function parse($file)
	{
		//全局公共对象
		global $A, $U, $D, $T, $R;

		//站点配置信息
		$SITE = $A->site;

		//模板变量绑定
		foreach ($this->vars as $key=>$value)
		{
			$$key = $value;
			unset($this->vars[$key]);
		}

		//加载模板文件
		$file = $this->root.$file.$this->ext;
		ob_start();
		if (!file_exists($file))
		{
			$R->print404();
		}
		else
		{
			include $file;
			$this->contents = ob_get_contents();
		}
		ob_end_clean();
	}

	//后端界面显示函数（前端主题渲染参见content模块content::display）
	public function show($file)
	{
		if (!$this->root)
		{
			$this->root = PATH_MODULE.'template/';
		}
		$this->parse($file);
		echo $this->contents;
	}

	//模板变量绑定函数
	public function bind($key, $value)
	{
		$this->vars[$key] = $value;
	}
}
?>
