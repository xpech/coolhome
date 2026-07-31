<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(int $id)
    {
        $auth = $this->requireUser('/home/index/' . $id);
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $auth;
        }

        $db = \App\Class\CDatabase::DB();
        $supportsLocation = $this->hasColumn('t_home', 'location');
        $supportsRoomAppearance = $this->hasColumn('t_room', 'icon') && $this->hasColumn('t_room', 'color');
        $rows = $db->keyedrows(
            'SELECT id,owner,title,mode,confort_temp,absence_temp FROM t_home WHERE id = %d AND owner = %d LIMIT 1',
            $id,
            (int) $auth['id']
        );
        $home = $rows ? $rows[0] : null;

        if (! $home) {
            return redirect()->to('/homes');
        }

        if ($supportsLocation) {
            $locationRow = $db->keyedrows('SELECT location FROM t_home WHERE id = %d LIMIT 1', $id);
            $home['location'] = (string) ($locationRow[0]['location'] ?? '');
        } else {
            $home['location'] = '';
        }

        if ($this->request->getMethod(true) === 'POST' && $this->request->getPost('action') === 'UpdateHomeMode') {
            $mode = (int) $this->request->getPost('mode');
            if (in_array($mode, [-1, 0, 1], true)) {
                $db->query(
                    'UPDATE t_home SET mode = %d WHERE id = %d',
                    $mode,
                    $id
                );
            }

            return redirect()->to('/home/index/' . $id);
        }

        if ($this->request->getMethod(true) === 'POST' && $this->request->getPost('action') === 'UpdateHome') {
            if ($supportsLocation) {
                $db->query(
                    'UPDATE t_home SET title = %s, confort_temp = %f, absence_temp = %f, location = %s, mode = %d WHERE id = %d',
                    $db->STRING(trim((string) $this->request->getPost('title'))),
                    (float) $this->request->getPost('confort_temp'),
                    (float) $this->request->getPost('absence_temp'),
                    $db->STRING(trim((string) $this->request->getPost('location'))),
                    (int) $this->request->getPost('mode'),
                    $id
                );
            } else {
                $db->query(
                    'UPDATE t_home SET title = %s, confort_temp = %f, absence_temp = %f, mode = %d WHERE id = %d',
                    $db->STRING(trim((string) $this->request->getPost('title'))),
                    (float) $this->request->getPost('confort_temp'),
                    (float) $this->request->getPost('absence_temp'),
                    (int) $this->request->getPost('mode'),
                    $id
                );
            }

            return redirect()->to('/home/index/' . $id);
        }

        $precipitations = null;
        if ($supportsLocation && trim((string) ($home['location'] ?? '')) !== '') {
            try {
                $homeObj = \App\Class\CHome::objectWithId($id);
                if ($homeObj) {
                    $p = $homeObj->precipitations();
                    if (is_numeric($p) && (float) $p >= 0) {
                        $precipitations = (float) $p;
                    }
                }
            } catch (\Throwable) {
                $precipitations = null;
            }
        }

        if ($supportsRoomAppearance) {
            $rooms = $db->keyedrows(
                'SELECT id,title,mode,icon,color FROM t_room WHERE home = %d ORDER BY title ASC',
                $id
            );
        } else {
            $rooms = $db->keyedrows(
                'SELECT id,title,mode FROM t_room WHERE home = %d ORDER BY title ASC',
                $id
            );
        }

        foreach ($rooms as &$room) {
            $room['target'] = $this->roomTarget((int) $room['id'], (int) $room['mode'], $home);
            $room['temp'] = $this->roomLatestTemp((int) $room['id']);
            if (! $supportsRoomAppearance) {
                $room['icon'] = '';
                $room['color'] = '';
            }
        }

        return view('home/index', [
            'home' => $home,
            'rooms' => $rooms,
            'supportsLocation' => $supportsLocation,
            'precipitations' => $precipitations,
            'supportsRoomAppearance' => $supportsRoomAppearance,
        ]);
    }

    public function addroom(int $id)
    {
        $auth = $this->requireUser('/home/index/' . $id);
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $auth;
        }

        $db = \App\Class\CDatabase::DB();
        $rows = $db->keyedrows(
            'SELECT id FROM t_home WHERE id = %d AND owner = %d LIMIT 1',
            $id,
            (int) $auth['id']
        );
        $home = $rows ? $rows[0] : null;

        if (! $home) {
            return redirect()->to('/homes');
        }

        $db->query(
            'INSERT INTO t_room (home,title,mode) VALUES (%d,%s,%d)',
            $id,
            $db->STRING('Nouvelle piece'),
            0
        );

        $roomId = (int) $db->getLastInsertId();
        if ($roomId > 0) {
            return redirect()->to('/room/index/' . $roomId);
        }

        return redirect()->to('/home/index/' . $id);
    }

    public function datachart(int $id)
    {
        $auth = $this->requireUser('/home/index/' . $id);
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'forbidden']);
        }

        $db = \App\Class\CDatabase::DB();
        $homeRows = $db->keyedrows(
            'SELECT id FROM t_home WHERE id = %d AND owner = %d LIMIT 1',
            $id,
            (int) $auth['id']
        );

        if (! $homeRows) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'not_found']);
        }

        $range = (string) ($this->request->getGet('range') ?? '7d');
        $fromInput = trim((string) ($this->request->getGet('from') ?? ''));
        $toInput = trim((string) ($this->request->getGet('to') ?? ''));

        $from = null;
        $to = null;

        if ($fromInput !== '' || $toInput !== '') {
            $from = $this->normalizeDateBoundary($fromInput, true);
            $to = $this->normalizeDateBoundary($toInput, false);
        } else {
            $now = new \DateTimeImmutable('now');
            if ($range === '24h') {
                $from = $now->sub(new \DateInterval('PT24H'))->format('Y-m-d H:i:s');
            } elseif ($range === '7d') {
                $from = $now->sub(new \DateInterval('P7D'))->format('Y-m-d H:i:s');
            } elseif ($range === '30d') {
                $from = $now->sub(new \DateInterval('P30D'))->format('Y-m-d H:i:s');
            }
        }

        if ($from !== null && $to !== null && strcmp($from, $to) > 0) {
            [$from, $to] = [$to, $from];
        }

        $timeBucket = 'FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(d.created) / 900) * 900)';
        $sql = 'SELECT r.id AS room_id,r.title AS room_title,' . $timeBucket . ' AS created,AVG(d.value) AS value FROM t_data d JOIN t_room r ON r.id = d.room WHERE r.home = %d AND d.kind = %s';
        $args = [$id, $db->STRING('temp')];

        if ($from !== null) {
            $sql .= ' AND d.created >= %s';
            $args[] = $db->STRING($from);
        }
        if ($to !== null) {
            $sql .= ' AND d.created <= %s';
            $args[] = $db->STRING($to);
        }

        $limit = $range === 'all' ? 12000 : 4000;
        if ($fromInput !== '' || $toInput !== '') {
            $limit = 12000;
        }
        $sql .= ' GROUP BY r.id,r.title,' . $timeBucket . ' ORDER BY created ASC';
        // $args[] = $limit;

        $points = $db->keyedrows($sql, ...$args);

        $seriesByRoom = [];
        foreach ($points as $p) {
            $roomKey = (string) ($p['room_id'] ?? '0');
            if (! isset($seriesByRoom[$roomKey])) {
                $seriesByRoom[$roomKey] = [
                    'label' => (string) ($p['room_title'] ?? ('Piece ' . $roomKey)),
                    'data' => [],
                ];
            }

            $seriesByRoom[$roomKey]['data'][] = [
                'x' => (string) ($p['created'] ?? ''),
                'y' => isset($p['value']) ? (float) $p['value'] : null,
            ];
        }

        return $this->response->setJSON(array_values($seriesByRoom));
    }

    private function normalizeDateBoundary(string $input, bool $isStart): ?string
    {
        if ($input === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $input) === 1) {
            return $input . ($isStart ? ' 00:00:00' : ' 23:59:59');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $input) === 1) {
            return str_replace('T', ' ', $input) . ':00';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}(:\d{2})?$/', $input) === 1) {
            if (strlen($input) === 16) {
                return $input . ':00';
            }

            return $input;
        }

        return null;
    }

    private function roomLatestTemp(int $roomId): ?float
    {
        try {
            $db = \App\Class\CDatabase::DB();
            $row = $db->keyedrows(
                'SELECT value FROM t_data WHERE room = %d AND kind = %s ORDER BY created DESC LIMIT 1',
                $roomId,
                $db->STRING('temp')
            );
        } catch (\Throwable) {
            return null;
        }

        return $row ? (float) $row[0]['value'] : null;
    }

    private function roomTarget(int $roomId, int $roomMode, array $home): ?float
    {
        if ($roomMode < 0) {
            return isset($home['absence_temp']) ? (float) $home['absence_temp'] : null;
        }
        if ($roomMode > 0) {
            return isset($home['confort_temp']) ? (float) $home['confort_temp'] : null;
        }

        $day = (int) date('N');
        $mask = 2 << $day;
        $time = date('H:i:s');

        $db = \App\Class\CDatabase::DB();
        $row = $db->keyedrows(
            "SELECT temp FROM t_room_program WHERE room = %d AND (weekday & %d) <> 0 AND start < %s ORDER BY start DESC LIMIT 1",
            $roomId,
            $mask,
            $db->STRING($time)
        );

        return $row ? (float) $row[0]['temp'] : null;
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
}
