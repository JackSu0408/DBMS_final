<?php
// filepath: c:\xampp\htdocs\DBMS_final\get_missions.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db_config.php';

try {
    // 檢查用戶是否已登入
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => '請先登入']);
        exit();
    }

    $userId = $_SESSION['user_id'];
    
    // 查詢任務資料 - 只取得當前用戶的任務並檢查是否已完成
    $stmt = $pdo->prepare("
        SELECT 
            m.*,
            CASE WHEN mc.MCID IS NOT NULL THEN 1 ELSE 0 END as is_completed
        FROM MISSION m
        LEFT JOIN MISSION_COMPLETED mc ON m.MID = mc.MCMISSION_ID AND mc.MCUSER_ID = ?
        WHERE m.MUSER_ID = ?
        ORDER BY m.MCREATED_AT DESC
    ");
    $stmt->execute([$userId, $userId]);
    
    $missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($missions);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>