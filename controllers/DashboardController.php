<?php

class DashboardController
{
    public function index(mixed $id = null): void
    {
        Auth::require();

        $period = $_GET['period'] ?? currentPeriod();

        if (Auth::isAdmin() || Auth::isManager()) {
            $stats       = User::getAdminStats();
            $topAgents   = User::getTopAgents($period);
            $trend       = User::getMonthlySalesTrend(6);
            $recentDeals = Deal::getRecentDeals(8);
            $auditLog    = AuditLog::getRecent(10);

            render('dashboard/admin', compact('stats', 'topAgents', 'trend', 'recentDeals', 'auditLog', 'period'));
        } else {
            // Agent view
            $agentId = Auth::id();
            $summary = User::getAgentSummary($agentId, $period);
            $deals   = Deal::getAgentDeals($agentId, ['period' => $period]);
            $ledger  = AgentIncentive::getLedger(['agent_id' => $agentId, 'period' => $period]);

            // Last 6 months trend for agent
            $trend = Database::fetchAll(
                "SELECT DATE_FORMAT(closing_date,'%Y-%m') AS period,
                        COUNT(*) AS deal_count,
                        COALESCE(SUM(CASE WHEN status='Approved' THEN deal_value END),0) AS total_value
                 FROM deals WHERE agent_id = ?
                   AND closing_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                 GROUP BY period ORDER BY period",
                [$agentId]
            );

            render('dashboard/agent', compact('summary', 'deals', 'ledger', 'trend', 'period'));
        }
    }
}
