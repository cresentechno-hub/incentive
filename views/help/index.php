<?php $pageTitle = 'Help'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0"><i class="bi bi-question-circle me-2 text-primary"></i>Help &amp; User Guide</h4>
</div>
<p class="text-muted mb-4">A quick reference for every module in the Incentive Management System.</p>

<!-- ── Role Overview ─────────────────────────────────────── -->
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h5 class="fw-bold mb-3"><i class="bi bi-shield-check me-2 text-primary"></i>Role Overview</h5>
    <p class="text-muted small">There are three roles in the system. Each role sees only the pages they need.</p>
    <div class="table-responsive">
      <table class="table table-bordered table-sm mb-0">
        <thead class="table-light">
          <tr><th>Role</th><th>What they can do</th><th>Pages visible</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="badge bg-danger">Admin</span></td>
            <td>Full access — create/edit/delete agents, deals, rules, batches, and view all audit logs.</td>
            <td>Dashboard, Agents, Deals, Rules, Calculate, Ledger, Payouts, Help</td>
          </tr>
          <tr>
            <td><span class="badge bg-warning text-dark">Manager</span></td>
            <td>Approve or reject deals, run calculations, create payout batches. Cannot delete agents or rules.</td>
            <td>Dashboard, Agents (view), Deals, Rules, Calculate, Ledger, Payouts, Help</td>
          </tr>
          <tr>
            <td><span class="badge bg-info text-dark">Agent</span></td>
            <td>Submit their own deals and view their own earnings ledger. Cannot see other agents' data.</td>
            <td>Dashboard (personal), Deals (own), Ledger (own), Help</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── Deals ─────────────────────────────────────────────── -->
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h5 class="fw-bold mb-3"><i class="bi bi-briefcase me-2 text-primary"></i>Deals Module</h5>
    <p class="text-muted small">Deals are the transactions that drive incentive calculations. Every incentive starts from an <strong>Approved</strong> deal.</p>
    <div class="table-responsive mb-3">
      <table class="table table-hover table-sm mb-0">
        <thead class="table-light">
          <tr><th>Field</th><th>What to enter</th><th>Example</th></tr>
        </thead>
        <tbody>
          <?php
          $dealRows = [
              ['Client Name',   'The customer or company name for this deal.',                        'Acme Corp Sdn Bhd'],
              ['Quotation No',  'Your internal quotation or reference number.',                       'QT-2026-0142'],
              ['Deal Value',    'Total value of the deal in dollars. Numbers only, no commas.',       '15000.00'],
              ['Notes',         'Optional remarks — delivery terms, special conditions, etc.',        'Customer requires delivery by Oct 31.'],
              ['Agent',         'Admin/Manager only — assign the deal to any active agent. Agents are automatically assigned to themselves.', 'Alice Sales'],
          ];
          ?>
          <?php foreach ($dealRows as $r): ?>
          <tr>
            <td class="fw-semibold text-nowrap"><?= e($r[0]) ?></td>
            <td><?= e($r[1]) ?></td>
            <td class="text-muted text-nowrap"><?= e($r[2]) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <h6 class="fw-semibold mb-2">Deal Status Workflow</h6>
    <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
      <span class="badge bg-warning text-dark">Pending</span>
      <i class="bi bi-arrow-right text-muted"></i>
      <span class="badge bg-success">Approved</span>
      <span class="text-muted small mx-1">or</span>
      <span class="badge bg-danger">Rejected</span>
      <i class="bi bi-arrow-right text-muted"></i>
      <span class="badge bg-primary">Paid</span>
    </div>
    <ul class="small text-muted mb-0">
      <li><strong>Pending</strong> — newly created; awaiting Admin/Manager review.</li>
      <li><strong>Approved</strong> — confirmed; eligible for incentive calculation.</li>
      <li><strong>Rejected</strong> — excluded from calculations.</li>
      <li><strong>Paid</strong> — automatically set when its payout batch is completed.</li>
    </ul>
  </div>
</div>

