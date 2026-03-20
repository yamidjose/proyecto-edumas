<?php
declare(strict_types=1);
$config = require __DIR__ . '/../config/config.php';

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    fwrite(STDERR, "No se encontró vendor/autoload.php. Ejecuta 'composer install' primero.
");
    exit(1);
}

$to = $argv[1] ?? null;
if (!$to) {
    fwrite(STDERR, "Uso: php scripts/test_mail.php destinatario@correo.com
");
    exit(1);
}   

$mail = new PHPMailer\PHPMailer\PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $config['mail']['smtp']['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['mail']['smtp']['username'];
    $mail->Password = $config['mail']['smtp']['password'];
    $mail->SMTPSecure = $config['mail']['smtp']['encryption'];
    $mail->Port = $config['mail']['smtp']['port'];

    $mail->setFrom($config['mail']['from_email'], $config['mail']['from_name']);
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = 'Prueba SMTP - Auth MVC';
    $mail->Body = '<p>Este es un correo de <strong>prueba</strong> desde Auth MVC.</p>';
    $mail->AltBody = 'Este es un correo de prueba desde Auth MVC.';

    $mail->send();
    echo "Correo enviado a $to
";
} catch (Throwable $e) {
    fwrite(STDERR, "Error al enviar correo: " . $e->getMessage() . "
");
    exit(1);
}
