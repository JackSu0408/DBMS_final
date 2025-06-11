<?php
// 調試頁面 - 檢查 session 狀態
session_start();

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'session_data' => $_SESSION,
    'logged_in' => isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'not set',
    'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set',
    'username' => isset($_SESSION['username']) ? $_SESSION['username'] : 'not set'
]);
?>
