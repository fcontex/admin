<?php
/***
 * 名称：查询构造器（系统唯一的读取入口）
 * 用法：DB::table('content')->field('ct_id')->order('ct_id', 'desc')->rows();
 * 约定：所有链式调用以 table() 开头（table() 会重置全部状态），每次调用返回新实例
 * 边界：字段名/表名/排序方向全部校验；值一律 ? 绑定；表达式必须显式 *Raw()
 * 写入：请使用数据驱动 $D 的 insert/update/delete/exec，本类只负责读取
*/

final class query
{
	private $db = NULL;
	
	private $table = '';
	private $fields = array();
	private $wheres = array();
	private $params = array();
	private $orders = array();
	private $limit = '';
	
	private $fieldRule = '/^(\*|[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?)$/';
	
	public function __construct($database = NULL)
	{
		$this->db = $database ? $database : FCApplication::sharedDataBase();
	}
	
	/***
	 * 指定数据表并重置全部查询状态
	 * 参数为裸表名，前缀由数据驱动统一补全，如 content
	*/
	public function table($table)
	{
		$this->table = $this->db->tableName($table);
		$this->fields = array();
		$this->wheres = array();
		$this->params = array();
		$this->orders = array();
		$this->limit = '';
		return $this;
	}
	
	/***
	 * 指定字段 支持 'a,b' 或 array('a','b')
	*/
	public function field($fields)
	{
		if (is_array($fields))
		{
			foreach ($fields as $item) $this->field($item);
			return $this;
		}
		
		foreach (explode(',', $fields) as $item)
		{
			$item = trim($item);
			if ($item === '') continue;
			$this->validateField($item);
			$this->fields[] = $item;
		}
		return $this;
	}
	
	/***
	 * 追加字段表达式（仅常量表达式 不接收用户输入）
	*/
	public function fieldRaw($expr)
	{
		$this->fields[] = $expr;
		return $this;
	}
	
	/***
	 * 追加where条件 使用 ? 绑定
	*/
	public function where($sql, $params = array())
	{
		$this->wheres[] = $sql;
		if (!empty($params))
		{
			foreach ($params as $value) $this->params[] = $value;
		}
		return $this;
	}
	
	/***
	 * 追加IN条件 空数组生成 1=0
	*/
	public function whereIn($column, array $values)
	{
		$this->validateField($column);
		
		if (empty($values))
		{
			$this->wheres[] = '1=0';
			return $this;
		}
		
		$marks = array();
		foreach ($values as $value)
		{
			$marks[] = '?';
			$this->params[] = $value;
		}
		$this->wheres[] = $column.' in ('.implode(', ', $marks).')';
		return $this;
	}
	
	/***
	 * 追加常量条件片段（仅常量表达式 不接收用户输入）
	*/
	public function whereRaw($sql)
	{
		$this->wheres[] = $sql;
		return $this;
	}
	
	/***
	 * 排序 方向仅允许 asc|desc
	*/
	public function order($column, $dir = 'asc')
	{
		$this->validateField($column);
		$dir = strtolower($dir);
		if ($dir != 'asc' && $dir != 'desc')
		{
			throw new Exception('非法的排序方向：'.$dir);
		}
		$this->orders[] = $column.' '.$dir;
		return $this;
	}
	
	/***
	 * 追加排序表达式（仅常量表达式 不接收用户输入）
	*/
	public function orderRaw($expr)
	{
		$this->orders[] = $expr;
		return $this;
	}
	
	/***
	 * 限制条数 limit($count) 或 limit($offset, $count)
	*/
	public function limit($offset, $count = NULL)
	{
		if ($count === NULL)
		{
			$this->limit = intval($offset);
		}
		else
		{
			$this->limit = intval($offset).', '.intval($count);
		}
		return $this;
	}
	
	/***
	 * 生成SQL及参数
	*/
	public function build()
	{
		return $this->compile($this->fieldSql());
	}
	
	/***
	 * 取一行 无记录返回 NULL
	*/
	public function row()
	{
		$stmt = $this->execute($this->fieldSql());
		if (!($stmt instanceof PDOStatement)) return NULL;
		$row = $stmt->fetch();
		return $row === FALSE ? NULL : $row;
	}
	
	/***
	 * 取多行 空结果返回 array()
	*/
	public function rows()
	{
		$stmt = $this->execute($this->fieldSql());
		return $stmt instanceof PDOStatement ? $stmt->fetchAll() : array();
	}
	
	/***
	 * 取首行首列 可指定字段
	*/
	public function value($column = NULL, $default = NULL)
	{
		$stmt = $this->execute($this->columnSql($column));
		if (!($stmt instanceof PDOStatement)) return $default;
		$row = $stmt->fetch();
		if ($row === FALSE) return $default;
		return reset($row);
	}
	
	/***
	 * 取首列数组 可指定字段
	*/
	public function column($column = NULL)
	{
		$stmt = $this->execute($this->columnSql($column));
		$array = array();
		if ($stmt instanceof PDOStatement)
		{
			while (($row = $stmt->fetch()) !== FALSE) $array[] = reset($row);
		}
		return $array;
	}
	
	/***
	 * 统计记录数
	*/
	public function count()
	{
		$sql = 'select count(*) as total from '.$this->table;
		if (!empty($this->wheres)) $sql .= ' where '.implode(' and ', $this->wheres);
		
		$stmt = $this->db->run($sql, $this->params);
		if (!($stmt instanceof PDOStatement)) return 0;
		
		$row = $stmt->fetch();
		return $row === FALSE ? 0 : intval($row['total']);
	}
	
	/***
	 * 记录是否存在
	*/
	public function exists()
	{
		return $this->count() > 0;
	}
	
	/***
	 * 分页查询 返回当前页数据
	 * 总数由构造器统计，页码与翻页条由 pageturnner 负责
	*/
	public function page($pager)
	{
		$pager->build($this->count());
		$this->limit(($pager->page - 1) * $pager->size, $pager->size);
		return $this->rows();
	}
	
	////////内部方法////////
	
	private function execute($fields)
	{
		list($sql, $params) = $this->compile($fields);
		return $this->db->run($sql, $params);
	}
	
	private function compile($fields)
	{
		$sql = 'select '.$fields.' from '.$this->table;
		if (!empty($this->wheres)) $sql .= ' where '.implode(' and ', $this->wheres);
		if (!empty($this->orders)) $sql .= ' order by '.implode(', ', $this->orders);
		if ($this->limit !== '') $sql .= ' limit '.$this->limit;
		return array($sql, $this->params);
	}
	
	private function fieldSql()
	{
		return empty($this->fields) ? '*' : implode(', ', $this->fields);
	}
	
	private function columnSql($column)
	{
		if ($column === NULL) return $this->fieldSql();
		$this->validateField($column);
		return $column;
	}
	
	private function validateField($field)
	{
		if (!preg_match($this->fieldRule, $field))
		{
			throw new Exception('非法的字段名：'.$field);
		}
	}
}
?>
