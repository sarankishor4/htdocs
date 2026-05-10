<?php
namespace AI\Core;

abstract class AgentBase {
    protected $db;
    protected $name;
    protected $role;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    abstract public function execute($command);

    protected function loadKnowledge() {
        $nodes = glob(__DIR__ . '/../knowledge/*.json');
        if (empty($nodes)) return [];
        $randomNode = $nodes[array_rand($nodes)];
        return json_decode(file_get_contents($randomNode), true);
    }

    protected function loadTask($id = null) {
        $tasks = glob(__DIR__ . "/../agents/{$this->role}/tasks/*.php");
        if (empty($tasks)) return null;
        $taskFile = $tasks[array_rand($tasks)];
        return include $taskFile;
    }

    protected function callPythonBrain($scriptPath, $data) {
        require_once __DIR__ . '/PythonBridge.php';
        return \AI\Core\PythonBridge::execute($scriptPath, $data);
    }

    protected function log($message, $type = 'info') {
        $msg = $this->db->escape($message);
        $role = $this->db->escape($this->role);
        $this->db->query("INSERT INTO logs (agent_name, message, type) VALUES ('$role', '$msg', '$type')");
    }

    public function getStatus() {
        return [
            'name' => $this->name,
            'role' => $this->role,
            'efficiency' => rand(85, 100)
        ];
    }
}
?>
