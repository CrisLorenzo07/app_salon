<?php
namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email
{

    public string $name;
    public string $email;
    public string $token;

    public function __construct($name, $email, $token)
    {
        $this->name = $name;
        $this->email = $email;
        $this->token = $token;
    }

    private function createMailer(): PHPMailer
    {
        $url = $_ENV['APP_URL'] ?? '';
        $parts = is_string($url) ? parse_url($url) : false;
        if (!$parts || empty($parts['host']) || !in_array($parts['scheme'] ?? '', ['http', 'https'], true)
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new \InvalidArgumentException('APP_URL debe ser una URL base http o https válida.');
        }
        if (($_ENV['APP_ENV'] ?? 'production') === 'production' && $parts['scheme'] !== 'https') {
            throw new \InvalidArgumentException('APP_URL debe usar HTTPS en producción.');
        }
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Timeout = 5;
        $mail->getSMTPInstance()->Timelimit = 5;
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->Port = (int) $_ENV['EMAIL_PORT'];
        $mail->SMTPAuth = filter_var($_ENV['EMAIL_AUTH'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
        $mail->Username = $_ENV['EMAIL_USER'] ?? '';
        $mail->Password = $_ENV['EMAIL_PASS'] ?? '';
        $mail->SMTPSecure = $_ENV['EMAIL_ENCRYPTION'] ?? '';
        if (!in_array($mail->SMTPSecure, ['', 'tls', 'ssl'], true)) {
            throw new \InvalidArgumentException('EMAIL_ENCRYPTION debe ser tls, ssl o vacío.');
        }
        if ($mail->SMTPAuth && $mail->SMTPSecure === '' && ($_ENV['APP_ENV'] ?? 'production') !== 'test') {
            throw new \InvalidArgumentException('El correo con autenticación requiere EMAIL_ENCRYPTION=tls o ssl.');
        }
        $mail->SMTPAutoTLS = $mail->SMTPSecure !== '';
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($_ENV['EMAIL_FROM'] ?? 'cuentas@appsalon.com', $_ENV['EMAIL_FROM_NAME'] ?? 'App Salón');
        $mail->addAddress($this->email, $this->name);
        $mail->isHTML(true);

        return $mail;
    }

    public function sendConfirmation(): bool
    {
        $mail = $this->createMailer();
        $mail->Subject = 'Confirma tu cuenta';

        $name = htmlspecialchars(
            $this->name,
            ENT_QUOTES,
            'UTF-8'
        );

        $url = $_ENV['APP_URL'] . '/confirmar-cuenta?token='
            . rawurlencode($this->token);
        $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        $content = '<html><body>';
        $content .= '<p><strong>Hola ' . $name . '</strong> Has creado tu cuenta en App Salón, solo debes confirmarla presionando el siguiente enlace</p>';
        $content .= '<p>Presiona aquí: <a href="' . $safeUrl . '">Confirmar Cuenta</a></p>';
        $content .= '</body></html>';
        $mail->Body = $content;
        $mail->AltBody = "Confirma tu cuenta: {$url}";

        return $mail->send();
    }

    public function sendInstallationInvitation(): bool
    {
        $mail = $this->createMailer();
        $mail->Subject = 'Configura el administrador de tu salón';
        $url = rtrim($_ENV['APP_URL'], '/') . '/configuracion-inicial?token=' . rawurlencode($this->token);
        $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $mail->Body = '<p>Abre este enlace privado para crear la cuenta del administrador de tu salón:</p>'
            . '<p><a href="' . $safeUrl . '">Crear administrador</a></p>'
            . '<p>El enlace caduca en 24 horas. Si no solicitaste configurar esta instalación, puedes ignorarlo.</p>';
        $mail->AltBody = "Crear administrador (válido durante 24 horas): {$url}";
        return $mail->send();
    }

    public function sendInstructions()
    {
        $mail = $this->createMailer();
        $mail->Subject = 'Restablecer tu contraseña';

        $name = htmlspecialchars(
            $this->name,
            ENT_QUOTES,
            'UTF-8'
        );

        $url = $_ENV['APP_URL'] . '/recuperar?token='
            . rawurlencode($this->token);
        $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        $content = '<html><body>';
        $content .= '<p><strong>Hola ' . $name . '</strong> Has solicitado restablecer tu contraseña, presiona el siguiente enlace</p>';
        $content .= '<p>Presiona aquí: <a href="' . $safeUrl . '">Restablecer Contraseña</a></p>';
        $content .= '<p>Si no solicitaste restablecer tu contraseña, ignora este email</p>';
        $content .= '</body></html>';
        $mail->Body = $content;
        $mail->AltBody = "Restablecer Contraseña: {$url}";

        return $mail->send();
    }
}
