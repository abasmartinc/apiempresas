<?php
namespace App\Strategies\Sms;

use Telnyx\Telnyx;
use Config\SmsConfig;
use App\Interfaces\SmsProviderInterface;

class TelnyxSmsStrategy implements SmsProviderInterface {
    private $from;

    public function __construct() {
        Telnyx::setApiKey(SmsConfig::TELNYX_API_KEY);
        $this->from = SmsConfig::TELNYX_FROM;
    }

    public function sendSms(string $to, string $message): array {
        try {
            $response = \Telnyx\Message::create([
                'from' => $this->from,
                'to' => $to,
                'text' => $message
            ]);
            return ['status' => 'success', 'message_id' => $response->id];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
