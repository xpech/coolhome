<?php

namespace App\Controllers;

use App\Libraries\HomeAccess;

class Homes extends BaseController
{
    public function index()
    {
        $user = $this->session->get('user');
        if (! is_array($user) || empty($user['id'])) {
            return redirect()->to('/login?backto=' . rawurlencode('/homes'));
        }

        $ownerId = (int) $user['id'];
        $db = \App\Class\CDatabase::DB();
        $supportsLocation = $this->hasColumn('t_home', 'location');

        if ($this->request->getMethod(true) === 'POST' && $this->request->getPost('action') === 'UpdateHomeMode') {
            $homeId = (int) $this->request->getPost('home_id');
            $mode = (int) $this->request->getPost('mode');

            if ($homeId > 0 && in_array($mode, [-1, 0, 1], true)) {
                $owned = $db->keyedRows(
                    'SELECT h.id FROM t_home h WHERE h.id = %d AND ' . HomeAccess::condition($ownerId) . ' LIMIT 1',
                    $homeId
                );

                if ($owned) {
                    $db->query(
                        'UPDATE t_home SET mode = %d WHERE id = %d',
                        $mode,
                        $homeId
                    );
                }
            }

            return redirect()->to('/homes');
        }

        $homeQuery = 'SELECT h.* FROM t_home h WHERE ' . HomeAccess::condition($ownerId) . ' ORDER BY h.title DESC';
        $homes = $db->keyedrows($homeQuery);

        if ($homes === []) {
            $db->query(
                'INSERT INTO t_home (owner,title,mode,confort_temp,absence_temp) VALUES (%d,%s,%d,%f,%f)',
                $ownerId,
                $db->STRING('Chez moi'),
                0,
                20,
                16
            );

            $homes = $db->keyedrows($homeQuery);
        }

        $supportsProgramKind = $this->hasColumn('t_room_program', 'kind');
        $roomsPreviewByHome = $this->buildRoomsPreviewForHomes($homes, $supportsProgramKind);

        $precipByLocation = [];
        foreach ($homes as &$home) {
            $home['role'] = HomeAccess::role((int) $home['id'], $ownerId);
            $home['location'] = (string) ($home['location'] ?? '');
            $home['precipitations'] = null;
            $home['rooms_preview'] = $roomsPreviewByHome[(int) $home['id']] ?? [];

            if ($supportsLocation && trim((string) $home['location']) !== '') {
                $locationKey = strtolower(trim((string) $home['location']));
                if (array_key_exists($locationKey, $precipByLocation)) {
                    $home['precipitations'] = $precipByLocation[$locationKey];
                    continue;
                }

                try {
                    $homeObj = new \App\Class\CHome();
                    $homeObj->id = (int) $home['id'];
                    $homeObj->location = (string) $home['location'];
                    $p = $homeObj->precipitations();
                    if (is_numeric($p) && (float) $p >= 0) {
                        $home['precipitations'] = (float) $p;
                    }
                } catch (\Throwable) {
                    $home['precipitations'] = null;
                }

                $precipByLocation[$locationKey] = $home['precipitations'];
            }
        }

        return view('homes/index', [
            'homes' => $homes,
            'user' => $user,
            'supportsLocation' => $supportsLocation,
        ]);
    }

    public function remove(int $id)
    {
        $user = $this->session->get('user');
        if (! is_array($user) || empty($user['id'])) {
            return redirect()->to('/login?backto=' . rawurlencode('/homes'));
        }

        $ownerId = (int) $user['id'];
        $db = \App\Class\CDatabase::DB();

        $home = $db->keyedrows(
            'SELECT h.id FROM t_home h WHERE h.id = %d AND ' . HomeAccess::condition($ownerId, 'h', true) . ' LIMIT 1',
            $id
        );

        if ($home) {
            $connection = db_connect();
            $connection->transStart();
            $db->query('UPDATE t_device SET room = NULL WHERE room IN (SELECT id FROM t_room WHERE home = %d)', $id);
            $db->query('DELETE FROM t_room_program WHERE room IN (SELECT id FROM t_room WHERE home = %d)', $id);
            $db->query('DELETE FROM t_room WHERE home = %d', $id);
            $db->query('DELETE FROM t_home_invitation WHERE home = %d', $id);
            $db->query('DELETE FROM t_home_member WHERE home = %d', $id);
            $db->query('DELETE FROM t_home WHERE id = %d', (int) $home[0]['id']);
            $connection->transComplete();
            if (! $connection->transStatus()) {
                throw new \RuntimeException('Home deletion failed.');
            }
        }

        return redirect()->to('/homes');
    }

    public function add()
    {
        $user = $this->session->get('user');
        if (! is_array($user) || empty($user['id'])) {
            return redirect()->to('/login?backto=' . rawurlencode('/homes'));
        }

        $ownerId = (int) $user['id'];
        $db = \App\Class\CDatabase::DB();

        $countRows = $db->keyedRows(
            'SELECT COUNT(*) AS nb FROM t_home WHERE owner = %d',
            $ownerId
        );
        $nextIndex = (int) ($countRows[0]['nb'] ?? 0) + 1;
        $title = 'Maison ' . $nextIndex;

        $db->query(
            'INSERT INTO t_home (owner,title,mode,confort_temp,absence_temp) VALUES (%d,%s,%d,%f,%f)',
            $ownerId,
            $db->STRING($title),
            0,
            20,
            16
        );

        $newId = (int) $db->getLastInsertId();
        if ($newId > 0) {
            return redirect()->to('/home/index/' . $newId);
        }

        return redirect()->to('/homes');
    }

