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

    public function sendConfirmation(): bool
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

        $name = htmlspecialchars(
            $this->name,
            ENT_QUOTES,
            'UTF-8'
        );

        $url = 'http://localhost:8000/confirmar-cuenta?token='
            . rawurlencode($this->token);

        $content = '<html><body>';
        $content .= '<p><strong>Hola ' . $name . '</strong> Has creado tu cuenta en App Salón, solo debes confirmarla presionando el siguiente enlace</p>';
        $content .= '<p>Presiona aquí: <a href="' . $url . '">Confirmar Cuenta</a></p>';
        $content .= '</body></html>';
        $mail->Body = $content;
        $mail->AltBody = "Confirma tu cuenta: {$url}";

        return $mail->send();
    }

    public function sendInstructions()
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

        $name = htmlspecialchars(
            $this->name,
            ENT_QUOTES,
            'UTF-8'
        );

        $url = 'http://localhost:8000/recuperar?token='
            . rawurlencode($this->token);

        $content = '<html><body>';
        $content .= '<p><strong>Hola ' . $name . '</strong> Has solicitado restablecer tu contraseña, presiona el siguiente enlace</p>';
        $content .= '<p>Presiona aquí: <a href="' . $url . '">Restablecer Contraseña</a></p>';
        $content .= '<p>Si no solicitaste restablecer tu contraseña, ignora este email</p>';
        $content .= '</body></html>';
        $mail->Body = $content;
        $mail->AltBody = "Restablecer Contraseña: {$url}";

        return $mail->send();
    }
}
