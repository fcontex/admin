<?php
/***
 * 名称：数据库类库
 * Alan, 2012.03
 * http://www.fcontex.com/
*/

/***
 * SQL表达式对象
 * 仅用于常量表达式（如 ct_hits + 1），不接收任何用户输入
*/
final class DBExpr
{
	public $sql;
	
	public function __construct($sql)
	{
		$this->sql = $sql;
	}
}

//数据库驱动层模型
abstract class FCDataBaseDriver
{
	public $conn = NULL;
	public $hold = TRUE;
	public $charset = '';
	public $name = '';
	//表前缀缓存（安装向导改前缀后需用 setPrefix 刷新）
	protected static $prefixCache = '';
	
	////////具体的驱动需要至少实现以下接口////////
	
	//构造数据库连接
	abstract public function __construct();
	
	//析构数据库连接
	public function __destruct(){$this->close();}
	
	/***
	 * 打开数据库连接
	*/
	abstract public function connect($database, $username='', $password='', $hostname='');
	
	/***
	 * 回收资源并关闭数据库连接
	 * 参数force指定是否强制关闭持久连接
	*/
	abstract public function close($force=FALSE);
	
	/***
	 * 执行SQL语句并返回结果集
	 * @internal 仅供查询构造器 query 使用，业务代码请通过 DB::table() 读取数据
	 * 参数以占位符 ? 传入，驱动负责参数绑定
	*/
	abstract public function run($sql, $params = array());
	
	
	////////具体驱动无需实现的公共继承接口////////
	
	/***
	 * 表前缀（取自 system.database 配置）
	*/
	public function prefix()
	{
		if (empty(self::$prefixCache)) self::$prefixCache = FCApplication::loadConfig('system.database', 'DB_PFIX');
		return self::$prefixCache;
	}
	
	/***
	 * 重设表前缀缓存（安装向导写入新配置后调用，避免继续沿用旧前缀）
	*/
	public static function setPrefix($prefix)
	{
		self::$prefixCache = trim($prefix);
	}
	
	/***
	 * 预处理SQL语句（仅用于 SQL 片段中的 T[表名] 标记，如表子查询）
	*/
	public function praseSQL($sql)
	{
		return preg_replace('/T\[(.+?)\]/i', $this->prefix().'$1', $sql);
	}
	
	/***
	 * 表名归一化：写方法的第一参数是独立表名，直接补前缀
	 * 只接受裸表名（如 user），不再需要 T[...] 标记
	*/
	public function tableName($table)
	{
		$table = trim($table);
		if (!preg_match('/^[A-Za-z0-9_]+$/', $table))
		{
			throw new Exception('非法的数据表名：'.$table);
		}
		return $this->prefix().$table;
	}
	
	/***
	 * 执行写语句（含DDL），返回影响行数
	*/
	public function exec($sql, $params = array())
	{
		$result = $this->run($sql, $params);
		if ($result instanceof PDOStatement) return $result->rowCount();
		if (is_int($result)) return $result;
		return 0;
	}
	
	/***
	 * 最后插入记录的自增ID
	*/
	public function lastId()
	{
		return $this->conn ? intval($this->conn->lastInsertId()) : 0;
	}
	
	/***
	 * 数据库是否已连接
	*/
	public function connected()
	{
		return $this->conn ? TRUE : FALSE;
	}
	
	/***
	 * 声明SQL表达式（仅常量表达式）
	*/
	public function raw($sql)
	{
		return new DBExpr($sql);
	}
	
	/***
	 * 快速添加数据
	 * 以关联数组方式输入一条记录，全部值以参数绑定
	 * 返回最后插入的自增ID
	*/
	public function insert($table, array $data)
	{
		if (empty($data)) return 0;
		
		$table = $this->tableName($table);
		$keys = array();
		$marks = array();
		$params = array();
		foreach ($data as $key => $value)
		{
			$keys[] = $key;
			if ($value instanceof DBExpr)
			{
				$marks[] = $value->sql;
			}
			else
			{
				$marks[] = '?';
				$params[] = $value;
			}
		}
		$sql = 'insert into '.$table.' ('.implode(', ', $keys).') values ('.implode(', ', $marks).')';
		$this->exec($sql, $params);
		return $this->lastId();
	}
	
