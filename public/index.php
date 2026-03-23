<?php
declare(strict_types=1);

require_once __DIR__ . '/../controller/AuthController.php';
require_once __DIR__ . '/../controller/DocumentoController.php';
require_once __DIR__ . '/../model/Database.php';

$auth = new AuthController();
$action = $_GET['action'] ?? 'login';

// Si hay logout
if ($action === 'logout') {
    $auth->logout();
    exit;
}

// Si no hay sesión, mostrar login
if (!isset($_SESSION['user'])) {
    switch ($action) {
        case 'password':
            if ($_SERVER['REQUEST_METHOD'] === 'POST')
                $auth->postPassword();
            else
                $auth->showLogin();
            break;
        case 'request_code':
            if ($_SERVER['REQUEST_METHOD'] === 'POST')
                $auth->postRequestCode();
            else
                $auth->showLogin();
            break;
        case 'verify_code':
            if ($_SERVER['REQUEST_METHOD'] === 'POST')
                $auth->postVerifyCode();
            else
                $auth->showLogin();
            break;
        case 'resend_code':
            if ($_SERVER['REQUEST_METHOD'] === 'POST')
                $auth->postResendCode();
            else
                $auth->showLogin();
            break;
        case 'cancel_code':
            $auth->cancelCode();
            break;
        default:
            $auth->showLogin();
            break;
    }
    exit;
}

// Usuario autenticado - inicializar PDO para DocumentoController
$config = require __DIR__ . '/../config/config.php';
$db = new Database($config['db']);
$pdo = $db->pdo();
$documentoController = new DocumentoController($pdo);

// Manejar acciones autenticadas
switch ($action) {
    case 'dashboard':
    case 'admin_dashboard':
        $documentoController->dashboard();
        break;

    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentoController->crear();
        } else {
            $documentoController->dashboard();
        }
        break;

    case 'crear_usuario':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentoController->crearUsuario();
        } else {
            $documentoController->dashboard();
        }
        break;

    case 'subir':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentoController->subir();
        } else {
            $documentoController->dashboard();
        }
        break;

    case 'ver_pdf':
        $documentoController->verPdf();
        break;

    case 'eliminar':
        $documentoController->eliminar();
        break;

    default:
        $documentoController->dashboard();
        break;
}
