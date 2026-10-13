<?php

class User extends BaseModel
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        return Database::fetch(
            "SELECT u.*, r.name AS role_name
             FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.email = ? AND u.status = 'active'
             LIMIT 1",
            [$email]
        );
    }

    public static function findWithRole(int $id): ?array
    {
        return Database::fetch(
            "SELECT u.*, r.name AS role_name, t.name AS team_name
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN teams t ON u.team_id = t.id
             WHERE u.id = ?",
            [$id]
        );
    }

    public static function getAllWithRoles(): array
    {
        return Database::fetchAll(
            "SELECT u.*, r.name AS role_name, t.name AS team_name
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN teams t ON u.team_id = t.id
             ORDER BY r.id, u.name"
        );
    }

    public static function getAgents(): array
    {
        return Database::fetchAll(
            "SELECT u.*, t.name AS team_name
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN teams t ON u.team_id = t.id
             WHERE r.name = 'Agent' AND u.status = 'active'
             ORDER BY u.name"
        );
    }

    public static function getRoles(): array
    {
        return Database::fetchAll('SELECT * FROM roles ORDER BY id');
    }

    public static function getTeams(): array
    {
        return Database::fetchAll('SELECT * FROM teams ORDER BY name');
    }

    public static function getAgentSummary(int $agentId, string $period): array
    {
        return Database::fetch(
            "SELECT
                COUNT(d.id)                                                AS total_deals,
                COALESCE(SUM(CASE WHEN d.status='Approved' THEN 1 END),0) AS approved_deals,
                COALESCE(SUM(CASE WHEN d.status='Pending'  THEN 1 END),0) AS pending_deals,
                COALESCE(SUM(CASE WHEN d.status='Approved' THEN d.deal_value END),0) AS approved_sales,
                COALESCE((SELECT SUM(ai.earned_amount) FROM agent_incentives ai
                          WHERE ai.agent_id=u.id AND ai.payout_period=?),0) AS total_earned,
                COALESCE((SELECT SUM(ai.earned_amount) FROM agent_incentives ai
                          WHERE ai.agent_id=u.id AND ai.payout_period=? AND ai.status='paid'),0) AS total_paid
             FROM users u
             LEFT JOIN deals d ON d.agent_id = u.id
                               AND DATE_FORMAT(d.closing_date,'%Y-%m') = ?
             WHERE u.id = ?
             GROUP BY u.id",
            [$period, $period, $period, $agentId]
        ) ?? [];
    }

    public static function getAdminStats(): array
    {
        return Database::fetch(
            "SELECT
                (SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id=r.id WHERE r.name='Agent') AS total_agents,
                (SELECT COUNT(*) FROM deals) AS total_deals,
                (SELECT COUNT(*) FROM deals WHERE status='Pending') AS pending_deals,
                (SELECT COUNT(*) FROM deals WHERE status='Approved') AS approved_deals,
                (SELECT COALESCE(SUM(deal_value),0) FROM deals WHERE status='Approved') AS total_approved_value,
                (SELECT COALESCE(SUM(earned_amount),0) FROM agent_incentives WHERE status='pending') AS pending_incentives,
                (SELECT COUNT(*) FROM payout_batches WHERE status='draft') AS draft_batches"
        ) ?? [];
    }

    public static function getTopAgents(string $period, int $limit = 5): array
    {
        return Database::fetchAll(
            "SELECT u.name, u.id,
                    COALESCE(SUM(d.deal_value),0) AS total_sales,
                    COALESCE(SUM(ai.earned_amount),0) AS total_earned
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN deals d ON d.agent_id = u.id
                              AND DATE_FORMAT(d.closing_date,'%Y-%m') = ?
                              AND d.status = 'Approved'
             LEFT JOIN agent_incentives ai ON ai.agent_id = u.id AND ai.payout_period = ?
             WHERE r.name = 'Agent'
             GROUP BY u.id, u.name
             ORDER BY total_sales DESC
             LIMIT ?",
            [$period, $period, $limit]
        );
    }

    public static function getMonthlySalesTrend(int $months = 6): array
    {
        return Database::fetchAll(
            "SELECT DATE_FORMAT(closing_date,'%Y-%m') AS period,
                    COUNT(*) AS deal_count,
                    COALESCE(SUM(deal_value),0) AS total_value
             FROM deals
             WHERE status = 'Approved'
               AND closing_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
             GROUP BY period
             ORDER BY period",
            [$months]
        );
    }
}
