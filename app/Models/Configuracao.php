<?php

namespace App\Models;

use App\Core\Database;

class Configuracao
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
            "ALTER TABLE configuracoes ADD COLUMN IF NOT EXISTS logo_path VARCHAR(255) NULL AFTER nome_remetente"
        );
        $this->db->query(
            "ALTER TABLE configuracoes ADD COLUMN IF NOT EXISTS evolution_enabled BOOLEAN NOT NULL DEFAULT FALSE AFTER dias_validade"
        );
        $this->db->query(
            "ALTER TABLE configuracoes ADD COLUMN IF NOT EXISTS evolution_url VARCHAR(500) NULL AFTER evolution_enabled"
        );
        $this->db->query(
            "ALTER TABLE configuracoes ADD COLUMN IF NOT EXISTS evolution_instance VARCHAR(255) NULL AFTER evolution_url"
        );
        $this->db->query(
            "ALTER TABLE configuracoes ADD COLUMN IF NOT EXISTS evolution_api_key TEXT NULL AFTER evolution_instance"
        );
    }

    public function get(): ?array
    {
        return $this->db->fetchOne('SELECT * FROM configuracoes ORDER BY id DESC LIMIT 1');
    }

    public function update(array $data): int
    {
        $config = $this->get();
        if ($config) {
            return $this->db->update(
                'configuracoes',
                $data,
                'id = :id',
                ['id' => $config['id']]
            );
        } else {
            return $this->db->insert('configuracoes', $data);
        }
    }
}
