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
        $rows = $db->keyedrows(
            'SELECT id,owner,title,mode,confort_temp,absence_temp FROM t_home WHERE id = %d AND owner = %d LIMIT 1',
            $id,
            (int) $auth['id']
        );
        $home = $rows ? $rows[0] : null;

        if (! $home) {
            return redirect()->to('/homes');
        }

        if ($this->request->getMethod(true) === 'POST' && $this->request->getPost('action') === 'UpdateHome') {
            $db->query(
                'UPDATE t_home SET title = %s, confort_temp = %f, absence_temp = %f, mode = %d WHERE id = %d',
                $db->STRING(trim((string) $this->request->getPost('title'))),
                (float) $this->request->getPost('confort_temp'),
                (float) $this->request->getPost('absence_temp'),
                (int) $this->request->getPost('mode'),
                $id
            );

            return redirect()->to('/home/index/' . $id);
        }

        $rooms = $db->keyedrows(
            'SELECT id,title,mode FROM t_room WHERE home = %d ORDER BY title ASC',
            $id
        );

        foreach ($rooms as &$room) {
            $room['target'] = $this->roomTarget((int) $room['id'], (int) $room['mode'], $home);
            $room['temp'] = $this->roomLatestTemp((int) $room['id']);
        }

        return view('home/index', [
            'home' => $home,
            'rooms' => $rooms,
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

        $roomId = (int) $db->getLastID();
        if ($roomId > 0) {
            return redirect()->to('/room/index/' . $roomId);
        }

        return redirect()->to('/home/index/' . $id);
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
}
