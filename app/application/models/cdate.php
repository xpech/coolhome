<?
/**
 *  HEADER_FILE_NAME, LiiNA
 * 
 * Utilitaires
 * 
 * @author Xavier Pechoultres <x.pechoultres@expert-solutions.fr>
 * @version 1.0
 * @category utils
 * @package liina
 * @copyright Copyright (c) 2001, expert solutions sarl pour I.R.T.S.Aquitaine, 
*/

class CDate
{
	static $INPUTDATE = 'Y-m-d';
	//-----------------------------------------------------------------------------
	//					GESTION DES HEURES
	//-----------------------------------------------------------------------------

	// "00:00:00" -> "00:00"
	static function shortTime($t,$ifnull="&nbsp;",$zeros=false)
	{
		if (!$t) return $ifnull;
		if ($t == '00:00') return $ifnull;
		if ($t == '00:00:00') return $ifnull;
		$l = explode(":",$t);
		if ($zeros)
			return sprintf('%02d:%02d',$l[0],$l[1]);
		else
			return sprintf('%d:%02d',$l[0],$l[1]);
	}

	static function timeToMinutes($t)
	{
		if (!$t) return 0;
		$t = getdate($t);
		return $t["hours"] * 60 + $t["minutes"];
	}

	static function userTimeToMinutes($t,$hmode=false)
	{
		if (strpos($t,",")) $t = str_replace(",",".",$t);
		if (strpos($t,"."))
		{
			return (float)$t * 60;
		}
		if ($hmode && $t) {if (strpos($t,':') === FALSE) $t .= ':00';} 
		$l = explode(":",CDate::userTime($t));
		if ($l[0] < 0) return (60 * $l[0]) - $l[1];
		return (60 * $l[0]) + $l[1];
	}

	static function minutesToTime($m)
	{
		return mktime(0,$m);
	}

	// Convertie une heure utilisateur en heure normale
	// "XX" -> "0:XX:00"
	// "XXX" -> "X:XX:00"
	// "XX:X" -> "XX:0X:00"
	// "XX:XX" -> "XX:XX:00"
	static function userTime($t, $timestamp=false)
	{
		$minutes = 0;
		$heures = 0;
		if (substr_count($t,"h") > 0)
		{
			$l = explode("h",$t);
			$heures = (int) $l[0];
			$minutes = (int) $l[1];
		} else if (substr_count($t,":") > 0)
		{
			$l = explode(":",$t);
			$heures = (int) $l[0];
			$minutes = (int) $l[1];
		} else
		{
			$minutes= (int) substr($t,-2);
			$heures= (int) substr($t,0,-2);
		}
		if ($timestamp) return mktime($heures,$minutes);  // xp to test
		return sprintf("%02d:%02d:00",$heures,$minutes);
	}

	// Convertie une heure utilisateur en heure normale
	// "XX" -> "0:XX:00"
	// "XXX" -> "X:XX:00"
	// "XX:X" -> "XX:0X:00"
	// "XX:XX" -> "XX:XX:00"
	static function userHours($t, $timestamp=false)
	{
		$minutes = 0;
		$heures = 0;
		if (substr_count($t,':') > 0)
		{
			$l = explode(':',$t);
			$heures = (int) $l[0];
			$minutes = (int) $l[1];
		} elseif (substr_count($t,'.') > 0)
		{
			$t = floatval($t);
			$heures = floor($t);
			$minutes = 60 * ($t - $heures);
		} elseif (substr_count($t,',') > 0)
		{
			$t = str_replace(",", ".", $t);
			$t = (float)$t;
			$heures = floor($t);
			$minutes = 60 * ($t - $heures);
		} else {
			$heures = (int)$t;
		}
		if ($timestamp) return mktime($heures,$minutes);  // xp to test
		return sprintf("%02d:%02d:00",$heures,$minutes);
	}


	static function unixTime($t)
	{
		return CDate::userTime($t, true);
	}

	// renvoie l'heure d'une date
	static function H($heure)
	{
		list($h,$m,$s) = sscanf($heure,"%d:%d:%d");
		return $h;
	}

