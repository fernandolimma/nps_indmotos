<?php

namespace App\Models;

use App\Core\Database;

class Usuario
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        $this->db->query(
            "ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS tipo_usuario VARCHAR(20) NOT NULL DEFAULT 'USUARIO' AFTER senha_hash"
        );
        $this->db->query(
            "UPDATE usuarios SET tipo_usuario = 'USUARIO' WHERE tipo_usuario IS NULL OR tipo_usuario = '' OR tipo_usuario NOT IN ('MASTER', 'USUARIO')"
        );

        $master = $this->db->fetchOne("SELECT COUNT(*) AS total FROM usuarios WHERE tipo_usuario = 'MASTER'");
        if (($master['total'] ?? 0) == 0) {
            $this->db->query("UPDATE usuarios SET tipo_usuario = 'MASTER'");
        }
    }

    public function create(array $data): int
    {
        $tipoUsuario = strtoupper((string) ($data['tipo_usuario'] ?? 'USUARIO'));
        if (!in_array($tipoUsuario, ['MASTER', 'USUARIO'], true)) {
            $tipoUsuario = 'USUARIO';
        }

        return $this->db->insert('usuarios', [
            'nome' => $data['nome'],
            'email' => $data['email'],
            'senha_hash' => password_hash($data['senha'], PASSWORD_DEFAULT),
            'tipo_usuario' => $tipoUsuario
        ]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM usuarios WHERE email = ?',
            [$email]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM usuarios WHERE id = ?',
            [$id]
        );
    }

    public function getAll(): array
    {
        return $this->db->fetchAll('SELECT id, nome, email, tipo_usuario, data_criacao FROM usuarios ORDER BY nome');
    }

    public function delete(int $id): int
    {
        return $this->db->delete('usuarios', 'id = ?', [$id]);
    }
}
