<?php

namespace App\Models;

use App\Core\Database;

class Pergunta
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int
    {
        return $this->db->insert('perguntas', [
            'texto_pergunta' => $data['texto_pergunta'],
            'tipo' => $data['tipo'],
            'ativa' => $data['ativa'],
            'ordem' => $data['ordem'] ?? 0
        ]);
    }

    public function getAll(bool $onlyActive = false): array
    {
        $sql = 'SELECT * FROM perguntas';
        if ($onlyActive) {
            $sql .= ' WHERE ativa = 1';
        }
        $sql .= ' ORDER BY ordem, id';
        return $this->db->fetchAll($sql);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM perguntas WHERE id = ?',
            [$id]
        );
    }

    public function update(int $id, array $data): int
    {
        return $this->db->update(
            'perguntas',
            $data,
            'id = :id',
            ['id' => $id]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->delete('perguntas', 'id = ?', [$id]);
    }
}