	/***
	 * 快速更新数据
	 * $data 的值可为标量（参数绑定）或 DBExpr（常量表达式）
	 * $where 为数组时按字段绑定；为字符串时原样拼接（仅限已校验的 in 列表或 1=1）
	*/
	public function update($table, array $data, $where = '')
	{
		if (empty($data)) return 0;
		
		$table = $this->tableName($table);
		$params = array();
		$sets = array();
		foreach ($data as $key => $value)
		{
			if ($value instanceof DBExpr)
			{
				$sets[] = $key.' = '.$value->sql;
			}
			else
			{
				$sets[] = $key.' = ?';
				$params[] = $value;
			}
		}
		
		list($whereSql, $whereParams) = $this->parseWhere($where);
		$params = array_merge($params, $whereParams);
		
		return $this->exec('update '.$table.' set '.implode(', ', $sets).' where '.$whereSql, $params);
	}
	
	/***
	 * 快速删除数据
	 * $where 为数组时按字段绑定；为字符串时原样拼接（仅限已校验的 in 列表或 1=1）
	*/
	public function delete($table, $where = '')
	{
		$table = $this->tableName($table);
		list($whereSql, $params) = $this->parseWhere($where);
		return $this->exec('delete from '.$table.' where '.$whereSql, $params);
	}
	
	/***
	 * 解析where条件 返回 array(条件SQL, 参数数组)
	*/
	private function parseWhere($where)
	{
		if (empty($where)) return array('1', array());
		
		if (is_array($where))
		{
			$arr = array();
			$params = array();
			foreach ($where as $key => $value)
			{
				$arr[] = $key.' = ?';
				$params[] = $value;
			}
			return array(implode(' and ', $arr), $params);
		}
		
		return array($where, array());
	}
	
	////////事务接口////////
	
	public function beginTransaction()
	{
		return $this->conn ? $this->conn->beginTransaction() : FALSE;
	}
	
	public function commit()
	{
		return $this->conn ? $this->conn->commit() : FALSE;
	}
	
	public function rollback()
	{
		return $this->conn ? $this->conn->rollBack() : FALSE;
	}
	
	public function inTransaction()
	{
		return $this->conn ? $this->conn->inTransaction() : FALSE;
	}
	
	/***
	 * 以闭包方式执行事务，异常时回滚并向上抛出
	*/
	public function transaction($callback)
	{
		$this->beginTransaction();
		try
		{
			$result = call_user_func($callback, $this);
			$this->commit();
			return $result;
		}
		catch (Exception $e)
		{
			$this->rollback();
			throw $e;
		}
	}
}

//数据库驱动 - PDO（MySQL）
final class DBDriverPdo extends FCDataBaseDriver
{
	public function __construct()
	{
		$this->name = 'pdo';
	}
	
	public function connect($database, $username='', $password='', $hostname='')
	{
		if (empty($database) || empty($hostname))
		{
			return;
		}
		
		$dsn = 'mysql:host='.$hostname.';dbname='.$database.';charset='.$this->charset;
		$options = array
		(
			PDO::ATTR_ERRMODE				=> PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE	=> PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES		=> FALSE,
			PDO::ATTR_STRINGIFY_FETCHES		=> FALSE
		);
		if ($this->hold) $options[PDO::ATTR_PERSISTENT] = TRUE;
		
		try
		{
			$this->conn = new PDO($dsn, $username, $password, $options);
		}
		catch (PDOException $e)
		{
			$this->conn = NULL;
		}
	}
	
	public function close($force=FALSE)
	{
		if ($this->conn && ($force || !$this->hold))
		{
			$this->conn = NULL;
		}
	}
	
	public function run($sql, $params = array())
	{
		if (!$this->conn) return FALSE;
		
		$sql = $this->praseSQL($sql);
		
		//无参数时走简单查询，避免 DDL 等语句受预处理限制
		if (empty($params))
		{
			return $this->conn->query($sql);
		}
		
		$stmt = $this->conn->prepare($sql);
		$stmt->execute(array_values($params));
		return $stmt;
	}
}

/***
 * 数据读取门面
 * 读取统一写法：DB::table('content')->where('ct_check = 1')->rows()
 * 每次返回新的构造器实例（复用共享连接），链式状态不会在嵌套调用间污染
*/
final class DB
{
	//指定数据表 返回查询构造器
	public static function table($table)
	{
		return FCApplication::query()->table($table);
	}
}
?>
