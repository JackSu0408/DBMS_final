USE rpg_project;

-- 顯示目前有哪些資料表
SHOW TABLES;

-- 查看 user 表的結構
DESCRIBE user;

-- 插入一筆使用者測試資料
INSERT INTO user (UUSERNAME, UEMAIL)
VALUES ('testuser', 'test@example.com');

-- 查詢該表的內容
SELECT * FROM user;
