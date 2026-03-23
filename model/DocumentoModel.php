<?php

class DocumentoModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function generarNumeroRegistro($area) {
        $year = date('Y');

        // Contar el total global de registros
        $totalGlobal = $this->pdo->query("SELECT COUNT(*) FROM documentos")->fetchColumn() + 1;

        // Contar el total de registros por área
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM documentos WHERE area = ?");
        $stmt->execute([$area]);
        $totalArea = $stmt->fetchColumn() + 1;

        // Formatear los valores a tres dígitos
        $totalGlobalFormatted = str_pad($totalGlobal, 3, '0', STR_PAD_LEFT);
        $totalAreaFormatted = str_pad($totalArea, 3, '0', STR_PAD_LEFT);

        // Concatenar el número de registro
        return sprintf("EDU-%s-%s-%s-%s", $year, $totalGlobalFormatted, $area, $totalAreaFormatted);
    }

    public function crear($data) {
        $sql = "INSERT INTO documentos 
        (numero_registro, area, fecha, nombre_documento, descripcion, usuario_id, estado)
        VALUES (?, ?, ?, ?, ?, ?, 'pendiente')";

        return $this->pdo->prepare($sql)->execute([
            $data['numero'],
            $data['area'],
            $data['fecha'],
            $data['nombre'],
            $data['descripcion'],
            $data['usuario_id']
        ]);
    }

    public function subirArchivo($id, $path) {
        // Check current state of the document
        $stmt = $this->pdo->prepare("SELECT estado FROM documentos WHERE id = ?");
        $stmt->execute([$id]);
        $currentState = $stmt->fetchColumn();

        if ($currentState === 'cargado') {
            return 'ya_cargado';
        }

        if ($currentState !== 'pendiente') {
            return 'estado_invalido';
        }

        // Update the document
        $updated = $this->pdo->prepare(
            "UPDATE documentos SET archivo_path = ?, estado = 'cargado' WHERE id = ? AND estado = 'pendiente'"
        )->execute([$path, $id]);

        return $updated ? 'actualizado' : 'error';
    }

    public function obtenerPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM documentos WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function eliminar($id) {
        // Obtener el archivo para eliminarlo del servidor
        $doc = $this->obtenerPorId($id);
        if ($doc && $doc['archivo_path'] && file_exists($doc['archivo_path'])) {
            unlink($doc['archivo_path']);
        }
        
        return $this->pdo->prepare("DELETE FROM documentos WHERE id=?")->execute([$id]);
    }

    public function filtrar($filtros, $userId, $rol) {
        $sql = "SELECT documentos.*, usuarios.nombre_usuario AS registrante_nombre, usuarios.correo AS registrante_correo, usuarios.nombre_usuario AS registrante_usuario
                FROM documentos
                INNER JOIN usuarios ON usuarios.id = documentos.usuario_id
                WHERE 1=1";
        $params = [];
        $selectedArea = $filtros['area'] ?? null;

        if ($rol === 'usuario') {
            if ($selectedArea === 'mi_area') {
                $sql .= " AND documentos.usuario_id = ?";
                $params[] = $userId;
            } else {
                $sql .= " AND documentos.estado = 'cargado'";

                if (!empty($selectedArea)) {
                    $sql .= " AND documentos.area = ?";
                    $params[] = $selectedArea;
                }
            }
        }

        if ($rol !== 'usuario' && !empty($selectedArea)) {
            $sql .= " AND documentos.area = ?";
            $params[] = $selectedArea;
        }

        if (!empty($filtros['nombre'])) {
            $sql .= " AND documentos.nombre_documento LIKE ?";
            $params[] = "%" . $filtros['nombre'] . "%";
        }

        if (!empty($filtros['fecha'])) {
            $sql .= " AND documentos.fecha = ?";
            $params[] = $filtros['fecha'];
        }

        if (!empty($filtros['numero_registro'])) {
            $sql .= " AND documentos.numero_registro LIKE ?";
            $params[] = "%" . $filtros['numero_registro'] . "%";
        }

        $sql .= " ORDER BY documentos.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerAreas() {
        $stmt = $this->pdo->query("SELECT DISTINCT area FROM documentos ORDER BY area");
        return $stmt->fetchAll();
    }

    public function contarDocumentos($userId, $rol) {
        if ($rol === 'usuario') {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM documentos WHERE usuario_id = ?");
            $stmt->execute([$userId]);
        } else {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM documentos");
        }
        return (int)$stmt->fetchColumn();
    }

    public function obtenerUltimoRegistro($userId, $rol) {
        if ($rol === 'usuario') {
            $stmt = $this->pdo->prepare("SELECT * FROM documentos WHERE usuario_id = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$userId]);
        } else {
            $stmt = $this->pdo->query("SELECT * FROM documentos ORDER BY id DESC LIMIT 1");
        }
        return $stmt->fetch();
    }
}
