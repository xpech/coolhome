<?

class CDebug
{
	static function TRACE($verbose,$msg)
	{
		log_message((is_string($verbose) ? $verbose : 'info'),$msg);
		return;
	}
	
	static function on() {
		return 	(ENVIRONMENT !== 'production');
	}

	static function pr($x) {
		if (!defined('CDEBUG_ON')) return;
		echo "<pre>";
		print_r($x);
		echo "</pre>";
	}
	static function log($txt) {
		CDebug::TRACE(1,$txt);
	}
	static function warn($txt) {
		CDebug::TRACE(10,$txt);
	}
	static function note($txt) {
		CDebug::TRACE(20,$txt);
	}
	static function obj($obj) {
		CDebug::TRACE(20,print_r($obj,true));
	}
	
	static function err($txt) {
		CDebug::TRACE(1,$txt);
		trigger_error($txt,E_USER_ERROR);
	}
}

