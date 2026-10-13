<?php

class PayoutBatch extends BaseModel
{
    protected static string $table = 'payout_batches';

    public static function getWithProcessor(): array
    {
        return Database::fetchAll(
            "SELECT pb.*, u.name AS processor_name
             FROM payout_batches pb
             LEFT JOIN users u ON pb.processed_by = u.id
             ORDER BY pb.created_at DESC"
        );
    }

    public static function getWithDetails(int $id): ?array
    {
        return Database::fetch(
            "SELECT pb.*, u.name AS processor_name
             FROM payout_batches pb
             LEFT JOIN users u ON pb.processed_by = u.id
             WHERE pb.id = ?",
            [$id]
        );
    }

    /** Recalculate and update the batch total from its linked incentives. */
    public static function recalcTotal(int $batchId): void
    {
        Database::execute(
            "UPDATE payout_batches pb
             SET total_amount = (
                 SELECT COALESCE(SUM(earned_amount),0)
                 FROM agent_incentives
                 WHERE payout_batch_id = ?
             ),
             agent_count = (
                 SELECT COUNT(DISTINCT agent_id)
                 FROM agent_incentives
                 WHERE payout_batch_id = ?
             )
             WHERE pb.id = ?",
            [$batchId, $batchId, $batchId]
        );
    }
}
