<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Admin extends CoolHomeController {
	var $breadcrumb = [['text' => 'Administration', 'url' => '/admin']];
	var $title= 'CoolHome - Admin';
	var $mainmenu = 'administration';
	var $menu = '';
	var $require = 'admin';
	var $acl = true;
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

	protected function check_access()
	{
		$admins = CConfig::load('admins');
		if (in_array(CUser::user()->email,$admins)) return true;
		$this->redirect('');
		die();
		return false;
	}
	public function index()
	{
		// $this->view('administration/users');
	}
	function users()
	{
		$this->check_access();
		$this->view("/admin/users");

	}
	function ubiquity($id_user)
	{
		$this->check_access();
		$this->session->user = CUser::objectWithId($id_user);
		$this->redirect();

	}


	public function user($id_user = null)
	{
		/*
		if ($id_user) $user = CUtilisateur::objectWithId($id_user);
		else $user = new  CUtilisateur();
		if (CHtml::val('save'))
		{
			$user->titre = CHtml::text('titre');
			$user->nom = CHtml::text('nom');
			$user->prenom = CHtml::text('prenom');
			$user->e_mail = CHtml::email('e_mail');
			$user->login = CHtml::text('code');
			$user->actif = CHtml::bool('actif');
			$user->intervenant = CHtml::int('intervenant');
			$user->causeBlocage = CHtml::text('causeBlocage');
			$user->groupes = CHtml::arr('groupes');
	
			if ($user->id) 
			{
				$user->update();
				if ($np = CHtml::val('new_password')) $user->changePassword($np);
			}
			else {
				$user->password =  CHtml::val('new_password');
				if ($user->create())
				{
					$this->redirect('/admin/user/'. $user->id);
				} else $this->error('Problème de création '. CDatabase::DB()->getLastError());
			}
			
		}
		
		$this->view('administration/user',['user' => $user]);
		*/
	}
	/*
	public function groups()
	{
		$this->view('administration/users');
	}
	public function updates()
	{
		$UPDATE_PATH = APPPATH .'/updates/*.php';
		$files = glob($UPDATE_PATH);
		$this->view('administration/updates',['files' => $files]);
	}
	public function database()
	{
		$this->view('administration/database');
	}
	public function messages()
	{
		$this->view('administration/messages');
	}
	public function backups()
	{
		$this->view('administration/backups');
	}
	public function sync()
	{
		$this->view('administration/windevsync');
	}

	protected function actionSyncIntervenants()
	{
		CIntervenant::syncContacts();
		return $this->jsonSuccess();
	}
	
	public function actionDoSync()
	{
		$mclass = CHtml::val('class');
		$x = new $mclass;
		$x::sync();
		$this->message('execute');
	}

	public function userlogs()
	{
		$this->view('administration/userlogs');
	}

	public function userlogs_datatable()
	{
		$tables = " FROM t_user_logs as tl left join t_utilisateurs as tu on (tu.id = tl.utilisateur) ";
		$fields = " tl.*, tu.nom as utilisateur";
		$filters = [];
		$search = CHtml::arr('search');

		$where = " WHERE date > '" . date('Y-m-d',strtotime('3 years ago')) . "' ";

		if (isset($search['value']) && $search['value'] != '')
		{
			$vals = explode(' ', $search['value']);
			foreach ($vals as $value) {
				$subss = [];
				$subss[] = 'refid='.(int)$value;
				$subss[] = "tu.nom like '".$value."%'";

				$filters[] = '('.implode(' OR ',$subss).')';
			}

		}


		$sort = '  order by  date desc';
		$order = CHtml::arr('order');
		$columns = CHtml::arr('columns');
		$sorts = [];
		if ($order)
		{
			foreach($order as $o)
				$sorts[]	 = $columns[$o['column']]['name'] . ' ' . $o['dir'];
		}
		if (count($sorts))
			$sort = ' order by '. implode(',',$sorts);
		$limits  = (CHtml::int('length') ? sprintf(' limit %d,%d',CHtml::int('start'),CHtml::int('length')) : '');

		// $where = (count($wheres) ? ' WHERE '. implode(' AND ',$wheres) : '');
		
		$filter = '';
		if (count($filters))
			$filter = ' AND '. implode(' AND ',$filters);

		$this->json([
				'draw' => CHtml::int('draw'),
				'recordsTotal' => CDatabase::DB()->oneValue('SELECT count(*) '.$tables. $where),
				'recordsFiltered' => CDatabase::DB()->oneValue('SELECT count(*) '.$tables . $where. $filter),
				'data' => CDatabase::DB()->keyedRows('SELECT ' .$fields .$tables . $where . $filter. $sort . $limits )]);
	}
	*/
}

