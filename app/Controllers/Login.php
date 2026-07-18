<?php

namespace App\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use App\Class\CDatabase;

class Login extends BaseController
{
    private string $salt = 'popodqdsdsqdqdl,sqdl,';

    public function index()
    {
        log_message('debug', 'Login index '.$this->request->getMethod(true));

        if ($this->request->getMethod(true) === 'POST') {
            $secret = (string) $this->request->getPost('secret') ;
            $password = (string) $this->request->getPost('p_' . $secret);
            $login = trim((string) $this->request->getPost('l_' . $secret));

            if ($this->tryAuthenticate($login, $password)) {
                $backto = (string) ($this->request->getPost('backto') ?? $this->request->getGet('backto') ?? '');
                return redirect()->to($backto !== '' ? $backto : '/');
            }

            return view('login/index', [
                'secret' => $secret  , 
                'error' => 'Identifiants invalides.',
                'backto' => (string) ($this->request->getGet('backto') ?? ''),
            ]);
        }

        return view('login/index', [
            'secret' => 'F' . dechex(time()),
            'error' => null,
            'backto' => (string) ($this->request->getGet('backto') ?? ''),
        ]);
    }

    public function recover()
    {
        $message = null;
        $error = null;

        if ($this->request->getMethod(true) === 'POST' && $this->request->getPost('action') === 'recovermail') {
            $email = trim((string) $this->request->getPost('email'));

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email non valide.';
            } else {
                $db = CDatabase::DB();
                $rows = $db->keyedrows(
                    'SELECT id,email FROM t_user WHERE email = %s LIMIT 1',
                    $db->STRING($email)
                );

                if ($rows) {
                    $this->sendPasswordRecoverMail($email);
                }

                // Message neutre pour ne pas divulguer l'existence d'un compte.
                $message = 'Si le compte existe, un email de reinitialisation a ete envoye.';
            }
        }

