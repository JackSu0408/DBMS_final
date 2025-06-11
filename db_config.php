<?php
// 資料庫連線設定
$host = 'localhost';
$dbname = 'rpg_project';
$username = 'Qian';  // 請改成你的資料庫使用者名稱
$password = 'admin0102';      // 請改成你的資料庫密碼

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("資料庫連線失敗: " . $e->getMessage());
}
?>
