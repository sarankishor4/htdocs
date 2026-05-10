<?php
return [
    'DEPLOY_PROD' => [
        'title' => 'Production Deployment',
        'commands' => ['npm run build', 'git push origin main', 'service restart nexus'],
        'danger_level' => 3
    ],
    'PATCH_SECURITY' => [
        'title' => 'Core Security Patch',
        'commands' => ['audit fix', 'update firewall', 'regenerate keys'],
        'danger_level' => 5
    ],
    'CLEAN_LOGS' => [
        'title' => 'System Maintenance',
        'commands' => ['rm -rf ./temp/*', 'truncate -s 0 error.log'],
        'danger_level' => 1
    ]
];
?>
