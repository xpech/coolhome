<?php

class CHtml
{
	
	static function utf8($txt)
	{
		return @iconv("ISO-8859-15","UTF-8//IGNORE",$txt);
	}
	
	static public function latin($txt)
	{
		return iconv("UTF-8","ISO-8859-15//IGNORE",$txt);
	}

	static function radioYN($radio_name,$val,$class=null)
	{	?><input type="radio" class="<?= $class ?>" name="<?= $radio_name ?>" value="1" <? if ($val) echo "checked"; ?>>oui <input type=radio name="<?= $radio_name ?>" value="0" class="<?= $class ?>" <? if (!$val) echo "checked"; ?>>non <? }


	static function selectMenuLocalStyle($menu,$selectionne) {
		if ( $menu == $selectionne) 
			echo " class='menulocalseletionne' ";
		else
			echo " class='menulocal' ";
	}
	static function checked($val) { if ($val) echo 'checked'; }
	static function selected($a) { if ($a) echo 'selected'; }

	static function StandardCombo($name,$liste,$selected,$options="",$nochange=false)
	{ 
		echo "<select name='$name' $options class=\"form-control\">\n";
		if ($nochange) 
			echo "<option value='$nochange'>Pas de changement</option>\n";
		foreach($liste as $o)
		{
			if($selected == $o->id) $sel = "selected";
			else $sel = "";
			echo "<option $sel value=". $o->id .">";
			echo $o->info() ;
			echo "</option>\n";
		}
		echo "</select>\n";
	}

	static function StandardComboAuncun($name,&$liste,$selected,$index = 0,$option="",$txtmethod="info",$nochange=false)
	{ 
		echo "<select name='$name' $option";
		if ($index) echo " tabindex=". $index ; 
		echo ">\n";
		if ($nochange) 
			echo "<option value='$nochange'>Pas de changement</option>\n";
			
		if(!$selected) $sel = "selected";
		echo "<option $sel value='0'>-</option>\n";
		foreach($liste as $o)
		{
			if ($selected == $o->id) $sel = "selected";
			else $sel = "";
			echo "<option $sel value=". $o->id .">";
			echo call_user_func(array($o,$txtmethod)); // ()$o->info() ;
			echo "</option>\n";
		}
		echo "</select>\n";
	}

	static public function arrayToURL($values,$name)
	{
		$r = array();
		foreach($values as $k => $v)
			$r[] = sprintf('%s[%s]=%s',$name,$key,$v);
		return implode('&',$r); 
	}
	static function trieStandard(& $o1,& $o2)
	{
		$a = $o1->getInfo();
		$b = $o2->getInfo();
		if($a->nom == $b->nom) return ($a->prenom < $b->prenom) ? -1 : 1;
		return ($a->nom < $b->nom) ? -1 : 1;
	}

