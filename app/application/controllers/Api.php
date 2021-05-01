<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Api extends CoolHomeController {
	var $acl = false;
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
		CDebug::log('API : call index');
		$headers = apache_request_headers();
		if (isset($headers['CoolHomeAccount']))
		{
			$user = CUser::withEmail($headers['CoolHomeAccount']);
			if ($user)
			{
				if (isset($headers['CoolHomeDeviceId']))
				{
					$uuid = $headers['CoolHomeDeviceId'];
					$x = CDevice::forUuidAndOwner($uuid,$user->id);
					if ($x)
					{
					} else {
						$x = new CDevice();
						$x->uuid = $uuid;
						$x->owner = $user->id;
						$x->create();
					}
					$json = json_decode(file_get_contents("php://input"));
					if (isset($json->sensors))
					{
						foreach ($json->sensors as $s) {
							$d = new CData();
							$d->owner = $user->id;
							$d->room = $x->room;
							$d->device = $x->id;
							$d->sensor = $s->name;
							$d->kind = $s->kind;
							$d->value = $s->value;
							$d->create();
						}
					}
					$heater = false;
					if ($x->kind == "HEATER")
					{

						if ($room = $x->room())
						{
							$heater = ($room->temp() < $room->target());
							CDebug::log("Radiateur " .$room->info() . " :  ".$heater);
						}

					}
					return $this->json([
						'connected' => true,
						'datetime' => date('now'),
						'heater' => $heater
					]);
					
				}
			} else CDebug::log('API : unknown UserId'. $headers['CoolHomeUserId']);
		} else {
			CDebug::log('API : no CoolHomeUserId autodetect ?');
		}
		return $this->json([
						'connected' => false,
						'datetime' => date('now')
					]);

	}
}

