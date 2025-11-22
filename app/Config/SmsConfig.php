<?php
namespace Config;

class SmsConfig {
    // Configuración para Twilio
    public const TWILIO_ACCOUNT_SID = 'your_twilio_sid';
    public const TWILIO_AUTH_TOKEN = 'your_twilio_auth_token';
    public const TWILIO_FROM = '+1987654321';

    // Configuración para Telnyx
    public const TELNYX_API_KEY = 'your_telnyx_api_key';
    public const TELNYX_FROM = '+1987654321';
}
