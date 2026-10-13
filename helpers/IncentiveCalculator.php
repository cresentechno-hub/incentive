<?php

/**
 * Evaluates approved deals against active incentive rules
 * and writes earned amounts into the agent_incentives ledger.
 *
 * Rule types:
 *   flat       — earn a fixed `rate_value` if deal_value is within [min, max] threshold
 *   percentage — earn rate_value% of deal_value if deal_value is within [min, max]
 *   tier       — earn rate_value% of deal_value if agent's total approved period sales
 *                fall within [min_threshold, max_threshold]
 */
class IncentiveCalculator
{
    private string $period;
    private array  $activeRules;

    public function __construct(string $period = null)
    {
        $this->period      = $period ?? currentPeriod();
        $this->activeRules = IncentiveRule::getActive();
    }

    /**
     * Calculate incentives for a single approved deal.
     * Returns an array of result records (one per matching rule).
     */
    public function calculateForDeal(array $deal): array
    {
        if ($deal['status'] !== 'Approved') {
            return [];
        }

        $results    = [];
        $agentId    = (int)$deal['agent_id'];
        $dealPeriod = date('Y-m', strtotime($deal['closing_date']));
        $dealValue  = (float)$deal['deal_value'];

        // Cache period total per agent to avoid repeated DB calls
        $periodTotal = $this->getPeriodTotal($agentId, $dealPeriod);

        foreach ($this->activeRules as $rule) {
            // Skip if already calculated for this deal+rule pair
            if (AgentIncentive::existsForDeal((int)$deal['id'], (int)$rule['id'])) {
                continue;
            }

            $earned = $this->applyRule($rule, $dealValue, $periodTotal);

            if ($earned > 0.00) {
                $notes = $this->buildNotes($rule, $deal, $earned, $periodTotal);

                $incentiveId = AgentIncentive::insert([
                    'agent_id'          => $agentId,
                    'deal_id'           => (int)$deal['id'],
                    'rule_id'           => (int)$rule['id'],
                    'earned_amount'     => round($earned, 2),
                    'status'            => 'pending',
                    'calculation_notes' => $notes,
                    'payout_period'     => $dealPeriod,
                ]);

                AuditLog::record('INCENTIVE_CALCULATED', 'agent_incentives', $incentiveId, [], [
                    'agent_id'  => $agentId,
                    'deal_id'   => $deal['id'],
                    'rule_id'   => $rule['id'],
                    'rule_name' => $rule['name'],
                    'earned'    => round($earned, 2),
                ]);

                $results[] = [
                    'incentive_id' => $incentiveId,
                    'rule_name'    => $rule['name'],
                    'rule_type'    => $rule['rule_type'],
                    'earned_amount'=> round($earned, 2),
                    'notes'        => $notes,
                ];
            }
        }

        return $results;
    }

    /**
     * Process all approved deals for a given period.
     * Returns a summary keyed by agent_id.
     */
    public function calculateForPeriod(string $period): array
    {
        $this->period      = $period;
        $this->activeRules = IncentiveRule::getActive();

        $deals = Database::fetchAll(
            "SELECT * FROM deals
             WHERE DATE_FORMAT(closing_date,'%Y-%m') = ?
               AND status = 'Approved'
             ORDER BY agent_id, closing_date",
            [$period]
        );

        $summary    = [];
        $processed  = 0;
        $totalEarned = 0.00;

        foreach ($deals as $deal) {
            $results = $this->calculateForDeal($deal);
            if (!empty($results)) {
                $agentId = (int)$deal['agent_id'];
                foreach ($results as $r) {
                    $totalEarned += $r['earned_amount'];
                    $summary[$agentId][] = $r;
                }
                $processed++;
            }
        }

        return [
            'period'        => $period,
            'deals_scanned' => count($deals),
            'deals_processed'=> $processed,
            'total_earned'  => round($totalEarned, 2),
            'by_agent'      => $summary,
        ];
    }

    // ─── Private helpers ─────────────────────────────────────

    private function applyRule(array $rule, float $dealValue, float $periodTotal): float
    {
        $min  = (float)$rule['min_threshold'];
        $max  = $rule['max_threshold'] !== null ? (float)$rule['max_threshold'] : PHP_FLOAT_MAX;
        $rate = (float)$rule['rate_value'];

        switch ($rule['rule_type']) {

            case 'flat':
                // Target: deal_value in [min, max] → earn fixed rate_value
                if ($dealValue >= $min && $dealValue <= $max) {
                    return $rate;
                }
                break;

            case 'percentage':
                // Target: deal_value in [min, max] → earn rate% of deal_value
                if ($dealValue >= $min && $dealValue <= $max) {
                    return ($rate / 100) * $dealValue;
                }
                break;

            case 'tier':
                // Target: agent's period_total in [min, max] → earn rate% of deal_value
                if ($periodTotal >= $min && $periodTotal <= $max) {
                    return ($rate / 100) * $dealValue;
                }
                break;
        }

        return 0.00;
    }

    /** Cache period totals to minimise DB round-trips. */
    private array $periodTotalCache = [];

    private function getPeriodTotal(int $agentId, string $period): float
    {
        $key = "{$agentId}:{$period}";
        if (!isset($this->periodTotalCache[$key])) {
            $this->periodTotalCache[$key] = Deal::getAgentPeriodTotal($agentId, $period);
        }
        return $this->periodTotalCache[$key];
    }

    private function buildNotes(array $rule, array $deal, float $earned, float $periodTotal): string
    {
        return sprintf(
            'Rule: "%s" (%s) | Deal #%d ($%.2f) | Period Total: $%.2f | Earned: $%.2f',
            $rule['name'],
            $rule['rule_type'],
            $deal['id'],
            $deal['deal_value'],
            $periodTotal,
            $earned
        );
    }
}
