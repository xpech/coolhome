<?php

namespace App\Controllers;

class Devices extends BaseController
{
    public function index()
    {
        $auth = $this->requireUser('/devices');
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $auth;
        }

        $db = App\Class\CDatabase::DB();
        $devices = $db->keyedrows(
            'SELECT d.id,d.uuid,d.title,d.room,d.kind,d.reference,d.owner,r.title AS room_title FROM t_device d LEFT JOIN t_room r ON r.id = d.room WHERE d.owner = %d ORDER BY d.title ASC',
            (int) $auth['id']
        );

        foreach ($devices as &$device) {
            $device['active'] = $this->isDeviceActive((int) $device['id']);
            $device['display_title'] = trim((string) $device['title']) !== ''
                ? (string) $device['title']
                : ('Satellite ' . (string) $device['uuid']);
            $device['local_url'] = 'http://sweethome_sat_' . (string) $device['uuid'] . '.local';
        }

        return view('devices/index', [
            'devices' => $devices,
        ]);
    }

    public function firmwares()
    {
        $auth = $this->requireUser('/devices/firmwares');
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $auth;
        }

        $firmwares = [];
        $basePath = ROOTPATH . 'application/firmwares';
        if (is_dir($basePath)) {
            $platforms = scandir($basePath) ?: [];
            foreach ($platforms as $ptf) {
                if ($ptf === '.' || $ptf === '..') {
                    continue;
                }
                $platformPath = $basePath . DIRECTORY_SEPARATOR . $ptf;
                if (! is_dir($platformPath)) {
                    continue;
                }
                $files = scandir($platformPath) ?: [];
                foreach ($files as $firm) {
                    $filePath = $platformPath . DIRECTORY_SEPARATOR . $firm;
                    if (! is_file($filePath)) {
                        continue;
                    }
                    $firmwares[] = [
                        'platform' => $ptf,
                        'file' => $firm,
                        'mtime' => filemtime($filePath) ?: null,
                    ];
                }
            }
        }

        usort($firmwares, static fn (array $a, array $b): int => strcmp($a['platform'] . '/' . $a['file'], $b['platform'] . '/' . $b['file']));

        return view('devices/firmwares', [
            'firmwares' => $firmwares,
        ]);
    }

    public function updates()
    {
        return $this->firmwares();
    }

    public function dl(string $ptf, string $firm)
    {
        $auth = $this->requireUser('/devices/firmwares');
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $auth;
        }

        $firmwaresRoot = realpath(ROOTPATH . 'application/firmwares');
        if ($firmwaresRoot === false) {
            return redirect()->to('/devices/firmwares');
        }

        $requested = realpath($firmwaresRoot . DIRECTORY_SEPARATOR . $ptf . DIRECTORY_SEPARATOR . $firm);
        if ($requested === false || ! str_starts_with($requested, $firmwaresRoot . DIRECTORY_SEPARATOR) || ! is_file($requested)) {
            return redirect()->to('/devices/firmwares');
        }

        return $this->response->download($requested, null)->setFileName(basename($requested));
    }

    private function isDeviceActive(int $deviceId): bool
    {
        $limit = date('Y-m-d H:i:s', strtotime('-2 hours'));
        $db = App\Class\CDatabase::DB();
        $cnt = (int) $db->oneValue(
            'SELECT count(*) FROM t_data WHERE device = %d AND created > %s',
            $deviceId,
            $db->STRING($limit)
        );

        return $cnt > 0;
    }
}
