<?
/**
 *  HEADER_FILE_NAME, LiiNA
 * 
 * Structure Objet
 * 
 * @author Xavier Pechoultres <x.pechoultres@expert-solutions.fr>
 * @version 1.0
 * @category class
 * @package liina
 * @copyright Copyright (c) 2001, expert solutions sarl pour I.R.T.S.Aquitaine, 
*/
$CDATABASE_TOTAL_TIME = 0;
$CDATABASE_TRACE_MAILS = null;
$CDATABASE_TRACE_CONTENT = null;

define("DATABASE_LOG",0);
define("TBDD",0);

 
class CDatabase
{
	public static $CURRENT_DB;
	static $VERBOSE = 0;
	
	static function DB() {
		if (!self::$CURRENT_DB) self::init();
		
		
		return self::$CURRENT_DB;
	}
	
	static function mySql($host,$dbname,$user,$pass)
	{
		$db = new CDatabase("mysql",$dbname);
		$db->user = $user;
		$db->password = $pass;
		if ($host) $db->address = $host;
		if ($db->connect())
		{
			return $db;
		}
		return null;
	}
	
	static function init()
	{
		if (self::$CURRENT_DB) return;
		include( APPPATH.'config/database.php');
		self::mySql($db['default']["hostname"],$db['default']["database"],$db['default']["username"],$db['default']["password"]);
	}
	
	var $connection;
	var $user ="root";
	var $password ="adminpapi";
	var $address ="localhost";
	var $name;
	var $derniereErreur;
	var $printError;
	var $on_transaction=false;
	
	function __construct($type,$name)
	{
		CDatabase::trace("CDatabase($type,$name)");
		$this->name = $name;
	}


	static function tracemail($mail)
	{
		global $CDATABASE_TRACE_MAILS;
		$CDATABASE_TRACE_MAILS = $mail;
	}
	static function trace($txt)
	{
		static::dbtrace($txt);
		return;
		global $CDATABASE_TRACE_CONTENT;
		global $CDATABASE_TRACE_MAILS;
		if ($CDATABASE_TRACE_MAILS)
		{
			$CDATABASE_TRACE_CONTENT .= $txt . "\n--\n";
		}
		CDebug::TRACE(TBDD,$txt);
	}

	function __toString() { return "<db>"; }

	static $count;
	function ESCAPE($val)
	{
		return substr($this->connection->quote($val),1,-1);
	}

	function STRING($val,$ifnull = "null")
	{
		//if (!isset($this)) 
		return self::$CURRENT_DB->FORMAT_STRING($val,$ifnull);
		return $this->FORMAT_STRING($val,$ifnull);
//		return self::FORMAT_STRING($val,$ifnull);
	}
	function FORMAT_STRING($val,$ifnull = "null")
	{
		if (!$val)
		{
			return $ifnull;
		}
		return "'" . $this->ESCAPE($val) . "'";
	}

	function BOOL($val)
	{
		if ($val) return 1;
		return 0;
	}
	

	function INT($val)
	{
		if ($val) return sprintf("%d",$val);
		return 0;
	}

	function FORMAT_INT($i)
	{
		if ($i) return "$i";
		return "0";
	}

	function FLOAT($val)
	{
		if ($val) return  sprintf("%f",$val);
		return 0.0;
	}
	
	function ID($val)
	{
		$val =(int)$val;
		if ($val) return $val;
		return "null";
	}
	
	function OBJ_IDS($arr)
	{
		$r ="";
		foreach($arr as $a)
		{
			if ($r != "") $r .= ",";
			$r .= (int)$a->id;
		}
		return "(". $r . ")";
	}

	function ARRAY_STRING(&$arr)
	{
		$r = '';
		foreach($arr as $a)
		{
			if ($r != '') $r .= ',';
			$r .= $this->STRING($a);
		}
		return '('. $r . ')';
	}

	FUNCTION ARRAY_INT_OR($col,$arr)
	{
		$r ="";
		foreach($arr as $a)
		{
			if ($r != "") $r .= " OR ";
			$r .= sprintf(" %s = %d ", $col,$arr);
		}
		return "(". $r . ")";
	}
	FUNCTION ARRAY_ID_OR($col,$arr)
	{
		if (count($arr) == 0) return "FALSE";
		$r ="";
		foreach($arr as $a)
		{
			if ($r != "") $r .= " OR ";
			if ($a) $r .= sprintf(" %s = %d ", $col,$a);
			else $r .= sprintf(" %s is null ", $col);
		}
		return "(". $r . ")";
	}