<!-- ── Incentive Rules ────────────────────────────────────── -->
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h5 class="fw-bold mb-3"><i class="bi bi-sliders me-2 text-primary"></i>Incentive Rules <span class="badge bg-secondary ms-1" style="font-size:.7rem">Admin / Manager</span></h5>
    <p class="text-muted small">Rules define how much an agent earns per deal. Multiple rules can match a single deal — all matching rules create separate ledger entries.</p>
    <div class="table-responsive mb-3">
      <table class="table table-bordered table-sm mb-0">
        <thead class="table-light">
          <tr><th>Rule Type</th><th>How it works</th><th>Example</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="badge bg-info text-dark">flat</span></td>
            <td>Pays a fixed dollar amount when the deal value falls within the min–max threshold.</td>
            <td>Deal ≥ $1,000 → earn $50 flat</td>
          </tr>
          <tr>
            <td><span class="badge bg-primary">percentage</span></td>
            <td>Pays a percentage of the deal value when the deal value falls within the min–max threshold.</td>
            <td>Deal ≥ $0 → earn 5% of deal value</td>
          </tr>
          <tr>
            <td><span class="badge bg-warning text-dark">tier</span></td>
            <td>Pays a percentage of the deal value when the agent's <em>total approved sales for the month</em> falls within the min–max threshold.</td>
            <td>Monthly total $10k–$25k → earn 4% kicker on each deal</td>
          </tr>
        </tbody>
      </table>
    </div>
    <h6 class="fw-semibold mb-2">Rule Fields</h6>
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0">
        <thead class="table-light">
          <tr><th>Field</th><th>Description</th></tr>
        </thead>
        <tbody>
          <?php
          $ruleRows = [
              ['Rule Name',      'A short descriptive label, e.g. "Standard Commission" or "Tier Gold".'],
              ['Rule Type',      'flat, percentage, or tier (see above).'],
              ['Rate Value',     'The dollar amount (flat) or percentage rate (percentage / tier).'],
              ['Min Threshold',  'Minimum deal value (or monthly total for tier) to trigger this rule. Set 0 for no minimum.'],
              ['Max Threshold',  'Maximum deal value (or monthly total for tier). Leave blank for no upper limit.'],
              ['Start Date',     'The date from which this rule applies. Deals closing on or after this date are eligible.'],
              ['End Date',       'Optional end date. Leave blank for an ongoing rule.'],
              ['Description',    'Optional notes explaining the purpose of the rule.'],
          ];
          ?>
          <?php foreach ($ruleRows as $r): ?>
          <tr>
            <td class="fw-semibold text-nowrap"><?= e($r[0]) ?></td>
            <td class="text-muted small"><?= e($r[1]) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="text-muted small mt-2 mb-0">
      <i class="bi bi-info-circle me-1"></i>
      Deleting a rule that already has ledger entries will <strong>deactivate</strong> it instead of hard-deleting, to preserve the history.
    </p>
  </div>
</div>

<!-- ── Run Calculation ───────────────────────────────────── -->
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h5 class="fw-bold mb-3"><i class="bi bi-calculator me-2 text-primary"></i>Run Calculation <span class="badge bg-secondary ms-1" style="font-size:.7rem">Admin / Manager</span></h5>
    <p class="text-muted small">
      The calculation engine evaluates every <strong>Approved</strong> deal in the selected month against all active incentive rules and creates ledger entries for each matching rule.
    </p>
    <ol class="small mb-2">
      <li>Go to <strong>Incentives → Calculate</strong>.</li>
      <li>Select the period (month) to process — quick-select buttons show periods that have approved deals.</li>
      <li>Click <strong>Run Calculation</strong>. The system skips deal + rule combinations that were already calculated, so it is safe to re-run.</li>
      <li>Review the results in the <strong>Ledger</strong>.</li>
    </ol>
    <p class="text-muted small mb-0">
      <i class="bi bi-info-circle me-1"></i>
      Tier rules use the agent's <em>cumulative approved sales for the month</em> to determine which tier applies, not the individual deal value.
    </p>
  </div>
</div>

<!-- ── Incentive Ledger ──────────────────────────────────── -->
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h5 class="fw-bold mb-3"><i class="bi bi-journal-text me-2 text-primary"></i>Incentive Ledger</h5>
    <p class="text-muted small">
      The ledger shows every incentive entry created by the calculation engine. Agents see only their own entries. Admin/Manager see all agents and can filter by period, agent, or status.
    </p>
    <div class="table-responsive">
      <table class="table table-bordered table-sm mb-0">
        <thead class="table-light">
          <tr><th>Status</th><th>Meaning</th></tr>
        </thead>
        <tbody>
          <tr><td><span class="badge bg-warning text-dark">pending</span></td><td>Calculated but not yet in a payout batch.</td></tr>
          <tr><td><span class="badge bg-success">approved</span></td><td>Included in a draft payout batch.</td></tr>
          <tr><td><span class="badge bg-danger">rejected</span></td><td>Manually overridden and excluded from payouts.</td></tr>
          <tr><td><span class="badge bg-primary">paid</span></td><td>Payout batch has been completed.</td></tr>
        </tbody>
      </table>
    </div>
    <p class="text-muted small mt-2 mb-0">Admin/Manager can individually approve or reject ledger entries before creating a payout batch.</p>
  </div>
