<?php

namespace App\Controllers;

class Room extends BaseController
{
    public function index(int $id)
    {
        $auth = $this->requireUser('/room/index/' . $id);
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $auth;
        }

        $db = \App\Class\CDatabase::DB();
        $supportsRoomAppearance = $this->hasColumn('t_room', 'icon') && $this->hasColumn('t_room', 'color');
        $supportsProgramKind = $this->hasColumn('t_room_program', 'kind');
        $rows = $db->keyedrows(
            'SELECT r.id,r.home,r.title,r.mode,h.owner,h.title AS home_title,h.confort_temp,h.absence_temp FROM t_room r JOIN t_home h ON h.id = r.home WHERE r.id = %d AND h.owner = %d LIMIT 1',
            $id,
            (int) $auth['id']
        );
        $room = $rows ? $rows[0] : null;

        if (! $room) {
            return redirect()->to('/homes');
        }

        if ($supportsRoomAppearance) {
            $extra = $db->keyedrows('SELECT icon,color FROM t_room WHERE id = %d LIMIT 1', $id);
            $room['icon'] = (string) ($extra[0]['icon'] ?? '');
            $room['color'] = (string) ($extra[0]['color'] ?? '');
        } else {
            $room['icon'] = '';
            $room['color'] = '';
        }

        $action = (string) ($this->request->getPost('action') ?? '');
        if ($this->request->getMethod(true) === 'POST') {
            if ($action === 'DeleteRoom') {
                $db->query('UPDATE t_device SET room = NULL WHERE room = %d', $id);
                $db->query('DELETE FROM t_room_program WHERE room = %d', $id);
                $db->query('DELETE FROM t_room WHERE id = %d', $id);

                return redirect()->to('/home/index/' . (int) $room['home']);
            }

            if ($action === 'UpdateRoom') {
                if ($supportsRoomAppearance) {
                    $db->query(
                        'UPDATE t_room SET title = %s, mode = %d, icon = %s, color = %s WHERE id = %d',
                        $db->STRING(trim((string) $this->request->getPost('title'))),
                        (int) $this->request->getPost('mode'),
                        $db->STRING(trim((string) $this->request->getPost('icon'))),
                        $db->STRING(trim((string) $this->request->getPost('color'))),
                        $id
                    );
                } else {
                    $db->query(
                        'UPDATE t_room SET title = %s, mode = %d WHERE id = %d',
                        $db->STRING(trim((string) $this->request->getPost('title'))),
                        (int) $this->request->getPost('mode'),
                        $id
                    );
                }

                return redirect()->to('/room/index/' . $id);
            }

            if (in_array($action, ['UpdateProgram', 'AddProgram', 'AddProgramHeater', 'AddProgramFan', 'AddProgramWatering'], true)) {
                $this->savePrograms($id, $action, $supportsProgramKind);
                return redirect()->to('/room/index/' . $id);
            }
        }

        $devices = $db->keyedrows(
            'SELECT id,title,uuid,kind FROM t_device WHERE room = %d ORDER BY title ASC',
            $id
        );

        if ($supportsProgramKind) {
            $programs = $db->keyedrows(
                'SELECT id,weekday,start,temp,kind FROM t_room_program WHERE room = %d ORDER BY kind ASC, start ASC',
                $id
            );
        } else {
            $programs = $db->keyedrows(
                'SELECT id,weekday,start,temp,%s AS kind FROM t_room_program WHERE room = %d ORDER BY start ASC',
                $db->STRING('HEATER'),
                $id
            );
        }

        $hasHeater = false;
        $hasFan = false;
        $hasWatering = false;
        foreach ($devices as $dev) {
            $k = (string) ($dev['kind'] ?? '');
            if ($k === 'HEATER') {
                $hasHeater = true;
            }
            if ($k === 'FAN') {
                $hasFan = true;
            }
            if ($k === 'WATERING') {
                $hasWatering = true;
            }
        }

        if (! $hasHeater && ! $hasFan && ! $hasWatering) {
            $hasHeater = true;
        }

        $temp = $this->roomLatestTemp($id);
        $target = $this->roomTarget($room, $id);

