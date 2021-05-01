<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CoolHomeController extends CI_Controller {

	var $show_navbar = true;
	var $show_menus = true;
	var $show_footer = true;
	var $auto_handel_post = false;
	var $onajax=false;
	
	var $menus = null;
	var $breadcrumb = null;
	var $menu = null;
	var $mainmenu = null;

	var $acl = true;
	var $acl_dmz = null;
	var $require = null;
	
	var $base_title = "CoolHome";
	var $title = "CoolHome";
	
	var $messages = [];
	
	var $breadcrumb_menus = null;
	var $tabmenus = null;
	var $current_tabmenus = null;
	
	var $debug = true;
	
	var $datas = [];
	
	var $stop_request = false;
	
	var $autoHandlePost = false;
	
	
	function __construct() {
		parent::__construct();

//		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
//		$this->load->driver('cache');
		if ($this->acl && !($this->router->method == $this->acl_dmz)) {
			if (!$this->session->user)
			{

				$this->redirect('/login?backto='.$_SERVER["REQUEST_URI"]);
				exit;
			}
			$this->session->user->reload();
			if (!$this->session->user) {
				$this->session = null;
				$this->redirect('/login?backto='.$_SERVER["REQUEST_URI"]);
				exit;
			} else if ($this->require)
			{
				$this->checkPermission($this->require);
			}
			
		}
		log_message('debug', '*************************** START '. $_SERVER['REQUEST_URI'] . ' *************************** ');
		
		$this->current_tabmenus = $this->router->method;
		if ($this->autoHandlePost) $this->handlePost();
	}
	
	protected function data($key,$val)
	{
		$this->datas[$key] = $val;
	}
	
	static protected function checkPermission($k = null)
	{
		if (!$k) $k = $this->require;
		if (!CUser::require($k))
		{
			http_response_code(401);
			header("Location: /");
			exit;
		}
	}
	
	public function Unauthorized()
	{
		http_response_code(401);
		echo "Erreur";
		die();
//		header()
	}
	
	public function logout()
	{
		$this->session->user = null;
		header("Location: /login");
		exit();
	}
	public function exit()
	{
		$this->session->user = null;
		header("Location: /login");
		exit();
	}


	protected function breadcrumb_menus()
	{
		return $this->breadcrumb_menus;
	}
	
	private function menus()
	{
		if (!$this->menus)
		{
			$conf = APPPATH.'menus/main.yml';
			$this->menus = yaml_parse_file($conf);
		}
		// print_r($this->menus);
		return $this->menus['main'];
	}

	public function ajax()
	{
		$this->onajax = true;
		if (!$this->autoHandlePost)
			$this->handlePost();
		if ($this->messages)
		{
			echo json_encode($this->messages);
		}
	}
	
	function headlessview($template,$datas=null)
	{
		$this->show_navbar = false;
		$this->show_menus = false;
		$this->show_footer = false;
		return $this->view($template,$datas);	
	}

	protected function mainMenu()
	{
		return null;
		if (isset($this->mainmenu))
			return ($this->menus()[$this->mainmenu]);
		return null;
	}
	protected function menu()
	{
		if (!$this->menu) return null;
		return ($this->mainMenu()['subs'][$this->menu]);	
	}
	
	public function pdfview($filename,$template)
	{
	}

	protected function menu_yaml_callback($value, $tag, $flags)
	{
		return str_replace('{id}',$this->object_id(),$value);
	}
	
	protected function tabmenus()
	{
		if ($this->tabmenus)
		{
			if (is_string($this->tabmenus))
			{
				$ndocs = 0;
				$this->tabmenus = yaml_parse_file(APPPATH.'menus/'.$this->tabmenus.'.yml',0,$ndocs,
					[ 	'!text' => [$this,'menu_yaml_callback'],
						'!id' => [$this,'menu_yaml_callback']]);
			}
			return $this->tabmenus;
		}
	}
	protected function object_id()
	{
		return null;
	}
	
	protected function require()
	{
		
		if (!call_user_func_array(['CUtilisateur','require'],func_get_args()))
		{
			
			$this->redirect('/');
		}
	}
	
	
	protected function postView($view,$data) {} 
	protected function afterView($view,$data) {} 
	
	protected function view($view = null,$datas=null)
	{
		if ($datas) $this->datas = array_merge($this->datas,$datas);
		if ($view && !is_file(APPPATH."views/$view.php")) @touch(APPPATH."views/$view.php");
		
		$this->load->view('templates/header',['title' => strip_tags($this->title), 'breadcrumb' => $this->breadcrumb]);
		if ($this->show_navbar)
		{
//			$this->load->view('templates/navbar');
//			$this->load->view('templates/header2');
		}
		if ($this->show_menus)
		{
			//$this->load->view('templates/menus',['menus' => $this->menus(), 'main' => $this->mainMenu(), 'menu' => $this->menu()]);
			/*
			$this->load->view('templates/main',);
			*/
			$this->load->view('templates/sidebar.php',['main' => $this->mainMenu(),'menu' => $this->menu()]);
			$this->load->view('templates/wrapper_top.php',[
								'breadcrumb' => $this->breadcrumb, 
								'breadcrumbmenus' => $this->breadcrumb_menus(), 
								'menus' => $this->menus(),
								'tabmenu' =>  $this->tabmenus(),
								'id' => $this->object_id(),
								'current_tabmenus' => $this->current_tabmenus,
								'main' => $this->mainMenu(),
								'menu' => $this->menu()]);			
		}
		$this->postView($view,$this->datas);
		if ($this->viewdatas) $this->load->view('templates/data',['data' => $this->viewdatas]);
		if ($view) {
			$this->load->view($view,$this->datas);
		} 
		$this->afterView($view,$this->datas);
		$this->load->view('templates/endmain');
		
		$this->load->view('templates/footer',['showfooter' => $this->show_footer,'messages' => $this->messages,]);
		
	}
	
	private $viewdatas = null;
	protected function part($view = null,$datas=null)
	{
		if ($this->datas) $datas = array_merge($this->datas,$datas);
		$this->viewdatas .= $this->load->view($view,$datas,TRUE);
	}
	

	public function success($txt)
	{
		$this->messages[] = ["txt"=> $txt, "class" => 'alert-success', 'toast'=> 'success'];
	}
	public function message($txt)
	{
		$this->messages[] = ["txt"=> $txt, "class" => 'alert-info', 'toast'=> 'info'];
	}
	public function warning($txt)
	{
		$this->messages[] = ["txt"=> $txt, "class" => 'alert-warning', 'toast'=> 'warning'];
	}
	public function error($txt)
	{
		$this->messages[] = ["txt"=> $txt, "class" => 'alert-danger', 'toast'=> 'error'];
	}
	public function log_r($value)
	{
		$this->messages[] = ["txt"=> print_r($value,true), "class" => 'alert-danger', 'toast'=> 'error'];
		
	}
	public function log($value)
	{
		if (CDebug::on())
			$this->message($value);
		
	}
	
	function jsonInfo($o) {
		return $o->getInfo();
	}
	function jsonFilter($v) {
		return ['id'=> $v->id, 'text' => $this->jsonInfo($v), 'icon' => null];
	}
	protected function jsonObjects($list,$http=null)
	{
		return $this->json(array_map([$this,'jsonFilter'],$list),$http);
	}
	
	protected function json($datas,$http=null)
	{
		if ($http) header($_SERVER["SERVER_PROTOCOL"].' '. $http);
		header("Content-type: application/json");
		
		echo json_encode($datas);
		if ($err = json_last_error())
		{
			echo json_last_error_msg() ;
			print_r($datas);
		}
	}
	
	public function jsonError($err=null)
	{
		if (!$err) $err = CDatabase::DB()->getLastError();
		$this->json(['status' => "error", 'msg' => $err ],'400 Bad Request');
	}
	
	public function jsonSuccess($msg  = null, $data = null)
	{
		$this->json(['status' => "OK", 'data' => $data, 'msg' => ($msg ? $msg : 'Opération effectuée'),'messages' => $this->messages ]);
	}
	
	function redirect()
	{
		$url = implode('/', func_get_args());
		if (substr($url,0,1) != '/' &&  substr($url,0,4) != 'http')
			$url = '/'.$url;

		header("Location: $url");
		exit;
	}
	
	protected function close()
	{
		echo('<html><body onload="javascript:window.setTimeout(\'self.close()\',1000);"></html>');
	}

	function addpath($text,$url = null,$title = null)
	{
		$this->breadcrumb[] = ['title' => $title, 'url' => $url,'text' => $text];
		$this->title = $this->base_title .' - '. $text;
		
	}
	function addBreadcrumbMenus($text,$url,$icon=null,$title=null,$attrs='')
	{
		$this->breadcrumb_menus[] = ['title' => ($title ? $title : $text),
				'text' => $text,
				'icon' => $icon,
				'attrs' => $attrs,
				'url' => $url];
	}
	var $handlePostDone = false;
	protected function handlePost()
	{
		if ($this->handlePostDone) {
			CDebug::warn("handlePost() called twice");
			return false;
		}
		if ($action = CHtml::val('action'))
		{
			$func = 'action'. $action;
			
			if (method_exists($this,$func))
			{
				call_user_func([$this,$func]);
				return true;
			} else CDebug::log('Action non reconnue :'. $action);
		}
		$this->handlePostDone = true;
		return false;
	}
	
	
	protected function mapWitIds($arr)
	{
		$res = [];
		foreach($arr as $o) $res[$o->id] = $o;
		return $o;
	}
	
	
	function getSecureKey($key)
	{
		return hash('md5',config_item('secret').$key);
	}
	
	
/*	 public function __call(string $name , array $arguments )
	 {
		 echo $name;
	 }
*/
}