</div>

<!-- ── Payouts ────────────────────────────────────────────── -->
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h5 class="fw-bold mb-3"><i class="bi bi-cash-stack me-2 text-primary"></i>Payout Batches <span class="badge bg-secondary ms-1" style="font-size:.7rem">Admin / Manager</span></h5>
    <p class="text-muted small">A payout batch groups all approved incentives for a period into a single payment run.</p>
    <ol class="small mb-3">
      <li>Go to <strong>Payouts → New Batch</strong>.</li>
      <li>Select the period. Periods with pending incentives are highlighted.</li>
      <li>Click <strong>Create Batch</strong> — all pending incentives for that period move to <em>approved</em> status and are locked into the batch.</li>
      <li>Review the batch. Use <strong>Export CSV</strong> to download the payout sheet.</li>
      <li>Once payment is confirmed, click <strong>Complete</strong> — all incentives move to <em>paid</em> and the linked deals are marked <em>Paid</em>.</li>
      <li>To undo before completing, click <strong>Cancel</strong> — incentives return to <em>pending</em>.</li>
    </ol>
    <div class="table-responsive">
      <table class="table table-bordered table-sm mb-0">
        <thead class="table-light">
          <tr><th>Batch Status</th><th>Meaning</th></tr>
        </thead>
        <tbody>
          <tr><td><span class="badge bg-warning text-dark">draft</span></td><td>Created but not yet completed. Can still be cancelled.</td></tr>
          <tr><td><span class="badge bg-success">completed</span></td><td>Payment confirmed. All linked incentives are marked paid.</td></tr>
          <tr><td><span class="badge bg-danger">cancelled</span></td><td>Voided. Incentives returned to pending.</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── Agents ─────────────────────────────────────────────── -->
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h5 class="fw-bold mb-3"><i class="bi bi-people me-2 text-primary"></i>Agents Module <span class="badge bg-secondary ms-1" style="font-size:.7rem">Admin / Manager</span></h5>
    <p class="text-muted small">Manage salesperson accounts. Each agent can log in to view their own deals and earnings.</p>
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0">
        <thead class="table-light">
          <tr><th>Field</th><th>Description</th><th>Example</th></tr>
        </thead>
        <tbody>
          <?php
          $agentRows = [
              ['Name',     'Full name of the agent.',                           'Alice Sales'],
              ['Email',    'Login email. Must be unique.',                      'alice@company.com'],
              ['Role',     'Agent, Manager, or Admin.',                         'Agent'],
              ['Team',     'Optional team grouping for reporting.',              'North Region'],
              ['Password', 'Set on create. Agent can request a reset from Admin.', '—'],
          ];
          ?>
          <?php foreach ($agentRows as $r): ?>
          <tr>
            <td class="fw-semibold text-nowrap"><?= e($r[0]) ?></td>
            <td class="text-muted small"><?= e($r[1]) ?></td>
            <td class="text-muted text-nowrap"><?= e($r[2]) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── Typical Workflow ───────────────────────────────────── -->
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h5 class="fw-bold mb-3"><i class="bi bi-arrow-repeat me-2 text-primary"></i>Typical Monthly Workflow</h5>
    <ol class="mb-0">
      <li class="mb-1"><strong>Agents</strong> submit deals during the month via <em>Deals → New Deal</em>.</li>
      <li class="mb-1"><strong>Manager / Admin</strong> approves deals as they come in via the Deals list.</li>
      <li class="mb-1">At month end, <strong>Admin / Manager</strong> goes to <em>Incentives → Calculate</em>, selects the month, and clicks <strong>Run Calculation</strong>.</li>
      <li class="mb-1">Review the <strong>Ledger</strong> — adjust individual entries if needed (approve/reject overrides).</li>
      <li class="mb-1">Go to <strong>Payouts → New Batch</strong>, create a batch for the month.</li>
      <li class="mb-1"><strong>Export CSV</strong> to process payments externally.</li>
      <li class="mb-0">Once paid, click <strong>Complete</strong> on the batch to lock everything as paid.</li>
    </ol>
  </div>
</div>
