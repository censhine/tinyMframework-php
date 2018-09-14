<?php
/**
 * Created by PhpStorm.
 * 数据库模型类
 * User: chengxiang
 * Date: 2017/11/26
 * Time: 10:36
 */

namespace core;

class Model
{
    public static $instance = null;
    protected $db_host;
    protected $db_username;
    protected $db_password;
    protected $db_name;
    protected $table_prefix;
    protected $conn;
    protected $sql;
    protected $options;

    private function __construct()
    {
		global $config;
        $this->db_host = $config['DB_HOST'];
        $this->db_username = $config['DB_USERNAME'];
        $this->db_password = $config['DB_PASSWORD'];
        $this->db_name = $config['DB_NAME'];
        $this->table_prefix = $config['TABLE_PREFIX'];
        $this->conn = $this->connect();
        $this->initOptions();
    }

    public static function db()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function initOptions()
    {
        $db_options = ['select', 'field', 'table', 'where', 'group', 'having', 'order', 'limit'];
        foreach ($db_options as $val) {
            $this->options[$val] = '';
            if ($val == 'table') {
                $this->options['table'] = 'user';
            }
            if ($val == 'field') {
                $this->options['field'] = '*';
            }
        }

        return $this->options;
    }

    protected function connect()
    {
        $this->conn = mysqli_connect($this->db_host, $this->db_username, $this->db_password);
        if ($this->conn) {
            mysqli_select_db($this->conn, $this->db_name);
            mysqli_set_charset($this->conn, 'utf8');
        } else {
            die('Database is not out of connection, please check ...');
        }
        return $this->conn;
    }

    public function table($table_name)
    {
        if (!empty($table_name)) {
            $this->options['table'] = $this->table_prefix . $table_name;
        } else {
            $class_name = get_class();
            $table = strtolower(substr($class_name, -5));
            $this->options['table'] = $this->table_prefix . $table;
        }
        return $this;
    }

    public function field($field)
    {
        if (is_string($field)) {
            $this->options['field'] = $field;
        } elseif (is_array($field)) {
            $this->options['field'] = join(',', $field);
        } else {
            $this->options['field'] = '*';
        }

        return $this;

    }

    public function where($where)
    {
        if (is_string($where)) {
            $this->options['where'] = 'where ' . $where;
        } elseif (is_array($where)) {
            foreach ($where as $k => $item) {
            	if(is_array($item)){
					$opt[] = $k .' ' .$item[0] .' '. "'" . $item[1] . "'";
				}else{
					$opt[] = $k . '=' . "'" . $item . "'";
				}
            }
			if(empty($this->options['where'])) {
				$this->options['where'] = 'where ' . implode(' and ', $opt);
			}else{
				$this->options['where'] .= ' and '. implode(' and ', $opt);
			}
        }
        return $this;
    }

    public function whereOr($where){
		if (is_string($where)) {
			$this->options['where'] = 'where ' . $where;
		} elseif (is_array($where)) {
			foreach ($where as $k => $item) {
				if(is_array($item)){
					$opt[] = $k .' ' .$item[0] .' '. "'" . $item[1] . "'";
				}else{
					$opt[] = $k . '=' . "'" . $item . "'";
				}
			}
			if(empty($this->options['where'])) {
				$this->options['where'] = 'where ' . implode(' or ', $opt);
			}else{
				$this->options['where'] .= ' or '. implode(' or ', $opt);
			}
		}
		return $this;
	}

    public function whereIn($field, $condition){
    	if(is_array($condition)){
    		$where = implode(',', $condition);
			$where = empty($where)? '\'\'':$where;
		}else{
    		$where = $condition;
		}
		if(empty($this->options['where'])) {
			$this->options['where'] = ' where '.$field.' in ('.$where.')';
		}else{
			$this->options['where'] .= ' and '.$field.' in ('.$where.')';
		}
		return $this;
	}

    public function group($field)
    {
        $this->options['group'] = 'group by ' . $field;
        return $this;
    }

    public function having($field)
    {
        $this->options['having'] = 'having ' . $field;
        return $this;
    }

    public function order($order)
    {
        if (is_string($order)) {
            $this->options['order'] = 'order by ' . $order;
        } elseif (is_array($order)) {
            foreach ($order as $k => $val) {
                $opt[] = $k . ' ' . $val;
            }
            $this->options['order'] = 'order by ' . join(',', $opt);
        }
        return $this;
    }

