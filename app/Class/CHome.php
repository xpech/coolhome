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
	var $location;
		
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
			'location' => 'STRING',
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

	function meteo()
	{
		if (! $this->location) {
			return null;
		}

		$cacheDir = WRITEPATH . 'cache/';
		if (! is_dir($cacheDir)) {
			@mkdir($cacheDir, 0775, true);
		}

		$cache = $cacheDir . 'meteo_' . (int) $this->id . '.json';
		if (is_file($cache)) {
			$datas = json_decode((string) file_get_contents($cache));
			if (
				isset($datas->days[0]->datetime)
				&& (string) $datas->days[0]->datetime === date('Y-m-d')
			) {
				return $datas;
			}
		}

		$apiKey = (string) (getenv('VISUALCROSSING_API_KEY') ?: 'HXDZBPETK643GNXFW2S8JE9TP');
		$url = 'https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/'
			. urlencode((string) $this->location)
			. '?unitGroup=metric&key=' . urlencode($apiKey)
			. '&contentType=json';

		if (! function_exists('curl_init')) {
			return null;
		}

		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
		]);
		$response = curl_exec($curl);
		$ok = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		if (! is_string($response) || $response === '' || (int) $ok < 200 || (int) $ok >= 300) {
			return null;
		}

		@file_put_contents($cache, $response);
		return json_decode($response);
	}

	public function precipitations($value = '')
	{
		if ($meteo = $this->meteo()) {
			if (isset($meteo->days[0]->precip)) {
				return (float) $meteo->days[0]->precip;
			}
		}

		return -1;
	}
}
