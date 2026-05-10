<?php
namespace AI\Agents\HR;

use AI\Core\AgentBase;

class Agent extends AgentBase {
    public function __construct() {
        parent::__construct();
        $this->name = "Pulse";
        $this->role = "HR";
    }

    public function execute($command) {
        $py = $this->callPythonBrain('agents/hr/brain.py', ['cmd' => $command]);
        return '[PY-HR] ' . ($py['analysis'] ?? 'Analysis failed.');
    }
}
?>
