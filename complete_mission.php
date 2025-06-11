<?php
// filepath: c:\xampp\htdocs\DBMS_final\complete_mission.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 資料庫連線設定
require_once 'db_config.php';

try {
    // 檢查用戶是否已登入
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => '請先登入']);
        exit();
    }

    // 檢查是否為 POST 請求
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'Only POST method allowed']);
        exit();
    }

    // 獲取 JSON 資料
    $input = json_decode(file_get_contents('php://input'), true);
    $missionId = intval($input['mission_id'] ?? 0);
    $expGained = intval($input['exp_gained'] ?? 0);
    $userId = $_SESSION['user_id'];

    // 驗證資料
    if ($missionId <= 0 || $expGained <= 0) {
        echo json_encode(['success' => false, 'error' => '無效的任務資料']);
        exit();
    }

    // 開始事務
    $pdo->beginTransaction();

    // 驗證任務是否存在
    $stmt = $pdo->prepare("SELECT MGOAL_EXP FROM MISSION WHERE MID = ?");
    $stmt->execute([$missionId]);
    $mission = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mission) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => '任務不存在']);
        exit();
    }

    // 驗證經驗值是否正確
    if ($mission['MGOAL_EXP'] != $expGained) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => '經驗值不符']);
        exit();
    }

    // 檢查是否已完成過此任務
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM MISSION_COMPLETED WHERE MCUSER_ID = ? AND MCMISSION_ID = ?");
    $stmt->execute([$userId, $missionId]);
    if ($stmt->fetchColumn() > 0) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => '此任務已完成過']);
        exit();
    }

    // 獲取用戶目前資料
    $stmt = $pdo->prepare("SELECT UEXP, ULEVEL FROM USER WHERE UID = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => '用戶不存在']);
        exit();
    }

    $currentExp = intval($user['UEXP']);
    $currentLevel = intval($user['ULEVEL']);
    $newExp = $currentExp + $expGained;

    // 計算新等級
    $newLevel = calculateLevel($newExp);
    $levelUp = $newLevel > $currentLevel;

    // 更新用戶經驗值和等級
    $stmt = $pdo->prepare("UPDATE USER SET UEXP = ?, ULEVEL = ? WHERE UID = ?");
    $stmt->execute([$newExp, $newLevel, $userId]);

    // 記錄任務完成
    $stmt = $pdo->prepare("INSERT INTO MISSION_COMPLETED (MCUSER_ID, MCMISSION_ID, MCCOMPLETED_AT, MCEXP_GAINED) VALUES (?, ?, NOW(), ?)");
    $stmt->execute([$userId, $missionId, $expGained]);

    // 獲取新等級稱號
    $newTitle = '';
    if ($levelUp) {
        // 修正：確保使用正確的欄位名稱
        $stmt = $pdo->prepare("SELECT LTLEVEL_NAME FROM LEVEL_TITLE WHERE LTLEVEL_NAME = ?");
        $stmt->execute([$newLevel]);
        $levelTitle = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($levelTitle) {
            $newTitle = $levelTitle['LTLEVEL_NAME'];
        } else {
            // 如果找不到對應等級的稱號，使用預設格式
            $newTitle = "等級 $newLevel";
        }
    }

    // 提交事務
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => '任務完成成功',
        'exp_gained' => $expGained,
        'new_exp' => $newExp,
        'level_up' => $levelUp,
        'new_level' => $newLevel,
        'new_title' => $newTitle,
        'old_level' => $currentLevel // 加入舊等級資訊
    ]);

} catch (Exception $e) {
    // 回滾事務
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// 計算等級函數
function calculateLevel($exp) {
    if ($exp < 10) return 0;
    if ($exp < 30) return 1;
    if ($exp < 60) return 2;
    if ($exp < 100) return 3;
    if ($exp < 150) return 4;
    if ($exp < 300) return 5;
    if ($exp < 600) return 6;
    if ($exp < 1200) return 7;
    if ($exp < 2400) return 8;
    if ($exp < 3000) return 9;
    if ($exp < 3500) return 10;
    if ($exp < 4500) return 11;
    if ($exp < 6000) return 12;
    if ($exp < 12000) return 13;
    if ($exp < 30000) return 14;
    return 15; // 最高等級
}
?>