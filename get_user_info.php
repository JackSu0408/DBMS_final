<?php
// 設定檔案編碼為 UTF-8
header('Content-Type: application/json; charset=utf-8');

// 開啟 session
session_start();

// 引入資料庫連線設定
require_once 'db_config.php';

// 檢查用戶是否已登入
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // 未登入，返回錯誤
    echo json_encode([
        'success' => false,
        'error' => '請先登入',
        'redirect' => 'index.html',
        'debug' => [
            'logged_in' => isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'not set',
            'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'
        ]
    ]);
    exit();
}

try {
    // 從資料庫取得最新的用戶資訊
    $stmt = $pdo->prepare("SELECT UID, UUSERNAME, UEMAIL, ULEVEL, UEXP, UCREATED_AT FROM USER WHERE UID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 添加調試資訊
    error_log("User query result: " . print_r($user, true));
    error_log("Session user_id: " . $_SESSION['user_id']);
    
    if ($user) {
        $current_exp = $user['UEXP'];
        
        // 根據經驗值確定用戶的實際等級和稱號
        $current_level_stmt = $pdo->prepare("SELECT LTLEVEL_NAME, LTNEED_EXP FROM LEVEL_TITLE WHERE LTNEED_EXP <= ? ORDER BY LTNEED_EXP DESC LIMIT 1");
        $current_level_stmt->execute([$current_exp]);
        $current_level_info = $current_level_stmt->fetch(PDO::FETCH_ASSOC);
        
        // 取得下一等級資訊
        $next_level_stmt = $pdo->prepare("SELECT LTNEED_EXP FROM LEVEL_TITLE WHERE LTNEED_EXP > ? ORDER BY LTNEED_EXP ASC LIMIT 1");
        $next_level_stmt->execute([$current_exp]);
        $next_level_info = $next_level_stmt->fetch(PDO::FETCH_ASSOC);
        
        // 設定等級資訊
        $level_title = $current_level_info ? $current_level_info['LTLEVEL_NAME'] : 'Novice Adventurer';
        $next_level_exp = $next_level_info ? $next_level_info['LTNEED_EXP'] : $current_exp;
        
        // 計算進度百分比
        $current_level_exp = $current_level_info ? $current_level_info['LTNEED_EXP'] : 0;
        if ($next_level_exp > $current_level_exp) {
            $exp_progress = (($current_exp - $current_level_exp) / ($next_level_exp - $current_level_exp)) * 100;
        } else {
            $exp_progress = 100; // 已達到最高等級
        }
        
        // 計算帳戶天數
        $created_date = new DateTime($user['UCREATED_AT']);
        $current_date = new DateTime();
        $days_active = $created_date->diff($current_date)->days + 1;
        
        // 取得已獲得的等級稱號（基於用戶當前經驗值）
        $level_titles_stmt = $pdo->prepare("SELECT LTLEVEL_NAME, LTTITLE_PICTURE FROM LEVEL_TITLE WHERE LTNEED_EXP <= ? ORDER BY LTNEED_EXP ASC");
        $level_titles_stmt->execute([$current_exp]);
        $earned_titles = $level_titles_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 計算任務統計 - 先檢查 MISSION 表是否存在
        try {
            $missions_completed_stmt = $pdo->prepare("SELECT COUNT(*) as completed FROM MISSION WHERE UID = ? AND MFINISHED_AT IS NOT NULL");
            $missions_completed_stmt->execute([$_SESSION['user_id']]);
            $missions_completed = $missions_completed_stmt->fetch(PDO::FETCH_ASSOC)['completed'];
            
            $total_missions_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM MISSION WHERE UID = ?");
            $total_missions_stmt->execute([$_SESSION['user_id']]);
            $total_missions = $total_missions_stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch(PDOException $mission_error) {
            // 如果 MISSION 表不存在，設為預設值
            $missions_completed = 0;
            $total_missions = 0;
            error_log("Mission table error: " . $mission_error->getMessage());
        }
        
        // 返回用戶資訊
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['UID'],
                'username' => $user['UUSERNAME'],
                'email' => $user['UEMAIL'],
                'level' => $user['ULEVEL'],
                'current_exp' => $current_exp,
                'next_level_exp' => $next_level_exp,
                'exp_progress' => round($exp_progress, 1),
                'level_title' => $level_title,
                'days_active' => $days_active,
                'missions_completed' => $missions_completed,
                'total_missions' => $total_missions,
                'earned_titles' => $earned_titles,
                'created_at' => $user['UCREATED_AT']
            ]
        ]);    } else {
        // 用戶不存在
        echo json_encode([
            'success' => false,
            'error' => '用戶資料不存在',
            'redirect' => 'index.html',
            'debug' => [
                'user_id_from_session' => $_SESSION['user_id'],
                'query_executed' => true
            ]
        ]);
    }
    
} catch(PDOException $e) {
    // 資料庫錯誤
    echo json_encode([
        'success' => false,
        'error' => '取得用戶資料失敗: ' . $e->getMessage()
    ]);
}
?>
