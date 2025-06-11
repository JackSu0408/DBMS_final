<?php
// filepath: c:\xampp\htdocs\DBMS_final\add_mission.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 資料庫連線設定
require_once 'db_config.php';

try {
    // 檢查是否為 POST 請求
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'Only POST method allowed']);
        exit();
    }

    // 獲取表單資料
    $MCATEGORY = trim($_POST['MCATEGORY'] ?? '');
    $MDESCRIPTION = trim($_POST['MDESCRIPTION'] ?? '');
    $MDIFFICULT = trim($_POST['MDIFFICULT'] ?? '');
    $MGOAL_EXP = intval($_POST['MGOAL_EXP'] ?? 0);
    $MGOAL_LEVEL = intval($_POST['MGOAL_LEVEL'] ?? 0);

    // 驗證必填欄位
    if (empty($MCATEGORY) || empty($MDESCRIPTION) || empty($MDIFFICULT) || $MGOAL_EXP <= 0 || $MGOAL_LEVEL <= 0) {
        echo json_encode(['success' => false, 'error' => 'All fields are required and numbers must be greater than 0']);
        exit();
    }

    // 準備 SQL 語句
    $stmt = $pdo->prepare("INSERT INTO MISSION (MCATEGORY, MDESCRIPTION, MDIFFICULT, MGOAL_EXP, MGOAL_LEVEL, MCREATED_AT) VALUES (?, ?, ?, ?, ?, NOW())");
    
    if ($stmt->execute([$MCATEGORY, $MDESCRIPTION, $MDIFFICULT, $MGOAL_EXP, $MGOAL_LEVEL])) {
        echo json_encode(['success' => true, 'message' => 'Mission created successfully', 'mission_id' => $pdo->lastInsertId()]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to insert mission']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>