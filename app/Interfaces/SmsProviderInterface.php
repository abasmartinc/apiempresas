<?php
namespace App\Interfaces;

interface SmsProviderInterface {
    public function sendSms(string $to, string $message): array;
}
