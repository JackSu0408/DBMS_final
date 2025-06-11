<?php
// 設定檔案編碼為 UTF-8
header('Content-Type: application/json; charset=utf-8');

// 開啟 session
session_start();

// 引入資料庫連線設定
require_once 'db_config.php';

// 檢查用戶是否已登入
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode([
        'success' => false,
        'error' => '請先登入',
        'redirect' => 'index.html'
    ]);
    exit();
}

// 檢查是否為 POST 請求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => '無效的請求方法'
    ]);
    exit();
}

// 取得 POST 資料
$input = json_decode(file_get_contents('php://input'), true);
$exp_amount = isset($input['exp_amount']) ? (int)$input['exp_amount'] : 0;

if ($exp_amount <= 0) {
    echo json_encode([
        'success' => false,
        'error' => '經驗值必須大於 0'
    ]);
    exit();
}

try {
    // 開始交易
    $pdo->beginTransaction();
    
    // 取得用戶當前經驗值
    $stmt = $pdo->prepare("SELECT UEXP FROM USER WHERE UID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('用戶不存在');
    }
    
    $new_exp = $user['UEXP'] + $exp_amount;
    
    // 更新用戶經驗值
    $update_stmt = $pdo->prepare("UPDATE USER SET UEXP = ? WHERE UID = ?");
    $update_stmt->execute([$new_exp, $_SESSION['user_id']]);
    
    // 提交交易
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "成功增加 {$exp_amount} 經驗值",
        'new_exp' => $new_exp
    ]);
    
} catch(Exception $e) {
    // 回滾交易
    $pdo->rollback();
    
    echo json_encode([
        'success' => false,
        'error' => '更新經驗值失敗: ' . $e->getMessage()
    ]);
}
?>
