<?
/**
 *  HEADER_FILE_NAME, 
 * 
 * Structure Objet
 * 
 * @author Xavier Pechoultres <x.pechoultres@expert-solutions.fr>
 * @version 1.0
 * @category class
 * @package CoolHome
 * @copyright Copyright (c) 2001, expert solutions sarl
*/

//-----------------------------------------------------------------------------
//					CLASSE  CUtilisateur
//-----------------------------------------------------------------------------
class CData extends CDbObject
{
//-----------------------------------------------------------------------------
//					VARIABLES MEMBRES
//-----------------------------------------------------------------------------
	var $id;
	var $owner;
	var $device;
	var $created;
	var $sensor;
	var $kind;
	var $value;
	
//-----------------------------------------------------------------------------
//					METHODES DE CLASSE
//-----------------------------------------------------------------------------
	static public function dbTable(	)
	{
		return 't_data';
	}

	static function dbFields()
	{
		return array(
			'id' => "AUTO",
 			'owner' => "ID",
			'device' => "ID",
			'room' => "ID",
			'created' => "DATE",
			'sensor' => "STRING",
			'kind' => "STRING",
			'value' => "FLOAT");
	}

	
	public static function forDevice($device_id,$from=null,$to=null)
	{
		$rq = '';
		if ($from) $rq .= " AND created > ". date("'Y-m-d H:i:s'",$from);
		if ($to) $rq .= " AND created < ". date("'Y-m-d H:i:s'",$to);


		return self::query("SELECT * FROM t_data WHERE device = %d %s ORDER BY created DESC",$device_id,$rq);
	}
	public static function forRoom($room_id,$from=null,$to=null)
	{
		$rq = '';
		if ($from) $rq .= " AND created > ". date("'Y-m-d H:i:s'",$from);
		if ($to) $rq .= " AND created < ". date("'Y-m-d H:i:s'",$to);


		return self::query("SELECT * FROM t_data WHERE room = %d %s ORDER BY created DESC",$room_id,$rq);
	}

	static function tempForRoom($id_room,$date=null)
	{

		if (!$date) $date = strtotime('now');
		$query = sprintf("SELECT value from t_data where room = %d AND kind='temp' and created < '%s' order by created desc limit 1 ",
			$id_room ,date('Y-m-d H:i:s',$date));
		// echo $query;
		return (float)CDatabase::DB()->oneValue($query);

	}

	static function countForDeviceSince($id_device,$date)
	{
		return CDatabase::DB()->oneValue("SELECT count(*) FROM t_data WHERE device = %d AND created > %s",
					$id_device,	CDatabase::DB()->DATETIME($date));
	}


//-----------------------------------------------------------------------------
//					SERIALISATION DB
//-----------------------------------------------------------------------------

	function getInfo()
	{
		return $this->sensor;
	}

	function info()
	{
		return $this->sensor;
	}

//-----------------------------------------------------------------------------
//					SECURITE
//-----------------------------------------------------------------------------
	function created($fmt="d/m/Y H:i:s")
	{
		return date($fmt,$this->created);
	}
	
		
}