        return view('room/index', [
            'room' => $room,
            'devices' => $devices,
            'programs' => $programs,
            'temp' => $temp,
            'target' => $target,
            'supportsRoomAppearance' => $supportsRoomAppearance,
            'supportsProgramKind' => $supportsProgramKind,
            'hasHeater' => $hasHeater,
            'hasFan' => $hasFan,
            'hasWatering' => $hasWatering,
            'iconOptions' => $supportsRoomAppearance ? $this->loadChoiceList('icons.yml') : [],
            'colorOptions' => $supportsRoomAppearance ? $this->loadChoiceList('colors.yml') : [],
        ]);
    }

    public function datachart(int $id)
    {
        $auth = $this->requireUser('/room/index/' . $id);
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'forbidden']);
        }

        $db = \App\Class\CDatabase::DB();
        $rows = $db->keyedRows(
            'SELECT r.id FROM t_room r JOIN t_home h ON h.id = r.home WHERE r.id = %d AND h.owner = %d LIMIT 1',
            $id,
            (int) $auth['id']
        );

        if (! $rows) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'not_found']);
        }

        $points = $db->keyedRows(
            'SELECT created,value FROM t_data WHERE room = %d AND kind = %s ORDER BY created ASC LIMIT 1200',
            $id,
            $db->STRING('temp')
        );

        $series = [];
        foreach ($points as $p) {
            $series[] = [
                'x' => (string) ($p['created'] ?? ''),
                'y' => isset($p['value']) ? (float) $p['value'] : null,
            ];
        }

        return $this->response->setJSON([
            [
                'label' => 'Temperature',
                'data' => $series,
            ],
        ]);
    }

    private function savePrograms(int $roomId, string $action, bool $supportsProgramKind): void
    {
        $db = \App\Class\CDatabase::DB();
        if ($supportsProgramKind) {
            $existing = $db->keyedrows('SELECT id,kind FROM t_room_program WHERE room = %d', $roomId);
        } else {
            $existing = $db->keyedrows('SELECT id FROM t_room_program WHERE room = %d', $roomId);
        }

        $starts = $this->request->getPost('p_start') ?? [];
        $days = $this->request->getPost('p_weekday') ?? [];
        $temps = $this->request->getPost('p_temp') ?? [];
        $kinds = $this->request->getPost('p_kind') ?? [];

        foreach ($existing as $row) {
            $programId = (int) $row['id'];
            if (! isset($starts[$programId])) {
                $db->query('DELETE FROM t_room_program WHERE id = %d', $programId);
                continue;
            }

            if ($supportsProgramKind) {
                $kind = trim((string) ($kinds[$programId] ?? ($row['kind'] ?? 'HEATER')));
                if (! in_array($kind, ['HEATER', 'FAN', 'WATERING'], true)) {
                    $kind = 'HEATER';
                }

                $db->query(
                    'UPDATE t_room_program SET start = %s, weekday = %d, temp = %f, kind = %s WHERE id = %d',
                    $db->STRING((string) $starts[$programId]),
                    $this->weekdayMask($days[$programId] ?? []),
                    (float) ($temps[$programId] ?? 0),
                    $db->STRING($kind),
                    $programId
                );
            } else {
                $db->query(
                    'UPDATE t_room_program SET start = %s, weekday = %d, temp = %f WHERE id = %d',
                    $db->STRING((string) $starts[$programId]),
                    $this->weekdayMask($days[$programId] ?? []),
                    (float) ($temps[$programId] ?? 0),
                    $programId
                );
            }
        }

        $prefix = 'new';
        $kindForInsert = 'HEATER';
        if ($action === 'AddProgramFan') {
            $prefix = 'newfan';
            $kindForInsert = 'FAN';
        } elseif ($action === 'AddProgramWatering') {
            $prefix = 'new_watering';
            $kindForInsert = 'WATERING';
        }

        if (in_array($action, ['AddProgram', 'AddProgramHeater', 'AddProgramFan', 'AddProgramWatering'], true)) {
            $newStart = trim((string) $this->request->getPost($prefix . '_start'));
            if ($newStart !== '') {
                $weekday = $this->weekdayMask($this->request->getPost($prefix . '_weekday') ?? []);
                $temp = (float) $this->request->getPost($prefix . '_temp');

                if ($supportsProgramKind) {
                    $db->query(
                        'INSERT INTO t_room_program (room,start,weekday,temp,kind) VALUES (%d,%s,%d,%f,%s)',
                        $roomId,
                        $db->STRING($newStart),
                        $weekday,
                        $temp,
                        $db->STRING($kindForInsert)
                    );
                } else {
                    $db->query(
                        'INSERT INTO t_room_program (room,start,weekday,temp) VALUES (%d,%s,%d,%f)',
                        $roomId,
                        $db->STRING($newStart),
                        $weekday,
                        $temp
                    );
                }
            }
        }
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

    private function loadChoiceList(string $filename): array
    {
        $path = ROOTPATH . 'application/config/' . $filename;
        if (! is_file($path)) {
            return [];
        }

        $items = [];
        $current = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === '' || str_starts_with($trim, '#')) {
                continue;
            }

            if (str_starts_with($trim, '- ')) {
                if ($current !== [] && isset($current['name'], $current['css'])) {
                    $items[] = $current;
                }
                $current = [];
                $trim = trim(substr($trim, 1));
            }

            $parts = explode(':', $trim, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                $current[$key] = trim($value, " \t\n\r\0\x0B\"'");
            }
        }

        if ($current !== [] && isset($current['name'], $current['css'])) {
            $items[] = $current;
        }

        return $items;
    }

    private function weekdayMask(array $days): int
    {
        $value = 0;
        foreach ($days as $day) {
            $d = (int) $day;
            if ($d >= 1 && $d <= 7) {
                $value += 2 << $d;
            }
        }

        return $value;
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

    private function roomTarget(array $room, int $roomId): ?float
    {
        $mode = (int) $room['mode'];
        if ($mode < 0) {
            return isset($room['absence_temp']) ? (float) $room['absence_temp'] : null;
        }
        if ($mode > 0) {
            return isset($room['confort_temp']) ? (float) $room['confort_temp'] : null;
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
}
