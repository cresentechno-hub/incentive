<?php

abstract class BaseModel
{
    protected static string $table = '';

    public static function find(int $id): ?array
    {
        return Database::fetch(
            'SELECT * FROM `' . static::$table . '` WHERE id = ?', [$id]
        );
    }

    public static function all(string $orderBy = 'id', string $dir = 'ASC'): array
    {
        return Database::fetchAll(
            'SELECT * FROM `' . static::$table . "` ORDER BY `{$orderBy}` {$dir}"
        );
    }

    public static function insert(array $data): int
    {
        $cols   = implode('`, `', array_keys($data));
        $phs    = implode(', ', array_fill(0, count($data), '?'));
        return Database::insert(
            "INSERT INTO `" . static::$table . "` (`{$cols}`) VALUES ({$phs})",
            array_values($data)
        );
    }

    public static function update(int $id, array $data): int
    {
        $sets = implode(', ', array_map(fn($c) => "`{$c}` = ?", array_keys($data)));
        return Database::execute(
            "UPDATE `" . static::$table . "` SET {$sets} WHERE id = ?",
            [...array_values($data), $id]
        );
    }

    public static function delete(int $id): int
    {
        return Database::execute(
            'DELETE FROM `' . static::$table . '` WHERE id = ?', [$id]
        );
    }

    public static function count(): int
    {
        return (int)(Database::fetch('SELECT COUNT(*) AS cnt FROM `' . static::$table . '`')['cnt'] ?? 0);
    }
}
