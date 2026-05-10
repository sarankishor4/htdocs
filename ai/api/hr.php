<?php
require_once '../core/Config.php';
require_once '../core/Database.php';
require_once '../core/AgentBase.php';
require_once '../agents/hr/Agent.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$command = $input['command'] ?? '';

$agent = new \AI\Agents\HR\Agent();
$response = $agent->execute($command);

echo json_encode([
    'status' => 'success',
    'agent' => 'HR',
    'response' => $response,
    'timestamp' => date('H:i:s')
]);
?>
