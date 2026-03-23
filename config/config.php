<?php
declare(strict_types=1);

return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'auth_mvc',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'session_name' => 'AUTHSESSID',
        'session_secure' => false, // true si usas HTTPS
        'session_httponly' => true,
        'session_samesite' => 'Strict',
        'csrf_key' => 'changeme-32+chars-secret',
    ],

    'mail' => [
    'use_phpmailer' => true,
    'from_email'   => 'no-reply@tu-dominio.com',
    'from_name'    => 'Soporte',
    'smtp' => [
        'host' => 'sandbox.smtp.mailtrap.io',
        'port' => 2525, // o 2525, cualquiera de los soportados
        'username' => '5fc7cc88d2cbdf',
        'password' => 'f7dea61938299e',
        'encryption' => 'tls', // Mailtrap usa STARTTLS, así que TLS es correcto
    ],
],
    'app' => [
        'code_expiry_minutes' => 5,
        'max_code_attempts_per_hour' => 50,
        'max_password_attempts_15min' => 5,
    ],
];
