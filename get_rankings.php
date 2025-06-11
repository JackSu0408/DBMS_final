<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入資料庫連線設定
require_once 'db_config.php';

try {
    // 查詢前20名用戶的排行榜，按經驗值從高到低排序
    $stmt = $pdo->prepare("
        SELECT 
            UID,
            UUSERNAME,
            UEXP,
            ULEVEL,
            UCREATED_AT
        FROM USER 
        ORDER BY UEXP DESC 
        LIMIT 20
    ");
    
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 為每個用戶添加排名
    $rankedUsers = [];
    foreach ($users as $index => $user) {
        $user['rank'] = $index + 1;
        
        // 根據經驗值確定用戶的等級稱號
        $level_stmt = $pdo->prepare("
            SELECT LTLEVEL_NAME 
            FROM LEVEL_TITLE 
            WHERE LTNEED_EXP <= ? 
            ORDER BY LTNEED_EXP DESC 
            LIMIT 1
        ");
        $level_stmt->execute([$user['UEXP']]);
        $level_info = $level_stmt->fetch(PDO::FETCH_ASSOC);
        
        $user['level_title'] = $level_info ? $level_info['LTLEVEL_NAME'] : 'Novice Adventurer';
        
        $rankedUsers[] = $user;
    }
    
    echo json_encode([
        'success' => true,
        'rankings' => $rankedUsers
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
