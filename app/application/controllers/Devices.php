<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Devices extends CoolHomeController {
	var $breadcrumb = [['text' => 'Satellites', 'url' => '/devices']];
	var $title= 'Satellites';
	var $mainmenu = 'satellites';
	var $menu = '';
	var $require = '';
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->view('devices/index',['devices',]);
	}
	public function firmwares()
	{
		$this->view('devices/firmwares',['devices',]);
	}
	public function updates()
	{
		$this->view('devices/firmwares');
	}

	public function dl($ptf,$firm)
	{
		$path = realpath(APPPATH.'/firmwares/'.$ptf.'/'.$firm);
		$firm_path = realpath(APPPATH.'/firmwares');
		if (substr($path,0 ,strlen($firm_path)) == $firm_path)
		{
			header('Content-Description: File Transfer');
    		header('Content-Type: application/octet-stream');
   			header('Content-Disposition: attachment; filename="'.$firm .'"');
    		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    		header('Pragma: public');
    		//header('Content-Length: ' . filesize($file));
    		ob_clean();
    		flush();
    		readfile($path);
		} else echo "error";

	}
}