	function ARRAY_INT($arr)
	{
		if (!$arr) return '()';
		$r ="";
		foreach($arr as $a)
		{
			if ($r != "") $r .= ",";
			$r .= $a;
		}
		return "(". $r . ")";
	}
	
	static function formatListeForSQL($tbl,$ifnull="",$deli="",$sep = ",")
	{
		if (!$tbl) return $ifnull;
		$tmp = '';
		$val = array_values($tbl);
		for($i =0; $i<count($val); $i++)
		{
			if ($i > 0) $tmp .= $sep;
				$tmp .= $val[$i];
		}
		if ($val) {return $deli . $tmp .$deli;}
		else return $ifnull;
	}
	
	function DBARRAY($ar,$sep='||')
	{
		$r = "";
		if (is_array($ar))
		{
			return $this->STRING(implode($sep,$ar));
			foreach($ar as $a) {if ($a) $r .= '||' . $a;}
		}
		return 'null';
	}
	function UNARRAY($ar)
	{
		return explode("||",$ar);
	}
	
	function INT_LIST(&$arr)
	{
		return self::ARRAY_INT($arr);	
	}

	function SERIALIZE($x)
	{
		return $this->STRING(serialize($x));
	}
	function UNSERIALIZE($x)
	{
		return unserialize($x);
	}

	function DATENULL($d)
	{
		if (!$d) return "null";
		else return $this->DATE($d);
	}
	function DATE($d)
	{
		if (!$d) return "null";
		return  strftime("'%Y-%m-%d'",$d);
	}

	function DATETO($d)
	{
		if (!$d) return "null";
		return  strftime("'%Y-%m-%d'",strtotime('+1 days', $d));
	}

	function DATETIME($d)
	{
		if (!$d) return "null";
		return date("'Y-m-d H:i:s'",$d);

	}
	function FORMAT_DATE($d)
	{
		return  sprintf("UNIX_TIMESTAMP(%d)",$d);
	}
	function TIME($d) { return $this->FORMAT_TIME($d); } 
	function FORMAT_TIME($d)
	{
		return ($d ? "'$d'" : "null");
	}
	function TIMESTAMP($ts) { return $this->FORMAT_TIMESTAMP($ts); }
	function FORMAT_TIMESTAMP($d)
	{
		if($d) return date("'Y-m-d H:i:s'",$d);
		else return  "null";
	}

	function PASSWORD($d)
	{
		return $this->STRING(password_hash($d,PASSWORD_BCRYPT));
	}
	
	function FORMAT_PASSWORD($d)
	{
		return "PASSWORD(" . $d . ")";
	}
	
	function ID_QUERY($field,$query)
	{
		CDatabase::trace("ID_QUERY($field,$query)");
		$items = explode(',',$query);
		$q = "(";
		$first = true;
		foreach($items as $i)
		{
			if ($first) $first = false; 
			else $q .= " OR ";
			$b = explode("-",$i);
			if (count($b) == 1)
				$q .= sprintf("(%s = %d)",$field,trim($b[0]));
			else 
				$q .= sprintf("(%s BETWEEN %d AND %d)",$field,trim($b[0]),trim($b[1]));
		}
		$q .= ')';
		return $q;
	}
	
	static function splitPrefix($arr,$sep='.') {
		$res = array();
		foreach($arr as $k => $v)
		{
			list($nk,$nn) = split($sep,$k,2);
			$res[$nk][$nn] = $v;
		}
		return $res;
	}

	function isEqual($field,$strValue)
	{
		if ($strValue) {
			$strValue =  $field . "='" . str_replace("'","\'",$strValue) . "'";}
		else $strValue = $field . " is null";
		return $strValue;
	}
	
	function begin()
	{
		if (!$this->on_transaction)
		{
			$this->query("START TRANSACTION");
			$this->on_transaction = true;
		} else 	CDatabase::trace("transaction already started");
	}
	
	function autoCommit()
	{
		$this->commit();
		$this->query("SET AUTOCOMMIT=1");
	}

