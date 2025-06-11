<?php
// 開啟 session
session_start();

// 引入資料庫連線設定
require_once 'db_config.php';

// 處理登入表單提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_email = trim($_POST['email']);
    $user_password = $_POST['password'];
    
    // 驗證資料
    $errors = [];
    
    if (empty($user_email)) {
        $errors[] = "電子郵件不能為空";
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "電子郵件格式不正確";
    }
    
    if (empty($user_password)) {
        $errors[] = "密碼不能為空";
    }
    
    // 如果沒有錯誤，進行登入驗證
    if (empty($errors)) {
        try {
            // 從資料庫查詢使用者
            $stmt = $pdo->prepare("SELECT UID, UUSERNAME, UEMAIL, UPASSWORD, ULEVEL, UEXP FROM USER WHERE UEMAIL = ?");
            $stmt->execute([$user_email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // 分離鹽值和雜湊密碼
                $password_parts = explode(':', $user['UPASSWORD']);
                
                if (count($password_parts) === 2) {
                    $salt = $password_parts[0];
                    $stored_hash = $password_parts[1];
                    
                    // 驗證密碼
                    $input_hash = hash('sha256', $user_password . $salt);
                    
                    if ($input_hash === $stored_hash) {
                        // 登入成功，設定 session
                        $_SESSION['user_id'] = $user['UID'];
                        $_SESSION['username'] = $user['UUSERNAME'];
                        $_SESSION['email'] = $user['UEMAIL'];
                        $_SESSION['level'] = $user['ULEVEL'];
                        $_SESSION['exp'] = $user['UEXP'];
                        $_SESSION['logged_in'] = true;
                        
                        // 重導向到首頁
                        header("Location: Home.html");
                        exit();
                    } else {
                        $errors[] = "電子郵件或密碼錯誤";
                    }
                } else {
                    $errors[] = "密碼格式錯誤，請聯繫管理員";
                }
            } else {
                $errors[] = "電子郵件或密碼錯誤";
            }
            
        } catch(PDOException $e) {
            $errors[] = "登入失敗: " . $e->getMessage();
        }
    }
    
    // 如果有錯誤，重導向回登入頁面並顯示錯誤
    if (!empty($errors)) {
        $error_message = implode(", ", $errors);
        header("Location: index.html?error=" . urlencode($error_message));
        exit();
    }
}

// 如果不是 POST 請求，重導向回登入頁面
header("Location: index.html");
exit();
?>
