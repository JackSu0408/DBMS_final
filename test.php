<?php
echo json_encode([
    'message' => 'Test file is working!',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion()
]);
?>
