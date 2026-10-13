<?php

class IncentiveRule extends BaseModel
{
    protected static string $table = 'incentive_rules';

    /** Get rules active on a given date. */
    public static function getActive(?string $date = null): array
    {
        $date = $date ?? date('Y-m-d');
        return Database::fetchAll(
            "SELECT * FROM incentive_rules
             WHERE is_active = 1
               AND start_date <= ?
               AND (end_date IS NULL OR end_date >= ?)
             ORDER BY rule_type, min_threshold ASC",
            [$date, $date]
        );
    }

    public static function getAllWithCreator(): array
    {
        return Database::fetchAll(
            "SELECT ir.*, u.name AS created_by_name
             FROM incentive_rules ir
             LEFT JOIN users u ON ir.created_by = u.id
             ORDER BY ir.is_active DESC, ir.created_at DESC"
        );
    }
}
