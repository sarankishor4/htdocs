<?php
namespace AI\Agents\Sales;

use AI\Core\AgentBase;

class Agent extends AgentBase {
    public function __construct() {
        parent::__construct();
        $this->name = "Flow";
        $this->role = "Sales";
    }

    public function execute($command) {
        $py = $this->callPythonBrain('agents/sales/brain.py', ['cmd' => $command]);
        return '[PY-SALES] ' . ($py['analysis'] ?? 'Analysis failed.');
    }
}
?>
