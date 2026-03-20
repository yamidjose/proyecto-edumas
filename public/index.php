<?php
declare(strict_types=1);
require_once __DIR__ . '/../controller/AuthController.php';

$auth = new AuthController();
$action = $_GET['action'] ?? 'login';

if (isset($_SESSION['user'])) {
    if ($action === 'logout') {
        $auth->logout();
    }
    $user = $_SESSION['user'];
    ?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body class="min-h-screen bg-[#f5f5dc] flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-lg p-8 max-w-md w-full text-center">
        <h1 class="text-2xl font-bold text-[#235347]">Hola, <?= htmlspecialchars($user['nombre_usuario']) ?></h1>
        <p class="mt-2 text-gray-600">Has logrado iniciar sesion correctamente.</p>
        <a href="index.php?action=logout"
            class="inline-block mt-6 px-4 py-2 rounded-xl bg-[#235347] text-white hover:bg-[#1d463d] transition">Cerrar
            sesion</a>
    </div>
</body>

</html>
<?php
    exit;
}

switch ($action) {
    case 'login':
        $auth->showLogin();
        break;
    case 'password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $auth->postPassword();
        else $auth->showLogin();
        break;
    case 'request_code':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $auth->postRequestCode();
        else $auth->showLogin();
        break;
    case 'verify_code':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $auth->postVerifyCode();
        else $auth->showLogin();
        break;
    case 'resend_code':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $auth->postResendCode();
        else $auth->showLogin();
        break;
    case 'cancel_code':
        $auth->cancelCode();
        break;
    default:
        $auth->showLogin();
        break;
}