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
        echo "\n";
        return $val;
    }
    echo $label;
    return trim(fgets(STDIN));
}

echo "=== Crear Usuario ===\n\n";

$email = $argv[1] ?? prompt('Correo: ');
$username = $argv[2] ?? prompt('Nombre de usuario: ');
$nombre = $argv[3] ?? prompt('Nombre completo: ');
$plain = $argv[4] ?? prompt('Contraseña: ', true);

// Rol
$roles = ['usuario', 'admin'];
echo "\nRoles disponibles:\n";
foreach ($roles as $i => $role) {
    echo "  [$i] $role\n";
}
$rolIndex = (int)prompt('Selecciona rol (0-1): ');
$rol = $roles[$rolIndex] ?? 'usuario';

// Área
$areas = ['COM', 'CU', 'DU', 'GA', 'JUR'];
echo "\nÁreas disponibles:\n";
foreach ($areas as $i => $area) {
    echo "  [$i] $area\n";
}
$areaIndex = (int)prompt('Selecciona área (0-4): ');
$area = $areas[$areaIndex] ?? 'COM';

// Validaciones
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "❌ Correo inválido\n");
    exit(1);
}
if (strlen($username) < 3) {
    fwrite(STDERR, "❌ El nombre de usuario debe tener al menos 3 caracteres\n");
    exit(1);
}
if (strlen($nombre) < 3) {
    fwrite(STDERR, "❌ El nombre completo debe tener al menos 3 caracteres\n");
    exit(1);
}
if (strlen($plain) < 6) {
    fwrite(STDERR, "❌ La contraseña debe tener al menos 6 caracteres\n");
    exit(1);
}

$algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
$hash = password_hash($plain, $algo);

try {
    $stmt = $pdo->prepare('INSERT INTO usuarios (correo, nombre_usuario, nombre, password, rol, area) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$email, $username, $nombre, $hash, $rol, $area]);
    
    echo "\n✅ Usuario creado exitosamente\n";
    echo "   ID: " . $pdo->lastInsertId() . "\n";
    echo "   Email: $email\n";
    echo "   Usuario: $username\n";
    echo "   Nombre: $nombre\n";
    echo "   Rol: $rol\n";
    echo "   Área: $area\n";
} catch (Throwable $e) {
    fwrite(STDERR, "❌ Error al crear usuario: " . $e->getMessage() . "\n");
    exit(1);
}
