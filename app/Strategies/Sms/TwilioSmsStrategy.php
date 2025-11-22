<?php
namespace App\Strategies\Sms;

use Twilio\Rest\Client;
use Config\SmsConfig;
use App\Interfaces\SmsProviderInterface;

class TwilioSmsStrategy implements SmsProviderInterface {
    private $client;
    private $from;

    public function __construct() {
        $this->client = new Client(SmsConfig::TWILIO_ACCOUNT_SID, SmsConfig::TWILIO_AUTH_TOKEN);
        $this->from = SmsConfig::TWILIO_FROM;
    }

    public function sendSms(string $to, string $message): array {
        try {
            $message = $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $message
            ]);
            return ['status' => 'success', 'message_sid' => $message->sid];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
