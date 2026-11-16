<?php

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;

    private function __construct()
    {
        $host = getenv('DB_HOST') ?: 'localhost';
        $dbname = getenv('DB_NAME') ?: 'nps_indmotos';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD');
        if ($password === false) {
            $password = '';
        }

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Falha na conexão com o banco de dados.', 0, $e);
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result === false ? null : $result;
    }

    public function columnExists(string $table, string $column): bool
    {
        return $this->fetchOne(
            'SELECT 1
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
             LIMIT 1',
            [$table, $column]
        ) !== null;
    }

    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->query($sql, $data);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "$column = :$column";
        }
        $setClause = implode(', ', $set);
        $sql = "UPDATE $table SET $setClause WHERE $where";
        $stmt = $this->query($sql, array_merge($data, $whereParams));
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
}
