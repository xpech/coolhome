<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Homes extends CoolHomeController {
	var $breadcrumb = [['text' => 'Maisons', 'url' => '/homes']];
	var $title= 'Mes maisons';
	var $mainmenu = 'homes';
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
		$homes = CHome::forUser();
		if (count($homes) == 0)
		{
			$home = new CHome();
			$home->owner = CUser::user()->id;
			$home->title = "Chez moi";
			$home->create();
			$homes[] = $home;
		}

		$this->view('homes/index',['homes' => $homes]);
	}


	function remove($id_home)
	{
		if ($home = CHome::objectWithId($id_home))
		{
			if ($home->owner == CUser::user()->id)
			{
				$home->del();
			}
		}
		$this->redirect('/');
	}
}

