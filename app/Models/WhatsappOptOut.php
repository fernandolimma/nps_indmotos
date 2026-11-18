<?php

namespace App\Models;

use App\Core\Database;
use App\Services\BrazilianPhoneNumber;

class WhatsappOptOut
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS whatsapp_optout (
                chave VARCHAR(12) NOT NULL PRIMARY KEY,
                telefone VARCHAR(30) NOT NULL,
                data_optout TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    // DDD + últimos 8 dígitos: ignora o nono dígito e o prefixo do país.
    public static function key(string $phone): string
    {
        $number = BrazilianPhoneNumber::normalize($phone);

        return substr($number, 2, 2) . substr($number, -8);
    }

    public function isOptedOut(string $phone): bool
    {
        return $this->db->fetchOne(
            'SELECT 1 FROM whatsapp_optout WHERE chave = ?',
            [self::key($phone)]
        ) !== null;
    }

    public function add(string $phone): void
    {
        $this->db->query(
            'INSERT IGNORE INTO whatsapp_optout (chave, telefone) VALUES (?, ?)',
            [self::key($phone), BrazilianPhoneNumber::normalize($phone)]
        );
    }
}
