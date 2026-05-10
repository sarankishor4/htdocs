<?php
namespace AI\Core;

class PythonBridge {
    public static function execute($scriptPath, $args = []) {
        $fullPath = realpath(__DIR__ . '/../' . $scriptPath);
        if (!$fullPath) return "Error: Python script not found at $scriptPath";

        $jsonArgs = escapeshellarg(json_encode($args));
        $command = "python $fullPath $jsonArgs";
        
        $output = shell_exec($command);
        return json_decode($output, true) ?? $output;
    }
}
?>
