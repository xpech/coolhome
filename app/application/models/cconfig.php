<? 

class CConfig
{
	private static $defaults = null;
	private static $user = null;
	private static $merged = null;

	static function init()
	{
		if (self::$defaults != null) return;

		if (is_file(APPPATH ."/config/default.conf"))
			self::$defaults = parse_ini_file(APPPATH ."/config/default.conf");

		if (is_file(APPPATH ."/config/user.conf"))
			self::$user = parse_ini_file(APPPATH ."/config/user.conf");
		else {
			self::$user = [];
		}
	}

	static function get($key)
	{
		CConfig::init();
		if (isset(self::$user[$key])) return self::$user[$key];
		elseif (isset(self::$user[$key]))
			 return self::$defaults[$key];
		return null;
	}

	static function set($key,$val)
	{
		self::$user[$key] = $val;
	}

	static function keys()
	{
		return array_keys(self::$defaults);
	}

	static function save($newconfig=null)
	{
		$tosave = array();
		foreach(self::$user as $k => $v)
		{
			if ($v != self::$defaults[$k]) $tosave[$k] = $v;
		}
		if ($f = fopen(APPPATH ."/config/user.conf","w"))
		{
			fwrite($f,sprintf("; updated %s by ip %s\n",date("d/m/Y H:M:S"),$_SERVER["REMOTE_ADDR"]));
			foreach($tosave as $k => $v)
			{
				switch  (gettype($v)) {
					case 'bool':
					case 'int':
					case 'integer':
						fwrite($f,sprintf("%s = %s\n",$k,(int)($v))); break;
					default:	
						fwrite($f,sprintf("%s = \"%s\" // %s \n ",$k,addslashes($v), gettype($v))); break;
				}
			}
			fclose($f);
		}
	}
	
	static public function titresignataire($date=null)
	{
		if ($date == null) $date = strtotime('now');
		if($date > strtotime('2017-09-25'))
		{
			return "Directrice générale";
		} else	if($date > strtotime('2014-06-25'))
		{
			return "Directeur général";
		}
		
	}
	static public function signataire($date=null,$titre=false)
	{
		if ($date == null) $date = strtotime('now');
		if($date > strtotime('2017-09-25'))
		{
			if ($titre) return "Madame Anne CREQUIS";
			return "Anne CREQUIS";
		} else		if($date > strtotime('2014-06-23'))
		{
			if ($titre) return "Monsieur Michel Monbeig";
			return "Michel Monbeig";
		} else if(strftime('%Y',$date) >= 2014)
		{
			if ($titre) return "Monsieur François Viñas";
			return "François Viñas";
		} else {
			if ($titre) return "Albert Klein";
			return "Albert Klein";
		}
	}
	
	static $SIG_PATHS = [
		APPPATH . '/documents/signatures/',
		'/opt/papi/app/signatures/',
		'/Library/WebServer/papi/dev/signatures/'];
	
	
	static public function signature($date=null) {
		$sign = strtolower(self::signataire($date));
//		$sign = strtr($sign, 'ÁÀÂÄÃÅÇÉÈÊËÍÏÎÌÑÓÒÔÖÕÚÙÛÜÝ', 'AAAAAACEEEEEIIIINOOOOOUUUUY');
		$sign = strtr($sign, ' áàâäãåçéèêëíìîïñóòôöõúùûüýÿ', '_aaaaaaceeeeiiiinooooouuuuyy');
		
		foreach(self::$SIG_PATHS as $p)
		{
			$filename = sprintf('%s%s.jpg',$p,$sign);
			if (file_exists($filename)) return $filename;
		}
		
		return null;
	}
	static public function signatureImageNom($nom) {
		$sign = strtolower($nom);
		$sign = strtr($sign, ' áàâäãåçéèêëíìîïñóòôöõúùûüýÿ', '_aaaaaaceeeeiiiinooooouuuuyy');

		foreach(self::$SIG_PATHS as $p)
		{
			$filename = sprintf('%s%s.jpg',$p,$sign);
			if (file_exists($filename)) return $filename;
		}

		return null;
	}

	static public function hsignature($date=null) {
		if ($date == null) $date = strtotime('now');
		if($date > strtotime('2017-09-25')) return 80;
		return 120;
	}
	
	
	private static $diplomes = null;
	static public function diplomes()
	{
		if (!self::$diplomes) self::$diplomes = yaml_parse_file(APPPATH.'config/diplomes.yml');
		return self::$diplomes;
	}
	
	
	private static $mailconfig = null;
	static public function setupMail($mail)
	{
		if (self::$mailconfig == null) self::$mailconfig = yaml_parse_file(APPPATH.'config/mail.yml');
		$mail->isSMTP();
		$mail->Host = self::$mailconfig['host'];
		$mail->SMTPAuth = true;
		$mail->CharSet = 'UTF-8';
		$mail->Username = self::$mailconfig['username'];
		$mail->Password = self::$mailconfig['password'];
		$mail->Port = (int)self::$mailconfig['port'];
		$mail->setFrom(self::$mailconfig['from'],self::$mailconfig['from_name']);
	}
	
	private static $insee_pcs = null;
	static public function insee_pcs()
	{
		if (!self::$insee_pcs) self::$insee_pcs = yaml_parse_file(APPPATH.'config/insee_pcs.yml');
		return self::$insee_pcs;
	}
	
	private static $situations = null;
	static public function situations()
	{
		if (!self::$situations) self::$situations = yaml_parse_file(APPPATH.'config/situations.yml');
		return self::$situations;
	}
	

	private static $theme = null;
	static public function colors()
	{
		if (!self::$theme) self::$theme = yaml_parse_file(APPPATH.'config/theme.yml');
		return self::$theme['colors'];
	}
	
	 static public function load($name)
	 {
	 	return yaml_parse_file(APPPATH."config/$name.yml");
	 }

	 static function templates($kind='')
	 {
	 	$path = APPPATH.'documents/templates/'.$kind;
	 	$res = array();
		if ($handle = opendir($path)) {

    		/* Ceci est la façon correcte de traverser un dossier. */
		    while (false !== ($entry = readdir($handle))) {
		    	if (substr($entry, -4) ==  '.doc' || substr($entry, -5) ==  '.docx' )
		        	$res[] = $entry;
    		}
    	}
    	return $res;

	 }
}

CConfig::init();

