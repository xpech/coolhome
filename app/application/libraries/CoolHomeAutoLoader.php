<?php


defined('BASEPATH') OR exit('No direct script access allowed');

class CoolHomeAutoLoader {
	
	public function __construct(){
		spl_autoload_register(array($this, 'loader'));
	}


	public function loader($className){
		$file_path = $className.'.php';
		if (is_file(APPPATH . '/controllers/'. $file_path))
			return include_once(APPPATH . '/controllers/'. $file_path);
		if (is_file(APPPATH . '/core/'. $file_path))
			return include_once( APPPATH . '/libraries/'. $file_path);
		if (is_file(APPPATH . '/libraries/'. $file_path))
			return include_once( APPPATH . '/libraries/'. $file_path);
		if (is_file(APPPATH . '/models/'. $file_path))
			return include_once( APPPATH . '/models/'. $file_path);
		$low = strtolower($className);
		if ($low != $className) return $this->loader(strtolower($className));
		return false;
	}
}
