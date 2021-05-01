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
class CRoomProgram extends CDbObject
{
//-----------------------------------------------------------------------------
//					VARIABLES MEMBRES
//-----------------------------------------------------------------------------
	var $id;
	var $room;
	var $weekday;
	var $start;
	var $temp;
	
//-----------------------------------------------------------------------------
//					METHODES DE CLASSE
//-----------------------------------------------------------------------------
	static public function dbTable(	)
	{
		return 't_room_program';
	}

	static function dbFields()
	{
		return array(
			'id' => "AUTO",
 			'room' => "ID",
			'weekday' => "INT",
			'start' => "TIME",
			'temp' => "FLOAT");
	}

	
	public static function forRoom($id_room)
	{
		return self::query("SELECT * FROM t_room_program WHERE room = %d ORDER BY start ASC",$id_room);
	}

	static function targetForRoom($id_room,$date=null)
	{
		if (!$date) $date = strtotime('now');
		$weekday = 2 << (int)date('N',$date);
		$time  = date('H:i:s',$date);
		$query = sprintf("SELECT temp from t_room_program where room = %d and weekday & %d and start < '%s' order by start desc limit 1 ",
			$id_room ,$weekday,$time);
		return (float)CDatabase::DB()->oneValue($query);

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
	public function weekday($idx)
	{
		return $this->weekday & (2 << $idx);
	}
	
	public function setWeekdaysArray($days)
	{
		$x= 0;
		foreach($days as $d ) $x += (2 << $d);
		return ($this->weekday = $x);
	}
		
}
