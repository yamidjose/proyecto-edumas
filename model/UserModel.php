<?php
declare(strict_types=1);

class UserModel {
    private \PDO $db;

    public function __construct(\PDO $pdo, array $appConfig) {
        $this->db = $pdo;
    }

    public function getByEmail(?string $email): ?array {
        if (!$email) return null;
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE correo = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function getByEmailAndUsername(string $email, string $username): ?array {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE correo = ? AND nombre_usuario = ? LIMIT 1');
        $stmt->execute([$email, $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function createUser(string $email, string $username, string $passwordHash): int {
        $stmt = $this->db->prepare('INSERT INTO usuarios (correo, nombre_usuario, password) VALUES (?, ?, ?)');
        $stmt->execute([$email, $username, $passwordHash]);
        return (int)$this->db->lastInsertId();
    }

    public function verifyPassword(array $user, string $plainPassword): bool {
        return password_verify($plainPassword, $user['password'] ?? '');
    }

    public function setVerificationCode(int $userId, string $codeHash, \DateTimeInterface $expiresAt): void {
        $stmt = $this->db->prepare('UPDATE usuarios SET codigo_verificacion = ?, codigo_verificacion_expires_at = ?, intentos_codigo = 0 WHERE id = ?');
        $stmt->execute([$codeHash, $expiresAt->format('Y-m-d H:i:s'), $userId]);
    }

    public function clearVerificationCode(int $userId): void {
        $stmt = $this->db->prepare('UPDATE usuarios SET codigo_verificacion = NULL, codigo_verificacion_expires_at = NULL, intentos_codigo = 0 WHERE id = ?');
        $stmt->execute([$userId]);
    }

    public function incrementCodeCounter(int $userId): void {
        $stmt = $this->db->prepare('UPDATE usuarios SET intentos_codigo = intentos_codigo + 1 WHERE id = ?');
        $stmt->execute([$userId]);
    }

    public function countCodeAttemptsInLastHour(?int $userId, string $email, string $ip): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) as c FROM login_intentos WHERE tipo IN ('code','request_code') AND (user_id = :uid OR email = :email OR ip = :ip) AND created_at >= (NOW() - INTERVAL 1 HOUR)");
        $stmt->execute([':uid' => $userId, ':email' => $email, ':ip' => $ip]);
        return (int)$stmt->fetchColumn();
    }

    public function countPasswordFailsLast15Min(?int $userId, string $email, string $ip): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM login_intentos WHERE tipo = 'password' AND exito = 0 AND (user_id = :uid OR email = :email OR ip = :ip) AND created_at >= (NOW() - INTERVAL 15 MINUTE)");
        $stmt->execute([':uid' => $userId, ':email' => $email, ':ip' => $ip]);
        return (int)$stmt->fetchColumn();
    }

    public function recordAttempt(?int $userId, ?string $email, string $ip, string $tipo, bool $exito): void {
        $stmt = $this->db->prepare('INSERT INTO login_intentos (user_id, email, ip, tipo, exito) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $email, $ip, $tipo, $exito ? 1 : 0]);
    }

    public function updatePassword(int $userId, string $newHash): void {
        $stmt = $this->db->prepare('UPDATE usuarios SET password = ? WHERE id = ?');
        $stmt->execute([$newHash, $userId]);
    }
}
