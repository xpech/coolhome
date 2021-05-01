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
class CUSer extends CDbObject
{
//-----------------------------------------------------------------------------
//					VARIABLES MEMBRES
//-----------------------------------------------------------------------------
	var $email;
	var $password = "";		// mot de passe cript
	var $nom = "";			// nom
	var $prenom = "";		// prenom
	var $actif;				// Si l'utilsateur peu se logger
	var $created;		// date de blocage de l'utilisateur
	var $id = 0;			// id en base
	
	public static $dbOrderBy = "nom, prenom";
//-----------------------------------------------------------------------------
//					METHODES DE CLASSE
//-----------------------------------------------------------------------------
	static public function dbTable()
	{
		return 't_user';
	}
	
	static function dbFields()
	{
		return array(
			'id' => 'AUTO',
 			'password' => 'PASSWORD',
 			'nom' => 'STRING',
 			'prenom' => 'STRING',
 			'actif' => 'BOOL',
 			'created' => 'DATETIME',
 			'email' => 'STRING');
	}

	static public function withEmail($email)
	{
		$x = self::query('SELECT * FROM t_user WHERE email = %s',CDatabase::DB()->STRING($email));
		return array_pop($x);
	}

	static function user()
	{
		return CI_Controller::get_instance()->session->user;
	}
	
	static function userInfo()
	{
		if ($u= self::user()) return $u->getInfo();
		return "?";
	}

	static function require($k=null)
	{
		if ($u  = self::user())
		{

			$admins = CConfig::load('admins');
			return in_array($u->email, $admins);

		} else return false;
	}

//-----------------------------------------------------------------------------
//					SERIALISATION DB
//-----------------------------------------------------------------------------

	function getInfo()
	{
		return $this->nom . " " . $this->prenom;
	}

	function info()
	{
		return $this->nom . " " . $this->prenom;
	}

//-----------------------------------------------------------------------------
//					SECURITE
//-----------------------------------------------------------------------------

	static function getUser($login, $password)
	{
		CDebug::log("getUser($login, $password)");
		$candidates = self::query("SELECT * FROM t_user WHERE  email = %s",CDatabase::DB()->STRING($login));
			
		foreach($candidates as $r)
		{
			CDebug::log("password_verify($password,$r->password)");
			if (password_verify($password,$r->password))
			{
				CDebug::log('Connect OK '. $r->id);
				return $r;
			}
		}
		return null;
	}


	// verifie le droit en fonction de la zone et du type
	function checkPermissionsType($zone,$type)
	{
		$zone = strtoupper($zone);
		if (!array_key_exists($zone,$this->droits))
		{
			$this->droits[$zone] = CTicket::checkUserTicket($this->id,$zone);
		}

		$i = $this->droits[$zone];
		switch($type)
		{
			case "r" :
					$bits = USER_PERMISSION_READ;
					break;
			case "w" :
					$bits = USER_PERMISSION_WRITE;
					break;
			case "u" :
					$bits = USER_PERMISSION_UPDATE;
					break;
			case "d" :
					$bits = USER_PERMISSION_DELETE;
					break;
			default:
				$bits = $type;
		}
		$b = $i & $bits;
		CDebug::LOG(0, "checkPermission('$zone',$bits) : $i -> $b");	
		return $b;
	}
	
	function reload()
	{

	}
	function setPassword($new_password)
	{
		$query = sprintf("UPDATE t_user set `password` = %s WHERE id = %d ",
					CDatabase::DB()->PASSWORD($new_password), $this->id);
		echo $query;		
		return CDatabase::DB()->query($query);
	}
			
	function email() { return $this->email; }

		
}
