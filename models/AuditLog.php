<?php

class AuditLog extends BaseModel
{
    protected static string $table = 'audit_log';

    public static function record(
        string $action,
        string $table,
        int    $recordId,
        array  $oldValues = [],
        array  $newValues = []
    ): void {
        Database::insert(
            "INSERT INTO audit_log
             (user_id, action, table_name, record_id, old_values, new_values, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                Auth::id(),
                $action,
                $table,
                $recordId,
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]
        );
    }

    public static function getRecent(int $limit = 30): array
    {
        return Database::fetchAll(
            "SELECT al.*, u.name AS user_name
             FROM audit_log al
             LEFT JOIN users u ON al.user_id = u.id
             ORDER BY al.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }
}
