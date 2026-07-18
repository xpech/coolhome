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
class CRoom extends CDbObject
{
//-----------------------------------------------------------------------------
//					VARIABLES MEMBRES
//-----------------------------------------------------------------------------

	var $id;
	var $home;
	var $title;
	var $color;
	var $icon;
	var $mode;

	public static $dbOrderBy = "title";
//-----------------------------------------------------------------------------
//					METHODES DE CLASSE
//-----------------------------------------------------------------------------
	static public function dbTable(	)
	{
		return 't_room';
	}
	
	static function dbFields()
	{
		return array(
			'id' => "AUTO",
 			'home' => 'ID',
 			'title' => 'STRING',
			'color' => 'STRING',
			'icon' => 'STRING',
 			'mode' => 'INT');
	}

	static function forHome($id_home)
	{
		return self::query("SELECT * FROM t_room WHERE home = %d ORDER BY title",$id_home);
	}
	
//-----------------------------------------------------------------------------
//					SERIALISATION DB
//-----------------------------------------------------------------------------

	function getInfo()
	{
		return $this->title;
	}

	function info()
	{
		if ($this->title)
			return $this->title;
		else 
			return "nouvelle pièce";
	}
	function del($info = null)
	{
		foreach($this->devices() as $dev)
		{
			$dev->room = null;
			$dev->update();
		}
		parent::del($info);
	}

//-----------------------------------------------------------------------------
//					SECURITE
//-----------------------------------------------------------------------------
	public function devices()
	{
		return CDevice::forRoom($this->id);
	}
	public function programs($kind = 'HEATER')
	{
		return CRoomProgram::forRoom($this->id, $kind);
	}
	public function home()
	{
		return CHome::objectWithId($this->home); 
	}
	public function mode()
	{
		if ($this->mode < 0) return "Absent";
		if ($this->mode == 0) return "Auto";
		if ($this->mode > 0) return "Confort";
		return '?';
	}
	public function target($kind = 'HEATER')
	{
		if ($kind == 'HEATER') {
			if ($this->mode < 0)
			{
				$h = $this->home();
				return $h->absence_temp;
			} elseif ($this->mode > 0)
			{
				$h = $this->home();
				return $h->confort_temp;
			}
		}
		return CRoomProgram::targetForRoom($this->id, null, $kind);
	}
	public function temp()
	{
		return CData::tempForRoom($this->id);
	}
	public function hum()
	{
		return CData::hrForRoom($this->id);
	}
	public function program($kind,$date=null)
	{
		return CRoomProgram::currentForRoom($this->id,$date,$kind);
	}

}
