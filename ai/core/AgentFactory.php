<?php
namespace AI\Core;

class AgentFactory {
    public static function getAgent($id) {
        $id = strtolower($id);
        
        switch ($id) {
            case 'ceo':
                require_once __DIR__ . '/../agents/ceo/Agent.php';
                return new \AI\Agents\CEO\Agent();
            case 'dev':
                require_once __DIR__ . '/../agents/dev/Agent.php';
                return new \AI\Agents\Dev\Agent();
            case 'marketing':
                require_once __DIR__ . '/../agents/marketing/Agent.php';
                return new \AI\Agents\Marketing\Agent();
            case 'finance':
                require_once __DIR__ . '/../agents/finance/Agent.php';
                return new \AI\Agents\Finance\Agent();
            case 'sales':
                require_once __DIR__ . '/../agents/sales/Agent.php';
                return new \AI\Agents\Sales\Agent();
            case 'hr':
                require_once __DIR__ . '/../agents/hr/Agent.php';
                return new \AI\Agents\HR\Agent();
            case 'social':
                require_once __DIR__ . '/../agents/social/Agent.php';
                return new \AI\Agents\Social\Agent();
            // Other agents will be added here
            default:
                // Fallback to a generic agent or throw error
                return null;
        }
    }
}
?>
