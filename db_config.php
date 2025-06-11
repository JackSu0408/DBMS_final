<?php
// 設定檔案編碼為 UTF-8
header('Content-Type: text/html; charset=utf-8');

// 資料庫連線設定
$host = '127.0.0.1';  // 資料庫主機名稱
$dbname = 'rpg_project';
$username = 'root';  // 請改成你的資料庫使用者名稱
$password = 'su08223020';      // 請改成你的資料庫密碼

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("資料庫連線失敗: " . $e->getMessage());
}
?>
