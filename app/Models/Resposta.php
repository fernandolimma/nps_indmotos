<?php

namespace App\Models;

use App\Core\Database;

class Resposta
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int
    {
        return $this->db->insert('respostas', $data);
    }

    public function getByClienteOS(int $clienteOsId): array
    {
        return $this->db->fetchAll(
            'SELECT r.*, p.texto_pergunta, p.tipo 
             FROM respostas r 
             JOIN perguntas p ON r.pergunta_id = p.id 
             WHERE r.cliente_os_id = ?
             ORDER BY p.ordem ASC, p.id ASC',
            [$clienteOsId]
        );
    }
}