	function commit()
	{
		if ($this->on_transaction)
		{
			$this->query("COMMIT");
		} else CDatabase::trace("Warning : Commit transaction without starting it ! ");
	}
	function rollback()
	{
		if ($this->on_transaction)
		{
			$this->query("ROLLBACK");
		} else CDatabase::trace("Warning :  rollback transaction without starting it !");
	}

	function connect()
	{
		CDatabase::trace("CDatabase::connect()");
		$this->connection = new PDO('mysql:host='.$this->address.';dbname='. $this->name.';charset=UTF8',$this->user,$this->password);

		if(!$this->connection) 
		{
			trigger_error('DATABASE ERROR');
			CDatabase::trace( "Erreur de connection");
			return false;
		}
		self::$CURRENT_DB = &$this;
		return true;
	}
	function getmicrotime()
	{
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}

	var $hidepassword = false;

	function exec($query)
	{
		$this->connection->exec($query);
	}

	
	function query()
	{
		if (!isset($this)) return self::$CURRENT_DB->query($query);
		global $CDATABASE_TOTAL_TIME;
		$t1 = CDatabase::getmicrotime();
		$rs = null;
		$query ="";
		if (func_num_args() > 1)
		{
			$args = func_get_args();
			$fmt = array_shift($args); 
			$query = vsprintf($fmt,$args);
		} else $query=func_get_arg(0);

		if (!$this->hidepassword) {
			CDatabase::dbtrace($query);
			$this->hidepassword = false;
		}
		$rs = $this->connection->query($query);

		$t2 = CDatabase::getmicrotime();
		$CDATABASE_TOTAL_TIME += ($t2 - $t1);
		CDatabase::trace( "query ok --------- > " . ($t2 - $t1));
		if($rs) return $rs;
		$this->derniereErreur = implode(',',$this->connection->errorInfo());
//		$DB = CDatabase::DB();
		CDatabase::trace( "query failed : $query");
		CDatabase::trace( $this->derniereErreur);
		if (isset($DB_MAIL_DEBUG))
		{
			mail($DB_MAIL_DEBUG,"[PAPI] ERREUR DB",
				sprintf("Fichier : %s\nuser: %s\nrequete=\n%s\n\n Erreur:\n%s",
						$_SERVER['SCRIPT_NAME'],
						CUtilisateur::userInfo(),
						$query,
						$this->derniereErreur));
		}
		if ($this->printError)
		{
			CDatabase::trace("ERREUR : %s \n %s",$query,$this->derniereErreur);
			$this->printUpdateMessage("ERREUR:%s <br><br> %s",$query,$this->derniereErreur);
		}
			
		return null;
	}

	function field($rs,$num=0)
	{
		if (is_object($rs)) return $rs->getColumnMeta($num);
		return mysql_fetch_field($rs,$num);
	}
	function countOfFields($rs)
	{
		if (is_object($rs)) return $rs->columnCount();
		return mysql_num_fields($rs);
	}
	

	function closeCursor($rs) {
		return $rs->closeCursor();
	}

	/*
	PDO::FETCH_ASSOC	=> [ 'col' => val ]
	PDO::FETCH_NUM
	*/
	function getRow($rs,$mode = null)
	{
	//	echo PDO::ATTR_DEFAULT_FETCH_MODE; die();
		return $rs->fetch($mode);
	}
	function getKeyedRow($rs)
	{
		if (is_object($rs)) return $rs->fetch(PDO::FETCH_ASSOC);
		return mysql_fetch_array($rs,MYSQL_ASSOC);
	}
	function getNumberedRow($rs)
	{
		return $rs->fetch(PDO::FETCH_NUM);
	}
	function getNumRows(& $rs)
	{
		CDatabase::trace('CDatabase::getNumRows('.$rs->queryString.')');
		return $rs->rowCount();
	}

	function getLastError()
	{
		CDatabase::trace("CDatabase::getLastError()");
		return $this->derniereErreur;
	}

	function getLastID()
	{
		if (!isset($this)) return self::$CURRENT_DB->getLastID();
		CDatabase::trace("CDatabase::getLastID()");
		return $this->connection->lastInsertId();
	}

	function close()
	{
		CDatabase::trace("CDatabase::close()");
		$this->connection = null;

		global $CDATABASE_TRACE_CONTENT;
		global $CDATABASE_TRACE_MAILS;
		if ($CDATABASE_TRACE_CONTENT && $CDATABASE_TRACE_MAILS)
		{
			mail($CDATABASE_TRACE_MAILS,"PAPI: dbtrace " . $_SERVER["SCRIPT_NAME"],$CDATABASE_TRACE_CONTENT);
		}
	}

