<?php
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

namespace App\Class;

//-----------------------------------------------------------------------------
//					CLASSE  CUtilisateur
//-----------------------------------------------------------------------------
class CRoomProgram extends CDbObject
{
//-----------------------------------------------------------------------------
//					VARIABLES MEMBRES
//-----------------------------------------------------------------------------
	var $id;
	var $kind;
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
			'kind' => 'STRING',
 			'room' => "ID",
			'weekday' => "INT",
			'start' => "TIME",
			'temp' => "FLOAT");
	}

	
	public static function forRoom($id_room, $kind = 'HEATER')
	{
		if (self::supportsKind()) {
			if ($kind === '*') {
				return self::query("SELECT * FROM t_room_program WHERE room = %d ORDER BY kind ASC, start ASC",$id_room);
			}

			return self::query("SELECT * FROM t_room_program WHERE room = %d AND kind = %s ORDER BY start ASC",$id_room, CDatabase::DB()->STRING((string) $kind));
		}

		return self::query("SELECT * FROM t_room_program WHERE room = %d ORDER BY start ASC",$id_room);
	}

	static function targetForRoom($id_room,$date=null,$kind = 'HEATER')
	{
		if (!$date) $date = strtotime('now');
		$weekday = 2 << (int)date('N',$date);
		$time  = date('H:i:s',$date);

		if (self::supportsKind()) {
			$query = sprintf("SELECT temp from t_room_program where room = %d and weekday & %d and start < '%s' and kind = %s order by start desc limit 1 ",
				$id_room ,$weekday,$time, CDatabase::DB()->STRING((string) $kind));
		} else {
			$query = sprintf("SELECT temp from t_room_program where room = %d and weekday & %d and start < '%s' order by start desc limit 1 ",
				$id_room ,$weekday,$time);
		}
		return (float)CDatabase::DB()->oneValue($query);

	}

	static function currentForRoom($id_room,$date=null,$kind = 'HEATER')
	{
		if (!$date) $date = strtotime('now');
		$weekday = 2 << (int)date('N',$date);
		$time  = date('H:i:s',$date);

		if (self::supportsKind()) {
			$query = sprintf("SELECT * from t_room_program where room = %d and weekday & %d and start < '%s' and kind = %s order by start desc limit 1 ",
				$id_room ,$weekday,$time, CDatabase::DB()->STRING((string) $kind));
		} else {
			$query = sprintf("SELECT * from t_room_program where room = %d and weekday & %d and start < '%s' order by start desc limit 1 ",
				$id_room ,$weekday,$time);
		}

		$cand = self::query($query);
		return array_pop($cand);
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

	public function watering()
	{
		if (! is_string($this->start) || $this->start === '') {
			return false;
		}

		[$h, $m] = array_pad(explode(':', $this->start), 2, '0');
		$start = (int)$h * 60 + (int)$m;
		$end = $start + (int)$this->temp;
		$now = (int)date('H') * 60 + (int)date('i');

		return (($start <= $now) && ($now < $end));
	}

	public function watering_duration()
	{
		return (float) $this->temp;
	}

	private static function supportsKind(): bool
	{
		static $supportsKind = null;
		if ($supportsKind !== null) {
			return $supportsKind;
		}

		$rows = CDatabase::DB()->keyedRows(
			"SHOW COLUMNS FROM `t_room_program` LIKE %s",
			CDatabase::DB()->STRING('kind')
		);
		$supportsKind = ! empty($rows);
		return $supportsKind;
	}
		
}
