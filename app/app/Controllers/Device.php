<?php

namespace App\Controllers;

class Device extends BaseController
{
    public function index(int $id)
    {
        $auth = $this->requireUser('/device/index/' . $id);
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $auth;
        }

        $db = \App\Class\CDatabase::DB();
        $rows = $db->keyedrows(
            'SELECT id,uuid,title,room,kind,temp,reference,owner FROM t_device WHERE id = %d AND owner = %d LIMIT 1',
            $id,
            (int) $auth['id']
        );
        $device = $rows ? $rows[0] : null;

        if (! $device) {
            return redirect()->to('/devices');
        }

        if ($this->request->getMethod(true) === 'POST') {
            $action = (string) ($this->request->getPost('action') ?? '');
            if ($action === 'UpdateDevice') {
                $room = (int) $this->request->getPost('room');
                $db->query(
                    'UPDATE t_device SET title = %s, kind = %s, reference = %d, room = %s WHERE id = %d',
                    $db->STRING(trim((string) $this->request->getPost('title'))),
                    $db->STRING(trim((string) $this->request->getPost('kind'))),
                    ($this->request->getPost('reference') ? 1 : 0),
                    ($room > 0 ? (string) $room : 'null'),
                    $id
                );
                return redirect()->to('/device/index/' . $id);
            }
            if ($action === 'DeleteDevice') {
                $db->query('DELETE FROM t_device WHERE id = %d', $id);
                return redirect()->to('/devices');
            }
        }

        $homes = $db->keyedrows(
            'SELECT id,title FROM t_home WHERE owner = %d ORDER BY title ASC',
            (int) $auth['id']
        );

        $roomsByHome = [];
        foreach ($homes as $home) {
            $roomsByHome[(int) $home['id']] = $db->keyedrows(
                'SELECT id,title FROM t_room WHERE home = %d ORDER BY title ASC',
                (int) $home['id']
            );
        }

        $displayTitle = trim((string) $device['title']) !== '' ? (string) $device['title'] : ('Satellite ' . (string) $device['uuid']);

        return view('device/index', [
            'device' => $device,
            'displayTitle' => $displayTitle,
            'homes' => $homes,
            'roomsByHome' => $roomsByHome,
            'localUrl' => 'http://sweethome_sat_' . (string) $device['uuid'] . '.local',
        ]);
    }

    public function datasfordt(int $id)
    {
        $auth = $this->requireUser('/device/index/' . $id);
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $auth;
        }

        $db = \App\Class\CDatabase::DB();
        $device = $db->keyedrows(
            'SELECT id FROM t_device WHERE id = %d AND owner = %d LIMIT 1',
            $id,
            (int) $auth['id']
        );

        if (! $device) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $draw = (int) ($this->request->getPost('draw') ?? 0);
        $start = max(0, (int) ($this->request->getPost('start') ?? 0));
        $length = max(10, min(200, (int) ($this->request->getPost('length') ?? 10)));

        $searchValue = trim((string) (($this->request->getPost('search')['value'] ?? '')));
        $columns = $this->request->getPost('columns') ?? [];
        $orders = $this->request->getPost('order') ?? [];

        $allowedOrderColumns = ['created', 'sensor', 'kind', 'value'];
        $orderBy = 'created';
        $orderDir = 'DESC';
        if (is_array($orders) && isset($orders[0]['column'], $orders[0]['dir'])) {
            $idx = (int) $orders[0]['column'];
            $colName = $columns[$idx]['data'] ?? 'created';
            if (in_array($colName, $allowedOrderColumns, true)) {
                $orderBy = $colName;
            }
            $dir = strtoupper((string) $orders[0]['dir']);
            if (in_array($dir, ['ASC', 'DESC'], true)) {
                $orderDir = $dir;
            }
        }

        $whereSearch = '';
        if ($searchValue !== '') {
            $searchSql = $db->STRING('%' . $searchValue . '%');
            $whereSearch = sprintf(' AND (created LIKE %s OR sensor LIKE %s OR kind LIKE %s)', $searchSql, $searchSql, $searchSql);
        }

        $total = (int) $db->oneValue('SELECT count(*) FROM t_data WHERE device = %d', $id);
        $filtered = (int) $db->oneValue('SELECT count(*) FROM t_data WHERE device = %d %s', $id, $whereSearch);

        $rows = $db->keyedrows(
            'SELECT created,sensor,kind,value FROM t_data WHERE device = %d %s ORDER BY %s %s LIMIT %d,%d',
            $id,
            $whereSearch,
            $orderBy,
            $orderDir,
            $start,
            $length
        );

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $rows,
        ]);
    }

    public function datachart(int $id)
    {
        $auth = $this->requireUser('/device/index/' . $id);
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $auth;
        }

        $db = \App\Class\CDatabase::DB();
        $device = $db->keyedrows(
            'SELECT id FROM t_device WHERE id = %d AND owner = %d LIMIT 1',
            $id,
            (int) $auth['id']
        );

        if (! $device) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $from = trim((string) ($this->request->getGet('from') ?? ''));
        $to = trim((string) ($this->request->getGet('to') ?? ''));

        $where = '';
        if ($from !== '') {
            $where .= ' AND created > ' . $db->STRING(date('Y-m-d H:i:s', strtotime($from)));
        }
        if ($to !== '') {
            $where .= ' AND created < ' . $db->STRING(date('Y-m-d H:i:s', strtotime($to)));
        }

        $rows = $db->keyedrows(
            'SELECT created,sensor,kind,value FROM t_data WHERE device = %d %s ORDER BY created DESC',
            $id,
            $where
        );

        $datasets = [];
        foreach ($rows as $row) {
            $label = trim((string) $row['sensor'] . ' ' . (string) $row['kind']);
            if (! isset($datasets[$label])) {
                $datasets[$label] = [
                    'label' => $label,
                    'data' => [],
                ];
            }
            $datasets[$label]['data'][] = [
                'x' => (string) $row['created'],
                'y' => (float) $row['value'],
            ];
        }

        return $this->response->setJSON(array_values($datasets));
    }
}
