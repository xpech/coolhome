<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Device extends CoolHomeController {
	var $breadcrumb = [['text' => 'Capteurs', 'url' => '/devices']];
	var $title= 'Satellites';
	var $mainmenu = 'satellites';
	var $menu = '';
	var $require = '';
	var $tabmenu = 'device';


	var $device;
	public function setObject($id_device)
	{
		$this->device = CDevice::objectWithId($id_device);
		if ($this->device)
		{
			$this->addpath($this->device->info(),'/device/index/'.$id_device);
			$this->data('device',$this->device);
		}
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


	public function index($id_device)
	{
		$this->setObject($id_device);
		if (CHtml::action('UpdateDevice'))
		{
			$this->device->title = CHtml::text('title');
			$this->device->kind = CHtml::text('kind');
			$this->device->reference = CHtml::bool('reference');
			$this->device->room = CHtml::int('room');
			$this->device->update();
			return $this->redirect('/device/index/'.$id_device);
		}else if (CHtml::action('DeleteDevice'))
		{
			$this->device->del();
			return $this->redirect('/devices');
		}
		$this->view('device/index');
	}

	function datasfordt($id_device)
	{
		$db = CDatabase::DB();
		$filter = '';
		$limits = sprintf(' limit %d,%d',CHtml::int('start'),CHtml::int('length'));
		$order = '';
		$orders = [];
		$colums = CHtml::arr('columns');
		foreach(CHtml::arr('order') as $ord)
		{
			$orders[] = $colums[$ord['column']]['data'] . ' '. $ord['dir'];
		}
		if (count($orders))
			$order = 'order by '. implode(',', $orders);
		if ($search = CHtml::arr('search'))
		{
			$key = filter_var($search['value'], FILTER_SANITIZE_STRING);
			if ($key)
			{
				$like = "like '%". $key ."%'";
				$filter = "AND (created $like OR sensor $like OR kind = '". $key ."')";
			}
		}


		if ($device = CDevice::objectWithId($id_device))
		{
			if ($device->owner == CUser::user()->id)
				$this->json([
					'draw' => CHtml::int('draw'),
					'recordsTotal' => $db->oneValue('SELECT count(*) FROM t_data WHERE device  = %d',$id_device),
					'recordsFiltered' => $db->oneValue('SELECT count(*) FROM t_data WHERE device = %d %s' ,$id_device,$filter),
					'data' =>  $db->keyedRows('SELECT * FROM t_data WHERE device = %d %s %s %s' ,$id_device,$filter,$order,$limits)
				]);
		}
		

	}

	protected function actionGetDataChart()
	{
		$datas = CData::forDevice(CHtml::int('id_device'),CHtml::date('from'),CHtml::date('to'));
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

