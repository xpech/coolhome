<?php

namespace App\Controllers;

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

        if ($this->request->getMethod(true) === 'POST' && $this->request->getPost('action') === 'UpdateHomeMode') {
            $homeId = (int) $this->request->getPost('home_id');
            $mode = (int) $this->request->getPost('mode');

            if ($homeId > 0 && in_array($mode, [-1, 0, 1], true)) {
                $owned = $db->keyedRows(
                    'SELECT id FROM t_home WHERE id = %d AND owner = %d LIMIT 1',
                    $homeId,
                    $ownerId
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

        $homes = $db->keyedrows(
            'SELECT id,title,mode,confort_temp,absence_temp FROM t_home WHERE owner = %d ORDER BY title DESC',
            $ownerId
        );

        if ($homes === []) {
            $db->query(
                'INSERT INTO t_home (owner,title,mode,confort_temp,absence_temp) VALUES (%d,%s,%d,%f,%f)',
                $ownerId,
                $db->STRING('Chez moi'),
                0,
                20,
                16
            );

            $homes = $db->keyedrows(
                'SELECT id,title,mode,confort_temp,absence_temp FROM t_home WHERE owner = %d ORDER BY title DESC',
                $ownerId
            );
        }

        return view('homes/index', [
            'homes' => $homes,
            'user' => $user,
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
            'SELECT id FROM t_home WHERE id = %d AND owner = %d LIMIT 1',
            $id,
            $ownerId
        );

        if ($home) {
            $db->query('DELETE FROM t_home WHERE id = %d', (int) $home[0]['id']);
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
}
