<?php
// filepath: c:\xampp\htdocs\DBMS_final\get_missions.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 資料庫連線設定
require_once 'db_config.php';

try {
    // 查詢任務資料
    $stmt = $pdo->prepare("SELECT * FROM MISSION ORDER BY MCREATED_AT DESC");
    $stmt->execute();
    
    $missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($missions);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>