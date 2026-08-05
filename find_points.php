<?php
$lines = file('C:/Users/papel/.gemini/antigravity/brain/479424cd-7c88-46f1-8256-375678fd1bea/.system_generated/logs/transcript.jsonl');
$found_user_prompt = false;
foreach($lines as $line) {
    $data = json_decode($line, true);
    if ($data['type'] === 'USER_INPUT' && strpos($data['content'], 'esta es la pagina de billing') !== false) {
        $found_user_prompt = true;
    }
    if ($found_user_prompt && $data['type'] === 'PLANNER_RESPONSE' && isset($data['content'])) {
        echo "Found response:\n" . substr($data['content'], 0, 5000) . "\n";
        break;
    }
}
