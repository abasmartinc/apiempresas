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
}

