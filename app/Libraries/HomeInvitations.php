<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use DomainException;
use RuntimeException;
use Throwable;

class HomeInvitations
{
    private BaseConnection $db;
    private Mailer $mailer;

    public function __construct(?BaseConnection $db = null, ?Mailer $mailer = null)
    {
        $this->db = $db ?? db_connect();
        $this->mailer = $mailer ?? new Mailer();
    }

    /** @return array{url: string, emailSent: bool} */
    public function send(int $homeId, int $actorId, string $email, string $role): array
    {
        $email = strtolower(trim($email));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254 || ! HomeAccess::validRole($role)) {
            throw new DomainException('Email ou role invalide.');
        }

        $home = $this->db->query(
            'SELECT h.* FROM t_home h WHERE h.id = ? AND ' . HomeAccess::condition($actorId, 'h', true),
            [$homeId]
        )->getRowArray();
        if (! $home) {
            throw new DomainException('Action reservee aux Owners.');
        }

        $user = $this->db->table('t_user')->where('LOWER(email)', $email)->get()->getRowArray();
        if ($user && ((int) $home['owner'] === (int) $user['id']
            || $this->db->table('t_home_member')->where(['home' => $homeId, 'user' => $user['id']])->countAllResults() > 0)) {
            throw new DomainException('Cette personne a deja acces a la maison.');
        }

        $token = bin2hex(random_bytes(32));
        $url = site_url('invitation/' . $token);
        $this->db->transBegin();
        try {
            $this->db->table('t_home_invitation')->where(['home' => $homeId, 'email' => $email])->delete();
            $this->db->table('t_home_invitation')->insert([
                'home' => $homeId,
                'email' => $email,
                'role' => $role,
                'token_hash' => hash('sha256', $token),
                'expires_at' => date('Y-m-d H:i:s', time() + 7 * DAY),
            ]);
            if (! $this->db->transStatus()) {
                throw new RuntimeException('Invitation persistence failed.');
            }
            $emailSent = $this->mailer->send(
                $email,
                '[CoolHome] Invitation a rejoindre une maison',
                sprintf(
                    "Bonjour,\nVous etes invite a rejoindre la maison \"%s\" en tant que %s.\n%s\nCe lien est valable 7 jours. Vous pourrez creer votre compte si necessaire.",
                    $home['title'],
                    HomeAccess::label($role),
                    $url
                )
            );
            if (! $this->db->transCommit()) {
                throw new RuntimeException('Invitation commit failed.');
            }
            return ['url' => $url, 'emailSent' => $emailSent];
        } catch (Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function find(string $token): ?array
    {
        if (preg_match('/^[a-f0-9]{64}$/D', $token) !== 1) {
            return null;
        }

        return $this->db->table('t_home_invitation i')
            ->select('i.*, h.title AS home_title')
            ->join('t_home h', 'h.id = i.home')
            ->where('i.token_hash', hash('sha256', $token))
            ->where('i.expires_at >', date('Y-m-d H:i:s'))
            ->get()->getRowArray();
    }

    public function accept(string $token, ?int $userId, string $password = ''): array
    {
        $this->db->transBegin();
        try {
            // Serialize acceptance so a link cannot create two accounts or memberships.
            $invitation = $this->db->query(
                'SELECT i.* FROM t_home_invitation i JOIN t_home h ON h.id = i.home WHERE i.token_hash = ? AND i.expires_at > ? FOR UPDATE',
                [hash('sha256', $token), date('Y-m-d H:i:s')]
            )->getRowArray();
            if (! $invitation || ! HomeAccess::validRole((string) $invitation['role'])) {
                throw new DomainException('Invitation invalide, expiree ou deja utilisee.');
            }

            if ($userId !== null) {
                $user = $this->db->table('t_user')->where('id', $userId)->get()->getRowArray();
                if (! $user || strcasecmp((string) $user['email'], $invitation['email']) !== 0 || ! $user['actif']) {
                    throw new DomainException('Connectez-vous avec le compte correspondant a l\'email invite.');
                }
            } else {
                if ($this->db->table('t_user')->where('LOWER(email)', $invitation['email'])->countAllResults() > 0) {
                    throw new DomainException('Ce compte existe deja. Connectez-vous pour accepter l\'invitation.');
                }
                if (strlen($password) < 8) {
                    throw new DomainException('Le mot de passe doit contenir au moins 8 caracteres.');
                }
                $user = [
                    'email' => $invitation['email'],
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'nom' => '',
                    'prenom' => '',
                    'actif' => 1,
                    'created' => date('Y-m-d H:i:s'),
                ];
                $this->db->table('t_user')->insert($user);
                $user['id'] = $this->db->insertID();
            }

            $membership = ['home' => $invitation['home'], 'user' => $user['id']];
            if ($this->db->table('t_home_member')->where($membership)->countAllResults() === 0) {
                $this->db->table('t_home_member')->insert($membership + ['role' => $invitation['role']]);
            }
            $this->db->table('t_home_invitation')->where('id', $invitation['id'])->delete();
            if (! $this->db->transStatus() || ! $this->db->transCommit()) {
                throw new RuntimeException('Invitation acceptance failed.');
            }

            unset($user['password']);
            return ['home' => (int) $invitation['home'], 'user' => $user];
        } catch (Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
