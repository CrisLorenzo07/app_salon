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

    public function enviarConfirmacion(): bool
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = '127.0.0.1';
        $mail->Port = 1025;
        $mail->SMTPAuth = false;
        $mail->SMTPAutoTLS = false;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('cuentas@appsalon.com', 'App Salón');
        $mail->addAddress($this->email, $this->name);
        $mail->isHTML(true);
        $mail->Subject = 'Confirma tu cuenta';

        $nombre = htmlspecialchars(
            $this->name,
            ENT_QUOTES,
            'UTF-8'
        );

        $url = 'http://localhost:8000/confirmar-cuenta?token='
            . rawurlencode($this->token);

        $contenido = '<html><body>';
        $contenido .= '<p><strong>Hola ' . $nombre . '</strong> Has creado tu cuenta en App Salón, solo debes confirmarla presionando el siguiente enlace</p>';
        $contenido .= '<p>Presiona aquí: <a href="' . $url . '">Confirmar Cuenta</a></p>';
        $contenido .= '</body></html>';
        $mail->Body = $contenido;
        $mail->AltBody = "Confirma tu cuenta: {$url}";

        return $mail->send();
    }

    public function enviarInstrucciones()
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = '127.0.0.1';
        $mail->Port = 1025;
        $mail->SMTPAuth = false;
        $mail->SMTPAutoTLS = false;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('cuentas@appsalon.com', 'App Salón');
        $mail->addAddress($this->email, $this->name);
        $mail->isHTML(true);
        $mail->Subject = 'Restablecer tu contraseña';

        $nombre = htmlspecialchars(
            $this->name,
            ENT_QUOTES,
            'UTF-8'
        );

        $url = 'http://localhost:8000/recuperar?token='
            . rawurlencode($this->token);

        $contenido = '<html><body>';
        $contenido .= '<p><strong>Hola ' . $nombre . '</strong> Has solicitado restablecer tu contraseña, presiona el siguiente enlace</p>';
        $contenido .= '<p>Presiona aquí: <a href="' . $url . '">Restablecer Contraseña</a></p>';
        $contenido .= '<p>Si no solicitaste restablecer tu contraseña, ignora este email</p>';
        $contenido .= '</body></html>';
        $mail->Body = $contenido;
        $mail->AltBody = "Restablecer Contraseña: {$url}";

        return $mail->send();
    }
}
