<?php

namespace App\Libraries;

class HomeAccess
{
    public const OWNER = 'owner';
    public const RESIDENT = 'resident';

    public static function validRole(string $role): bool
    {
        return in_array($role, [self::OWNER, self::RESIDENT], true);
    }

    public static function label(string $role): string
    {
        return $role === self::OWNER ? 'Owner' : 'Habitant';
    }

    public static function condition(int $userId, string $alias = 'h', bool $ownersOnly = false): string
    {
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/D', $alias) !== 1) {
            throw new \InvalidArgumentException('Invalid home table alias.');
        }

        $roles = $ownersOnly ? "'owner'" : "'owner','resident'";

        return "({$alias}.owner = {$userId} OR EXISTS (SELECT 1 FROM t_home_member hm WHERE hm.home = {$alias}.id AND hm.user = {$userId} AND hm.role IN ({$roles})))";
    }

    public static function role(int $homeId, int $userId): ?string
    {
        $db = db_connect();
        $home = $db->table('t_home')->select('owner')->where('id', $homeId)->get()->getRowArray();
        if (! $home) {
            return null;
        }
        if ((int) $home['owner'] === $userId) {
            return self::OWNER;
        }

        $member = $db->table('t_home_member')->where(['home' => $homeId, 'user' => $userId])->get()->getRowArray();
        $role = (string) ($member['role'] ?? '');

        return self::validRole($role) ? $role : null;
    }

    public static function deviceCondition(int $userId): string
    {
        return "((d.owner = {$userId} AND NOT EXISTS (SELECT 1 FROM t_room dr WHERE dr.id = d.room))"
            . ' OR EXISTS (SELECT 1 FROM t_room dr JOIN t_home h ON h.id = dr.home WHERE dr.id = d.room AND '
            . self::condition($userId, 'h', true) . '))';
    }
}
