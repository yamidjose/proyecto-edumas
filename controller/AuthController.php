<?php
declare(strict_types=1);

require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/UserModel.php';

class AuthController {
    private array $config;
    private UserModel $users;
    private \PDO $pdo;

    public function __construct() {
        $this->config = require __DIR__ . '/../config/config.php';
        $db = new Database($this->config['db']);
        $this->pdo = $db->pdo();
        $this->users = new UserModel($this->pdo, $this->config['app']);
        $this->initSession();
    }

    private function initSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            $secure = $this->config['security']['session_secure'];
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => $this->config['security']['session_httponly'],
                'samesite' => $this->config['security']['session_samesite'],
            ]);
            session_name($this->config['security']['session_name']);
            session_start();
        }
    }

    private function csrfToken(): string {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }
    private function checkCsrf(string $token): bool {
        return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
    }

    public function showLogin(): void {
        $csrf = $this->csrfToken();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        require __DIR__ . '/../view/login.php';
    }

    public function postPassword(): void {
        if (!$this->checkCsrf($_POST['csrf'] ?? '')) {
            $this->flash('Solicitud inválida. Intenta de nuevo.', 'error');
             $this->redirectToLogin();
        }
        $email = trim((string)($_POST['email'] ?? ''));
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $username === '' || strlen($password) < 6) {
            $this->flash('Credenciales inválidas.', 'error');
             $this->redirectToLogin();
        }

        $user = $this->users->getByEmailAndUsername($email, $username);
        $userId = $user['id'] ?? null;

        $fails = $this->users->countPasswordFailsLast15Min($userId, $email, $ip);
        if ($fails >= $this->config['app']['max_password_attempts_15min']) {
            $this->flash('Demasiados intentos fallidos. Intenta más tarde.', 'error');
             $this->redirectToLogin();
        }

        $ok = false;
        if ($user) {
            $ok = $this->users->verifyPassword($user, $password);
        }

        $this->users->recordAttempt($userId, $email, $ip, 'password', $ok);

        if (!$ok) {
            $this->flash('Credenciales inválidas.', 'error');
             $this->redirectToLogin();
        }

        // Usuario confirmado, enviar código
        $code = (string)random_int(100000, 999999);
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        $codeHash = password_hash($code, $algo);
        $expires = (new DateTimeImmutable())->modify('+' . (int)$this->config['app']['code_expiry_minutes'] . ' minutes');
        $this->users->setVerificationCode((int)$user['id'], $codeHash, $expires);

        $sent = $this->sendVerificationEmail($user['correo'], $user['nombre_usuario'], $code, $expires);
        if ($sent) {
            $_SESSION['awaiting_code'] = [
                'user_id' => $user['id'],
                'email' => $email,
                'username' => $username,
            ];
            $this->flash('Usuario confirmado. Hemos enviado un código de verificación a su correo.', 'success');
        } else {
            $this->flash('Error al enviar el código. Intenta más tarde.', 'error');
        }
        $this->redirectToLogin();
    }

    public function postRequestCode(): void {
        if (!$this->checkCsrf($_POST['csrf'] ?? '')) {
            $this->flash('Solicitud inválida. Intenta de nuevo.', 'error');
             $this->redirectToLogin();
        }
        $email = trim((string)($_POST['email'] ?? ''));
        $username = trim((string)($_POST['username'] ?? ''));
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $username === '') {
            $this->flash('Si los datos son correctos, te enviaremos un código.', 'info');
             $this->redirectToLogin();
        }

        $user = $this->users->getByEmailAndUsername($email, $username);
        $userId = $user['id'] ?? null;

        $attemptsHour = $this->users->countCodeAttemptsInLastHour($userId, $email, $ip);
        if ($attemptsHour >= $this->config['app']['max_code_attempts_per_hour']) {
            $this->users->recordAttempt($userId, $email, $ip, 'request_code', false);
            $this->flash('Si los datos son correctos, te enviaremos un código.', 'info');
             $this->redirectToLogin();
        }

        if ($user) {
            $code = (string)random_int(100000, 999999);
            $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
            $codeHash = password_hash($code, $algo);
            $expires = (new DateTimeImmutable())->modify('+' . (int)$this->config['app']['code_expiry_minutes'] . ' minutes');
            $this->users->setVerificationCode((int)$user['id'], $codeHash, $expires);

            $sent = $this->sendVerificationEmail($user['correo'], $user['nombre_usuario'], $code, $expires);
            $this->users->recordAttempt((int)$user['id'], $email, $ip, 'request_code', $sent);
        } else {
            $this->users->recordAttempt(null, $email, $ip, 'request_code', false);
        }

        $this->flash('Si los datos son correctos, te enviaremos un código.', 'info');
         $this->redirectToLogin();
    }

    public function postVerifyCode(): void {
        if (!$this->checkCsrf($_POST['csrf'] ?? '')) {
            $this->flash('Solicitud inválida. Intenta de nuevo.', 'error');
             $this->redirectToLogin();
        }
        if (!isset($_SESSION['awaiting_code'])) {
            $this->flash('Sesión expirada. Intenta de nuevo.', 'error');
             $this->redirectToLogin();
        }
        $awaiting = $_SESSION['awaiting_code'];
        $email = $awaiting['email'];
        $username = $awaiting['username'];
        $userId = $awaiting['user_id'];
        $inputCode = trim((string)($_POST['code'] ?? ''));
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (!preg_match('/^\d{6}$/', $inputCode)) {
            $this->flash('Código inválido.', 'error');
             $this->redirectToLogin();
        }

        $user = $this->users->getByEmailAndUsername($email, $username);
        if (!$user) {
            unset($_SESSION['awaiting_code']);
            $this->flash('Usuario no encontrado.', 'error');
             $this->redirectToLogin();
        }

        $attemptsHour = $this->users->countCodeAttemptsInLastHour($userId, $email, $ip);
        if ($attemptsHour >= $this->config['app']['max_code_attempts_per_hour']) {
            $this->users->recordAttempt($userId, $email, $ip, 'code', false);
            $this->flash('Demasiados intentos. Intenta más tarde.', 'error');
             $this->redirectToLogin();
        }

        $ok = false;
        if ($user['codigo_verificacion'] && $user['codigo_verificacion_expires_at']) {
            $now = new DateTimeImmutable();
            $expiresAt = new DateTimeImmutable($user['codigo_verificacion_expires_at']);
            if ($now <= $expiresAt) {
                $ok = password_verify($inputCode, $user['codigo_verificacion']);
            }
        }

        $this->users->recordAttempt($userId, $email, $ip, 'code', $ok);

        if (!$ok) {
            if ($userId) $this->users->incrementCodeCounter($userId);
            $this->flash('Código incorrecto.', 'error');
             $this->redirectToLogin();
        }

        $this->users->clearVerificationCode($userId);
        unset($_SESSION['awaiting_code']);

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'correo' => $user['correo'],
            'nombre_usuario' => $user['nombre_usuario'],
            'nombre' => $user['nombre'] ?? $user['nombre_usuario'],
            'rol' => $user['rol'] ?? 'usuario',
            'area' => $user['area'] ?? 'COM',
        ];

        if (($user['rol'] ?? 'usuario') === 'admin') {
            header('Location: index.php?action=admin_dashboard');
        } else {
            header('Location: index.php?action=dashboard');
        }
        exit;
    }

    public function postResendCode(): void {
        if (!isset($_SESSION['awaiting_code'])) {
            $this->flash('Sesión expirada. Intenta de nuevo.', 'error');
             $this->redirectToLogin();
        }
        $awaiting = $_SESSION['awaiting_code'];
        $userId = $awaiting['user_id'];
        $email = $awaiting['email'];
        $username = $awaiting['username'];
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $user = $this->users->getByEmailAndUsername($email, $username);
        if (!$user) {
            unset($_SESSION['awaiting_code']);
            $this->flash('Usuario no encontrado.', 'error');
             $this->redirectToLogin();
        }

        $attemptsHour = $this->users->countCodeAttemptsInLastHour($userId, $email, $ip);
        if ($attemptsHour >= $this->config['app']['max_code_attempts_per_hour']) {
            $this->flash('Demasiados intentos. Intenta más tarde.', 'error');
             $this->redirectToLogin();
        }

        $code = (string)random_int(100000, 999999);
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        $codeHash = password_hash($code, $algo);
        $expires = (new DateTimeImmutable())->modify('+' . (int)$this->config['app']['code_expiry_minutes'] . ' minutes');
        $this->users->setVerificationCode($userId, $codeHash, $expires);

        $sent = $this->sendVerificationEmail($user['correo'], $user['nombre_usuario'], $code, $expires);
        $this->users->recordAttempt($userId, $email, $ip, 'resend_code', $sent);

        if ($sent) {
            $this->flash('Nuevo código enviado.', 'success');
        } else {
            $this->flash('Error al enviar el código.', 'error');
        }
        $this->redirectToLogin();
    }

    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        header('Location: /index.php');
        exit;
    }

    public function cancelCode(): void {
        if (!$this->checkCsrf($_POST['csrf'] ?? '')) {
            $this->flash('Solicitud inválida. Intenta de nuevo.', 'error');
             $this->redirectToLogin();
        }
        unset($_SESSION['awaiting_code']);
        $this->flash('Se canceló la verificación.', 'info');
        $this->redirectToLogin();
    }

    private function redirectToLogin(): void {
        header('Location: index.php');
        exit;
    }

    private function flash(string $msg, string $type = 'info'): void {
        $_SESSION['flash'] = ['type' => $type, 'message' => $msg];
    }

    private function sendVerificationEmail(string $toEmail, string $username, string $code, \DateTimeInterface $expiresAt): bool {
        $subject = 'Tu código temporal';
        $fromEmail = $this->config['mail']['from_email'];
        $fromName  = $this->config['mail']['from_name'];

        ob_start();
        $displayExpiry = $expiresAt->format('d/m/Y H:i');
        $emailUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        $emailCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
        $brandColor = '#235347';
        include __DIR__ . '/../view/emails/codigo_verificacion.php';
        $html = ob_get_clean();

        if ($this->config['mail']['use_phpmailer']) {
            try {
                // Autoload de Composer
                if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                    require_once __DIR__ . '/../vendor/autoload.php';
                }
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = $this->config['mail']['smtp']['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $this->config['mail']['smtp']['username'];
                $mail->Password = $this->config['mail']['smtp']['password'];
                $mail->SMTPSecure = $this->config['mail']['smtp']['encryption'];
                $mail->Port = $this->config['mail']['smtp']['port'];

                $mail->setFrom($fromEmail, $fromName);
                $mail->addAddress($toEmail);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $html;
                $mail->AltBody = "Hola $username,
Tu código es: $code (expira: $displayExpiry)
";

                $mail->send();
                return true;
            } catch (\Throwable $e) {
                // En producción, registra el error en logs
                return false;
            }
        } else {
            $headers  = "MIME-Version: 1.0
";
            $headers .= "Content-type: text/html; charset=utf-8
";
            $headers .= "From: $fromName <$fromEmail>
";
            return @mail($toEmail, $subject, $html, $headers);
        }
    }
}
