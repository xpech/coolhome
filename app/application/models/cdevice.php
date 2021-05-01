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
class CDevice extends CDbObject
{
//-----------------------------------------------------------------------------
//					VARIABLES MEMBRES
//-----------------------------------------------------------------------------
	var $id; //  int(11) auto_increment PRIMARY KEY,
	var $uuid; //  varchar(200),
	var $title; //  varchar(200),
	var $owner; //  varchar(200),
	var $room; //  int(11) default null,
	var $temp; //  int(1),
	var $kind; // varchar(200))");
	var $reference; // bool

	public static $dbOrderBy = "title, id";
//-----------------------------------------------------------------------------
//					METHODES DE CLASSE
//-----------------------------------------------------------------------------
	static public function dbTable(	)
	{
		return 't_device';
	}
	
	static function dbFields()
	{
		return array(
			'id' => "AUTO",
			'owner' => 'ID',
 			'uuid' => 'STRING',
 			'title' => 'STRING',
 			'temp' => 'BOOL',
 			'room' => 'ID',
 			'reference' => 'BOOL',
 			'kind' => 'STRING');
	}

	static public function forUuidAndOwner($uuid,$owner)
	{
		$x =  self::query("SELECT * FROM t_device WHERE owner = %d AND uuid = %s",$owner,CDatabase::DB()->STRING($uuid));
		return array_pop($x);
	}

	static function forUser($owner)
	{
		return self::query("SELECT * FROM t_device WHERE owner = %d ",$owner);
	}
	static function forRoom($id_room)
	{
		return self::query("SELECT * FROM t_device WHERE room = %d ",$id_room);
	}

	function info()
	{
		if ($this->title) return $this->title;
		return "Satellite ". $this->uuid;
	}

	function url()
	{
		return 'http://sweethome_sat_'.$this->uuid.'.local';
	}

	function room()
	{
		if ($this->room)
			return CRoom::objectWithId($this->room);
		return null;
	}
	function isActive()
	{
		return 	CData::countForDeviceSince($this->id,strtotime("-2 hour"));

	}
	
}
