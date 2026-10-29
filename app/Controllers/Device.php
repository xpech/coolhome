<?php

namespace App\Controllers;

use App\Libraries\HomeAccess;

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
            'SELECT d.id,d.uuid,d.title,d.room,d.kind,d.temp,d.reference,d.owner FROM t_device d WHERE d.id = %d AND ' . HomeAccess::deviceCondition((int) $auth['id']) . ' LIMIT 1',
            $id
        );
        $device = $rows ? $rows[0] : null;

        if (! $device) {
            return redirect()->to('/devices');
        }

        if ($this->request->getMethod(true) === 'POST') {
            $action = (string) ($this->request->getPost('action') ?? '');
            if ($action === 'UpdateDevice') {
                $room = (int) $this->request->getPost('room');
                if ($room > 0 && ! $db->keyedrows(
                    'SELECT r.id FROM t_room r JOIN t_home h ON h.id = r.home WHERE r.id = %d AND ' . HomeAccess::condition((int) $auth['id'], 'h', true),
                    $room
                )) {
                    return $this->response->setStatusCode(403)->setBody('Cette piece ne vous est pas accessible en tant qu\'Owner.');
                }
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
            'SELECT h.id,h.title FROM t_home h WHERE ' . HomeAccess::condition((int) $auth['id'], 'h', true) . ' ORDER BY h.title ASC'
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
            'SELECT d.id FROM t_device d WHERE d.id = %d AND ' . HomeAccess::deviceCondition((int) $auth['id']) . ' LIMIT 1',
            $id
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
            'SELECT d.id FROM t_device d WHERE d.id = %d AND ' . HomeAccess::deviceCondition((int) $auth['id']) . ' LIMIT 1',
            $id
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
