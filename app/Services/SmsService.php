<?php
namespace App\Services;

use App\Strategies\Sms\TwilioSmsStrategy;
use App\Strategies\Sms\TelnyxSmsStrategy;
use App\Interfaces\SmsProviderInterface;

class SmsService {
    private $provider;

    public function __construct(string $provider) {
        switch (strtolower($provider)) {
            case 'twilio':
                $this->provider = new TwilioSmsStrategy();
                break;
            case 'telnyx':
                $this->provider = new TelnyxSmsStrategy();
                break;
            default:
                throw new \Exception("Invalid provider. Supported: twilio, telnyx.");
        }
    }

    public function sendSms(string $to, string $message): array {
        return $this->provider->sendSms($to, $message);
    }
}