	// renvoie en sur la base heure : 08:30:00 => 8.5 
	static function BaseHeure($heure)
	{
		list($h,$m,$s) = sscanf($heure,"%d:%d:%d");
		return $h + $m / 60 + $s / 3600;
	}

	// renvoie les minutes d'une date
	static function M($heure)
	{
		list($h,$m,$s) = sscanf($heure,"%d:%d:%d");
		return $m;
	}

	static function getUserTime($d,$h_sep = ":")
	{
		$l = explode(":",$d); return $l[0] . $h_sep .$l[1];
	}

	//renvoie un heure avec un nombre de minutes et d'heures
	// (cas ou le nombre de minutes > 60)
	static function getHeure($h,$m)
	{
		$tmp += $m % 60;
		$h += (int)(($m - $tmp) / 60);
		$m = $tmp;
		return "$h:$m:00";
	}

	static function timeDiff($d1,$d2)
	{
		list($h1,$m1) = sscanf($d1,"%d:%d");
		list($h2,$m2) = sscanf($d2,"%d:%d");
		$h = $h1 - $h2;
		$m = $m1 - $m2;
		$s = 0;
		if ($m < 0) 
		{
			$m += 60;
			$h -= 1;
		}
		if (strlen($m) < 2) $m = "0".$m;
		return "$h:$m";
	}



	static function echotime($t)
	{
		global $ECHOTIME_DECIMAL_MODE;
		if (!$t) { printf("&nbsp;"); return;}
		if ($ECHOTIME_DECIMAL_MODE) { 
			echo(number_format(($t / 60),2,",",""));
			// printf("%01,2f",); 
			return;};
		
		$minutes = (int) ($t);
		$heures = (int) ($minutes / 60);
		$minutes = $minutes - ($heures * 60);
		printf("%d:%02d",$heures,$minutes);
	}
	static function strtime($t)
	{
		global $ECHOTIME_DECIMAL_MODE;
		if (!$t) { return "";}
		if ($ECHOTIME_DECIMAL_MODE)
		{ 
			return number_format(($t / 60),2,",","");
		};
		
		$minutes = (int) ($t);
		$heures = (int) ($minutes / 60);
		$minutes = $minutes - ($heures * 60);
		return sprintf("%d:%02d",$heures,$minutes);
	}

	static function minutes2Time($t,$symb = ':',$ifnull=null,$with_seconds = false)
	{
		if ($ifnull != null && !$t) return $ifnull;
		if (strpos($t, $symb) > 0) return $t;
		$neg = '';
		if ($t < 0) { $neg = '-'; $t = abs($t); }
		if (!$t) { return '00' .$symb .'00';}
		$seconds = 60 * ($t - (int)$t);
		$minutes = (int) ($t);
		$heures = (int) ($minutes / 60);
		$minutes = $minutes - ($heures * 60);
		if ($with_seconds) return sprintf('%s%02d'. $symb . '%02d' . $symb . '%02d',$neg,$heures,$minutes,$seconds);
		return sprintf('%s%02d'. $symb . '%02d',$neg,$heures,$minutes);
	}
	static function time2minutes($d) {
		$arr = explode(":",$d);
		if (count($arr) > 1)
			return (int)$arr[0] * 60 + (int)$arr[1];
		return (int)$arr[0];
	}
	
	static function ensureMinute($x)
	{
		if (strpos($x,":") > 0) return CDate::time2minutes($x);
		return $x;
	}
	
	
	
	static function xml2date($x)
	{
		list($y,$m,$d) = sscanf($x,"%4d%2d%2d");
		return mktime(0,0,0,$m,$d,$y);
	}
	
	static function beginDST($year)
	{
		// begins the last Sunday in March
		$ld = strtotime("31 March $year");
		return mktime(8, 0, 0, date("n",$ld), date("j",$ld) - date("w"), date("Y",$ld));
	}
	static function endDST($year)
	{
	 	// ends the last Sunday in October. In the EU, all time zones change at the same moment.
		$ld = strtotime("31 October $year");
		return mktime(8, 0, 0, date("n",$ld), date("j",$ld) - date("w"), date("Y",$ld));
	}


	//-----------------------------------------------------------------------------
	//					GESTION DES DATES
	//-----------------------------------------------------------------------------

