<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', static fn () => redirect()->to('/homes'));
$routes->get('migration', 'Migration::index');
$routes->get('legacy', static fn () => redirect()->to('/legacy.php'));

$routes->match(['GET', 'POST'], 'login', 'Login::index');
$routes->get('login/recover', 'Login::recover');
$routes->match(['GET', 'POST'], 'login/create', 'Login::create');
$routes->match(['GET', 'POST'], 'login/setPassword/(:any)/(:any)', 'Login::setPassword/$1/$2');
$routes->get('login/out', 'Login::out');

$routes->match(['GET', 'POST'], 'homes', 'Homes::index');
$routes->get('homes/add', 'Homes::add');
$routes->get('homes/remove/(:num)', 'Homes::remove/$1');

$routes->match(['GET', 'POST'], 'home/index/(:num)', 'Home::index/$1');
$routes->get('home/addroom/(:num)', 'Home::addroom/$1');
$routes->get('home/datachart/(:num)', 'Home::datachart/$1');
$routes->match(['GET', 'POST'], 'room/index/(:num)', 'Room::index/$1');
$routes->get('room/datachart/(:num)', 'Room::datachart/$1');

$routes->get('devices', 'Devices::index');
$routes->get('devices/firmwares', 'Devices::firmwares');
$routes->get('devices/updates', 'Devices::updates');
$routes->get('devices/dl/(:any)/(:any)', 'Devices::dl/$1/$2');

$routes->match(['GET', 'POST'], 'device/index/(:num)', 'Device::index/$1');
$routes->post('device/datasfordt/(:num)', 'Device::datasfordt/$1');
$routes->get('device/datachart/(:num)', 'Device::datachart/$1');


$routes->match(['GET', 'POST'], 'api', 'Api::index');
$routes->match(['GET', 'POST'], 'api/(:num)', 'Api::index/$1');
