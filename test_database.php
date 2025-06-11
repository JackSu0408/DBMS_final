<?php
// 測試檔案 - 檢查資料庫表格和資料
header('Content-Type: application/json; charset=utf-8');

require_once 'db_config.php';

try {
    $tests = [];
    
    // 檢查 USER 表
    $user_count = $pdo->query("SELECT COUNT(*) FROM USER")->fetchColumn();
    $tests['user_table'] = [
        'exists' => true,
        'count' => $user_count
    ];
    
    // 檢查 LEVEL_TITLE 表
    try {
        $level_count = $pdo->query("SELECT COUNT(*) FROM LEVEL_TITLE")->fetchColumn();
        $tests['level_title_table'] = [
            'exists' => true,
            'count' => $level_count
        ];
        
        // 獲取一些等級稱號資料
        $stmt = $pdo->query("SELECT LTLEVEL_NAME, LTNEED_EXP FROM LEVEL_TITLE ORDER BY LTNEED_EXP LIMIT 5");
        $tests['level_titles_sample'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $tests['level_title_table'] = [
            'exists' => false,
            'error' => $e->getMessage()
        ];
    }
    
    // 檢查 MISSION 表
    try {
        $mission_count = $pdo->query("SELECT COUNT(*) FROM MISSION")->fetchColumn();
        $tests['mission_table'] = [
            'exists' => true,
            'count' => $mission_count
        ];
    } catch(PDOException $e) {
        $tests['mission_table'] = [
            'exists' => false,
            'error' => $e->getMessage()
        ];
    }
    
    // 獲取一些用戶資料 (不含密碼)
    $stmt = $pdo->query("SELECT UID, UUSERNAME, UEMAIL, UEXP FROM USER LIMIT 3");
    $tests['users_sample'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'tests' => $tests
    ], JSON_PRETTY_PRINT);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
