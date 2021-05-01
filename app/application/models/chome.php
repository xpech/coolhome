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
class CHome extends CDbObject
{
//-----------------------------------------------------------------------------
//					VARIABLES MEMBRES
//-----------------------------------------------------------------------------
	var $id;
	var $owner;
	var $title;
	var $mode;
	var $confort_temp;
	var $absence_temp;
		
	public static $dbOrderBy = "title";
//-----------------------------------------------------------------------------
//					METHODES DE CLASSE
//-----------------------------------------------------------------------------
	static public function dbTable(	)
	{
		return 't_home';
	}
	
	static function dbFields()
	{
		return array(
			'id' => "AUTO",
			'owner' => 'ID',
			'title' => 'STRING',
			'mode' => 'INT',
			'confort_temp' => 'FLOAT',
			'absence_temp' => 'FLOAT');
	}

	static function forUser($id_user = null)
	{
		if (!$id_user) $id_user = CUser::user()->id;

		return self::query("SELECT * FROM t_home WHERE owner = %d ORDER by title desc",$id_user);
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
		return $this->title;
	}

	function del($info = null)
	{
		foreach($this->rooms as $r)
		{
			if (!$r->del()) return false;
		}
		return parent::del($info);
	}

//-----------------------------------------------------------------------------
//					SECURITE
//-----------------------------------------------------------------------------
	
	function rooms()
	{
		return CRoom::forHome($this->id);
	}

	function mode()
	{
		switch ($this->mode) {
			case -1: return 'Absence';
			case 0: return 'Automatique';
			case 1: return 'Confort';
		}
		return '?';
	}
}
