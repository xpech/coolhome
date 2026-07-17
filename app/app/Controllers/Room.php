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
        $rows = $db->keyedrows(
            'SELECT r.id,r.home,r.title,r.mode,h.owner,h.title AS home_title,h.confort_temp,h.absence_temp FROM t_room r JOIN t_home h ON h.id = r.home WHERE r.id = %d AND h.owner = %d LIMIT 1',
            $id,
            (int) $auth['id']
        );
        $room = $rows ? $rows[0] : null;

        if (! $room) {
            return redirect()->to('/homes');
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
                $db->query(
                    'UPDATE t_room SET title = %s, mode = %d WHERE id = %d',
                    $db->STRING(trim((string) $this->request->getPost('title'))),
                    (int) $this->request->getPost('mode'),
                    $id
                );

                return redirect()->to('/room/index/' . $id);
            }

            if ($action === 'UpdateProgram' || $action === 'AddProgram') {
                $this->savePrograms($id);
                return redirect()->to('/room/index/' . $id);
            }
        }

        $devices = $db->keyedrows(
            'SELECT id,title,uuid,kind FROM t_device WHERE room = %d ORDER BY title ASC',
            $id
        );

        $programs = $db->keyedrows(
            'SELECT id,weekday,start,temp FROM t_room_program WHERE room = %d ORDER BY start ASC',
            $id
        );

        $temp = $this->roomLatestTemp($id);
        $target = $this->roomTarget($room, $id);

        return view('room/index', [
            'room' => $room,
            'devices' => $devices,
            'programs' => $programs,
            'temp' => $temp,
            'target' => $target,
        ]);
    }

    private function savePrograms(int $roomId): void
    {
        $db = \App\Class\CDatabase::DB();
        $existing = $db->keyedrows('SELECT id FROM t_room_program WHERE room = %d', $roomId);

        $starts = $this->request->getPost('p_start') ?? [];
        $days = $this->request->getPost('p_weekday') ?? [];
        $temps = $this->request->getPost('p_temp') ?? [];

        foreach ($existing as $row) {
            $programId = (int) $row['id'];
            if (! isset($starts[$programId])) {
                $db->query('DELETE FROM t_room_program WHERE id = %d', $programId);
                continue;
            }

            $db->query(
                'UPDATE t_room_program SET start = %s, weekday = %d, temp = %f WHERE id = %d',
                $db->STRING((string) $starts[$programId]),
                $this->weekdayMask($days[$programId] ?? []),
                (float) ($temps[$programId] ?? 0),
                $programId
            );
        }

        if ((string) ($this->request->getPost('action') ?? '') === 'AddProgram') {
            $newStart = trim((string) $this->request->getPost('new_start'));
            if ($newStart !== '') {
                $db->query(
                    'INSERT INTO t_room_program (room,start,weekday,temp) VALUES (%d,%s,%d,%f)',
                    $roomId,
                    $db->STRING($newStart),
                    $this->weekdayMask($this->request->getPost('new_weekday') ?? []),
                    (float) $this->request->getPost('new_temp')
                );
            }
        }
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
