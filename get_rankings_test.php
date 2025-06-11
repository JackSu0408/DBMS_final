<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 模擬排行榜資料 - 用於測試
$mockRankings = [
    [
        'rank' => 1,
        'UID' => 1,
        'UUSERNAME' => '測試用戶1',
        'UEXP' => 5000,
        'level_title' => 'Expert Guardian'
    ],
    [
        'rank' => 2,
        'UID' => 2,
        'UUSERNAME' => '測試用戶2',
        'UEXP' => 3500,
        'level_title' => 'Legendary Hero'
    ],
    [
        'rank' => 3,
        'UID' => 3,
        'UUSERNAME' => '測試用戶3',
        'UEXP' => 2800,
        'level_title' => 'Elite Champion'
    ],
    [
        'rank' => 4,
        'UID' => 4,
        'UUSERNAME' => '測試用戶4',
        'UEXP' => 2200,
        'level_title' => 'Master Defender'
    ],
    [
        'rank' => 5,
        'UID' => 5,
        'UUSERNAME' => '測試用戶5',
        'UEXP' => 1800,
        'level_title' => 'Expert Guardian'
    ]
];

echo json_encode([
    'success' => true,
    'rankings' => $mockRankings,
    'message' => '這是模擬資料，用於測試排行榜功能'
]);
?>
