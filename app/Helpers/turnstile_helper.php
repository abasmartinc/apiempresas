<?php

if (!function_exists('verify_turnstile')) {
    /**
     * Verify Cloudflare Turnstile token
     *
     * @param string $token The cf-turnstile-response token from the form
     * @param string $ip The user's IP address (optional but recommended)
     * @return bool True if verification is successful, False otherwise
     */
    function verify_turnstile(string $token, string $ip = null): bool
    {
        if (empty($token)) {
            return false;
        }

        $secretKey = env('TURNSTILE_SECRET_KEY');
        
        if (empty($secretKey)) {
            log_message('error', 'Turnstile secret key is missing in environment variables.');
            return false;
        }

        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        
        $data = [
            'secret'   => $secretKey,
            'response' => $token
        ];

        if ($ip) {
            $data['remoteip'] = $ip;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        
        if(curl_errno($ch)){
            log_message('error', 'Curl error in Turnstile verification: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);

        $result = json_decode($response, true);
        
        if (isset($result['success']) && $result['success'] === true) {
            return true;
        }

        log_message('warning', 'Turnstile validation failed: ' . json_encode($result));
        return false;
    }
}
