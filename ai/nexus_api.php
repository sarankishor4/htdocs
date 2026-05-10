<?php
require_once 'core/Config.php';
require_once 'core/Database.php';
require_once 'core/AgentBase.php';
require_once 'core/AgentFactory.php';
require_once 'core/Router.php';

use AI\Core\Router;

header('Content-Type: application/json');

echo json_encode(Router::handleRequest());
