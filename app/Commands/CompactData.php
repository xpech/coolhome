<?php

namespace App\Commands;

use App\Class\CData;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CompactData extends BaseCommand
{
    private const BATCH_SIZE = 1000;
    private const MAX_AGE = 3600;

    protected $group = 'CoolHome';
    protected $name = 'data:compact';
    protected $description = 'Supprime les mesures historiques trop proches en conservant les variations et un point par heure.';
    protected $usage = 'data:compact [--before "YYYY-MM-DD HH:MM:SS"] [--execute]';
    protected $options = [
        '--before' => 'Nettoie les mesures anterieures a cette date (au moins une heure dans le passe).',
        '--execute' => 'Applique les suppressions. Sans cette option, la commande simule le nettoyage.',
    ];

    public function run(array $params)
    {
        $execute = array_key_exists('execute', $params) || CLI::getOption('execute');
        $beforeOption = CLI::getOption('before');
        $latestAllowed = new \DateTimeImmutable('-1 hour');

        try {
            $before = $beforeOption
                ? new \DateTimeImmutable((string)$beforeOption)
                : $latestAllowed;
        } catch (\Exception) {
            CLI::error('Date --before invalide. Format conseille: YYYY-MM-DD HH:MM:SS.');
            return EXIT_ERROR;
        }

        if ($before > $latestAllowed) {
            CLI::error('La date --before doit etre anterieure d au moins une heure.');
            return EXIT_ERROR;
        }

        $beforeSql = $before->format('Y-m-d H:i:s');
        $db = db_connect();
        $series = $db->query(
            'SELECT DISTINCT device,sensor,kind FROM t_data WHERE kind IN (?, ?) AND created < ? ORDER BY device,sensor,kind',
            ['temp', 'hr', $beforeSql]
        )->getResultArray();

        $scanned = 0;
        $removable = 0;

        foreach ($series as $serie) {
            $threshold = CData::retentionThreshold($serie['kind']);
            if ($threshold === null) {
                continue;
            }

            $lastKeptCreated = null;
            $lastKeptValue = null;
            $cursorCreated = null;
            $cursorId = 0;

            do {
                $sql = 'SELECT id,created,value FROM t_data WHERE device = ? AND sensor = ? AND kind = ? AND created < ?';
                $bindings = [(int)$serie['device'], (string)$serie['sensor'], (string)$serie['kind'], $beforeSql];

                if ($cursorCreated !== null) {
                    $sql .= ' AND (created > ? OR (created = ? AND id > ?))';
                    $bindings[] = $cursorCreated;
                    $bindings[] = $cursorCreated;
                    $bindings[] = $cursorId;
                }

                $sql .= ' ORDER BY created,id LIMIT ' . self::BATCH_SIZE;
                $rows = $db->query($sql, $bindings)->getResultArray();
                $deleteIds = [];

                foreach ($rows as $row) {
                    $scanned++;
                    $created = strtotime((string)$row['created']);
                    $keep = $lastKeptCreated === null
                        || $created === false
                        || $created - $lastKeptCreated >= self::MAX_AGE
                        || abs((float)$row['value'] - $lastKeptValue) >= $threshold;

                    if ($keep) {
                        $lastKeptCreated = $created;
                        $lastKeptValue = (float)$row['value'];
                    } else {
                        $removable++;
                        $deleteIds[] = (int)$row['id'];
                    }
                }

                if ($execute && $deleteIds) {
                    $db->table('t_data')->whereIn('id', $deleteIds)->delete();
                }

                if ($rows) {
                    $lastRow = $rows[count($rows) - 1];
                    $cursorCreated = (string)$lastRow['created'];
                    $cursorId = (int)$lastRow['id'];
                }
            } while (count($rows) === self::BATCH_SIZE);
        }

        CLI::write('Limite: ' . $beforeSql);
        CLI::write('Mesures analysees: ' . $scanned);
        CLI::write(($execute ? 'Mesures supprimees: ' : 'Mesures supprimables: ') . $removable, $execute ? 'green' : 'yellow');

        if (!$execute) {
            CLI::write('Simulation uniquement. Relancez avec --execute pour appliquer.', 'yellow');
        }

        return EXIT_SUCCESS;
    }
}