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
        if (!$this->db->columnExists('usuarios', 'tipo_usuario')) {
            $this->db->query(
                "ALTER TABLE usuarios ADD COLUMN tipo_usuario VARCHAR(20) NOT NULL DEFAULT 'USUARIO' AFTER senha_hash"
            );
        }

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

    public function findByEmailExcept(string $email, int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT id FROM usuarios WHERE email = ? AND id <> ?',
            [$email, $id]
        );
    }

    public function countMasters(): int
    {
        $row = $this->db->fetchOne("SELECT COUNT(*) AS total FROM usuarios WHERE tipo_usuario = 'MASTER'");
        return (int) ($row['total'] ?? 0);
    }

    public function update(int $id, array $data): int
    {
        $tipoUsuario = strtoupper((string) ($data['tipo_usuario'] ?? 'USUARIO'));
        if (!in_array($tipoUsuario, ['MASTER', 'USUARIO'], true)) {
            $tipoUsuario = 'USUARIO';
        }

        $fields = [
            'nome' => $data['nome'],
            'email' => $data['email'],
            'tipo_usuario' => $tipoUsuario
        ];

        if (!empty($data['senha'])) {
            $fields['senha_hash'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }

        return $this->db->update('usuarios', $fields, 'id = :usuario_id', ['usuario_id' => $id]);
    }

    public function delete(int $id): int
    {
        return $this->db->delete('usuarios', 'id = ?', [$id]);
    }
}
