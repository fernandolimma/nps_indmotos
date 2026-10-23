<?php

namespace App\Models;

use App\Core\Database;

class ClienteOS
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
            "ALTER TABLE clientes_os ADD COLUMN IF NOT EXISTS telefone_whatsapp VARCHAR(30) NULL AFTER email_cliente"
        );
        $this->db->query(
            "ALTER TABLE clientes_os ADD COLUMN IF NOT EXISTS status_whatsapp ENUM('nao_enviado', 'enviado', 'retornado_erro', 'nao_disponivel') NOT NULL DEFAULT 'nao_disponivel' AFTER status_email"
        );
    }

    public function create(array $data): int
    {
        return $this->db->insert('clientes_os', $data);
    }

    public function findByToken(string $token): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM clientes_os WHERE token_unico = ?',
            [$token]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM clientes_os WHERE id = ?',
            [$id]
        );
    }

    public function getPendingNotifications(): array
    {
        return $this->db->fetchAll(
            "SELECT *
             FROM clientes_os
             WHERE status_email IN ('nao_enviado', 'retornado_erro')
                OR status_whatsapp IN ('nao_enviado', 'retornado_erro')
             ORDER BY data_importacao, id"
        );
    }

    public function getPendingImportedData(): array
    {
        return $this->db->fetchAll(
            "SELECT *
             FROM clientes_os
            WHERE (
                   status_email IN ('nao_enviado', 'retornado_erro')
                   OR status_whatsapp IN ('nao_enviado', 'retornado_erro')
                  )
              AND status_resposta = 'pendente'
            ORDER BY data_importacao DESC, id DESC"
        );
    }

    public function clearPendingImportedData(): int
    {
        return $this->db->delete(
            'clientes_os',
            "status_email <> :status_email_enviado
             AND status_whatsapp <> :status_whatsapp_enviado
             AND status_resposta = :status_resposta",
            [
                'status_email_enviado' => 'enviado',
                'status_whatsapp_enviado' => 'enviado',
                'status_resposta' => 'pendente'
            ]
        );
    }

    public function updateStatusEmail(int $id, string $status): int
    {
        return $this->db->update(
            'clientes_os',
            ['status_email' => $status],
            'id = :id',
            ['id' => $id]
        );
    }

    public function updateStatusWhatsapp(int $id, string $status): int
    {
        return $this->db->update(
            'clientes_os',
            ['status_whatsapp' => $status],
            'id = :id',
            ['id' => $id]
        );
    }

    public function updateStatusResposta(int $id, string $status): int
    {
        return $this->db->update(
            'clientes_os',
            ['status_resposta' => $status],
            'id = :id',
            ['id' => $id]
        );
    }

    public function getRespondidasPaginated(int $page = 1, int $perPage = 20, ?string $dataInicial = null, ?string $dataFinal = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = ["c.status_resposta = 'respondido'"];
        $params = [];

        if (!empty($dataInicial)) {
            $where[] = 'DATE(c.data_importacao) >= ?';
            $params[] = $dataInicial;
        }

        if (!empty($dataFinal)) {
            $where[] = 'DATE(c.data_importacao) <= ?';
            $params[] = $dataFinal;
        }

        if (empty($dataInicial) && empty($dataFinal)) {
            $where[] = 'MONTH(c.data_importacao) = ?';
            $where[] = 'YEAR(c.data_importacao) = ?';
            $params[] = date('m');
            $params[] = date('Y');
        }

        $whereSql = implode(' AND ', $where);

        $items = $this->db->fetchAll(
            "SELECT c.*, 
                (
                    SELECT ROUND(AVG(r.nota), 2)
                    FROM respostas r
                    WHERE r.cliente_os_id = c.id
                      AND r.nota IS NOT NULL
                ) as nota_nps
             FROM clientes_os c 
             WHERE {$whereSql}
             ORDER BY c.data_importacao DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM clientes_os c 
             WHERE {$whereSql}",
            $params
        );

        return [
            'items' => $items,
            'total' => (int) ($total['total'] ?? 0),
            'pages' => max(1, (int) ceil(((int) ($total['total'] ?? 0)) / $perPage)),
            'current_page' => $page,
            'per_page' => $perPage,
            'data_inicial' => $dataInicial,
            'data_final' => $dataFinal
        ];
    }

    public function getPendentesRespostaPaginated(int $page = 1, int $perPage = 20, ?string $dataInicial = null, ?string $dataFinal = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = ["status_resposta = 'pendente'"];
        $params = [];

        if (!empty($dataInicial)) {
            $where[] = 'DATE(data_importacao) >= ?';
            $params[] = $dataInicial;
        }

        if (!empty($dataFinal)) {
            $where[] = 'DATE(data_importacao) <= ?';
            $params[] = $dataFinal;
        }

        $whereSql = implode(' AND ', $where);

        $items = $this->db->fetchAll(
            "SELECT *
             FROM clientes_os
             WHERE {$whereSql}
             ORDER BY data_importacao DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = $this->db->fetchOne(
            "SELECT COUNT(*) AS total
             FROM clientes_os
             WHERE {$whereSql}",
            $params
        );

        return [
            'items' => $items,
            'total' => (int) ($total['total'] ?? 0),
            'pages' => max(1, (int) ceil(((int) ($total['total'] ?? 0)) / $perPage)),
            'current_page' => $page,
            'per_page' => $perPage,
            'data_inicial' => $dataInicial,
            'data_final' => $dataFinal
        ];
    }

    public function count(): int
    {
        $result = $this->db->fetchOne('SELECT COUNT(*) as total FROM clientes_os');
        return $result['total'];
    }

    public function countMesAtual(): int
    {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) AS total
             FROM clientes_os
             WHERE MONTH(data_importacao) = ?
               AND YEAR(data_importacao) = ?",
            [date('m'), date('Y')]
        );

        return (int) ($result['total'] ?? 0);
    }

    public function countRespondidas(): int
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM clientes_os WHERE status_resposta = 'respondido'");
        return $result['total'];
    }

    public function countRespondidasMesAtual(): int
    {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) AS total
             FROM clientes_os
             WHERE status_resposta = 'respondido'
               AND MONTH(data_importacao) = ?
               AND YEAR(data_importacao) = ?",
            [date('m'), date('Y')]
        );

        return (int) ($result['total'] ?? 0);
    }

    public function countPendentes(): int
    {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) AS total
             FROM clientes_os
             WHERE status_email IN ('nao_enviado', 'retornado_erro')"
        );
        return $result['total'];
    }

    public function countWhatsappPendentes(): int
    {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) AS total
             FROM clientes_os
             WHERE status_whatsapp IN ('nao_enviado', 'retornado_erro')
               AND telefone_whatsapp IS NOT NULL
               AND telefone_whatsapp <> ''"
        );

        return (int) ($result['total'] ?? 0);
    }

    public function getEmailsComErroPaginated(int $page = 1, int $perPage = 20, ?string $dataInicial = null, ?string $dataFinal = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = ["status_email = 'retornado_erro'"];
        $params = [];

        if (!empty($dataInicial)) {
            $where[] = 'DATE(data_importacao) >= ?';
            $params[] = $dataInicial;
        }

        if (!empty($dataFinal)) {
            $where[] = 'DATE(data_importacao) <= ?';
            $params[] = $dataFinal;
        }

        $whereSql = implode(' AND ', $where);

        $items = $this->db->fetchAll(
            "SELECT *
             FROM clientes_os
             WHERE {$whereSql}
             ORDER BY data_importacao DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = $this->db->fetchOne(
            "SELECT COUNT(*) AS total
             FROM clientes_os
             WHERE {$whereSql}",
            $params
        );

        return [
            'items' => $items,
            'total' => (int) ($total['total'] ?? 0),
            'pages' => max(1, (int) ceil(((int) ($total['total'] ?? 0)) / $perPage)),
            'current_page' => $page,
            'per_page' => $perPage,
            'data_inicial' => $dataInicial,
            'data_final' => $dataFinal
        ];
    }

    public function getMediaNpsMesAtual(): ?float
    {
        $result = $this->db->fetchOne(
            "SELECT ROUND(AVG(r.nota), 2) AS media_nps
             FROM respostas r
             INNER JOIN clientes_os c ON c.id = r.cliente_os_id
             WHERE c.status_resposta = 'respondido'
               AND r.nota IS NOT NULL
               AND MONTH(r.data_resposta) = ?
               AND YEAR(r.data_resposta) = ?",
            [date('m'), date('Y')]
        );

        return isset($result['media_nps']) ? (float) $result['media_nps'] : null;
    }

    public function getMediaNotasDetratorasMesAtual(): ?float
    {
        $result = $this->db->fetchOne(
            "SELECT ROUND(AVG(pesquisa.nota_nps), 2) AS media_notas_detratoras
             FROM (
                SELECT c.id,
                       (
                           SELECT ROUND(AVG(r.nota), 2)
                           FROM respostas r
                           WHERE r.cliente_os_id = c.id
                             AND r.nota IS NOT NULL
                       ) AS nota_nps
                FROM clientes_os c
                WHERE c.status_resposta = 'respondido'
                  AND MONTH(c.data_importacao) = ?
                  AND YEAR(c.data_importacao) = ?
             ) AS pesquisa
             WHERE pesquisa.nota_nps IS NOT NULL
               AND pesquisa.nota_nps < 7",
            [date('m'), date('Y')]
        );

        return isset($result['media_notas_detratoras']) ? (float) $result['media_notas_detratoras'] : null;
    }
}
