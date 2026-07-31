<?php
namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Class\CDebug;
use App\Class\CUser;
use App\Class\CDevice;
use App\Class\CData;
use App\Class\CDatabase;

class Api extends BaseController {
    use ResponseTrait;

	var $acl = false;
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/api
	 *	- or -
	 * 		http://example.com/index.php/api/index
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
							if (CData::shouldStore($d->device, $d->sensor, $d->kind, $d->value)) {
								$d->create();
							}
						}
					}
					$heater = false;
					$duration = 0;
					if ($x->kind == "HEATER")
					{

						if ($room = $x->room())
						{
							$heater = ($room->temp() < $room->target());
							CDebug::log("Radiateur " .$room->info() . " :  ".(int)$heater);
						}

					} else if ($x->kind == "FAN")
					{
						if ($room = $x->room())
						{
							$heater = ($room->hum() > $room->target('FAN'));
							CDebug::log("Fan " .$room->info() . " :  ".$heater);
						}
					} else if ($x->kind == "WATERING")
					{
						if ($room = $x->room())
						{
							$home = $room->home();
							$precipitation = $home->precipitations();

							if (false && $precipitation > 5 )
							{
								CDebug::log("precipitation : not watering");
								$heater  = false;
								$duration = 0;
							} else if ($p = $room->program('WATERING'))
							{
								$heater = $p->watering();
								$duration = ($heater ? $p->watering_duration() : 0);
							} else
							{
								CDebug::log('no watering program');
								$duration = 0;
								$heater = 0;
							}
							CDebug::log('MODE WATERING '.(int)$heater.' duration = '.$duration);
						}
					}
					return $this->setResponseFormat('json')->respond([
						'connected' => true,
						'datetime' => date('now'),
						'heater' => $heater,
						'duration' => $duration
					]);
				}
			} else CDebug::log('API : unknown CoolHomeAccount'. $headers['CoolHomeAccount']);
		} else {
			CDebug::log('API : no CoolHomeAccount autodetect ?');
		}
		return $this->setResponseFormat('json')->respond([
						'connected' => false,
						'datetime' => date('now')
					]);

	}
}