	/**
	 *  pour les requetes devant retourner une seule valeur
	 *
	 * @param string $query requête SQL a executer
	 * @return any 
	 */
	function oneValue() {
		$res = null;
		$args=func_get_args();
		if (!isset($this)) return  call_user_func_array(array(self::$CURRENT_DB,'query'),$args);
		$rs = call_user_func_array(array($this,'query'),$args); // $this->query($query);
		if ($rs)
		{
			while ($tmp = $this->getRow($rs,PDO::FETCH_NUM)) {
				$res = $tmp[0];
			}
			$this->closeCursor($rs);
		}
		return $res;
	}
	
	
	function oneRow($query)
	{
		$res = null;
		if ($rs = $this->query($query))
		{
			while ($tmp = $this->getRow($rs)) {
				$res = $tmp;
			}
			$this->closeCursor($rs);
		}
		return $res;
	}
	
	function rows($query) {
		$args=func_get_args();
		if (!isset($this)) return  call_user_func_array(array(self::$CURRENT_DB,'query'),$args);
		$rs = call_user_func_array(array($this,'query'),$args); // $this->query($query);
		$res = array();
		if ($rs)
		{
			while ($tmp = $this->getRow($rs)) {
				// $this->trace(print_r($tmp,true));
				$res[] = $tmp;
			}
			$this->closeCursor($rs);
		}
		return $res;
	}
	function keyedrows($query) {
		$args=func_get_args();
		if (!isset($this)) return  call_user_func_array(array(self::$CURRENT_DB,'query'),$args);
		$rs = call_user_func_array(array($this,'query'),$args); // $this->query($query);
		$res = array();
		if ($rs)
		{
			while ($tmp = $this->getKeyedRow($rs)) {
				$res[] = $tmp;
			}
			$this->closeCursor($rs);
		}
		return $res;
	}
	

	/**
	 *  retour la liste des première valeurs de la requetes
	 *
	 * @param string $query requête SQL a executer
	 * @return array
	 */
	 function arrayWhithQuery($query,$first_col=true)
	{
		$res = array();
		$rs = $this->query($query);
		if ($rs)
		{
			while ($tmp = $this->getRow($rs)) {
				if ($first_col) $res[] = array_shift($tmp);
				else $res[] = $tmp;
			}
			$this->closeCursor($rs);
		}
		return $res;		
	}
	
	function keyedArray()
	{
		$query ="";
		$toutf8 = false;
		if (func_num_args() > 1)
		{
			$args = func_get_args();
			$fmt = array_shift($args); 
			$query = vsprintf($fmt,$args);
		} else $query=func_get_arg(0);
		$res = array();
		$rs = $this->query($query);
		if ($rs)
		{
			while ($tmp = $this->getKeyedRow($rs)) {
				if ($toutf8) {
					$tmp2 = array();
					foreach($tmp as $k=>$val)
					{
						if (is_string($val))
						{
							$tmp2[$k] = utf8_encode($val) ;
						}
					}
					$res[] = $tmp2;
				}
				else $res[] = $tmp;
			}
			$this->closeCursor($rs);
		}
		return $res;		
	}
	
	function strings($query)
	{
		$rs = array();
		$rs = $this->query($query);
		if ($rs)
		{
			while ($tmp = $this->getNumberedRow($rs))
				$res[] = stripslashes($tmp[0]);
			$this->closeCursor($rs);
		}
		return $res;		
	}
	
	/**
	 *  retour la liste des première valeurs de la requêtes comme un tableau d'entier
	 *
	 * @param string $query requête SQL a exécuter
	 * @return array[int]
	 */
	 function arrayOfIntegerWhithQuery($query)
	{
		$res = array();
		$rs = $this->query($query);
		if ($rs)
		{
			while ($tmp = $this->getNumberedRow($rs))
			{
				$res[] = (int)$tmp[0];
			}
		}
		$this->closeCursor($rs);
		return $res;		
	}
	
	function map($query,$key_idx=0,$val_idx=0) {
		$map = array();
		if ($rs = $this->query($query))
		{
			while ($tmp = $this->getNumberedRow($rs))
			{
				$map[$tmp[$key_idx]] = $tmp[$val_idx];
			}
		}
		return $map;
	}
	
