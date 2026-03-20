<?php
declare(strict_types=1);
require_once __DIR__ . '/../model/Database.php';

$config = require __DIR__ . '/../config/config.php';
$db = new Database($config['db']);
$pdo = $db->pdo();

function prompt(string $label, bool $hidden = false): string {
    if ($hidden && stripos(PHP_OS, 'WIN') === false) {
        // En UNIX, desactiva echo para password
        echo $label;
        system('stty -echo');
        $val = trim(fgets(STDIN));
        system('stty echo');
        echo "
";
        return $val;
    }
    echo $label;
    return trim(fgets(STDIN));
}

$email = $argv[1] ?? prompt('Correo: ');
$username = $argv[2] ?? prompt('Nombre de usuario: ');
$plain = $argv[3] ?? prompt('Contraseña: ', true);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Correo inválido
");
    exit(1);
}
if (strlen($plain) < 6) {
    fwrite(STDERR, "La contraseña debe tener al menos 6 caracteres
");
    exit(1);
}

$algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
$hash = password_hash($plain, $algo);

try {
    $stmt = $pdo->prepare('INSERT INTO usuarios (correo, nombre_usuario, password) VALUES (?, ?, ?)');
    $stmt->execute([$email, $username, $hash]);
    echo "Usuario creado con ID: " . $pdo->lastInsertId() . "
";
} catch (Throwable $e) {
    fwrite(STDERR, "Error al crear usuario: " . $e->getMessage() . "
");
    exit(1);
}
