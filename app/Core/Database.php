<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Thin PDO wrapper (singleton).
 *
 * Every query in the application goes through here with bound parameters,
 * so there is no string-concatenated SQL anywhere in the codebase.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;
    private string $driver;

    private function __construct(array $cfg)
    {
        $this->driver = $cfg['driver'];

        if ($this->driver === 'sqlite') {
            $dsn = 'sqlite:' . $cfg['sqlite'];
            $this->pdo = new PDO($dsn, null, null, $this->options());
            $this->pdo->exec('PRAGMA foreign_keys = ON');
        } else {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $cfg['host'],
                $cfg['port'],
                $cfg['database']
            );
            $this->pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $this->options());
        }
    }

    private function options(): array
    {
        return [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
    }

    public static function instance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self(App::config('db'));
        }
        return self::$instance;
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function driver(): string
    {
        return $this->driver;
    }

    /** Run a prepared statement and return the statement handle. */
    public function run(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Fetch every row. */
    public function all(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll();
    }

    /** Fetch a single row or null. */
    public function first(string $sql, array $params = []): ?array
    {
        $row = $this->run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /** Fetch a single scalar value. */
    public function scalar(string $sql, array $params = [])
    {
        $value = $this->run($sql, $params)->fetchColumn();
        return $value === false ? null : $value;
    }

    /** Insert and return the new primary key. */
    public function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        $sql  = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $cols),
            implode(', ', array_map(fn($c) => ':' . $c, $cols))
        );
        $this->run($sql, $data);
        return (int) $this->pdo->lastInsertId();
    }

    /** Update rows matching a primary key. */
    public function update(string $table, array $data, int $id, string $key = 'id'): void
    {
        $sets = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
        $data[$key] = $id;
        $this->run("UPDATE $table SET $sets WHERE $key = :$key", $data);
    }

    public function delete(string $table, int $id, string $key = 'id'): void
    {
        $this->run("DELETE FROM $table WHERE $key = :id", ['id' => $id]);
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollBack(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }
}