	static $db_trace_file = null;
	static function dbtrace($msg)
	{
		log_message('DEBUG',$msg);
		return;
	}

	function tableInfo($table,$k= "")
	{
		$r = $this->oneRow(sprintf("SHOW TABLE STATUS WHERE Name = '%s'",$table));
		if ($k) return $r[$k];
		return $r;
	}
	
	
	function existsFieldOfTable($field,$table)
	{
		$rs = $this->query("DESCRIBE $table $field");
		if ($rs)
		{
			return $this->getRow($rs);
		}
		return false;
	}
	function existFieldOfTable($field,$table)
	{
		$rs = $this->query("DESCRIBE $table $field");
		if ($rs)
		{
			return $this->getRow($rs);
		}
		return false;
	}
	
	public function tables()
	{
		return $this->arrayWhithQuery("SHOW TABLES",true);
	}
	public function fields($table)
	{
		return $this->arrayWhithQuery("DESCRIBE ".$this->ESCAPE($table),false);
	}
	
	
	function existsTable($table)
	{
		$rs = $this->query("SHOW TABLES LIKE '$table'");
		if ($rs)
		{
			$arr = array();
			while($tmp = $this->getRow($rs)) $arr[] = $tmp[0];
			if (count($arr) > 0) return $arr;
			return null;
		}
		return false;
	}
	function dropTable($table)
	{
		if ($this->existsTable($table))
			$this->query("DROP TABLE `%s`",$table);
	}

	function getDatabaseVersion()
	{
		if (!$this->existsTable("xl_db_metada"))
		{
			$this->query("CREATE TABLE xl_db_metada (strkey varchar(30) not null, txtval varchar(255) default null, intval int default null)");
			$this->query("INSERT INTO xl_db_metada (strkey,intval) VALUES ('version',0)");
		}
		return $this->oneValue("SELECT intval from xl_db_metada where strkey='version'");
	}
	
	function setDatabaseVerion($ver)
	{
		$this->query(sprintf("UPDATE xl_db_metada SET  intval = %d where strkey='version'",$ver));
	}
	
	function existsConstraintKey($table,$key)
	{
		if ($k = $this->oneRow(sprintf("SHOW create table %s",$table)))
		{
			$r = $k['Create Table'];
			if (preg_match(sprintf("/CONSTRAINT `%s` FOREIGN KEY/i",$key),$r)) return true;
		}
		return false;
	}
	function existsIndexOfTable($indexName,$table)
	{
		$ok = false;
		if ($rs = $this->query("SHOW INDEX FROM `%s` where Key_name like '%s'",$table,$indexName))
		{
			while ($r = $this->getRow($rs)) $ok = true;
		}
		return $ok;
	}
	function setTableEngine($table,$engine='InnoDB')
	{
		if ($this->tableInfo($table,"Engine") != $engine)
		{
			printf("<p class='dbupdate'>Changing db engine of %s TO %s</p>",$table,$engine);
			$this->query("ALTER TABLE `%s` ENGINE = `%s`",$table,$engine);
		} else printf("<p class='dbupdate'>Db Engine of %s is yet %s</p>",$table,$engine);
	}
	
	function indexName($table,$fields)
	{
		if (is_array($fields))
		{
			$fields = implode("_",$fields);
		}
		return sprintf("i%s_%s",substr($table, 1),$fields);
		
	}
	
	function addIndexToTableForFieldWithName($table,$field,$name="",$mode="")
	{
		if (!$name) $name = $this->indexName($table,$field); // sprintf("i%s_%s",substr($table, 1),$field);
		if (!$this->existsIndexOfTable($name,$table))
		{
			if ($this->query("ALTER TABLE `%s` ADD INDEX `%s` (%s %s)",$table,$name,$field,$mode))
				printf("<p class='dbupdate'>Creating index on %s for field %s (%s) </p>",$table,$field,$name);
			else 
				printf("<p class='dbupdate'>FAILED TO CREATE INDEX %s for field %s (%s) : %s </p>",$table,$field,$name,$this->getLastError());
		} else printf("<p class='dbupdate'>Index on %s for field %s  (%s) already exists</p>",$table,$field,$name);
	}
	function dropIndex($table,$name) 
	{
		if ($this->existsIndexOfTable($name,$table))
		{
			if ($this->query("ALTER TABLE `%s` DROP INDEX `%s`"))
				printf("<p class='dbupdate'>Removing index on %s for name %s</p>",$table,$name);
			else 
				printf("<p class='dbupdate'>FAILED TO DROP INDEX ON %s NAMED %s : %s </p>",$table,$name,$this->getLastError());
		} else printf("<p class='dbupdate'>Index on %s named %s already not exists</p>",$table,$name);
	}
		
