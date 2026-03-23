<?php
declare(strict_types=1);

require_once __DIR__ . '/../model/DocumentoModel.php';

class DocumentoController {
    private DocumentoModel $model;
    private array $config;
    private \PDO $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->config = require __DIR__ . '/../config/config.php';
        $this->model = new DocumentoModel($pdo);
    }

    private function requireLogin(): void {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php');
            exit;
        }
    }

    private function requireAdmin(): void {
        $this->requireLogin();
        if ($_SESSION['user']['rol'] !== 'admin') {
            die('No tienes permisos de administrador');
        }
    }

    public function dashboard(): void {
        $this->requireLogin();
        $user = $_SESSION['user'];
        $selectedArea = $_GET['area'] ?? null;
        if ($user['rol'] === 'usuario' && $selectedArea === null) {
            $selectedArea = 'mi_area';
        }

        // Filtros
        $filtros = [
            'area' => $selectedArea,
            'nombre' => $_GET['nombre'] ?? null,
            'fecha' => $_GET['fecha'] ?? null,
            'numero_registro' => $_GET['numero_registro'] ?? null,
        ];
        $consultaGlobal = $user['rol'] === 'usuario' && $filtros['area'] !== 'mi_area';

        // Obtener documentos
        $documentos = $this->model->filtrar($filtros, $user['id'], $user['rol']);
        foreach ($documentos as &$documento) {
            $documento['archivo_disponible'] = $this->archivoDisponible($documento['archivo_path'] ?? null);
        }
        unset($documento);
        
        // Separar por estado
        $documentosPendientes = array_filter($documentos, fn($doc) => $doc['estado'] === 'pendiente');
        $documentosCargados = array_filter($documentos, fn($doc) => $doc['estado'] === 'cargado');

        // Estadísticas
        $totalDocumentos = $this->model->contarDocumentos($user['id'], $user['rol']);
        $ultimoRegistro = $this->model->obtenerUltimoRegistro($user['id'], $user['rol']);
        if ($consultaGlobal) {
            $documentosPendientes = [];
            $documentosCargados = $documentos;
            $totalDocumentos = count($documentosCargados);
            $ultimoRegistro = $documentosCargados[0] ?? null;
        }

        // Estadísticas adicionales para admin
        $totalUsuarios = 0;
        $usuariosActivos = 0;
        $areaConteos = [];
        if ($user['rol'] === 'admin') {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM usuarios");
            $totalUsuarios = (int)$stmt->fetchColumn();
            
            // Usuarios con documentos en los últimos 30 días
            $stmt = $this->pdo->query("SELECT COUNT(DISTINCT usuario_id) FROM documentos WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
            $usuariosActivos = (int)$stmt->fetchColumn();

            // Conteo por área sin número de registro
            $stmt = $this->pdo->query("SELECT area, COUNT(*) as total FROM documentos GROUP BY area ORDER BY area");
            $areaConteos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        require __DIR__ . '/../view/dashboard.php';
    }

    private function archivoDisponible(?string $path): bool {
        if (!$path) {
            return false;
        }

        $relativePath = ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
        $fullPath = realpath(__DIR__ . '/../public/' . $relativePath);

        return $fullPath !== false && is_file($fullPath);
    }

    public function verPdf(): void {
        $this->requireLogin();

        $docId = (int)($_GET['id'] ?? 0);
        if ($docId <= 0) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Documento inválido'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        $user = $_SESSION['user'];
        $documento = $this->model->obtenerPorId($docId);
        if (!$documento) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Documento no encontrado'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        if (
            $user['rol'] !== 'admin'
            && (int)$documento['usuario_id'] !== (int)$user['id']
            && ($documento['estado'] ?? '') !== 'cargado'
        ) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'No tienes permisos para ver este PDF'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        if (!$this->archivoDisponible($documento['archivo_path'] ?? null)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'El archivo PDF ya no está disponible en el servidor'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        $relativePath = ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $documento['archivo_path']), DIRECTORY_SEPARATOR);
        $fullPath = realpath(__DIR__ . '/../public/' . $relativePath);
        $fileName = basename($fullPath);

        header('Content-Type: application/pdf');
        header('Content-Length: ' . filesize($fullPath));
        header('Content-Disposition: inline; filename="' . rawurlencode($fileName) . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($fullPath);
        exit;
    }

    public function crear(): void {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=dashboard');
            exit;
        }

        $user = $_SESSION['user'];

        // Validaciones
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (empty($nombre)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'El nombre del documento es obligatorio'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        // Generar número de registro
        $numero = $this->model->generarNumeroRegistro($user['area']);

        // Crear documento
        try {
            $this->model->crear([
                'numero' => $numero,
                'area' => $user['area'],
                'fecha' => date('Y-m-d'),
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'usuario_id' => $user['id']
            ]);

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => "Documento creado exitosamente: $numero"
            ];
        } catch (\PDOException $e) {
            error_log('Error al guardar el documento: ' . $e->getMessage());
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Error al guardar el documento '
            ];
        }

        header('Location: index.php?action=dashboard');
        exit;
    }

    public function subir(): void {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=dashboard');
            exit;
        }

        $user = $_SESSION['user'];
        $docId = (int)($_POST['id'] ?? 0);

        if (!$docId) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Documento inválido'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        // Verificar que el documento pertenece al usuario
        $documento = $this->model->obtenerPorId($docId);
        if (!$documento || $documento['usuario_id'] != $user['id']) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Documento no encontrado o no tienes permisos'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        // Verificar que el documento esté pendiente
        if ($documento['estado'] !== 'pendiente') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'El documento ya ha sido cargado'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        // Validar que el archivo sea PDF
        $file = $_FILES['pdf'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Error al subir el archivo'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mimeType !== 'application/pdf') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Solo se permiten archivos PDF'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        // Crear directorio si no existe
        $uploadsDir = __DIR__ . '/../public/uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        // Guardar archivo
        $filename = time() . '_' . basename($file['name']);
        $filepath = 'uploads/' . $filename;
        $fullpath = $uploadsDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $fullpath)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Error al guardar el archivo'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        try {
            // Actualizar documento
            $result = $this->model->subirArchivo($docId, $filepath);

            if ($result === 'ya_cargado') {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'El documento ya estaba cargado.'];
            } elseif ($result === 'estado_invalido') {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'El documento no está en un estado válido para ser cargado.'];
            } elseif ($result === 'error') {
                throw new Exception('Error al actualizar el documento en la base de datos');
            } else {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Documento cargado exitosamente.'];
            }

            // Enviar email si se cargó correctamente
            if ($result === 'actualizado') {
                $this->sendUploadEmail($user, $docId);
            }
        } catch (Exception $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $e->getMessage()];
        }

        header('Location: index.php?action=dashboard');
        exit;
    }

    public function crearUsuario(): void {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=dashboard');
            exit;
        }

        // Validaciones
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $password = $_POST['password'] ?? '';
        $rol = $_POST['rol'] ?? 'usuario';
        $area = $_POST['area'] ?? 'COM';

        if (empty($email) || empty($username) || empty($nombre) || empty($password)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Todos los campos son obligatorios'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Correo electrónico inválido'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'La contraseña debe tener al menos 6 caracteres'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        if (!in_array($rol, ['usuario', 'admin'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Rol inválido'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        $areasValidas = ['COM', 'CU', 'DU', 'GA', 'JUR'];
        if (!in_array($area, $areasValidas)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Área inválida'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        try {
            // Hash de la contraseña
            $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
            $hash = password_hash($password, $algo);

            // Determinar columnas existentes en usuarios (para compatibilidad con esquemas viejos)
            $cols = [];
            $stmt = $this->pdo->query('SHOW COLUMNS FROM usuarios');
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $cols = array_map('strtolower', $columns);

            $fields = ['correo', 'nombre_usuario', 'password'];
            $values = [$email, $username, $hash];

            if (in_array('nombre', $cols)) {
                $fields[] = 'nombre';
                $values[] = $nombre;
            }
            if (in_array('rol', $cols)) {
                $fields[] = 'rol';
                $values[] = $rol;
            }
            if (in_array('area', $cols)) {
                $fields[] = 'area';
                $values[] = $area;
            }

            $sql = 'INSERT INTO usuarios (' . implode(', ', $fields) . ') VALUES (' . implode(', ', array_fill(0, count($fields), '?')) . ')';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => "Usuario '{$username}' creado exitosamente"
            ];
        } catch (\Throwable $e) {
            $mensaje = $e->getMessage();
            if (strpos($mensaje, 'Duplicate entry') !== false) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'El correo o nombre de usuario ya existe'
                ];
            } elseif (strpos($mensaje, 'Unknown column') !== false) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'La estructura de la tabla usuarios no coincide con el esquema esperado (falta columna). Actualiza DB.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Error al crear el usuario: ' . $mensaje
                ];
            }
        }

        header('Location: index.php?action=dashboard');
        exit;
    }

    public function eliminar(): void {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header('Location: index.php?action=dashboard');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'ID de documento inválido'];
            header('Location: index.php?action=dashboard');
            exit;
        }

        try {
            // Obtener documento para verificar existencia y obtener path del archivo
            $doc = $this->model->obtenerPorId($id);
            if (!$doc) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Documento no encontrado'];
                header('Location: index.php?action=dashboard');
                exit;
            }

            // Depuración: Verificar contenido de $doc
            error_log(print_r($doc, true));

            // Eliminar archivo físico si existe
            if (!empty($doc['archivo_path'])) {
                $filePath = __DIR__ . '/../public/' . $doc['archivo_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Eliminar documento de la base de datos
            $this->model->eliminar($id);

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Documento eliminado exitosamente'
            ];
        } catch (\Throwable $e) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Error al eliminar el documento: ' . $e->getMessage()
            ];
        }

        header('Location: index.php?action=dashboard');
        exit;
    }

    private function sendUploadEmail(array $user, int $docId): void {
        $doc = $this->model->obtenerPorId($docId);
        if (!$doc) return;

        $subject = 'Documento cargado: ' . $doc['numero_registro'];
        $fromEmail = $this->config['mail']['from_email'];
        $fromName = $this->config['mail']['from_name'];

        $areaLabels = [
            'COM' => 'Comunicacion',
            'CU' => 'Control Urbano',
            'DU' => 'Desarrollo Urbano',
            'GA' => 'Gestion Ambiental',
            'JUR' => 'Juridico',
        ];
        $displayName = $user['nombre'] ?? $user['nombre_usuario'] ?? 'usuario';
        $displayDate = !empty($doc['fecha']) ? date('d/m/Y', strtotime((string)$doc['fecha'])) : 'Fecha no disponible';
        $displayArea = $areaLabels[$doc['area']] ?? $doc['area'];
        $emailDoc = [
            'numero' => $doc['numero_registro'] ?? '',
            'nombre' => $doc['nombre_documento'] ?? '',
            'area_codigo' => $doc['area'] ?? '',
            'area_nombre' => $displayArea,
            'fecha' => $displayDate,
        ];

        ob_start();
        include __DIR__ . '/../view/emails/documento_cargado.php';
        $html = (string) ob_get_clean();
        $altBody = "Documento cargado\n"
            . "Se ha cargado el archivo PDF para el documento.\n\n"
            . "Numero: " . $emailDoc['numero'] . "\n"
            . "Nombre: " . $emailDoc['nombre'] . "\n"
            . "Area: " . $emailDoc['area_nombre'] . " (" . $emailDoc['area_codigo'] . ")\n"
            . "Fecha: " . $emailDoc['fecha'] . "\n";

        if ($this->config['mail']['use_phpmailer']) {
            if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                require_once __DIR__ . '/../vendor/autoload.php';
            }
            try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                $mail->SMTPDebug = 0;
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';
                $mail->isSMTP();
                $mail->Host = $this->config['mail']['smtp']['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $this->config['mail']['smtp']['username'];
                $mail->Password = $this->config['mail']['smtp']['password'];
                $mail->SMTPSecure = $this->config['mail']['smtp']['encryption'];
                $mail->Port = $this->config['mail']['smtp']['port'];
                $mail->Timeout = 10;
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];

                $mail->setFrom($fromEmail, $fromName);
                $mail->addAddress($user['correo'], $displayName);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $html;
                $mail->AltBody = $altBody;
                $mail->send();
            } catch (\Throwable $e) {
                error_log('Error enviando correo de documento cargado: ' . $e->getMessage());
            }
        }
    }
}
