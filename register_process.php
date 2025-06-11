<?php
// filepath: c:\xampp\htdocs\DBMS_final\register_process.php
// 設定編碼和啟動 session
header('Content-Type: text/html; charset=utf-8');
session_start();

// 引入資料庫連線設定
require_once 'db_config.php';

// 電子郵件驗證函數
function isValidEmail($email) {
    $pattern = "/^[^@\s]+@[^@\s]+\.[^@\s]+$/";
    $allowedDomains = [
        "gmail.com",
        "yahoo.com.tw",
        "hotmail.com",
        "outlook.com"
    ];

    if (!preg_match($pattern, $email)) {
        return false;
    }

    $parts = explode("@", $email);
    if (count($parts) != 2) return false;

    $domain = strtolower($parts[1]);
    return in_array($domain, $allowedDomains);
}

// 處理註冊表單提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = trim($_POST['username']);
    $user_email = trim($_POST['email']);
    $user_password = $_POST['password'];
    
    // 驗證資料
    $errors = [];
    
    if (empty($user_name)) {
        $errors[] = "使用者名稱不能為空";
    }
    
    if (empty($user_email)) {
        $errors[] = "電子郵件不能為空";
    } elseif (!isValidEmail($user_email)) {
        $errors[] = "電子郵件格式錯誤或網域不被接受。僅接受 gmail.com, yahoo.com.tw, hotmail.com, outlook.com";
    }
    
    if (empty($user_password)) {
        $errors[] = "密碼不能為空";
    } elseif (strlen($user_password) < 8) {
        $errors[] = "密碼長度至少需要8個字元";
    }
    
    // 檢查電子郵件是否已存在
    if (empty($errors)) {
        $check_email = $pdo->prepare("SELECT COUNT(*) FROM USER WHERE UEMAIL = ?");
        $check_email->execute([$user_email]);
        
        if ($check_email->fetchColumn() > 0) {
            $errors[] = "此電子郵件已被註冊";
        }
    }
    
    // 檢查用戶名是否已存在
    if (empty($errors)) {
        $check_username = $pdo->prepare("SELECT COUNT(*) FROM USER WHERE UUSERNAME = ?");
        $check_username->execute([$user_name]);
        
        if ($check_username->fetchColumn() > 0) {
            $errors[] = "此用戶名已被使用";
        }
    }
    
    // 如果沒有錯誤，進行註冊
    if (empty($errors)) {
        // 加鹽並加密密碼
        $salt = bin2hex(random_bytes(16)); // 產生隨機鹽值
        $hashed_password = hash('sha256', $user_password . $salt);
        
        try {
            // 插入新用戶，包含預設的經驗值和等級
            $stmt = $pdo->prepare("INSERT INTO USER (UUSERNAME, UEMAIL, UPASSWORD, UEXP, ULEVEL, UCREATED_AT) VALUES (?, ?, ?, 0, 0, NOW())");
            $stmt->execute([$user_name, $user_email, $salt . ':' . $hashed_password]);
            
            // 取得新插入用戶的 ID
            $new_user_id = $pdo->lastInsertId();
            
            // 清除任何現有的 session 資料
            $_SESSION = array();
            
            // 設定新用戶的 session 資料
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['username'] = $user_name;
            $_SESSION['email'] = $user_email;
            
            // 重新生成 session ID 以防止 session 劫持
            session_regenerate_id(true);
            
            // 註冊成功，重導向到 Home 頁面（現在已經自動登入）
            header("Location: Home.html?welcome=1");
            exit();
            
        } catch(PDOException $e) {
            error_log('Registration error: ' . $e->getMessage());
            $errors[] = "註冊失敗，請稍後再試";
        }
    }
    
    // 如果有錯誤，重導向回註冊頁面並顯示錯誤
    if (!empty($errors)) {
        $error_message = implode(", ", $errors);
        header("Location: register.html?error=" . urlencode($error_message));
        exit();
    }
}
?>
