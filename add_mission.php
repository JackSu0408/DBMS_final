<?php
// filepath: c:\xampp\htdocs\DBMS_final\add_mission.php
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

    // 檢查是否為 POST 請求
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'Only POST method allowed']);
        exit();
    }

    $userId = $_SESSION['user_id'];
    
    // 獲取表單資料
    $MCATEGORY = trim($_POST['MCATEGORY'] ?? '');
    $MDESCRIPTION = trim($_POST['MDESCRIPTION'] ?? '');
    $MDIFFICULT = trim($_POST['MDIFFICULT'] ?? '');
    $MGOAL_EXP = intval($_POST['MGOAL_EXP'] ?? 0);

    // 驗證必填欄位
    if (empty($MCATEGORY) || empty($MDESCRIPTION) || empty($MDIFFICULT) || $MGOAL_EXP <= 0) {
        echo json_encode(['success' => false, 'error' => '所有欄位都是必填的，且數值必須大於0']);
        exit();
    }

    // 驗證難度與經驗值的對應關係
    $validCombinations = [
        'Easy' => 5,
        'Medium' => 10,
        'Hard' => 20
    ];

    if (!isset($validCombinations[$MDIFFICULT]) || $validCombinations[$MDIFFICULT] != $MGOAL_EXP) {
        echo json_encode(['success' => false, 'error' => '難度與經驗值組合無效']);
        exit();
    }

    // 準備 SQL 語句 - 加入用戶ID
    $stmt = $pdo->prepare("INSERT INTO MISSION (MCATEGORY, MDESCRIPTION, MDIFFICULT, MGOAL_EXP, MUSER_ID, MCREATED_AT) VALUES (?, ?, ?, ?, ?, NOW())");
    
    if ($stmt->execute([$MCATEGORY, $MDESCRIPTION, $MDIFFICULT, $MGOAL_EXP, $userId])) {
        echo json_encode([
            'success' => true, 
            'message' => '任務創建成功', 
            'mission_id' => $pdo->lastInsertId()
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => '任務創建失敗']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>