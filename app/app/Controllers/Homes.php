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