    public static function modeLabel(int $mode): string
    {
        return match ($mode) {
            -1 => 'Absence',
            0 => 'Automatique',
            1 => 'Confort',
            default => '?',
        };
    }

    private function hasColumn(string $table, string $column): bool
    {
        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        if ($safeTable === '') {
            return false;
        }

        $db = \App\Class\CDatabase::DB();
        $rows = $db->keyedrows(
            "SHOW COLUMNS FROM `{$safeTable}` LIKE %s",
            $db->STRING($column)
        );

        return ! empty($rows);
    }

    private function buildRoomsPreviewForHomes(array $homes, bool $supportsProgramKind): array
    {
        $homeIds = [];
        $homeTemps = [];
        foreach ($homes as $home) {
            $homeId = (int) ($home['id'] ?? 0);
            if ($homeId <= 0) {
                continue;
            }

            $homeIds[] = $homeId;
            $homeTemps[$homeId] = [
                'absence_temp' => isset($home['absence_temp']) ? (float) $home['absence_temp'] : null,
                'confort_temp' => isset($home['confort_temp']) ? (float) $home['confort_temp'] : null,
            ];
        }

        if ($homeIds === []) {
            return [];
        }

        $db = \App\Class\CDatabase::DB();
        $roomsAll = $db->keyedrows(
            'SELECT id,home,title,mode FROM t_room WHERE home IN %s ORDER BY home ASC, title ASC',
            $db->ARRAY_INT($homeIds)
        );

        $roomsByHome = [];
        $roomIds = [];
        foreach ($roomsAll as $room) {
            $homeId = (int) ($room['home'] ?? 0);
            if ($homeId <= 0) {
                continue;
            }

            if (! isset($roomsByHome[$homeId])) {
                $roomsByHome[$homeId] = [];
            }
            if (count($roomsByHome[$homeId]) >= 4) {
                continue;
            }

            $room['target'] = null;
            $room['temp'] = null;
            $room['hr'] = null;
            $roomsByHome[$homeId][] = $room;
            $roomIds[] = (int) ($room['id'] ?? 0);
        }

        if ($roomIds === []) {
            return $roomsByHome;
        }

        $latestByRoom = [];
        $latestRows = $db->keyedrows(
            'SELECT d.room,d.kind,d.value FROM t_data d JOIN (SELECT room,kind,MAX(created) AS max_created FROM t_data WHERE room IN %s AND kind IN (%s,%s) GROUP BY room,kind) x ON x.room = d.room AND x.kind = d.kind AND x.max_created = d.created',
            $db->ARRAY_INT($roomIds),
            $db->STRING('temp'),
            $db->STRING('hr')
        );
        foreach ($latestRows as $row) {
            $roomId = (int) ($row['room'] ?? 0);
            $kind = (string) ($row['kind'] ?? '');
            if ($roomId <= 0 || ($kind !== 'temp' && $kind !== 'hr')) {
                continue;
            }
            $latestByRoom[$roomId][$kind] = isset($row['value']) ? (float) $row['value'] : null;
        }

        $day = (int) date('N');
        $mask = 2 << $day;
        $time = date('H:i:s');
        if ($supportsProgramKind) {
            $targetRows = $db->keyedrows(
                'SELECT p.room,p.temp FROM t_room_program p JOIN (SELECT room,MAX(start) AS max_start FROM t_room_program WHERE room IN %s AND kind = %s AND (weekday & %d) <> 0 AND start < %s GROUP BY room) x ON x.room = p.room AND x.max_start = p.start WHERE p.kind = %s',
                $db->ARRAY_INT($roomIds),
                $db->STRING('HEATER'),
                $mask,
                $db->STRING($time),
                $db->STRING('HEATER')
            );
        } else {
            $targetRows = $db->keyedrows(
                'SELECT p.room,p.temp FROM t_room_program p JOIN (SELECT room,MAX(start) AS max_start FROM t_room_program WHERE room IN %s AND (weekday & %d) <> 0 AND start < %s GROUP BY room) x ON x.room = p.room AND x.max_start = p.start',
                $db->ARRAY_INT($roomIds),
                $mask,
                $db->STRING($time)
            );
        }

        $targetByRoom = [];
        foreach ($targetRows as $row) {
            $roomId = (int) ($row['room'] ?? 0);
            if ($roomId <= 0) {
                continue;
            }
            $targetByRoom[$roomId] = isset($row['temp']) ? (float) $row['temp'] : null;
        }

        foreach ($roomsByHome as $homeId => &$rooms) {
            $absence = $homeTemps[$homeId]['absence_temp'] ?? null;
            $confort = $homeTemps[$homeId]['confort_temp'] ?? null;

            foreach ($rooms as &$room) {
                $roomId = (int) ($room['id'] ?? 0);
                $mode = (int) ($room['mode'] ?? 0);

                if ($mode < 0) {
                    $room['target'] = $absence;
                } elseif ($mode > 0) {
                    $room['target'] = $confort;
                } else {
                    $room['target'] = $targetByRoom[$roomId] ?? null;
                }

                $room['temp'] = $latestByRoom[$roomId]['temp'] ?? null;
                $room['hr'] = $latestByRoom[$roomId]['hr'] ?? null;
            }
        }

        return $roomsByHome;
    }
}
