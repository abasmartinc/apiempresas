<?php
$host = '217.61.210.127';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$dbname = 'reseller3537_apiempresas';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Obtener logs de emails de automatización
$emailsSent = [];
$sql = "SELECT user_id, type, created_at as sent_at FROM email_logs ORDER BY created_at ASC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $emailsSent[] = $row;
}

// 2. Obtener uso de API por usuario y fecha
$usage = [];
$sql = "SELECT user_id, date, requests_count FROM api_usage_daily ORDER BY date ASC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $usage[$row['user_id']][] = $row;
}

$analysis = [
    'summary' => [
        'total_emails_sent' => count($emailsSent),
        'unique_users_contacted' => count(array_unique(array_column($emailsSent, 'user_id'))),
        'reactivated_users' => 0,
        'first_time_users' => 0,
        'no_effect_users' => 0
    ],
    'user_details' => []
];

$userStatus = [];

foreach ($emailsSent as $email) {
    $userId = $email['user_id'];
    $sentDate = substr($email['sent_at'], 0, 10);
    
    if (!isset($userStatus[$userId])) {
        $userStatus[$userId] = [
            'has_usage_before' => false,
            'has_usage_after' => false,
            'usage_dates' => []
        ];
        
        if (isset($usage[$userId])) {
            foreach ($usage[$userId] as $u) {
                $userStatus[$userId]['usage_dates'][] = $u['date'];
                if ($u['date'] < $sentDate) {
                    $userStatus[$userId]['has_usage_before'] = true;
                }
                if ($u['date'] >= $sentDate) {
                    $userStatus[$userId]['has_usage_after'] = true;
                }
            }
        }
    }
}

foreach ($userStatus as $uid => $status) {
    if (!$status['has_usage_before'] && $status['has_usage_after']) {
        $analysis['summary']['first_time_users']++;
    } elseif ($status['has_usage_before'] && $status['has_usage_after']) {
        $analysis['summary']['reactivated_users']++;
    } else {
        $analysis['summary']['no_effect_users']++;
    }
}

echo json_encode($analysis, JSON_PRETTY_PRINT);
