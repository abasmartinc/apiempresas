<?php

namespace App\Validation;

class CustomRules
{
    /**
     * Validar que el campo BCC sea un arreglo de correos válidos.
     */
    public function validateBCC($bcc): bool
    {
        if (!is_array($bcc)) {
            return false; // No es un arreglo
        }

        /*foreach ($bcc as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false; // Algún correo no es válido
            }
        }*/

        return true;
    }

    /**
     * Validar que el correo no sea temporal o desechable.
     */
    public function not_disposable_email(string $str): bool
    {
        if (empty($str)) {
            return true;
        }

        $parts = explode('@', $str);
        if (count($parts) < 2) {
            return false;
        }
        $domain = strtolower(trim(end($parts)));

        $blacklist = [
            'yopmail.com', 'yopmail.fr', 'yopmail.net', 'cool.fr.nf', 'jetable.fr.nf', 'courriel.fr.nf', 'moncourriel.fr.nf', 'monemail.fr.nf', 'monmail.fr.nf',
            'mailinator.com',
            'guerrillamail.com', 'guerrillamailblock.com', 'guerrillamail.net', 'guerrillamail.org', 'guerrillamail.biz', 'grr.la', 'guerrillamail.de',
            'tempmail.com', 'temp-mail.org', 'tempmailo.com', 'tempmail.plus',
            '10minutemail.com', '10minutemail.co.za', '10minutemail.net',
            'throwawaymail.com',
            'getairmail.com',
            'maildrop.cc',
            'dispostable.com',
            'trashmail.com',
            'mailnesia.com',
            'sharklasers.com',
            'fakeinbox.com',
            'boun.cr',
            'generator.email',
            'disposable.com',
            'dayrep.com', 'teleworm.us', 'fleckens.hu', 'rhyta.com', 'einrot.com', 'gustr.com'
        ];

        if (in_array($domain, $blacklist)) {
            return false;
        }

        $pattern = '/(tempmail|temp-mail|disposable|trashmail|throwaway|yopmail|guerrillamail|10minutemail|mailinator)/i';
        if (preg_match($pattern, $domain)) {
            return false;
        }

        return true;
    }
}

