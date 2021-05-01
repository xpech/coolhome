<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Home extends CoolHomeController {
	var $breadcrumb = [['text' => 'Maisons', 'url' => '/homes']];
	var $title= 'Ma Maison';
	var $mainmenu = 'homes';
	var $menu = '';
	var $require = '';

	var $home;

	protected function setHome($id_home)
	{
		$this->home = CHome::objectWithId($id_home);
		if (!$this->home || $this->home->owner != CUser::user()->id)
		{
			$this->redirect('/');
			return;
		}
		$this->data('home',$this->home);
		$this->addpath($this->home->info(),'/home/index/'.(int)$id_home);
	}

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

	public function index($id_home)
	{
		$this->setHome($id_home);
		if( CHtml::action('UpdateHome'))
		{
			$this->home->title = CHtml::text('title');
			$this->home->confort_temp = CHtml::float('confort_temp');
			$this->home->absence_temp = CHtml::float('absence_temp');
			$this->home->mode = CHtml::int('mode');
			$this->home->update();
			return $this->redirect('/home/index/'.$this->home->id);

		}

		$this->view('home/index');
	}
	function addroom($id_home)
	{
		$this->setHome($id_home);
		$x = new CRoom();
		$x->home = $id_home;
		if ($x->create())
		{
			$this->redirect('/room/index/'.$x->id);
		} else $this->redirect('/home/index/'.(int)$id_home);
	}
}