	static function showWarning($ar = null,$title=null)
	{
		if(!$ar) printf('<i class="fas fa-exclamation-triangle text-warning" title="%s"></i>',$title);
		else if (!is_array($ar)) printf('<i class="fas fa-exclamation-triangle text-warning"  title="%s" alt="%s"></i>',$title,$ar);
		else {
	?><i class="fas fa-exclamation-triangle text-warning globalmodal" title="<?= $title ?>" href="/enseignement/interferences?<?= CHtml::arrayToURL($ar,"interferences"); ?>"></i><? }
	}
	
	
	static function value($key,$default=false) { return self::val($key,$default); }
	static function val($key,$default=false) {
		return (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $default));
	}
	
	static function float($k) {
		if ($v = self::val($k)) return (double)str_replace(',','.',$v);
		return  0.0;
	}
	static function tofloat($k) {
		return (double)str_replace(',','.',$k);
	}
	
	static function isGet($key) {return array_key_exists($key,$_GET); }
	static function isPost($key) {return array_key_exists($key,$_POST); }
	static function isSend($key) {return (self::isGet($key) or self::isPost($key));}
	static function is($key) {return (self::isGet($key) or self::isPost($key));}
	

	static function bool($key,$default=false) {
		return (isset($_POST[$key]) ? (bool)$_POST[$key] : (isset($_GET[$key]) ? (bool)$_GET[$key] : $default));
	}
	static function text($key,$default=false) {
		return (isset($_POST[$key]) ? filter_var($_POST[$key]) : (isset($_GET[$key]) ? filter_var($_GET[$key]) : $default));
	}
	static function email($key,$default=false) {
		return (isset($_POST[$key]) ? filter_var($_POST[$key],FILTER_VALIDATE_EMAIL) : (isset($_GET[$key]) ? filter_var($_GET[$key],FILTER_VALIDATE_EMAIL) : $default));
	}
	static function int($key,$default=0) {
		return (isset($_POST[$key]) ? (int)$_POST[$key] : (isset($_GET[$key]) ? (int)$_GET[$key] : (int)$default));
	}
	static function id($key) {
		$x = self::int($key);
		return ($x ? $x : null);
	}

	static function arr($key) {
		$x = self::val($key,array());
		if (is_array($x)) return $x;
		return array($x);
	}
	
	static public function time($key)
	{
		return (($v = self::val($key)) ? CDate::userTime($v) : null);
	}
	static public function hours($key)
	{
		return CDate::userHours(self::val($key));
	}
	static public function minutes($key)
	{
		return CDate::time2minutes(self::val($key));
	}


	static function onPost()
	{
		return ($_SERVER['REQUEST_METHOD'] == "POST");
	}
	
	static function form()
	{
		return (self::onPost() ? $_POST : $_GET);
	}

	static function date($key) {
		if ($v = self::val($key)) {
			return CDate::unixDate($v);
		} else return null;
	}
	
	static public function formDate($d,$df = 'Y-m-d')
	{
		if ($d) echo date($df,$d);
	}
	static public function fmtDateInput($d,$df = 'Y-m-d')
	{
		if ($d) echo date($df,$d);
	}
	
	
    static function session($key,$defaul=false) { return (array_key_exists($key,$_SESSION) ? $_SESSION[$key] : $default); }

    static function set($key,$val=true) { $_SESSION[$key] = $val; return $val; }
    static function setSession($key,$val=true) { $_SESSION[$key] = $val; return $val; }
    
	
	static function unsetSession($key) { unset($_SESSION[$key]); return null; }
	
	
	
	static function session_start() {
		session_start();
		if (ini_get('register_globals') != '1')
		{
			foreach($_SESSION as $k => $v) {
				if ($k != 'superglobals') $GLOBALS[$k] = $v;
			}
		}
	}
	
	static function session_store() {
		foreach($GLOBALS as $k => $v) {
			if ($k[0] == 's' && $k != 'superglobals') {
				$_SESSION[$k] = $v;}
		}
	}
	
	static function json($datas)
	{
		header("Pragma: no-cache");
		header("Expires: -1");
		header("Cache-Control: no-cache, must-revalidate");
		header("Content-Type: application/json; charset=UTF-8");
		echo json_encode($datas);
	}
	
	
	static function redirect($url,$die=true)
	{
		if (!headers_sent())
			header('Location: '. $url);
		else
			printf('<script language="javascript">document.location.href="%s";</script>',$url);
		if ($die) die();
		
	}
	
	static function boolInfo($bool) { if ($bool) echo "oui"; else echo "non";}
	
	
	static function array2utf8($data)
	{
		foreach($data as $k => $v)
		{
			if (is_string($v)) $data[$k] = CHtml::utf8($v);
			elseif (is_array($v)) $data[$k] = CHtml::array2utf8($v);
			elseif (is_object($v)) $data[$k] = CHtml::array2utf8((array)$v);
			
		}
		return  $data;
	}
	

	static $GENRES = array(0 => "", 1 => "Monsieur", 2 =>"Madame", 3 => "Mademoiselle");
	static function showGenre($selected)
	{ 
		foreach(self::$GENRES as $k => $g)
		{
			echo "<option " . (($selected == $k) ? "selected" : '') .  'value="'.$k.'">'.$g.'</option>';
		}
	}
	
	
	static public function upload($key)
	{
		$res = [];
		if (!isset($_FILES[$key])) return $res;
		CDebug::log('FILES : '. print_r($_FILES,true));
		
		$f = $_FILES[$key];
		$res = [];
		if (is_array($f['name']))
		{
			for($i = 0; $i < count($f['name']);$i++)
			{
				$res[] = ['name' => $f['name'][$i],
						'type' => $f['type'][$i],
						'size' => $f['size'][$i],
						'tmp_name' => $f['tmp_name'][$i],
						'error' => $f['error'][$i]];
			}			
		} else $res[] = $f;
		return $res;
	}
	
	static public function disabled($v)
	{
		if ($v) echo 'disabled="disabled"';
	}
	static public function disabledBut()
	{
		if (!call_user_func_array('CUtilisateur::require', func_get_args())) echo 'disabled="disabled"';
	}
	static public function require()
	{
		if (!call_user_func_array('CUtilisateur::require', func_get_args())) echo 'disabled="disabled"';
	}
	
	static function uri()
	{
		return $_SERVER['REQUEST_URI'];
	}
	
	
	static function action($action =null)
	{
		if (!$action) return self::val('action');
		return (self::val('action') == $action);
	}
	
	
	static function contractColor($bgColor){
		//ensure that the color code will not have # in the beginning
		$bgColor = str_replace('#','',$bgColor);
		//now just add it
		$hex = '#'.$bgColor;
		list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
		$color = 1 - ( 0.299 * $r + 0.587 * $g + 0.114 * $b)/255;

		if ($color < 0.5)
			$color = '#000000'; // bright colors - black font
		else
			$color = '#ffffff'; // dark colors - white font

		return $color;
	}
	
	static public function fmtFileSize($bytes, $decimals = 2)
	{
		$factor = floor((strlen($bytes) - 1) / 3);
	    if ($factor > 0) $sz = 'KMGT';
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor - 1] . 'B';
		
	}
	
	static function parseForm(&$obj,$form = null)
	{
		foreach($obj->dbFields() as $k => $f)
		{
			if (CHtml::is($k))
			{
				switch($f){
					case 'ID':
					case 'INT':
					case 'AUTO':
						$obj->$k = CHtml::int($k); break;
					case 'FLOAT':	
					case 'DOUBLE':	
						$obj->$k = CHtml::float($k); break;
					case 'DATE':
						$obj->$k = CHtml::date($k); break;
					case 'BOOL':
						$obj->$k = CHtml::bool($k); break;
					default:
					$obj->$k = CHtml::text($k); break;
				}
			}
		}
	}
}

