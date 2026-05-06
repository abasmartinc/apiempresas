<?php
$host = '217.61.210.127';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$dbname = 'reseller3537_apiempresas';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Obtener registros de automatización
$automations = [];
$sql = "SELECT user_id, email_type, sent_at FROM user_email_automation WHERE sent_at IS NOT NULL ORDER BY sent_at ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $automations[] = $row;
    }
}

// 2. Obtener uso de API
$usage = [];
$sql = "SELECT user_id, date, requests_count FROM api_usage_daily ORDER BY date ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $usage[$row['user_id']][] = $row;
    }
}

// 3. Analizar impacto
$impact = [
    'summary' => [
        'total_automations_sent' => count($automations),
        'unique_users_contacted' => 0,
        'conversions_after_email' => 0,
        'first_time_activations' => 0,
        'reactivations' => 0,
        'no_usage_after' => 0
    ],
    'by_type' => []
];

$userFirstAutomation = [];
foreach ($automations as $auto) {
    $uid = $auto['user_id'];
    if (!isset($userFirstAutomation[$uid]) || $auto['sent_at'] < $userFirstAutomation[$uid]['sent_at']) {
        $userFirstAutomation[$uid] = $auto;
    }
    
    $type = $auto['email_type'];
    if (!isset($impact['by_type'][$type])) {
        $impact['by_type'][$type] = ['sent' => 0, 'active_after' => 0];
    }
    $impact['by_type'][$type]['sent']++;
}

$impact['summary']['unique_users_contacted'] = count($userFirstAutomation);

foreach ($userFirstAutomation as $uid => $auto) {
    $sentDate = substr($auto['sent_at'], 0, 10);
    $hasUsageBefore = false;
    $hasUsageAfter = false;
    
    if (isset($usage[$uid])) {
        foreach ($usage[$uid] as $u) {
            if ($u['date'] < $sentDate) {
                $hasUsageBefore = true;
            }
            if ($u['date'] >= $sentDate) {
                $hasUsageAfter = true;
            }
        }
    }
    
    if ($hasUsageAfter) {
        $impact['summary']['conversions_after_email']++;
        if (!$hasUsageBefore) {
            $impact['summary']['first_time_activations']++;
        } else {
            $impact['summary']['reactivations']++;
        }
        
        // Atribuir al tipo de email (simplificado al primero)
        $impact['by_type'][$auto['email_type']]['active_after']++;
    } else {
        $impact['summary']['no_usage_after']++;
    }
}

header('Content-Type: application/json');
echo json_encode($impact, JSON_PRETTY_PRINT);
