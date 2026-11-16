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
        $columns = [
            'logo_path' => "VARCHAR(255) NULL AFTER nome_remetente",
            'evolution_enabled' => 'BOOLEAN NOT NULL DEFAULT FALSE AFTER dias_validade',
            'evolution_url' => 'VARCHAR(500) NULL AFTER evolution_enabled',
            'evolution_instance' => 'VARCHAR(255) NULL AFTER evolution_url',
            'evolution_api_key' => 'TEXT NULL AFTER evolution_instance'
        ];

        foreach ($columns as $column => $definition) {
            if (!$this->db->columnExists('configuracoes', $column)) {
                $this->db->query("ALTER TABLE configuracoes ADD COLUMN {$column} {$definition}");
            }
        }
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
