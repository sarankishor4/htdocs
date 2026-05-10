<?php
namespace AI\Agents\Finance;

use AI\Core\AgentBase;

class Agent extends AgentBase {
    public function __construct() {
        parent::__construct();
        $this->name = "Ledger";
        $this->role = "Finance";
    }

    public function execute($command) {
        $py = $this->callPythonBrain('agents/finance/brain.py', ['cmd' => $command]);
        return '[PY-FINANCE] ' . ($py['analysis'] ?? 'Analysis failed.');
    }
}
?>