        return view('login/recover', [
            'message' => $message,
            'error' => $error,
            'email' => (string) $this->request->getPost('email'),
        ]);
    }

    public function create()
    {
        $message = null;
        $error = null;

        if ($this->request->getMethod(true) === 'POST' && $this->request->getPost('action') === 'SendCreateMail') {
            $email = trim((string) $this->request->getPost('email'));
            $secureBot = trim((string) $this->request->getPost('securebot'));

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email non valide.';
            } elseif (substr($email, 0, 3) !== $secureBot) {
                $error = 'Verification anti-robot invalide.';
            } else {
                $sent = $this->sendAccountCreationMail($email);
                if ($sent) {
                    $message = 'Vous allez recevoir un email de creation de compte.';
                } else {
                    $error = 'Impossible d\'envoyer l\'email pour le moment.';
                }
            }
        }

        return view('login/create', [
            'message' => $message,
            'error' => $error,
            'email' => (string) $this->request->getPost('email'),
        ]);
    }

    public function setPassword(string $email, string $key)
    {
        $decodedEmail = urldecode($email);

        if ($this->emailKey($decodedEmail) !== $key) {
            return redirect()->to('/login');
        }

        $error = null;

        if ($this->request->getMethod(true) === 'POST' && $this->request->getPost('action') === 'SendUpdatePwd') {
            $pwd1 = (string) $this->request->getPost('pwd1');
            $pwd2 = (string) $this->request->getPost('pwd2');

            if ($pwd1 === '' || $pwd1 !== $pwd2) {
                $error = 'Le mot de passe est vide ou ne correspond pas.';
            } elseif (! $this->upsertUserPassword($decodedEmail, $pwd1)) {
                $error = 'Erreur pendant la mise a jour du mot de passe.';
            } else {
                $this->tryAuthenticate($decodedEmail, $pwd1);
                return redirect()->to('/');
            }
        }

        return view('login/setpwd', [
            'email' => $decodedEmail,
            'key' => $key,
            'error' => $error,
        ]);
    }

    public function out()
    {
        $this->session->remove('user');
        $this->session->destroy();

        return redirect()->to('/login');
    }

    private function tryAuthenticate(string $login, string $password): bool
    {
        log_message('debug', 'Login tryAuthenticate '.$login.' / '.$password);
        if ($login === '' || $password === '') {
            return false;
        }

        try {
            $db = \App\Class\CDatabase::DB();
            $rows = $db->keyedrows(
                'SELECT id,email,nom,prenom,password,actif FROM t_user WHERE email = %s LIMIT 1',
                $db->STRING($login)
            );
            $user = $rows ? $rows[0] : null;
        } catch (\Throwable $e) {
            log_message('error', 'Login DB error: {message}', ['message' => $e->getMessage()]);
            return false;
        }

        if (! $user) {
            return false;
        }

        if (! password_verify($password, (string) $user['password'])) {
            return false;
        }

        unset($user['password']);
        $this->session->set('user', $user);

        return true;
    }

    private function upsertUserPassword(string $email, string $password): bool
    {
        $db = \App\Class\CDatabase::DB();
        $existingId = (int) $db->oneValue(
            'SELECT id FROM t_user WHERE email = %s LIMIT 1',
            $db->STRING($email)
        );

        if ($existingId > 0) {
            return (bool) $db->query(
                'UPDATE t_user SET password = %s WHERE id = %d',
                $db->PASSWORD($password),
                $existingId
            );
        }

        return (bool) $db->query(
            'INSERT INTO t_user (email,password,nom,prenom,actif,created) VALUES (%s,%s,%s,%s,%d,NOW())',
            $db->STRING($email),
            $db->PASSWORD($password),
            $db->STRING(''),
            $db->STRING(''),
            1
        );
    }

    private function emailKey(string $email): string
    {
        return md5($email . $this->salt);
    }

    private function sendAccountCreationMail(string $email): bool
    {
        $config = $this->loadLegacyMailConfig();
        if ($config === []) {
            return false;
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->Host = (string) ($config['host'] ?? 'localhost');
        $mail->Port = (int) ($config['port'] ?? 587);
        $mail->SMTPAuth = true;
        $mail->Username = (string) ($config['username'] ?? '');
        $mail->Password = (string) ($config['password'] ?? '');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $from = (string) ($config['from'] ?? $mail->Username);
        $fromName = (string) ($config['from_name'] ?? 'CoolHome');

        $mail->setFrom($from, $fromName);
        $mail->addAddress($email);
        $mail->Subject = '[CoolHome] Lien de creation de compte';
        $mail->Body = sprintf(
            "Bonjour,\nVeuillez cliquer sur le lien suivant pour creer votre compte :\n%s\nL'equipe CoolHome",
            site_url('login/setPassword/' . rawurlencode($email) . '/' . $this->emailKey($email)),
        );

        try {
            return $mail->send();
        } catch (\Throwable $e) {
            log_message('error', 'Login create mail error: {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    private function sendPasswordRecoverMail(string $email): bool
    {
        $config = $this->loadLegacyMailConfig();
        if ($config === []) {
            return false;
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->Host = (string) ($config['host'] ?? 'localhost');
        $mail->Port = (int) ($config['port'] ?? 587);
        $mail->SMTPAuth = true;
        $mail->Username = (string) ($config['username'] ?? '');
        $mail->Password = (string) ($config['password'] ?? '');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $from = (string) ($config['from'] ?? $mail->Username);
        $fromName = (string) ($config['from_name'] ?? 'CoolHome');

        $mail->setFrom($from, $fromName);
        $mail->addAddress($email);
        $mail->Subject = '[CoolHome] Reinitialisation du mot de passe';
        $mail->Body = sprintf(
            "Bonjour,\nVeuillez cliquer sur le lien suivant pour definir un nouveau mot de passe :\n%s\nL'equipe CoolHome",
            site_url('login/setPassword/' . rawurlencode($email) . '/' . $this->emailKey($email)),
        );

        try {
            return $mail->send();
        } catch (\Throwable $e) {
            log_message('error', 'Login recover mail error: {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    private function loadLegacyMailConfig(): array
    {
        $path = ROOTPATH . 'application/config/mail.yml';
        if (! is_file($path)) {
            return [];
        }

        $result = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }
            $parts = explode(':', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            if ($key === '') {
                continue;
            }
            $result[$key] = trim($value, " \t\n\r\0\x0B\"'");
        }

        return $result;
    }
}
