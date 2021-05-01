<?


class CDbObject {
	static $db_sort_key = 'id';

	function createTable()
	{
	}

	function vars()
	{
		return array();
	}

	static function query()
	{
		$DB = CDatabase::DB();
		$query="";
		if (func_num_args() > 1)
		{
			$args = func_get_args();
			$fmt = array_shift($args); 
			$query = vsprintf($fmt,$args);
		} else $query=func_get_arg(0);

		$res = array();
		$rs = $DB->query($query);
		if($rs)
		{
			while($tmp = $DB->getRow($rs))
			{
				$x = static::objectWithArray($tmp);
				$res[] = $x;
			}
		}
		return $res;		
	}
	
	static function deleteWithId($id)
	{
		return CDatabase::DB()->query("DELETE FROM %s WHERE id = %d",static::dbTable(),$id);

	}
	
	static function objectWithArray(&$arr)
	{
		$x = new static();
		$x->initWithArray($arr);
		return $x;
	}
	


	function initWithArray(&$arr)
	{
		foreach(static::dbFields() as $k => $type)
		{
			if (isset($arr[$k]))
			{
				switch ($type)
				{
					case "ID":
					case "AUTO":
					case "INT":
						$this->$k = (int)$arr[$k];
						break;
					case "CHAR":
					case "STRING":
						$this->$k = (string)$arr[$k];
						break;
					case "DATE":
					case "DATETIME":
						$this->$k = strtotime($arr[$k]);
						break;
					case "ARRAY":
					
						if ($v = $arr[$k]) $this->$k = unserialize($v);
						else $this->$k = array();
						break;
					default:
						if ($type[0] == 'C') $this->$k = (int)$arr[$k];
						else $this->$k = (string)$arr[$k];
				}
			}
		}
	}