    public function limit($limit)
    {
        if (is_string($limit)) {
            $this->options['order'] = 'limit ' . $limit;
        } elseif (is_array($limit)) {
            foreach ($limit as $k => $val) {
                $opt[] = $k . ' ' . $val;
            }
            $this->options['order'] = 'limit ' . join('', $opt);
        }
        return $this;
    }

    public function query($sql)
    {
        $data = [];
        $result = mysqli_query($this->conn, $sql);
        if ($result && mysqli_affected_rows($this->conn)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function select()
    {
        $sql = "SELECT %FIELD% FROM %TABLE_NAME%  %WHERE% %GROUP% %HAVING% %ORDER% %LIMIT%";
        $sql = str_replace([
            '%FIELD%',
            '%TABLE_NAME%',
            '%WHERE%',
            '%GROUP%',
            '%HAVING%',
            '%ORDER%',
            '%LIMIT%'
        ], [
            $this->options['field'],
            $this->options['table'],
            $this->options['where'],
            $this->options['group'],
            $this->options['having'],
            $this->options['order'],
            $this->options['limit']
        ], $sql);
        $this->sql = $sql;
		$this->options['where'] = '';
        return $this->query($sql);
    }

    public function execute($sql, $action = 'insert')
    {
        $result = mysqli_query($this->conn, $sql);

        if ($result && mysqli_affected_rows($this->conn)) {
            if ($action == 'insert') {
                return mysqli_insert_id($this->conn);
            }

            if ($action == 'update' || $action == 'delete') {
                return mysqli_affected_rows($this->conn);
            }
        }

        return false;

    }

    public function update($data)
    {
        $sql = "UPDATE %TABLE_NAME% SET %KEY_VALUES% %WHERE%";
        if($this->options['where'])
        {
            $sql = str_replace(
                [
                    '%TABLE_NAME%',
                    '%KEY_VALUES%',
					'%WHERE%'
                ],
                [
					$this->options['table'],
                    $this->processKeyValues($data),
                    $this->options['where']
                ],$sql);
			$this->sql = $sql;
			$this->options['where'] = '';
            return $this->execute($sql,'update');
        }
        return false;
    }

    public function insert($data)
    {
        $sql = "INSERT INTO %TABLE_NAME% (%KEYS%) VALUES(%VALUES%)";
        if (is_array($data)) {
            $keys = array_keys($data);
            $values = array_values($data);
            $sql = str_replace(
                [
                    '%TABLE_NAME%',
                    '%KEYS%',
                    '%VALUES%'
                ],
                [   $this->options['table'],
                    $this->processKeys($keys),
                    $this->processValues($values)
                ]
                , $sql);
            $this->sql = $sql;
			$this->options['where'] = '';
            return $this->execute($sql);
        }
        return false;
    }

    private function processKeyValues($data)
    {
        $opt = [];
        foreach ($data as $k => $val)
        {
            $opt[] = "`".$k."` = '".$val."'";
        }
        return join(',',$opt);
    }

    private function processKeys($data)
    {
        $opt = [];
        foreach ($data as $k )
        {
            $opt[] = "`".$k."`";
        }
        return join(',',$opt);
    }

    private function processValues($data){
        $opt = [];
        foreach ($data as $val)
        {
            $opt[] = "'".$val."'";
        }
        return join(',',$opt);
    }

    public function delete()
    {
        $sql = " DELETE FROM %TABLE_NAME% %WHERE% ";

        if( $this->options['where'] )
        {
            $sql = str_replace(
                [
                    '%TABLE_NAME%',
                    '%WHERE%'
                ],
                [
                    $this->options['table'],
                    $this->options['where']]
                ,$sql);
            $this->sql = $sql;
			$this->options['where'] = '';
            return $this->execute($sql, 'delete');
        }

        return false;
    }

    public function getRow($table_name,$where){
    	$row = self::db()->table($table_name)->where($where)->limit(1)->select();
    	return empty($row)? []:$row[0];
	}

    public function lastSql(){
    	return $this->sql;
	}

    function __get($name)
    {
        if( $name == 'sql' )
        {
            return $this->sql;
        }
        return false;
    }

    function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->options = '';
        mysqli_close($this->conn);
    }
}