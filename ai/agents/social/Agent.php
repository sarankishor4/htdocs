<?php
namespace AI\Agents\Social;

use AI\Core\AgentBase;

class Agent extends AgentBase {
    public function __construct() {
        parent::__construct();
        $this->name = "Sphere";
        $this->role = "Social";
    }

    public function execute($command) {
        $py = $this->callPythonBrain('agents/social/brain.py', ['cmd' => $command]);
        return '[PY-SOCIAL] ' . ($py['analysis'] ?? 'Analysis failed.');
    }
}
?>