	function updateWithArray($arr)
	{
		foreach(static::dbFields() as $k => $type)
		{
			if (array_key_exists($k,$arr))
			{
				switch ($type)
				{
					case "ID":
					case "INT":
						$this->$k = (int)$arr[$k];
						break;
					case "CHAR":
					case "STRING":
						$this->$k = (string)$arr[$k];
						break;
					case "DATE":
						$this->$k = strtotime($arr[$k]);
						break;
					case "ARRAY":
						if ($v = $arr[$k]) $this->$k = unserialize($v);
						else $this->$k = array();
						break;
					default:
						if ($type[0] == 'C') $this->$k = (int)$arr[$k];
						else $this->$k = (string)$arr[$k];
				}
			}
		}
	}

	
	static function dbTable()
	{
		$x = get_called_class();
		return sprintf("t_%s",strtolower(substr($x,1,strlen($x))));
	}
	
	
	static function objectWithId($id)
	{
		$x = static::query("SELECT * FROM %s WHERE id=%d",static::dbTable(),$id);
		if (count($x) > 0) return $x[0];
		return null;
	}
	static function objectsWithIds(array $ids)
	{
		return self::query("SELECT * from %s
				WHERE id in %s
				ORDER BY %s",static::dbTable(),CDatabase::DB()->ARRAY_INT($ids),static::$db_sort_key);

	}

	static function dbCreateTable()
	{
		$query = sprintf("CREATE TABLE %s (",static::dbTable());
		$str = "";
		$sep=" ";
		$cst = array();
		$fields = array();
		foreach(static::dbFields() as $f => $type)
		{
			switch($type) {
				case "ID":
					$fields[] = sprintf("%s int(11) auto_increment primary key",$f);
					break;
				case "INT":
					$fields[] = sprintf("%s int(11) default 0",$f);
					break;
				case "DATETIME":
					$fields[] = sprintf("%s DATETIME",$f);
					break;
				case "STRING":
					$fields[] = sprintf("%s VARCHAR(255)",$f);
					break;
				case "ARRAY":
				case "BLOB":
					$fields[] = sprintf("%s LONGBLOB",$f);
					break;
				case "DATE":
					$fields[] = sprintf("%s DATE",$f);
					break;
				case "FLOAT":
					$fields[] = sprintf("%s double default 0.0",$f);
					break;
				case "TIME":
					$fields[] = sprintf("%s TIME",$f);
					break;
				default:
					if (class_exists($type))
					{
						$fields[] = sprintf("%s int default null, FOREIGN KEY (%s) REFERENCES %s (id)",$f,$f,$type::dbTable());
					} else CDebug::err('CDbObject unknown type '. $type);
			}
		}
		$query .= implode(',',$fields) .") ENGINE = InnoDB CHARSET=latin1";
		
		CDatabase::DB()->query($query);
	}
	
	static function objects()
	{
		return static::query("SELECT * FROM %s ORDER BY id DESC",static::dbTable());
	}
	function sortedObjects($sortkey="id")
	{
		return static::query("SELECT * FROM %s ORDER BY `%s`",static::dbTable(),$sortkey);
	}
	
	function hydrate($arr)
	{
		CDebug::log('hydrate');
		CDebug::log(print_r($arr,true));
		foreach(static::dbFields() as $f => $type)
		{
			if (array_key_exists($f,$arr))
			{
				CDebug::log('hydrate $f '. $arr[$f]);
				switch($type) {
					case "AUTO":
						break;
					case "ID":
					case "INT":
						$this->$f = (int)$arr[$f];
						break;
					case "DATE":
					case "DATETIME":
						$this->$f = CDate::unixDate($arr[$f]);
						break;
					case "STRING":
					case "BLOB":
					case "CHAR":
						$this->$f = $arr[$f];
						break;
					case "ARRAY":
						$this->$f = $arr[$f];
						break;
					case "FLOAT":
						$this->$f = (float)$arr[$f];
						break;
					case "TIME":
						$this->$f = CDate::userTime($arr[$f]);
						break;
					default:
						$this->$f = $arr[$f];
				}
			}
		}		
		CDebug::pr($this);
	}
	
	
	
	static function dbStrForValueType($v,$type="STRING")
	{
		$DB = CDatabase::DB();
		switch ($type)
		{
			case "ID":
			case "AUTO":
			case "OBJECT":
				return $DB->ID($v);
			case "ARRAY":
				return $DB->SERIALIZE($v);
			case "INT":
			case "BOOL":
				return $DB->INT($v);
			case "CHAR":
			case "STRING":
			case "BLOB":
				return $DB->STRING($v);
			case "DATE":
				return $DB->DATE($v);
			case "TIME":
				return $DB->TIME($v);
			case "FLOAT":
			case "DOUBLE":
				return $DB->FLOAT($v);
			case "DATETIME":
				return $DB->DATETIME($v);
			default:
				if ($type[0] == "C") 
					return $DB->ID($v);
				else return $DB->STRING($v);
		}	
	}

	function preCreate() {}
	
	function create($info=null)
	{
		$DB= CDatabase::DB();
		$fields = static::dbFields();
		$table = static::dbTable();

		$this->preCreate();
		$fields_string = "";
		$sep = "";
		
		foreach($fields as $k => $t)
		{
			if ($t != 'AUTO' || ($t == 'AUTO' && $this->$k))
			{
				$fields_string .= sprintf("%s%s",$sep,$k);
				$sep = ",";
			}
		}
		$values_string = "";
		$sep = "";
		foreach($fields as $k => $t)
		{
			if ($t != 'AUTO' || ($t == 'AUTO' && $this->$k))
			{
				if ($k == 'updated' || $k == 'created')
					$values_string .= sprintf("%s NOW()",$sep);
				else $values_string .= sprintf("%s%s",$sep,static::dbStrForValueType($this->$k,$t));
				$sep = ",";
			}
		}
		$query = sprintf("INSERT INTO %s (%s) values (%s)",$table,$fields_string,$values_string);
		if ($rs = $DB->query($query))
		{
			$this->id = $DB->getLastID();
			$this->postCreate();
			//CUserLog::trace($table,$this->id,'CREATE',$info);
			return true;
		}
		return false;
	}
	function postCreate() {}
		
	function preUpdate() {}
	
	function update($info=null,$sfields=null)
	{
		if (!$this->id) return $this->create($info);
		$DB= CDatabase::DB();
		
		$this->preUpdate();
		
		$fields = static::dbFields();
		$table = static::dbTable();
		
		$fields_string = "";
		$sep = "";
		foreach($fields as $k => $t)
		{
			if ($t != 'AUTO')
			{
				if ($k == 'updated')
					$fields_string .= sprintf("%s updated = NOW()",$sep);
				else $fields_string .= sprintf("%s%s = %s",$sep,$k,static::dbStrForValueType($this->$k,$t));
				$sep = ",";
			}
		}
		$query = sprintf("UPDATE %s SET %s WHERE id = %d",$table,$fields_string,$this->id);
		if ($DB->query($query))
		{
			//CUserLog::trace($table,$this->id,'UPDATE',$info);
			$this->postUpdate();
			return true;
		}
		return false;
	}
	
	function postUpdate() {}
	
	function del($info=null)
	{
		if (!$this->id) return false;
		$DB= CDatabase::DB();
		$table = static::dbTable();
		$query = sprintf("DELETE FROM %s WHERE id = %d",$table,$this->id);
		//CUserLog::trace($table,$this->id,'DELETE',$info);
		return $DB->query($query);
	}
	
	public function info()
	{
		return sprintf("[%s:%d]",get_called_class(),$this->id);
	}
	
	static public function countAll()
	{
		return CDatabase::DB()->oneValue('SELECT count(*) from '.static::dbTable());
	}
}