	static function getNomJour($date_ts)
	{
		$j = strftime("%w",$date_ts);
		switch($j)
		{
			case 1 : $nom = "lundi";	break;
			case 2 : $nom = "mardi";	break;
			case 3 : $nom = "mercredi";	break;
			case 4 : $nom = "jeudi";	break;
			case 5 : $nom = "vendredi";	break;
			case 6 : $nom = "samedi"; 	break;
			case 0 : $nom = "dimanche"; break;
		}
		return $nom;
	}
	static function getNomMois($date_ts,$ts = true)
	{
		if ($ts) $j = strftime("%m",$date_ts);
		else $j = $date_ts;
		switch($j)
		{
			case 1 : return "janvier";
			case 2 : return "février";
			case 3 : return "mars";
			case 4 : return "avril";
			case 5 : return "mai";
			case 6 : return "juin";
			case 7 : return "juillet";
			case 8 : return "août";
			case 9 : return "septembre";
			case 10 : return "octobre";
			case 11 : return "novembre";
			case 12 : return "décembre";
		}
		return $j;
	}

	static function getLundi($date_timestamp = null)
	{
		if (!$date_timestamp) $date_timestamp = time();
		$j = date("w",$date_timestamp) - 1;
		return mktime(0,0,0,date("m",$date_timestamp), date("d",$date_timestamp) - $j, date("Y",$date_timestamp) );
	}
	static function getDimanche($date_timestamp = null)
	{
		if (!$date_timestamp) $date_timestamp = time();
		$j = 6 - date("w",$date_timestamp);
		return mktime(0,0,0,date("m",$date_timestamp), date("d",$date_timestamp) + $j, date("Y",$date_timestamp) );
	}
	
	
	static public function inputDate($d)
	{
		if ($d) return date("YYYY-mm-dd");
		return "";
		# code...
	}


	// Convertie une date utilisateur en date normale
	// "XXXX" -> "XX/XX/[Année courante]"
	// "XXXXXX" -> "XX/XX/XX"
	// "XX/XX" -> "XX/XX/[Année courante]"
	// "XX/XX/XX" -> "XX/XX/XX"
	// "XX/XX/XX" -> "XX/XX/XXXX"
	static function userDate($t, $return_timestamp=false)
	{
		if (!$t) return null;
		$annee = date("Y");
		$mois = date("m");
		$jour = date("d");
		if (preg_match('/\d{4}-\d{1,2}-\d{1,2}/',$t)) {
			 return ($return_timestamp ? strtotime($t) : $t);
		} else if(substr_count($t,"/") == 0)
		{
			$ln = strlen($t);
			if ($ln == 2)
			{
				$jour = substr($t,0,2);
			}
			else if ($ln == 4)
			{
				$jour = substr($t,0,2);
				$mois = substr($t,2,2);
			} else if ($ln == 6)
			{
				$jour = substr($t,0,2);
				$mois = substr($t,2,2);
				$annee = substr($t,-2);
			} else if ($ln == 8)
			{
				$jour = substr($t,0,2);
				$mois = substr($t,2,2);
				$annee = substr($t,-4);
			}
		} elseif (substr_count($t,"/") == 1)
		{
				$l = explode("/",$t);
				$jour = $l[0];
				$mois = $l[1];
		} elseif (substr_count($t,"/") == 2)
		{
				$l = explode("/",$t);
				$jour = $l[0];
				$mois = $l[1];
				$annee = $l[2];
		} 
		
		if ($return_timestamp) return mktime(0,0,0,$mois,$jour,$annee);

		return date("Y-m-d",mktime(0,0,0,$mois,$jour,$annee));
	}

	static function unixDate($d)
	{
		return self::userDate($d,true);
	}

	static public function htmlDate($d)
	{
		return ($d ? date('Y-m-d',$d) : '');
	}

	// Formate une date US en date Francaise
	static function formatFDate($date) { return date("d/m/Y",$date); }

	static function formatFDateLong($date,$aInserer = " ") { return self::getNomJour($date) . $aInserer . date("d/m/Y",$date);}

