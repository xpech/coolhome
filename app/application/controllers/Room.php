<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Room extends CoolHomeController {
	var $breadcrumb = [['text' => 'Maisons', 'url' => '/homes']];
	var $title= 'Ma Maison';
	var $mainmenu = 'homes';
	var $menu = '';
	var $require = '';

	var $home;
	var $room;

	protected function setRoom($id_room)
	{
		$this->room = CRoom::objectWithId($id_room);
		$this->home = $this->room->home();
		if (!$this->home || $this->home->owner != CUser::user()->id)
		{
			$this->redirect('/');
			return;
		}
		$this->data('home',$this->home);
		$this->addpath($this->home->info(),'/home/index/'.$this->home->id);
		$this->data('room',$this->room);
		$this->addpath("pièces",null);
		$this->addpath($this->room->info(),'/room/index/'.(int)$id_room);
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

	public function index($id_room)
	{
		$this->setRoom($id_room);
		if (CHtml::action('DeleteRoom'))
		{

			if ($this->room->id)
			{
				CDebug::log("Suppresion ". $this->room->id);
				$this->room->del();
			} else {
				CDebug::log("Pas de pièc ea suprprimer");
			}
			return $this->redirect('/home/index',$this->room->home);


		}
		if (CHtml::action('UpdateRoom'))
		{
			$this->room->title= CHtml::text('title');
			$this->room->mode = CHtml::int('mode');
			$this->room->update();
			return $this->redirect('/room/index/'.$id_room);
		} else if (CHtml::action('UpdateProgram') || CHtml::action('AddProgram'))
		{
			$starts = CHtml::arr('p_start');
			$days = CHtml::arr('p_weekday');
			$temps = CHtml::arr('p_temp');
			foreach($this->room->programs() as $p)
			{
				if (isset($starts[$p->id]))
				{
					$p->start = $starts[$p->id];
					$p->setWeekdaysArray($days[$p->id]);
					$p->temp = $temps[$p->id];
					$p->update();
				} else {
					$p->del();
				}
			}
			if (CHtml::action('AddProgram'))
			{
				$p = new CRoomProgram();
				$p->room = (int)$id_room;
				$p->start = CHtml::text('new_start');
				$p->setWeekdaysArray(CHtml::arr('new_weekday'));
				$p->temp = CHtml::float('new_temp');
				$p->create();
			}
		}

		$this->view('room/index');
	}
	function programs($id_home)
	{
	}


	protected function actionGetDataChart()
	{
		$datas = CData::forRoom(CHtml::int('id_room'),CHtml::date('from'),CHtml::date('to'));
		$datasets = [];

		foreach($datas as $data)
		{
			$k = $data->sensor . ' '.$data->kind;
			if (!isset($datasets[$k]))
			{
				$datasets[$k] = [
					'label' => $k,
					'data' => []];
			}
			$datasets[$k]['data'][] = [
					'x' => $data->created('Y-m-d H:i:s'),
					'y' => $data->value];
		}
		$this->json(array_values($datasets));

	}
}