	function createTableForClass($classname) {
		if (class_exists($classname)) $classname::dbCreateTable();
		else printf("<p class='dbupdate'>UNKNOW class %s</p>",$classname);
	}
	
	function createFieldOfTable($field,$table,$request,$update=false,$iftype=null)
	{
		if ($v = $this->existsFieldOfTable($field,$table))
		{
			$need_up = true;
			if ($iftype)
				$need_up = (strtolower($v["Type"]) == strtolower($iftype));
			if ($update && $need_up)
			{
				if ($this->query("ALTER TABLE `%s` MODIFY COLUMN `%s` %s ",$table,$field,$request))
					printf("<p class='dbupdate'>%s.%s updated</p>",$table,$field);
				else printf("<p class='dbupdate dbfailed'>FAILED TO UPDATE %s.%s</p>",$table,$field);
			} else printf("<p class='dbupdate'>%s.%s already exists .. no change</p>",$table,$field);
				
		} else
		{
			if ($this->query("ALTER TABLE `%s` ADD `%s` %s ",$table,$field,$request))
			{	printf("<p class='dbupdate'>%s.%s : created</p>",$table,$field); return true; }
			else 
				printf("<p class='dbupdate'>FAILED to create %s.%s</p>",$table,$field);
		}
		return false;
	}
	function printUpdateMessage()
	{
		$args = func_get_args();
		$fmt = array_shift($args);
		printf("<p class='dbupdate'>%s</p>",vsprintf($fmt,$args));
	}
	
	function addUniqueIndex($table,$fields,$name=null)
	{
		if (!$name) $name = $this->indexName($table,$fields);
		if (!$this->existsIndexOfTable($name,$table))
		{
			if (is_array($fields)) $fields = implode(",",$fields);
			if ($this->query("ALTER TABLE `%s` ADD UNIQUE INDEX `%s` (%s) ",$table,$name,$field))
					printf("<p class='dbupdate'>unique index %s.%s created</p>",$table,$name);
			else printf("<p class='dbupdate dbfailed'>FAILED TO CREATE UNIQUE INDEX %s.%s</p>",$table,$name);
		} else printf("<p class='dbupdate'>unique index %s.%s already exists .. no change</p>",$table,$name);
	}
	
	function addConstraintKey($table,$field,$reftable,$refkey="id",$mode="RESTRICT",$nocheck=false)
	{
		if (!$mode) $mode = "RESTRICT";
		$keyname = str_replace(",","__",sprintf("%s_%s_fk",$table,$field));
		
		if (!$this->existsConstraintKey($table,$keyname))
		{
			if ($nocheck) $this->query("SET FOREIGN_KEY_CHECKS = 0");
			$this->query("ALTER TABLE %s ADD CONSTRAINT `%s` FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s;",
				$table,$keyname,$field,$reftable,$refkey,$mode);
			if ($nocheck) $this->query("SET FOREIGN_KEY_CHECKS = 1");
			$this->printUpdateMessage("%s : %s created",$table,$keyname);
		} else $this->printUpdateMessage("%s : %s already exist",$table,$keyname);
	}
	
	function addFunction($sql)
	{
//		return $this->query("DELIMITER $$\n" . $sql . "\n$$\n");
		return $this->query($sql);
	}
	
	/* Legacy */
	static function formatForSQL($val,$def,$quot)
	{
		return ($val ? $quot.CDatabase::DB()->ESCAPE($val).$quot : $def);
	}
	static function formatNumberForSQL($val,$ifnull=0,$del="") {
		if (!$val) return "0";
		$val = str_replace(",",".",$val);
		return floatval($val);
	}
	
}
CDatabase::init();
$DB = CDatabase::DB();
// LEGACY 

function formatForSQL($val,$def,$quot)
{
	return ($val ? $quot.CDatabase::DB()->ESCAPE($val).$quot : $def);
}