	static function formatFDateLongLong($date,$aInserer = " ") { 
		return self::getNomJour($date) . $aInserer . date("d",$date) .  $aInserer . self::getNomMois($date) .  $aInserer . date("Y",$date);
	}

	// Ajout de dates
	static function timeAdd($t1,$t2)
	{
		return CDate::dateAdd($t1,$t2);
	}

	static function dateAdd($d1,$d2)
	{
		list($h1,$m1,$s1) = sscanf($d1,"%d:%d:%d");
		list($h2,$m2,$s2) = sscanf($d2,"%d:%d:%d");
		$h = $h1 + $h2;
		$m = $m1 + $m2;
		$s = 0;
		if ($m >= 60) 
		{
			$m -= 60;
			$h += 1;
		}
		if (strlen($m) < 2) $m = "0".$m;

		return "$h:$m:00";
	}



	static function dateAddInterval($interval, $number, $date,$timestamp_mode = false)
	{
	//	echo "dateAddInterval($interval, $number, $date)";
		if (!$timestamp_mode) $date = strtotime($date);
		$date_time_array = getdate($date);
		$hours = $date_time_array["hours"];
		$minutes = $date_time_array["minutes"];
		$seconds = $date_time_array["seconds"];
		$month = $date_time_array["mon"];
		$day = $date_time_array["mday"];
		$year = $date_time_array["year"];
		switch ($interval) {
			case "yyyy":
				$year +=$number;
				break;
			case "y":
			case "q":
				$year +=($number*3);
				break;
			case "m":
				$month +=$number;
				break;
			case "d":
			case "w":
				$day+=$number;
				break;
			case "ww":
				$day+=($number*7);
				break;
			case "h":
				$hours+=$number;
				break;
			case "n":
				$minutes+=$number;
				break;
			case "s":
				$seconds+=$number;
				break;
		}
		$timestamp = mktime($hours ,$minutes, $seconds,$month ,$day, $year);

		if (!$timestamp_mode) return date("Y-m-d",$timestamp);
		return $timestamp;
	}

	// Test si la date n'est pas un jour feriÈ
	static function jourFerie($t)
	{
		// samedi ou dimanche
		$w = date("w",$t); // jour de la semaine
		if ($w == 0 ) return true;  // dimanche
		if ($w == 6 ) return true;  // samedi

		$d = date("d",$t); // jour du mois
		$n = date("n",$t); // num du mois
		if ($d == 1 & $n == 1) return true;		// 1er janvier
		if ($d == 1 & $n == 5) return true;		// 1er mai
		if ($d == 8 & $n == 5) return true;		// 8 mai
		if ($d == 14 & $n == 7) return true;	// 14 Juillet
		if ($d == 15 & $n == 8) return true;	// 15 Aout
		if ($d == 1 & $n == 11) return true;	// 1er novembre
		if ($d == 11 & $n == 11) return true;	// 11 novembre
		if ($d == 25 & $n == 12) return true;	// 25 Decembre Noel

		return false;
	}
	
	public static function daysBetween($ts1,$ts2)
	{
		return ($ts1 - $ts2) / (3600 * 24);
	}
	public static function addDays($ts,$nday=0)
	{
		return $ts + (3600 * 24) * $nday;
	}
	
	static public function glissante($delta = 0)
	{
		return array('from' => mktime(0, 0, 0, date("m") + 1  ,0, date("Y") - 1 + $delta),
					'to' => mktime(0, 0, 0, date("m") + 1  ,0, date("Y") + $delta)
					);
	}
	
	static public function scolaires($annee=null)
	{
		if (!$annee) {
			if ((int) date('n') >= 9) $annee = (int)date('Y');
			else $annee = (int)date('Y') - 1;
		} else if ($annee < 30)  {
			if ((int) date('n') > 9) $ref = (int)date('Y');
			else $ref = (int)date('Y') - 1;
			$annee += $ref;
			
		}
		return array('from' => mktime(1,1,1,9,1,$annee), 'to' => mktime(1,1,1,9,0,$annee+1));
	}
	
	static function annee($a = 0) {
		if ($a > 10) return $a;
		$m  = (int)date('n');
		if ($m > 8) return (int)date('Y') + $a;
		else return (int)date('Y') - 1 + $a;
	}
	
}
