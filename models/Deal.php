<?php

class Deal extends BaseModel
{
    protected static string $table = 'deals';

    public static function getWithAgent(int $id): ?array
    {
        return Database::fetch(
            "SELECT d.*, u.name AS agent_name, u.email AS agent_email
             FROM deals d
             JOIN users u ON d.agent_id = u.id
             WHERE d.id = ?",
            [$id]
        );
    }

    public static function getAllWithAgents(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['agent_id'])) {
            $where[]  = 'd.agent_id = ?';
            $params[] = (int)$filters['agent_id'];
        }
        if (!empty($filters['status'])) {
            $where[]  = 'd.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['period'])) {
            $where[]  = "DATE_FORMAT(d.closing_date,'%Y-%m') = ?";
            $params[] = $filters['period'];
        }
        if (!empty($filters['search'])) {
            $where[]  = "(d.client_name LIKE ? OR u.name LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $where);
        return Database::fetchAll(
            "SELECT d.*, u.name AS agent_name
             FROM deals d
             JOIN users u ON d.agent_id = u.id
             WHERE {$whereClause}
             ORDER BY d.created_at DESC",
            $params
        );
    }

    public static function getAgentDeals(int $agentId, array $filters = []): array
    {
        $filters['agent_id'] = $agentId;
        return self::getAllWithAgents($filters);
    }

    /** Sum of approved deal_values for an agent in a given period. */
    public static function getAgentPeriodTotal(int $agentId, string $period): float
    {
        $row = Database::fetch(
            "SELECT COALESCE(SUM(deal_value),0) AS total
             FROM deals
             WHERE agent_id = ?
               AND DATE_FORMAT(closing_date,'%Y-%m') = ?
               AND status = 'Approved'",
            [$agentId, $period]
        );
        return (float)($row['total'] ?? 0);
    }

    public static function getStats(): array
    {
        return Database::fetch(
            "SELECT
                COUNT(*)                                               AS total,
                COALESCE(SUM(CASE WHEN status='Pending'  THEN 1 END),0) AS pending,
                COALESCE(SUM(CASE WHEN status='Approved' THEN 1 END),0) AS approved,
                COALESCE(SUM(CASE WHEN status='Rejected' THEN 1 END),0) AS rejected,
                COALESCE(SUM(CASE WHEN status='Approved' THEN deal_value END),0) AS total_approved_value
             FROM deals"
        ) ?? [];
    }

    public static function getRecentDeals(int $limit = 10): array
    {
        return Database::fetchAll(
            "SELECT d.*, u.name AS agent_name
             FROM deals d
             JOIN users u ON d.agent_id = u.id
             ORDER BY d.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }
}
