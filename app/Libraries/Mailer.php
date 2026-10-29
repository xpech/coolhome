<?php

namespace App\Libraries;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    public function send(string $email, string $subject, string $body): bool
    {
        $config = $this->loadConfig();
        if ($config === []) {
            log_message('error', 'Mail configuration missing.');
            return false;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Host = (string) ($config['host'] ?? 'localhost');
            $mail->Port = (int) ($config['port'] ?? 587);
            $mail->SMTPAuth = true;
            $mail->Username = (string) ($config['username'] ?? '');
            $mail->Password = (string) ($config['password'] ?? '');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->setFrom((string) ($config['from'] ?? $mail->Username), (string) ($config['from_name'] ?? 'CoolHome'));
            $mail->addAddress($email);
            $mail->Subject = $subject;
            $mail->Body = $body;

            return $mail->send();
        } catch (Exception $e) {
            log_message('error', 'Mail delivery error: {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    private function loadConfig(): array
    {
        $path = ROOTPATH . 'application/config/mail.yml';
        $result = [];
        if (is_file($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                if (str_starts_with(trim($line), '#')) {
                    continue;
                }
                $parts = explode(':', $line, 2);
                if (count($parts) === 2 && trim($parts[0]) !== '') {
                    $result[trim($parts[0])] = trim(trim($parts[1]), " \t\n\r\0\x0B\"'");
                }
            }
        }

        foreach (['host', 'port', 'username', 'password', 'from', 'from_name'] as $key) {
            $value = env('mail.' . $key);
            if ($value !== null && $value !== '') {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
