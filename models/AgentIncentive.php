<?php

class AgentIncentive extends BaseModel
{
    protected static string $table = 'agent_incentives';

    public static function getLedger(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['agent_id'])) {
            $where[]  = 'ai.agent_id = ?';
            $params[] = (int)$filters['agent_id'];
        }
        if (!empty($filters['period'])) {
            $where[]  = 'ai.payout_period = ?';
            $params[] = $filters['period'];
        }
        if (!empty($filters['status'])) {
            $where[]  = 'ai.status = ?';
            $params[] = $filters['status'];
        }

        $whereClause = implode(' AND ', $where);
        return Database::fetchAll(
            "SELECT ai.*, u.name AS agent_name, ir.name AS rule_name,
                    ir.rule_type, d.client_name, d.deal_value
             FROM agent_incentives ai
             JOIN users u ON ai.agent_id = u.id
             JOIN incentive_rules ir ON ai.rule_id = ir.id
             LEFT JOIN deals d ON ai.deal_id = d.id
             WHERE {$whereClause}
             ORDER BY ai.created_at DESC",
            $params
        );
    }

    /** Summary per agent for a period. */
    public static function getPeriodAgentSummary(string $period): array
    {
        return Database::fetchAll(
            "SELECT ai.agent_id, u.name AS agent_name,
                    COUNT(*) AS incentive_count,
                    SUM(ai.earned_amount) AS total_earned,
                    SUM(CASE WHEN ai.status='paid' THEN ai.earned_amount ELSE 0 END) AS total_paid,
                    SUM(CASE WHEN ai.status='pending' THEN ai.earned_amount ELSE 0 END) AS total_pending
             FROM agent_incentives ai
             JOIN users u ON ai.agent_id = u.id
             WHERE ai.payout_period = ?
             GROUP BY ai.agent_id, u.name
             ORDER BY total_earned DESC",
            [$period]
        );
    }

    /** Check if an incentive entry already exists for a deal+rule combo. */
    public static function existsForDeal(int $dealId, int $ruleId): bool
    {
        return Database::fetch(
            'SELECT id FROM agent_incentives WHERE deal_id = ? AND rule_id = ? LIMIT 1',
            [$dealId, $ruleId]
        ) !== null;
    }

    public static function getAgentTotalByStatus(int $agentId, string $status): float
    {
        $row = Database::fetch(
            "SELECT COALESCE(SUM(earned_amount),0) AS total
             FROM agent_incentives WHERE agent_id = ? AND status = ?",
            [$agentId, $status]
        );
        return (float)($row['total'] ?? 0);
    }

    /** Approve all pending incentives for a payout batch. */
    public static function approveForBatch(int $batchId, string $period): int
    {
        return Database::execute(
            "UPDATE agent_incentives
             SET status = 'approved', payout_batch_id = ?
             WHERE payout_period = ? AND status = 'pending'",
            [$batchId, $period]
        );
    }

    /** Mark incentives in a batch as paid. */
    public static function markPaidForBatch(int $batchId): int
    {
        return Database::execute(
            "UPDATE agent_incentives SET status = 'paid'
             WHERE payout_batch_id = ? AND status = 'approved'",
            [$batchId]
        );
    }
}
