<?php
namespace AI\Core;

class Router {
    public static function handleRequest() {
        $input = json_decode(file_get_contents('php://input'), true);
        $command = $input['command'] ?? '';
        $dept = $input['department'] ?? 'ceo';
        
        if (empty($command)) {
            return ['status' => 'error', 'message' => 'Matrix error: Command null.'];
        }

        $agent = AgentFactory::getAgent($dept);
        if ($agent) {
            return [
                'status' => 'success',
                'agent' => $dept,
                'response' => $agent->execute($command),
                'status_data' => $agent->getStatus(),
                'timestamp' => date('H:i:s')
            ];
        }

        return ['status' => 'error', 'message' => "Agent $dept offline."];
    }
}
?>
